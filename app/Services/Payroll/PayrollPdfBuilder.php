<?php

namespace App\Services\Payroll;

use App\Models\Group;
use App\Models\PresenceImport;
use Barryvdh\DomPDF\Facade\Pdf;

/**
 * Builds the professional payment PDF for an import, dispatching by mode
 * (weekly / period / hourly). Shared by the admin and professor controllers
 * so both produce an identical document.
 */
class PayrollPdfBuilder
{
    /**
     * @return \Barryvdh\DomPDF\PDF
     */
    public function build(PresenceImport $import, Group $group)
    {
        $import->loadMissing(['students.records', 'paymentSummary']);

        $profName    = $import->crm_teacher_name ?? $group->teacher?->name ?? '—';
        $logoBase64  = base64_encode(file_get_contents(public_path('assets/images/logo/gls.png')));
        $statusLabel = $import->statusLabel();
        $statusColor = ['draft' => '#6c757d', 'validated' => '#0dcaf0', 'paid' => '#198754', 'locked' => '#212529'][$import->status] ?? '#6c757d';

        // ── PERIOD ─────────────────────────────────────────────────────
        if ($import->isPeriod()) {
            $allDates = $import->students
                ->flatMap(fn ($s) => $s->records->pluck('date')->map(fn ($d) => (string) $d))
                ->unique()->sort()->values();
            $summary    = $import->paymentSummary;
            $tiers      = $import->getFrozenTiers();
            $unit       = $import->getPeriodUnitAmount();
            $grandTotal = $import->students->sum(fn ($s) => $s->getPeriodEffectiveAmount());

            return Pdf::loadView('backoffice.payroll.crm.imports.pdf-period', compact(
                'group', 'import', 'allDates', 'summary', 'tiers', 'unit',
                'grandTotal', 'profName', 'logoBase64', 'statusLabel', 'statusColor'
            ))
            ->setPaper('a4', 'landscape')
            ->set_option('isHtml5ParserEnabled', true)
            ->set_option('isRemoteEnabled', false)
            ->set_option('defaultFont', 'dejavu sans');
        }

        // ── HOURLY ─────────────────────────────────────────────────────
        if ($import->isHourly()) {
            return Pdf::loadView('backoffice.payroll.crm.imports.pdf-hourly', compact(
                'group', 'import', 'profName', 'logoBase64', 'statusLabel', 'statusColor'
            ))
            ->setPaper('a4', 'portrait')
            ->set_option('isHtml5ParserEnabled', true)
            ->set_option('isRemoteEnabled', false)
            ->set_option('defaultFont', 'dejavu sans');
        }

        // ── WEEKLY (legacy) ────────────────────────────────────────────
        $allDates      = $import->students
            ->flatMap(fn ($s) => $s->records->pluck('date')->map(fn ($d) => (string) $d))
            ->unique()->sort()->values();
        $weekThreshold = $import->getThreshold();
        $weeklyUnit    = $import->getWeeklyUnitAmount();
        $dayCount      = $import->date_start->diffInDays($import->date_end) + 1;
        $numWeeks      = min(4, max(1, (int) ceil($dayCount / 7)));

        $colTotals  = array_fill(1, $numWeeks, 0);
        $grandTotal = 0;
        foreach ($import->students as $student) {
            for ($w = 1; $w <= $numWeeks; $w++) {
                $override = $student->{"week_{$w}_amount_override"};
                $auto     = (float) $student->{"week_{$w}_amount"};
                $colTotals[$w] += $override !== null ? (float) $override : $auto;
            }
            $grandTotal += (float) $student->weighted_amount;
        }

        return Pdf::loadView('backoffice.payroll.crm.imports.pdf', compact(
            'group', 'import', 'allDates', 'weekThreshold', 'weeklyUnit',
            'numWeeks', 'profName', 'logoBase64', 'colTotals', 'grandTotal'
        ))
        ->setPaper('a4', 'landscape')
        ->set_option('isHtml5ParserEnabled', true)
        ->set_option('isRemoteEnabled', false)
        ->set_option('defaultFont', 'dejavu sans');
    }

    public function filename(PresenceImport $import, Group $group): string
    {
        return 'paiement-' . str($group->name)->slug() . '-v' . $import->version . '.pdf';
    }
}
