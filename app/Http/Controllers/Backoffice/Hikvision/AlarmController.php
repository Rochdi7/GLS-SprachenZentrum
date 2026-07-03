<?php

namespace App\Http\Controllers\Backoffice\Hikvision;

use App\Http\Controllers\Controller;
use App\Models\Hikvision\HikvisionAlarm;
use Illuminate\Http\Request;

class AlarmController extends Controller
{
    public function index(Request $request)
    {
        $query = HikvisionAlarm::query()->with('device');

        if ($search = trim((string) $request->input('q'))) {
            $query->where(function ($builder) use ($search) {
                $builder->where('alarm_type', 'like', "%{$search}%")
                    ->orWhere('external_id', 'like', "%{$search}%")
                    ->orWhere('device_external_id', 'like', "%{$search}%")
                    ->orWhereHas('device', function ($deviceQuery) use ($search) {
                        $deviceQuery->where('name', 'like', "%{$search}%");
                    });
            });
        }

        if ($severity = $request->input('severity')) {
            $query->where('severity', $severity);
        }

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        $alarms = $query->latest('triggered_at')->paginate(15)->withQueryString();

        $summary = [
            'total' => HikvisionAlarm::count(),
            'open' => HikvisionAlarm::whereNotIn('status', ['resolved', 'closed'])->count(),
            'resolved' => HikvisionAlarm::whereIn('status', ['resolved', 'closed'])->count(),
            'critical' => HikvisionAlarm::whereIn('severity', ['critical', 'high'])->count(),
        ];

        return view('backoffice.hikvision.alarms.index', compact('alarms', 'summary'));
    }
}
