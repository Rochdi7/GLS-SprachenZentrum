<?php

namespace App\Http\Controllers\Backoffice;

use App\Http\Controllers\Controller;
use App\Models\Translation;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class TranslationController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->query('status');
        $q      = trim((string) $request->query('q', ''));

        $query = Translation::query()->latest('date_received')->latest('id');

        if (in_array($status, [Translation::STATUS_PENDING, Translation::STATUS_TRANSLATOR, Translation::STATUS_DELIVERED], true)) {
            $query->where('status', $status);
        }

        if ($q !== '') {
            $query->where(function ($w) use ($q) {
                $w->where('cin', 'like', "%{$q}%")
                  ->orWhere('student_name', 'like', "%{$q}%")
                  ->orWhere('doc_type', 'like', "%{$q}%")
                  ->orWhere('phone', 'like', "%{$q}%");
            });
        }

        $translations = $query->get();

        $counts = [
            'all'        => Translation::count(),
            'pending'    => Translation::where('status', Translation::STATUS_PENDING)->count(),
            'translator' => Translation::where('status', Translation::STATUS_TRANSLATOR)->count(),
            'delivered'  => Translation::where('status', Translation::STATUS_DELIVERED)->count(),
        ];

        $grandTotal = (int) $translations->sum('total_cost');

        $defaultPrice = (int) (Translation::latest('id')->value('price_per_page') ?? 200);

        return view('backoffice.translations.index', [
            'translations'  => $translations,
            'currentStatus' => $status,
            'q'             => $q,
            'counts'        => $counts,
            'grandTotal'    => $grandTotal,
            'defaultPrice'  => $defaultPrice,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'cin'              => ['required', 'string', 'max:32'],
            'student_name'     => ['required', 'string', 'max:255'],
            'phone'            => ['nullable', 'string', 'max:32'],
            'doc_type'         => ['nullable', 'string', 'max:255'],
            'page_count'       => ['required', 'integer', 'min:1'],
            'price_per_page'   => ['required', 'integer', 'min:0'],
            'date_received'    => ['nullable', 'date'],
            'notes'            => ['nullable', 'string', 'max:5000'],
        ]);

        $data['cin']          = Translation::normalizeCin($data['cin']);
        $data['doc_type']     = $data['doc_type'] ?: 'Documents divers';
        $data['total_cost']   = (int) $data['page_count'] * (int) $data['price_per_page'];
        $data['date_received'] = $data['date_received'] ?? now()->toDateString();
        $data['status']       = Translation::STATUS_PENDING;

        Translation::create($data);

        return redirect()->route('backoffice.translations.index')
            ->with('success', 'Commande enregistrée.');
    }

    public function update(Request $request, Translation $translation)
    {
        $data = $request->validate([
            'cin'              => ['required', 'string', 'max:32'],
            'student_name'     => ['required', 'string', 'max:255'],
            'phone'            => ['nullable', 'string', 'max:32'],
            'doc_type'         => ['nullable', 'string', 'max:255'],
            'page_count'       => ['required', 'integer', 'min:1'],
            'price_per_page'   => ['required', 'integer', 'min:0'],
            'date_received'    => ['nullable', 'date'],
            'date_handed_over' => ['nullable', 'date'],
            'status'           => ['required', 'in:pending,translator,delivered'],
            'notes'            => ['nullable', 'string', 'max:5000'],
        ]);

        $data['cin']        = Translation::normalizeCin($data['cin']);
        $data['doc_type']   = $data['doc_type'] ?: 'Documents divers';
        $data['total_cost'] = (int) $data['page_count'] * (int) $data['price_per_page'];

        if ($data['status'] === Translation::STATUS_DELIVERED && empty($data['date_handed_over'])) {
            $data['date_handed_over'] = now()->toDateString();
        }

        $translation->update($data);

        return redirect()->route('backoffice.translations.index')
            ->with('success', 'Commande mise à jour.');
    }

    public function updateStatus(Request $request, Translation $translation)
    {
        $next = match ($translation->status) {
            Translation::STATUS_PENDING    => Translation::STATUS_TRANSLATOR,
            Translation::STATUS_TRANSLATOR => Translation::STATUS_DELIVERED,
            default                        => Translation::STATUS_PENDING,
        };

        $payload = ['status' => $next];

        if ($next === Translation::STATUS_DELIVERED && !$translation->date_handed_over) {
            $payload['date_handed_over'] = now()->toDateString();
        }

        $translation->update($payload);

        return back()->with('success', 'Statut mis à jour.');
    }

    public function updateHandover(Request $request, Translation $translation)
    {
        $data = $request->validate([
            'date_handed_over' => ['nullable', 'date'],
        ]);

        $translation->update($data);

        return back();
    }

    public function destroy(Translation $translation)
    {
        $translation->delete();

        return back()->with('success', 'Commande supprimée.');
    }

    public function exportCsv(Request $request): StreamedResponse
    {
        $rows = Translation::query()
            ->latest('date_received')->latest('id')
            ->get();

        $filename = 'GLS_Traductions_' . now()->format('Y-m-d') . '.csv';

        return response()->streamDownload(function () use ($rows) {
            $out = fopen('php://output', 'w');
            // BOM so Excel picks UTF-8
            fwrite($out, chr(0xEF) . chr(0xBB) . chr(0xBF));
            fputcsv($out, ['CIN', 'Étudiant', 'Téléphone', 'Documents', 'Pages', 'Prix/page', 'Total (DH)', 'Date Dépôt', 'Date Remise', 'Statut'], ';');
            foreach ($rows as $r) {
                fputcsv($out, [
                    $r->cin,
                    $r->student_name,
                    $r->phone,
                    $r->doc_type,
                    $r->page_count,
                    $r->price_per_page,
                    $r->total_cost,
                    optional($r->date_received)->format('d/m/Y'),
                    optional($r->date_handed_over)->format('d/m/Y'),
                    Translation::statuses()[$r->status] ?? $r->status,
                ], ';');
            }
            fclose($out);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }
}
