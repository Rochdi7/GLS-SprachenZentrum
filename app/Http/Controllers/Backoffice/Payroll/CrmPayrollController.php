<?php

namespace App\Http\Controllers\Backoffice\Payroll;

use App\Http\Controllers\Backoffice\Crm\BaseCrmController;
use App\Models\Group;
use App\Models\PresenceImport;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Services\Crm\Crm;
use App\Services\Crm\CenterContext;
use App\Services\Crm\CrmLovProvider;
use App\Services\Payroll\CrmPresenceImportService;
use App\Services\Payroll\ProfPaymentCalculationService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class CrmPayrollController extends BaseCrmController
{
    public function __construct(
        protected Crm $crm,
        protected CenterContext $centers,
        protected CrmLovProvider $lovs,
        protected CrmPresenceImportService $importService,
        protected ProfPaymentCalculationService $calculator,
    ) {
        parent::__construct($crm, $centers, $lovs);
    }

    /**
     * Show the CRM API payroll dashboard.
     */
    public function dashboard()
    {
        $groups = Group::with(['teacher', 'latestPresenceImport.paymentSummary'])
            ->whereNotNull('crm_class_id')
            ->orderBy('name')
            ->get();

        return $this->view('backoffice.payroll.crm.dashboard', compact('groups'));
    }

    /**
     * JSON feed of payroll activity (imports) grouped by day, for the dashboard calendar.
     * One "event" per import, keyed by its creation date.
     */
    public function calendarEvents(Request $request)
    {
        $request->validate(['start' => 'required|date', 'end' => 'required|date']);

        $imports = PresenceImport::with('group')
            ->whereHas('group', fn ($q) => $q->whereNotNull('crm_class_id'))
            ->whereBetween('created_at', [
                Carbon::parse($request->start)->startOfDay(),
                Carbon::parse($request->end)->endOfDay(),
            ])
            ->orderBy('created_at')
            ->get()
            ->map(fn (PresenceImport $import) => [
                'id'           => $import->id,
                'date'         => $import->created_at->format('Y-m-d'),
                'group_id'     => $import->group_id,
                'group_name'   => $import->group?->name ?? '—',
                'teacher_name' => $import->crm_teacher_name ?? $import->group?->teacher?->name ?? '—',
                'status'       => $import->status,
                'status_label' => $import->statusLabel(),
                'amount'       => (float) ($import->paymentSummary?->total_payment ?? $import->final_total ?? 0),
            ]);

        return response()->json($imports);
    }

    /**
     * Detail page for a single day — all payroll imports created that day.
     */
    public function dayHistory(Request $request)
    {
        $request->validate(['date' => 'required|date']);

        $date = Carbon::parse($request->date);

        $imports = PresenceImport::with(['group.teacher', 'paymentSummary'])
            ->whereHas('group', fn ($q) => $q->whereNotNull('crm_class_id'))
            ->whereBetween('created_at', [$date->copy()->startOfDay(), $date->copy()->endOfDay()])
            ->orderBy('created_at')
            ->get();

        return $this->view('backoffice.payroll.crm.day', compact('date', 'imports'));
    }

    /**
     * Show the CRM API import form — loads classes live from the CRM API.
     */
    public function create(Request $request)
    {
        $strStoreId = $this->centers->currentStoreId();
        $crmClasses = [];
        $error      = null;

        // Read from local crm_classes mirror — zero API calls, instant response
        // Data is kept fresh by crm:sync-all running every 2 hours
        $classes = \App\Models\CrmClass::query()
            ->when($strStoreId, fn ($q) => $q->where('site_id', $strStoreId))
            ->whereNotNull('class_id')
            ->orderBy('name')
            ->get();

        if ($classes->isEmpty()) {
            $error = 'Aucune classe trouvée dans le miroir local. Le sync automatique tourne toutes les 2h.';
        }

        $crmIds     = $classes->pluck('crm_id')->toArray();
        $stubGroups = Group::whereIn('crm_class_id', $crmIds)
            ->with('latestPresenceImport')
            ->get()
            ->keyBy('crm_class_id');

        foreach ($classes as $class) {
            $raw   = $class->raw_data ?? [];
            $stub  = $stubGroups->get($class->crm_id);

            $crmClasses[] = [
                'crm_id'    => $class->crm_id,
                'class_id'  => $class->class_id,
                'name'      => $class->name,
                'level'     => $raw['SCHOOL_LEVEL_NAME'] ?? '—',
                'teacher'   => $raw['EMPLOYEE_TEACHER_FULL_NAME'] ?? '—',
                'status'    => $raw['STATUS_NAME'] ?? '—',
                'last_rate' => $stub?->latestPresenceImport?->payment_per_student,
            ];
        }

        $selectedCrmId = $request->get('crm_class_id');

        return $this->view('backoffice.payroll.crm.imports.create', compact('crmClasses', 'selectedCrmId', 'error'));
    }

    /**
     * Process the CRM API import.
     */
    public function store(Request $request)
    {
        $request->validate([
            'crm_class_id'        => 'required|integer',
            'date_start'          => 'required|date',
            'date_end'            => 'required|date|after_or_equal:date_start',
            'month_label'         => 'nullable|string|max:100',
            'payment_per_student' => 'nullable|numeric|min:0',
            'notes'               => 'nullable|string|max:1000',
        ]);

        $dateStart = Carbon::parse($request->date_start)->startOfDay();
        $dateEnd   = Carbon::parse($request->date_end)->startOfDay();

        if ($dateStart->diffInDays($dateEnd) > 62) {
            return back()->withInput()->with('error', 'La période ne peut pas dépasser 62 jours.');
        }

        $scopedCrm = $this->scopedCrm();

        $crmName    = $request->input('crm_class_name', "CRM Class #{$request->crm_class_id}");
        $crmLevelRaw = $request->input('crm_level', 'A1');
        // Map raw CRM level string to A1/A2/B1/B2
        $level = 'A1';
        foreach (['B2', 'B1', 'A2', 'A1'] as $l) {
            if (stripos($crmLevelRaw, $l) !== false) { $level = $l; break; }
        }

        // Find existing CRM stub or create one — never shown in normal CRUD
        $group = Group::firstOrCreate(
            ['crm_class_id' => $request->crm_class_id],
            [
                'name'       => $crmName,
                'level'      => $level,
                'time_range' => '',
                'status'     => 'active',
                'crm_only'   => true,
            ]
        );

        // Always refresh name & level in case the CRM data changed
        if ($group->crm_only) {
            $group->update(['name' => $crmName, 'level' => $level]);
        }

        try {
            $import = $this->importService->importFromCrm(
                group: $group,
                crm: $scopedCrm,
                dateStart: $dateStart,
                dateEnd: $dateEnd,
                paymentPerStudent: $request->payment_per_student,
                notes: $request->notes,
                importedBy: auth()->id(),
                monthLabel: $request->month_label,
                crmTeacherName: $request->input('crm_teacher_name')
                    ?: (\App\Models\CrmClass::where('crm_id', $request->crm_class_id)->first()?->raw_data['EMPLOYEE_TEACHER_FULL_NAME'] ?? null),
            );
        } catch (\RuntimeException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        return redirect()
            ->route('backoffice.payroll.crm.legacy.import.show', ['group' => $group->id, 'import' => $import->id])
            ->with('success', "Import CRM API v{$import->version} créé — {$import->students->count()} étudiants importés.");
    }

    /**
     * Import history for a group (CRM API only).
     */
    public function index(Group $group, Request $request)
    {
        $query = $group->presenceImports()
            ->with(['paymentSummary', 'validatedBy', 'paidBy', 'lockedBy'])
            ->withCount('students')
            ->orderByDesc('version');

        // Optional filters (mode, status, month/year, group month number)
        if ($request->filled('mode')) {
            $query->where('payment_mode', $request->mode);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('attached_year')) {
            $query->where('attached_year', $request->attached_year);
        }
        if ($request->filled('group_month_number')) {
            $query->where('group_month_number', $request->group_month_number);
        }

        $imports = $query->get();

        // Group by group_month_number for the "Mois 1 / Mois 2 / …" history view.
        // Imports without a month number (legacy weekly) fall into a null bucket.
        $byMonthNumber = $imports->groupBy(fn ($i) => $i->group_month_number)
            ->sortKeys();

        return $this->view('backoffice.payroll.crm.imports.index', compact('group', 'imports', 'byMonthNumber'));
    }

    /**
     * Show details of a specific CRM API import.
     *
     * Dispatches by payment_mode:
     *   - weekly (legacy, default) → the original show.blade.php, unchanged
     *   - period                    → period results table
     *   - hourly                    → hourly summary card
     */
    public function show(Group $group, PresenceImport $import)
    {
        $import->load(['students.records', 'paymentSummary', 'importedBy']);

        // Lifecycle panel needs the audit trail + who-did-what relations.
        $lifecycleRelations = ['statusLogs.user', 'validatedBy', 'paidBy', 'lockedBy'];

        if ($import->isPeriod()) {
            $import->load(array_merge(['students.overriddenBy'], $lifecycleRelations));

            return $this->view('backoffice.payroll.crm.imports.show-period', compact('group', 'import'));
        }

        if ($import->isHourly()) {
            $import->load($lifecycleRelations);

            return $this->view('backoffice.payroll.crm.imports.show-hourly', compact('group', 'import'));
        }

        // Legacy weekly path — rendered exactly as before.
        return $this->view('backoffice.payroll.crm.imports.show', compact('group', 'import'));
    }

    /**
     * Export import as a professional PDF to send to the professor.
     */
    public function pdf(Group $group, PresenceImport $import, \App\Services\Payroll\PayrollPdfBuilder $builder)
    {
        return $builder->build($import, $group)->download($builder->filename($import, $group));
    }

    /**
     * Delete a CRM import (super admin only).
     */
    public function destroy(Group $group, PresenceImport $import)
    {
        // GUARD: only draft imports are deletable (Super Admin may also delete
        // validated/paid, but never a locked one).
        if (! $import->canDelete(auth()->user())) {
            return back()->with('error', 'Ce paiement est ' . strtolower($import->statusLabel()) . ' et ne peut pas être supprimé.');
        }

        $import->paymentSummary?->delete();
        $import->students()->each(fn($s) => $s->records()->delete() && $s->delete());
        $import->delete();

        return redirect()
            ->route('backoffice.payroll.crm.legacy.group.imports', $group)
            ->with('success', 'Import v' . $import->version . ' supprimé.');
    }

    /**
     * Update rate and recalculate payment.
     */
    public function recalculate(Request $request, Group $group, PresenceImport $import)
    {
        $request->validate([
            'payment_per_student' => 'required|numeric|min:0',
        ]);

        $import->update(['payment_per_student' => $request->payment_per_student]);

        $import->paymentSummary?->delete();

        $this->calculator->calculate($import);

        return redirect()
            ->route('backoffice.payroll.crm.legacy.import.show', ['group' => $group->id, 'import' => $import->id])
            ->with('success', 'Paiement recalculé avec le taux ' . $request->payment_per_student . ' DH/étudiant.');
    }
}
