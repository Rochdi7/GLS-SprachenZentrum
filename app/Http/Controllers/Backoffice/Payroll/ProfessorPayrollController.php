<?php

namespace App\Http\Controllers\Backoffice\Payroll;

use App\Http\Controllers\Controller;
use App\Models\PresenceImport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Professor-facing, READ-ONLY payroll history.
 *
 * A professor sees only imports belonging to their own groups. Scoping is
 * enforced two ways (defence in depth):
 *   1. Every query is filtered by the authenticated user's teacher_id.
 *   2. show() runs PresenceImportPolicy@view, which 403s any import that is
 *      not this teacher's — so a professor cannot open another professor's
 *      payment by guessing the URL.
 *
 * No create / edit / override / status actions exist here at all.
 */
class ProfessorPayrollController extends Controller
{
    /**
     * Resolve the authenticated professor's teacher, or abort.
     */
    protected function teacherOrAbort()
    {
        $teacher = Auth::user()?->teacher;

        abort_if($teacher === null, 403, 'Aucun professeur associé à ce compte.');

        return $teacher;
    }

    /**
     * History dashboard — the professor's own imports, grouped by group and
     * by group month number (Mois 1 / Mois 2 / …).
     */
    public function index(Request $request)
    {
        $teacher = $this->teacherOrAbort();

        $query = $teacher->presenceImports()
            ->with(['paymentSummary', 'group'])
            ->withCount('students')
            ->orderByDesc('attached_year')
            ->orderByDesc('attached_month')
            ->orderByDesc('version');

        // Optional read-only filters
        if ($request->filled('mode')) {
            $query->where('payment_mode', $request->mode);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('attached_year')) {
            $query->where('attached_year', $request->attached_year);
        }

        $imports = $query->get();

        // Nest: group name → group month number → imports
        $byGroup = $imports->groupBy(fn ($i) => $i->group?->name ?? '—')
            ->map(fn ($rows) => $rows->groupBy(fn ($i) => $i->group_month_number)->sortKeys());

        // Headline totals (all-time, this teacher only)
        $grandTotal = $imports->sum(fn ($i) => (float) ($i->paymentSummary?->total_payment ?? $i->final_total ?? 0));
        $paidTotal  = $imports->where('status', PresenceImport::STATUS_PAID)
            ->sum(fn ($i) => (float) ($i->paymentSummary?->total_payment ?? $i->final_total ?? 0));

        return view('backoffice.payroll.professor.index', compact(
            'teacher', 'imports', 'byGroup', 'grandTotal', 'paidTotal'
        ));
    }

    /**
     * Read-only detail of one import. Policy enforces ownership.
     */
    public function show(PresenceImport $import)
    {
        // 403 if this import is not the authenticated professor's.
        $this->authorize('view', $import);

        $teacher = $this->teacherOrAbort();
        $import->load(['students.overriddenBy', 'paymentSummary', 'group', 'validatedBy', 'paidBy', 'lockedBy']);

        return view('backoffice.payroll.professor.show', compact('teacher', 'import'));
    }

    /**
     * Download the payment PDF for one of the professor's own imports.
     * Policy enforces ownership (403 for another professor's import).
     */
    public function pdf(PresenceImport $import, \App\Services\Payroll\PayrollPdfBuilder $builder)
    {
        $this->authorize('view', $import);
        $this->teacherOrAbort();

        $import->load(['students.records', 'paymentSummary', 'group']);

        return $builder->build($import, $import->group)->download($builder->filename($import, $import->group));
    }
}
