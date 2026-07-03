<?php

namespace App\Http\Controllers\Backoffice\Hikvision;

use App\Http\Controllers\Controller;
use App\Models\Hikvision\HikvisionAttendance;
use Illuminate\Http\Request;

class AttendanceController extends Controller
{
    public function index(Request $request)
    {
        $query = HikvisionAttendance::query()->with(['person', 'device']);

        if ($search = trim((string) $request->input('q'))) {
            $query->where(function ($builder) use ($search) {
                $builder->where('external_id', 'like', "%{$search}%")
                    ->orWhere('person_external_id', 'like', "%{$search}%")
                    ->orWhere('device_external_id', 'like', "%{$search}%")
                    ->orWhereHas('person', function ($personQuery) use ($search) {
                        $personQuery->where('first_name', 'like', "%{$search}%")
                            ->orWhere('last_name', 'like', "%{$search}%")
                            ->orWhere('employee_no', 'like', "%{$search}%");
                    })
                    ->orWhereHas('device', function ($deviceQuery) use ($search) {
                        $deviceQuery->where('name', 'like', "%{$search}%")
                            ->orWhere('serial_number', 'like', "%{$search}%");
                    });
            });
        }

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        if ($from = $request->input('date_from')) {
            $query->whereDate('occurred_at', '>=', $from);
        }

        if ($to = $request->input('date_to')) {
            $query->whereDate('occurred_at', '<=', $to);
        }

        $attendance = $query->latest('occurred_at')->paginate(20)->withQueryString();

        $summary = [
            'total' => HikvisionAttendance::count(),
            'today' => HikvisionAttendance::whereDate('occurred_at', now()->toDateString())->count(),
            'entries' => HikvisionAttendance::where('direction', 'in')->count(),
            'exits' => HikvisionAttendance::where('direction', 'out')->count(),
        ];

        return view('backoffice.hikvision.attendance.index', compact('attendance', 'summary'));
    }

    public function show(HikvisionAttendance $attendance)
    {
        $attendance->load(['person', 'device']);

        return view('backoffice.hikvision.attendance.show', compact('attendance'));
    }
}
