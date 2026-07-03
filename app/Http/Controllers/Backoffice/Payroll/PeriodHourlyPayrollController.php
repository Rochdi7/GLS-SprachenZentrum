<?php

namespace App\Http\Controllers\Backoffice\Payroll;

use App\Http\Controllers\Backoffice\Crm\BaseCrmController;
use App\Http\Requests\Backoffice\Payroll\MarkPaidRequest;
use App\Http\Requests\Backoffice\Payroll\StoreHourlyImportRequest;
use App\Http\Requests\Backoffice\Payroll\StorePeriodImportRequest;
use App\Models\CrmClass;
use App\Models\Group;
use App\Models\PresenceImport;
use App\Models\PresenceImportStudent;
use App\Services\Crm\CenterContext;
use App\Services\Crm\Crm;
use App\Services\Crm\CrmLovProvider;
use App\Models\PayrollStatusLog;
use App\Services\Payroll\CrmPresenceImportService;
use App\Services\Payroll\HourlyPaymentCalculationService;
use App\Services\Payroll\PayrollLifecycleService;
use App\Services\Payroll\PeriodPaymentCalculationService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Handles the NEW period / hourly professor payment modes.
 *
 * Deliberately separate from CrmPayrollController (the legacy weekly path),
 * so the old weekly logic and views keep working exactly as before and the
 * two calculation models never mix.
 */
class PeriodHourlyPayrollController extends BaseCrmController
{
    public function __construct(
        protected Crm $crm,
        protected CenterContext $centers,
        protected CrmLovProvider $lovs,
        protected CrmPresenceImportService $importService,
        protected PeriodPaymentCalculationService $periodCalc,
        protected HourlyPaymentCalculationService $hourlyCalc,
        protected PayrollLifecycleService $lifecycle,
    ) {
        parent::__construct($crm, $centers, $lovs);
    }

    /* ================================================================== */
    /*  CREATE — mode picker                                               */
    /* ================================================================== */

    public function create(Request $request)
    {
        $strStoreId = $this->centers->currentStoreId();

        $classes = CrmClass::query()
            ->when($strStoreId, fn ($q) => $q->where('site_id', $strStoreId))
            ->whereNotNull('class_id')
            ->orderBy('name')
            ->get();

        $crmIds     = $classes->pluck('crm_id')->toArray();
        $stubGroups = Group::whereIn('crm_class_id', $crmIds)
            ->with('latestPresenceImport')
            ->get()
            ->keyBy('crm_class_id');

        $crmClasses = [];
        foreach ($classes as $class) {
            $raw  = $class->raw_data ?? [];
            $stub = $stubGroups->get($class->crm_id);
            $crmClasses[] = [
                'crm_id'    => $class->crm_id,
                'class_id'  => $class->class_id,
                'name'      => $class->name,
                'level'     => $raw['SCHOOL_LEVEL_NAME'] ?? '—',
                'teacher'   => $raw['EMPLOYEE_TEACHER_FULL_NAME'] ?? '—',
                'last_rate' => $stub?->latestPresenceImport?->payment_per_student
                    ?? $stub?->latestPresenceImport?->base_price,
            ];
        }

        $selectedCrmId = $request->get('crm_class_id');
        $mode          = $request->get('mode', PresenceImport::MODE_PERIOD);

        // The frozen-at-creation tier default, shown as a preview in the form
        $tiers          = PeriodPaymentCalculationService::currentTiersSnapshot();
        $weeksPerPeriod = PeriodPaymentCalculationService::currentWeeksPerPeriod();

        return $this->view('backoffice.payroll.crm.imports.create-modes', compact(
            'crmClasses', 'selectedCrmId', 'mode', 'tiers', 'weeksPerPeriod'
        ));
    }

    /* ================================================================== */
    /*  STORE — period mode                                                */
    /* ================================================================== */

    public function storePeriod(StorePeriodImportRequest $request)
    {
        $data = $request->validated();

        $dateStart = Carbon::parse($data['date_start'])->startOfDay();
        $dateEnd   = Carbon::parse($data['date_end'])->startOfDay();

        $group = $this->resolveGroup($request);

        try {
            $scopedCrm = $this->scopedCrm();

            // Reuse the CRM fetch/import plumbing to create the import + students
            // with total_present counts, then convert it to a period import.
            $import = $this->importService->importFromCrm(
                group: $group,
                crm: $scopedCrm,
                dateStart: $dateStart,
                dateEnd: $dateEnd,
                paymentPerStudent: null,
                notes: $data['notes'] ?? null,
                importedBy: auth()->id(),
                monthLabel: $data['month_label'] ?? null,
                crmTeacherName: $request->input('crm_teacher_name')
                    ?: (CrmClass::where('crm_id', $data['crm_class_id'])->first()?->raw_data['EMPLOYEE_TEACHER_FULL_NAME'] ?? null),
            );
        } catch (\RuntimeException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        // Convert the freshly-created import into PERIOD mode with FROZEN tiers.
        $import->update([
            'payment_mode'       => PresenceImport::MODE_PERIOD,
            'status'             => PresenceImport::STATUS_DRAFT,
            'base_price'         => $data['base_price'],
            'period_tiers_json'  => PeriodPaymentCalculationService::currentTiersSnapshot(),
            'attached_month'     => $data['attached_month'],
            'attached_year'      => $data['attached_year'],
            'group_month_number' => $data['group_month_number'],
        ]);

        // Remove the weekly summary the import service created, then compute period.
        $import->paymentSummary?->delete();
        $this->periodCalc->calculate($import->fresh());

        return redirect()
            ->route('backoffice.payroll.crm.legacy.import.show', ['group' => $group->id, 'import' => $import->id])
            ->with('success', "Paiement période créé (Mois {$data['group_month_number']}) — {$import->students()->count()} étudiants.");
    }

    /* ================================================================== */
    /*  STORE — hourly mode                                                */
    /* ================================================================== */

    public function storeHourly(StoreHourlyImportRequest $request)
    {
        $data  = $request->validated();
        $group = $this->resolveGroup($request);

        $nextVersion = ($group->presenceImports()->max('version') ?? 0) + 1;

        $import = DB::transaction(function () use ($group, $data, $nextVersion, $request) {
            $import = PresenceImport::create([
                'group_id'          => $group->id,
                'version'           => $nextVersion,
                'month'             => Carbon::create($data['attached_year'], $data['attached_month'], 1)->startOfMonth(),
                'date_start'        => Carbon::create($data['attached_year'], $data['attached_month'], 1)->startOfMonth(),
                'date_end'          => Carbon::create($data['attached_year'], $data['attached_month'], 1)->endOfMonth(),
                'total_days'        => 0,
                'file_name'         => 'HOURLY',
                'file_path'         => 'hourly',
                'notes'             => $data['notes'] ?? null,
                'imported_by'       => auth()->id(),
                'is_crm_api'        => false,
                'month_label'       => $data['month_label'] ?? null,
                'crm_teacher_name'  => $request->input('crm_teacher_name'),
                'payment_mode'      => PresenceImport::MODE_HOURLY,
                'status'            => PresenceImport::STATUS_DRAFT,
                'attached_month'    => $data['attached_month'],
                'attached_year'     => $data['attached_year'],
                'group_month_number' => $data['group_month_number'] ?? null,
                'hourly_rate'       => $data['hourly_rate'],
                'total_hours'       => $data['total_hours'],
            ]);

            $this->hourlyCalc->calculate($import);

            return $import;
        });

        return redirect()
            ->route('backoffice.payroll.crm.legacy.import.show', ['group' => $group->id, 'import' => $import->id])
            ->with('success', 'Paiement horaire créé — Total : ' . number_format($import->fresh()->final_total, 2) . ' DH.');
    }

    /* ================================================================== */
    /*  PERIOD — override a single student's amount (AJAX)                 */
    /* ================================================================== */

    public function overridePeriodStudent(Request $request, PresenceImportStudent $student): JsonResponse
    {
        $import = $student->presenceImport;

        // GUARD: overrides are only allowed while the import is a DRAFT.
        if (! $import->canOverride()) {
            return response()->json([
                'success' => false,
                'message' => 'Ce paiement est ' . strtolower($import->statusLabel()) . ' — les ajustements ne sont possibles qu’en brouillon.',
            ], 422);
        }

        // GUARD: only valid on period imports.
        if (! $import->isPeriod()) {
            return response()->json([
                'success' => false,
                'message' => 'Cet ajustement ne s’applique qu’aux paiements par période.',
            ], 422);
        }

        $validated = $request->validate([
            'amount' => 'nullable|numeric|min:0|max:9999999',
            'reason' => 'nullable|string|max:500',
        ]);

        $amount = array_key_exists('amount', $validated) && $validated['amount'] !== null
            ? (float) $validated['amount']
            : null;

        $student->update([
            'period_amount_override' => $amount,
            'override_reason'        => $amount !== null ? ($validated['reason'] ?? null) : null,
            'override_by'            => $amount !== null ? auth()->id() : null,
            'override_at'            => $amount !== null ? now() : null,
        ]);

        $summary = $this->periodCalc->calculate($import->fresh());

        $this->lifecycle->logAction(
            $import,
            auth()->user(),
            PayrollStatusLog::ACTION_OVERRIDE,
            $amount !== null ? "Ajustement {$student->student_name} : {$amount} DH" : "Ajustement retiré : {$student->student_name}",
            ['student_id' => $student->id, 'amount' => $amount, 'reason' => $validated['reason'] ?? null],
        );

        return response()->json([
            'success'       => true,
            'student_total' => round($student->fresh()->getPeriodEffectiveAmount(), 2),
            'grand_total'   => round((float) $summary->total_payment, 2),
            'is_override'   => $amount !== null,
        ]);
    }

    /* ================================================================== */
    /*  PERIOD — recalculate with a new base price                        */
    /* ================================================================== */

    public function recalculatePeriod(Request $request, Group $group, PresenceImport $import)
    {
        if (! $import->canRecalculate()) {
            return back()->with('error', 'Paiement ' . strtolower($import->statusLabel()) . ' — le recalcul n’est possible qu’en brouillon.');
        }
        if (! $import->isPeriod()) {
            return back()->with('error', 'Recalcul période invalide pour ce mode.');
        }

        $request->validate([
            'base_price' => 'required|numeric|min:0|max:9999999',
        ], [], ['base_price' => 'prix de base par étudiant']);

        // Tiers stay FROZEN — only the base price changes.
        $import->update(['base_price' => $request->base_price]);
        $this->periodCalc->calculate($import->fresh());

        $this->lifecycle->logAction(
            $import, auth()->user(), PayrollStatusLog::ACTION_RECALCULATE,
            'Prix de base : ' . number_format((float) $request->base_price, 2) . ' DH',
        );

        return back()->with('success', 'Paiement recalculé avec le prix ' . number_format((float) $request->base_price, 2) . ' DH/étudiant.');
    }

    /* ================================================================== */
    /*  PAYMENT INFO (optional — no status lifecycle)                     */
    /* ================================================================== */

    /**
     * Save/clear the optional payment info (date, method, reference, notes).
     * There is no status workflow — imports are always editable by admins.
     */
    public function savePaymentInfo(Request $request, Group $group, PresenceImport $import)
    {
        $data = $request->validate([
            'payment_date'      => ['nullable', 'date'],
            'payment_method'    => ['nullable', \Illuminate\Validation\Rule::in(PresenceImport::PAYMENT_METHODS)],
            'payment_reference' => ['nullable', 'string', 'max:100'],
            'payment_notes'     => ['nullable', 'string', 'max:1000'],
        ], [], [
            'payment_date'   => 'date de paiement',
            'payment_method' => 'mode de paiement',
        ]);

        $hasPayment = ! empty($data['payment_date']);

        $import->update([
            'payment_date'      => $data['payment_date'] ?? null,
            'payment_method'    => $data['payment_method'] ?? null,
            'payment_reference' => $data['payment_reference'] ?? null,
            'payment_notes'     => $data['payment_notes'] ?? null,
            // Record who last set the payment (kept for display)
            'paid_by'           => $hasPayment ? auth()->id() : null,
            'paid_at'           => $hasPayment ? now() : null,
        ]);

        return back()->with('success', $hasPayment
            ? 'Informations de paiement enregistrées.'
            : 'Informations de paiement effacées.');
    }

    /* ================================================================== */
    /*  Helpers                                                            */
    /* ================================================================== */

    /**
     * Find or create the CRM stub group for the selected class.
     */
    protected function resolveGroup(Request $request): Group
    {
        $crmClassId = (int) $request->input('crm_class_id');
        $crmName    = $request->input('crm_class_name', "CRM Class #{$crmClassId}");
        $crmLevelRaw = $request->input('crm_level', 'A1');

        $level = 'A1';
        foreach (['B2', 'B1', 'A2', 'A1'] as $l) {
            if (stripos($crmLevelRaw, $l) !== false) {
                $level = $l;
                break;
            }
        }

        $group = Group::firstOrCreate(
            ['crm_class_id' => $crmClassId],
            [
                'name'       => $crmName,
                'level'      => $level,
                'time_range' => '',
                'status'     => 'active',
                'crm_only'   => true,
            ]
        );

        if ($group->crm_only) {
            $group->update(['name' => $crmName, 'level' => $level]);
        }

        return $group;
    }
}
