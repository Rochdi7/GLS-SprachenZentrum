<?php

namespace App\Http\Controllers\Backoffice\Payroll;

use App\Http\Controllers\Controller;
use App\Models\Group;
use App\Models\PresenceImport;
use App\Services\Crm\Crm;
use App\Services\Crm\CenterContext;
use App\Services\Crm\CrmLovProvider;
use App\Services\Payroll\CrmPresenceImportService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class CrmPayrollController extends Controller
{
    public function __construct(
        protected Crm $crm,
        protected CenterContext $centers,
        protected CrmLovProvider $lovs,
        protected CrmPresenceImportService $importService,
    ) {}

    /**
     * Show the CRM API payroll dashboard.
     */
    public function dashboard()
    {
        $groups = Group::with(['teacher', 'latestPresenceImport.paymentSummary'])
            ->whereNotNull('crm_class_id')
            ->orderBy('name')
            ->get();

        return view('backoffice.payroll.crm.dashboard', compact('groups'));
    }

    /**
     * Show the CRM API import form.
     */
    public function create(Request $request)
    {
        $groups = Group::with(['teacher', 'latestPresenceImport'])
            ->whereNotNull('crm_class_id')
            ->orderBy('name')
            ->get();

        $selectedGroupId = $request->get('group_id');

        return view('backoffice.payroll.crm.imports.create', compact('groups', 'selectedGroupId'));
    }

    /**
     * Process the CRM API import.
     */
    public function store(Request $request)
    {
        $request->validate([
            'group_id' => 'required|exists:groups,id',
            'date_start' => 'required|date',
            'date_end' => 'required|date|after_or_equal:date_start',
            'payment_per_student' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string|max:1000',
        ]);

        $group = Group::findOrFail($request->group_id);
        $dateStart = Carbon::parse($request->date_start)->startOfDay();
        $dateEnd = Carbon::parse($request->date_end)->startOfDay();

        if ($dateStart->diffInDays($dateEnd) > 62) {
            return back()->withInput()->with('error', 'La période ne peut pas dépasser 62 jours.');
        }

        $strStoreId = $this->centers->currentStoreId();
        $token = $this->centers->currentToken();
        $scopedCrm = $this->crm->withToken($token);

        try {
            $import = $this->importService->importFromCrm(
                group: $group,
                crm: $scopedCrm,
                dateStart: $dateStart,
                dateEnd: $dateEnd,
                paymentPerStudent: $request->payment_per_student,
                notes: $request->notes,
                importedBy: auth()->id(),
            );
        } catch (\RuntimeException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        return redirect()
            ->route('backoffice.payroll.crm.import.show', ['group' => $group->id, 'import' => $import->id])
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

        return view('backoffice.payroll.crm.imports.index', compact('group', 'imports'));
    }

    /**
     * Show details of a specific CRM API import.
     */
    public function show(Group $group, PresenceImport $import)
    {
        $import->load(['students.records', 'paymentSummary', 'importedBy']);

        return view('backoffice.payroll.crm.imports.show', compact('group', 'import'));
    }
}
