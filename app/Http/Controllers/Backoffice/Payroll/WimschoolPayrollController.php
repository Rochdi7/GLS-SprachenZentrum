<?php

namespace App\Http\Controllers\Backoffice\Payroll;

use App\Http\Controllers\Backoffice\Crm\BaseCrmController;
use App\Models\CrmClass;
use App\Models\Group;
use App\Models\WimschoolSyncLog;
use App\Services\Payroll\WimschoolAttendanceService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class WimschoolPayrollController extends BaseCrmController
{
    public function __construct(
        protected WimschoolAttendanceService $attendanceService,
        protected \App\Services\Crm\Crm $crm,
        protected \App\Services\Crm\CenterContext $centers,
        protected \App\Services\Crm\CrmLovProvider $lovs,
    ) {
        parent::__construct($crm, $centers, $lovs);
    }

    public function index()
    {
        $groups = Group::with(['teacher', 'latestPresenceImport'])
            ->whereNotNull('crm_class_id')
            ->orderBy('name')
            ->get();

        $allGroups = Group::with('teacher')
            ->orderBy('name')
            ->get();

        $syncLogs = WimschoolSyncLog::with(['group', 'createdBy'])->latest()->limit(50)->get();

        return $this->view('backoffice.payroll.wimschool.index', compact('groups', 'allGroups', 'syncLogs'));
    }

    /**
     * Preview Wimschool API data
     */
    public function preview(Request $request)
    {
        $request->validate([
            'group_id' => 'required|exists:groups,id',
            'date_start' => 'required|date',
            'date_end' => 'required|date|after_or_equal:date_start',
        ]);

        try {
            $group = Group::findOrFail($request->group_id);
            $dateStart = Carbon::parse($request->date_start);
            $dateEnd = Carbon::parse($request->date_end);

            $preview = $this->attendanceService->preview($group, $dateStart, $dateEnd);

            return response()->json([
                'success' => true,
                ...$preview
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function sync(Request $request)
    {
        $request->validate([
            'group_id' => 'required|exists:groups,id',
            'date_start' => 'required|date',
            'date_end' => 'required|date|after_or_equal:date_start',
            'payment_per_student' => 'required|numeric',
            'notes' => 'nullable|string',
        ]);

        $group = Group::findOrFail($request->group_id);
        $dateStart = Carbon::parse($request->date_start);
        $dateEnd = Carbon::parse($request->date_end);
        $paymentPerStudent = (float) $request->payment_per_student;
        $notes = $request->notes;

        try {
            $result = $this->attendanceService->syncAndCalculate(
                group: $group,
                dateStart: $dateStart,
                dateEnd: $dateEnd,
                paymentPerStudent: $paymentPerStudent,
                notes: $notes,
                userId: auth()->id()
            );

            return redirect()->route('backoffice.payroll.presence.import.show', [
                'group' => $group->id,
                'import' => $result['import']->id
            ])->with('success', "Successfully synced attendance and calculated payment ({$result['records_synced']} records)");
        } catch (\Exception $e) {
            return back()->withInput()->with('error', 'Sync failed: ' . $e->getMessage());
        }
    }

    public function getClassesForCenter(Request $request)
    {
        $request->validate([
            'strStoreId' => 'nullable|integer',
        ]);

        try {
            $strStoreId = $request->input('strStoreId');

            // Fetch classes from CRM API
            $apiClasses = $this->scopedCrm()->groups()->classes(
                page: 0,
                size: 100,
                strStoreId: $strStoreId
            );

            // Build a lookup: crm_id → linked local Group (if any)
            $crmIds = collect($apiClasses['data'] ?? [])
                ->map(fn($c) => $c['CLASS_ID'] ?? $c['id'] ?? null)
                ->filter()
                ->values()
                ->all();

            $linkedGroups = Group::whereIn('crm_class_id', $crmIds)
                ->with('teacher')
                ->get()
                ->keyBy('crm_class_id');

            $classes = [];
            foreach ($apiClasses['data'] ?? [] as $apiClass) {
                $crmId = $apiClass['CLASS_ID'] ?? $apiClass['id'] ?? null;
                if (!$crmId) continue;

                $linkedGroup = $linkedGroups->get($crmId);

                $classes[] = [
                    'crm_class_id'   => $crmId,
                    'name'           => $apiClass['NAME'] ?? $apiClass['name'] ?? "Class {$crmId}",
                    'level'          => $apiClass['SCHOOL_LEVEL_NAME'] ?? $apiClass['level'] ?? null,
                    // local group link (null if not yet linked)
                    'group_id'       => $linkedGroup?->id,
                    'group_name'     => $linkedGroup?->name,
                    'teacher_name'   => $linkedGroup?->teacher?->name ?? '—',
                ];
            }

            return response()->json([
                'success' => true,
                'groups'  => $classes,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
