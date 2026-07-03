<?php

namespace App\Http\Controllers\Backoffice\Hikvision;

use App\Http\Controllers\Controller;
use App\Models\Hikvision\HikvisionDevice;
use Illuminate\Http\Request;

class DeviceController extends Controller
{
    public function index(Request $request)
    {
        $query = HikvisionDevice::query();

        if ($search = trim((string) $request->input('q'))) {
            $query->where(function ($builder) use ($search) {
                $builder->where('name', 'like', "%{$search}%")
                    ->orWhere('external_id', 'like', "%{$search}%")
                    ->orWhere('serial_number', 'like', "%{$search}%")
                    ->orWhere('ip_address', 'like', "%{$search}%");
            });
        }

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        $devices = $query->latest('last_seen_at')->paginate(15)->withQueryString();

        $summary = [
            'total' => HikvisionDevice::count(),
            'online' => HikvisionDevice::whereIn('status', ['online', 'active', 'connected'])->count(),
            'offline' => HikvisionDevice::whereIn('status', ['offline', 'disconnected'])->count(),
            'attention' => HikvisionDevice::whereNotNull('status')
                ->whereNotIn('status', ['online', 'active', 'connected', 'offline', 'disconnected'])
                ->count(),
        ];

        return view('backoffice.hikvision.devices.index', compact('devices', 'summary'));
    }

    public function show(HikvisionDevice $device)
    {
        $recentAttendance = $device->attendanceRecords()
            ->with('person')
            ->latest('occurred_at')
            ->limit(15)
            ->get();

        $recentAlarms = $device->alarms()
            ->latest('triggered_at')
            ->limit(10)
            ->get();

        return view('backoffice.hikvision.devices.show', compact('device', 'recentAttendance', 'recentAlarms'));
    }
}
