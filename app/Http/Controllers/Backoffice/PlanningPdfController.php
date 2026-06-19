<?php

namespace App\Http\Controllers\Backoffice;

use App\Http\Controllers\Concerns\ScopesToUserSites;
use App\Http\Controllers\Controller;
use App\Models\Site;
use App\Models\User;
use App\Models\UserSchedule;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class PlanningPdfController extends Controller
{
    use ScopesToUserSites;
    /**
     * PDF for a single staff member (bound as User via /pdf/employee/{employee}).
     */
    public function employee(Request $request, User $employee)
    {
        $request->validate([
            'date_from' => 'required|date',
            'date_to' => 'required|date|after_or_equal:date_from',
        ]);

        $schedules = UserSchedule::where('user_id', $employee->id)
            ->whereBetween('date', [$request->date_from, $request->date_to])
            ->orderBy('date')
            ->get();

        $data = [
            'employee' => $employee,
            'site' => $employee->site,
            'schedules' => $schedules,
            'dateFrom' => $request->date_from,
            'dateTo' => $request->date_to,
            'totalWorked' => $schedules->sum('worked_minutes'),
            'totalBreak' => $schedules->sum('break_minutes'),
        ];

        $pdf = Pdf::loadView('backoffice.pdf.planning', $data)->setPaper('A4', 'landscape');
        $filename = 'planning_' . str_replace(' ', '_', $employee->name) . '_' . $request->date_from . '_' . $request->date_to . '.pdf';

        return $pdf->download($filename);
    }

    /**
     * Combined PDF for all staff of a site.
     */
    public function site(Request $request, Site $site)
    {
        $request->validate([
            'date_from' => 'required|date',
            'date_to' => 'required|date|after_or_equal:date_from',
        ]);

        $employees = $site->staff()->where('is_active', true)->orderBy('name')->get();

        $employeePlannings = [];
        foreach ($employees as $employee) {
            $schedules = UserSchedule::where('user_id', $employee->id)
                ->whereBetween('date', [$request->date_from, $request->date_to])
                ->orderBy('date')
                ->get();

            if ($schedules->isNotEmpty()) {
                $employeePlannings[] = [
                    'employee' => $employee,
                    'schedules' => $schedules,
                    'totalWorked' => $schedules->sum('worked_minutes'),
                    'totalBreak' => $schedules->sum('break_minutes'),
                ];
            }
        }

        $data = [
            'site' => $site,
            'employeePlannings' => $employeePlannings,
            'dateFrom' => $request->date_from,
            'dateTo' => $request->date_to,
        ];

        $pdf = Pdf::loadView('backoffice.pdf.planning-site', $data)->setPaper('A4', 'landscape');
        $filename = 'planning_' . str_replace(' ', '_', $site->name) . '_' . $request->date_from . '_' . $request->date_to . '.pdf';

        return $pdf->download($filename);
    }

    public function exportForm()
    {
        $sites = $this->accessibleSites();
        $allowedSiteIds = $this->accessibleSiteIds();
        $employees = User::whereNotNull('staff_role')
            ->where('is_active', true)
            ->when($allowedSiteIds !== null, function ($q) use ($allowedSiteIds) {
                if (empty($allowedSiteIds)) {
                    $q->whereRaw('1 = 0');
                } else {
                    $q->where(function ($q2) use ($allowedSiteIds) {
                        $q2->whereIn('site_id', $allowedSiteIds)
                           ->orWhereHas('sites', fn ($sq) => $sq->whereIn('sites.id', $allowedSiteIds));
                    });
                }
            })
            ->with('site')
            ->orderBy('name')
            ->get();

        return view('backoffice.schedules.export', compact('sites', 'employees'));
    }
}
