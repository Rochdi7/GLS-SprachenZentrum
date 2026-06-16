<?php

namespace App\Http\Controllers\Backoffice\Crm;

use App\Services\Crm\Stats\GroupProfitabilityService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CrmProfitabilityController extends BaseCrmController
{
    /**
     * GET /backoffice/crm/profitability
     */
    public function index(Request $r, GroupProfitabilityService $svc): View
    {
        $storeId     = $this->currentStrStoreId();
        $periodMonth = $r->query('month', now('Africa/Casablanca')->format('Y-m'));
        $sortBy      = $r->query('sort', 'revenue');
        $sortDir     = $r->query('dir', 'desc');

        $report = $svc->forPeriod($periodMonth, $storeId);

        $allowed = ['revenue', 'teacher_salary', 'other_expenses', 'profit', 'margin_pct', 'attendance_rate', 'active_students'];
        if (!in_array($sortBy, $allowed)) {
            $sortBy = 'revenue';
        }

        $report['rows'] = $sortDir === 'asc'
            ? $report['rows']->sortBy($sortBy)
            : $report['rows']->sortByDesc($sortBy);

        return $this->view('backoffice.crm.profitability.index', [
            'report'      => $report,
            'periodMonth' => $periodMonth,
            'sortBy'      => $sortBy,
            'sortDir'     => $sortDir,
        ]);
    }

    /**
     * POST /backoffice/crm/profitability/rebuild
     */
    public function rebuild(Request $r): RedirectResponse
    {
        $storeId     = $this->currentStrStoreId();
        $periodMonth = $r->input('month', now('Africa/Casablanca')->format('Y-m'));

        $args = ['--month' => $periodMonth, '--months' => 1];
        if ($storeId) {
            $args['--store'] = [$storeId];
        }

        Artisan::call('crm:build-group-profitability', $args);

        return redirect()
            ->route('backoffice.crm.profitability.index', ['month' => $periodMonth])
            ->with('success', "Rentabilité recalculée pour {$periodMonth}.");
    }

    /**
     * GET /backoffice/crm/profitability/export
     * CSV export of current period view.
     */
    public function export(Request $r, GroupProfitabilityService $svc): StreamedResponse
    {
        $storeId     = $this->currentStrStoreId();
        $periodMonth = $r->query('month', now('Africa/Casablanca')->format('Y-m'));

        $report = $svc->forPeriod($periodMonth, $storeId);

        $filename = "rentabilite-groupes-{$periodMonth}.csv";

        return response()->streamDownload(function () use ($report) {
            $out = fopen('php://output', 'w');
            // UTF-8 BOM for Excel
            fputs($out, "\xEF\xBB\xBF");

            fputcsv($out, [
                'Groupe', 'Centre', 'Prof', 'Niveau',
                'Étudiants actifs', 'CA (DH)', 'Salaire prof (DH)',
                'Autres charges (DH)', 'Bénéfice (DH)', 'Marge %',
                'Taux présence %', 'Méthode attribution salaire',
            ], ';');

            foreach ($report['rows'] as $row) {
                fputcsv($out, [
                    $row->class_name,
                    $row->site_name,
                    $row->teacher_name,
                    $row->level_name,
                    $row->active_students,
                    number_format($row->revenue, 2, ',', ''),
                    number_format($row->teacher_salary, 2, ',', ''),
                    number_format($row->other_expenses, 2, ',', ''),
                    number_format($row->profit, 2, ',', ''),
                    number_format($row->margin_pct, 2, ',', ''),
                    $row->attendance_rate !== null ? number_format($row->attendance_rate, 1, ',', '') : '',
                    $row->salary_match_method ?? '',
                ], ';');
            }

            fclose($out);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }
}
