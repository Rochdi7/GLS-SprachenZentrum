<?php

namespace App\Http\Controllers\Backoffice;

use App\Http\Controllers\Controller;
use App\Models\GlsInscription;
use App\Models\GlsInscriptionStep1Data;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Partial leads captured at step 1 of the GLS inscription modal.
 *
 * A row here means the visitor filled in their contact details and clicked
 * "Continuer" — it does NOT mean they finished the inscription. Rows whose
 * email also exists in gls_inscriptions are flagged as converted so the team
 * can focus on the ones that dropped off.
 */
class GlsInscriptionStep1Controller extends Controller
{
    public function index(Request $request)
    {
        $leads = $this->filteredLeads($request);

        $convertedCount = $leads->where('is_converted', true)->count();

        $stats = [
            'total' => GlsInscriptionStep1Data::count(),
            'today' => GlsInscriptionStep1Data::whereDate('created_at', now()->toDateString())->count(),
            'this_week' => GlsInscriptionStep1Data::whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->count(),
            'abandoned' => $leads->count() - $convertedCount,
        ];

        $sources = $this->sources();

        return view('backoffice.gls_step1_leads.index', compact('leads', 'stats', 'sources'));
    }

    /**
     * Excel (.xlsx) export of the leads.
     *
     * Honours the same search / source filters as the table, plus a `status`
     * filter — all | converted | abandoned — so the team can pull just the
     * drop-offs for a relance campaign.
     */
    public function export(Request $request): StreamedResponse
    {
        $leads = $this->filteredLeads($request);

        $status = $request->input('status', 'all');
        if ($status === 'converted') {
            $leads = $leads->where('is_converted', true)->values();
        } elseif ($status === 'abandoned') {
            $leads = $leads->where('is_converted', false)->values();
        } else {
            $status = 'all';
        }

        $statusLabel = [
            'converted' => 'inscrits',
            'abandoned' => 'non-finalises',
            'all' => 'tous',
        ][$status];

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Leads Etape 1');

        $headers = ['#ID', 'Prénom', 'Nom', 'Email', 'Téléphone', 'Adresse', 'Source', 'Statut', 'Date'];
        foreach ($headers as $i => $label) {
            $sheet->setCellValue(Coordinate::stringFromColumnIndex($i + 1).'1', $label);
        }

        $lastCol = Coordinate::stringFromColumnIndex(count($headers));
        $headerRange = "A1:{$lastCol}1";
        $sheet->getStyle($headerRange)->getFont()->setBold(true)->getColor()->setRGB('FFFFFF');
        $sheet->getStyle($headerRange)->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setRGB('2C6ECB');
        $sheet->getStyle($headerRange)->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->freezePane('A2');

        $row = 2;
        foreach ($leads as $lead) {
            $sheet->setCellValue("A{$row}", $lead->id);

            // Explicit strings so leading "+" / "0" in phones and any
            // Excel-formula-looking value survive the round-trip untouched.
            foreach (['B' => 'prenom', 'C' => 'nom', 'D' => 'email', 'E' => 'phone', 'F' => 'adresse', 'G' => 'form_source'] as $col => $attr) {
                $sheet->setCellValueExplicit("{$col}{$row}", (string) $lead->{$attr}, DataType::TYPE_STRING);
            }

            $sheet->setCellValue("H{$row}", $lead->is_converted ? 'Inscrit' : 'Non finalisé');
            $sheet->getStyle("H{$row}")->getFill()
                ->setFillType(Fill::FILL_SOLID)
                ->getStartColor()->setRGB($lead->is_converted ? 'B3E6C2' : 'F5C98A');
            $sheet->getStyle("H{$row}")->getFont()->getColor()
                ->setRGB($lead->is_converted ? '14532D' : '7A3E00');

            $sheet->setCellValue("I{$row}", $lead->created_at?->format('Y-m-d H:i') ?? '');
            $row++;
        }

        foreach (range('A', $lastCol) as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $filename = 'leads-inscription-etape1-'.Str::slug($statusLabel).'-'.now()->format('Y-m-d_His').'.xlsx';

        return response()->streamDownload(function () use ($spreadsheet) {
            (new Xlsx($spreadsheet))->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Cache-Control' => 'max-age=0, no-store',
        ]);
    }

    public function destroy(GlsInscriptionStep1Data $lead)
    {
        $lead->delete();

        return redirect()
            ->route('backoffice.gls_step1_leads.index')
            ->with('success', 'Lead supprimé avec succès.');
    }

    /**
     * Search + source filtering, with the `is_converted` flag resolved.
     * Shared by the table and the export so the two can never diverge.
     */
    private function filteredLeads(Request $request): Collection
    {
        $query = GlsInscriptionStep1Data::query();

        if ($search = trim((string) $request->input('q'))) {
            $query->where(function ($q) use ($search) {
                $q->where('email', 'like', '%'.$search.'%')
                    ->orWhere('nom', 'like', '%'.$search.'%')
                    ->orWhere('prenom', 'like', '%'.$search.'%')
                    ->orWhere('phone', 'like', '%'.$search.'%');
            });
        }

        if ($source = $request->input('form_source')) {
            $query->where('form_source', $source);
        }

        $leads = $query->orderByDesc('created_at')->orderByDesc('id')->get();

        // A partial lead is "converted" once the same email completed a full inscription.
        $completedEmails = GlsInscription::query()
            ->whereIn('email', $leads->pluck('email')->filter())
            ->pluck('email')
            ->map(fn ($email) => mb_strtolower($email))
            ->flip();

        $leads->each(function ($lead) use ($completedEmails) {
            $lead->is_converted = $completedEmails->has(mb_strtolower((string) $lead->email));
        });

        return $leads;
    }

    private function sources(): Collection
    {
        return GlsInscriptionStep1Data::query()
            ->whereNotNull('form_source')
            ->distinct()
            ->pluck('form_source');
    }
}
