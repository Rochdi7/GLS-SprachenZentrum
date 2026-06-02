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
     * Show the CRM API import form — loads classes live from the CRM API.
     */
    public function create(Request $request)
    {
        $strStoreId = $this->centers->currentStoreId();
        $crmClasses = [];
        $error = null;

        try {
            // API max page size is 25 — paginate through all classes
            $raw = [];
            $page = 0;
            do {
                $response = $this->scopedCrm()->groups()->classes(
                    page: $page,
                    size: 25,
                    strStoreId: $strStoreId,
                );
                $raw = array_merge($raw, $response['data'] ?? []);
                $page++;
            } while ($response['pagination']['hasMore'] ?? false);

            // Look up last-used rates from any existing CRM-stub groups
            $crmIds = array_column($raw, 'ID');
            $stubGroups = Group::whereIn('crm_class_id', $crmIds)
                ->with('latestPresenceImport')
                ->get()
                ->keyBy('crm_class_id');

            foreach ($raw as $item) {
                $crmId = $item['ID'] ?? null;
                if (!$crmId) continue;
                $stub = $stubGroups->get($crmId);
                $crmClasses[] = [
                    'crm_id'       => $crmId,
                    'class_id'     => $item['CLASS_ID'] ?? null,
                    'name'         => $item['NAME'] ?? $item['REFERENCE'] ?? "Class {$crmId}",
                    'level'        => $item['SCHOOL_LEVEL_NAME'] ?? '—',
                    'teacher'      => $item['EMPLOYEE_TEACHER_FULL_NAME'] ?? '—',
                    'status'       => $item['STATUS_NAME'] ?? '—',
                    'last_rate'    => $stub?->latestPresenceImport?->payment_per_student,
                ];
            }
        } catch (\Throwable $e) {
            $error = $e->getMessage();
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
    public function index(Group $group)
    {
        $imports = $group->presenceImports()
            ->where('is_crm_api', true)
            ->with(['paymentSummary', 'students.records'])
            ->withCount('students')
            ->orderByDesc('version')
            ->get();

        return $this->view('backoffice.payroll.crm.imports.index', compact('group', 'imports'));
    }

    /**
     * Show details of a specific CRM API import.
     */
    public function show(Group $group, PresenceImport $import)
    {
        $import->load(['students.records', 'paymentSummary', 'importedBy']);

        return $this->view('backoffice.payroll.crm.imports.show', compact('group', 'import'));
    }

    /**
     * Export import as a professional PDF to send to the professor.
     */
    public function pdf(Group $group, PresenceImport $import)
    {
        $import->load(['students.records', 'paymentSummary', 'importedBy']);

        $allDates      = $import->students
            ->flatMap(fn($s) => $s->records->pluck('date')->map(fn($d) => (string) $d))
            ->unique()->sort()->values();
        $weekThreshold = $import->getThreshold();
        $weeklyUnit    = $import->getWeeklyUnitAmount();
        $dayCount      = $import->date_start->diffInDays($import->date_end) + 1;
        $numWeeks      = min(4, max(1, (int) ceil($dayCount / 7)));
        $profName      = $import->crm_teacher_name ?? $group->teacher?->name ?? '—';
        $logoPath      = public_path('assets/images/logo/gls.png');
        $logoBase64    = base64_encode(file_get_contents($logoPath));

        $colTotals = array_fill(1, $numWeeks, 0);
        $grandTotal = 0;
        foreach ($import->students as $student) {
            for ($w = 1; $w <= $numWeeks; $w++) {
                $override = $student->{"week_{$w}_amount_override"};
                $auto     = (float) $student->{"week_{$w}_amount"};
                $colTotals[$w] += $override !== null ? (float) $override : $auto;
            }
            $grandTotal += (float) $student->weighted_amount;
        }

        $pdf = Pdf::loadView('backoffice.payroll.crm.imports.pdf', compact(
            'group', 'import', 'allDates', 'weekThreshold', 'weeklyUnit',
            'numWeeks', 'profName', 'logoBase64', 'colTotals', 'grandTotal'
        ))
        ->setPaper('a4', 'landscape')
        ->set_option('isHtml5ParserEnabled', true)
        ->set_option('isRemoteEnabled', false)
        ->set_option('defaultFont', 'dejavu sans');

        $filename = 'paiement-' . str($group->name)->slug() . '-v' . $import->version . '.pdf';

        return $pdf->download($filename);
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
