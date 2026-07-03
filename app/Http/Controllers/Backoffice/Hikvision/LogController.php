<?php

namespace App\Http\Controllers\Backoffice\Hikvision;

use App\Http\Controllers\Controller;
use App\Models\Hikvision\HikvisionSyncLog;
use Illuminate\Http\Request;

class LogController extends Controller
{
    public function index(Request $request)
    {
        $query = HikvisionSyncLog::query();

        if ($channel = trim((string) $request->input('channel'))) {
            $query->where('channel', 'like', "%{$channel}%");
        }

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        $logs = $query->latest('started_at')->paginate(20)->withQueryString();

        $summary = [
            'total' => HikvisionSyncLog::count(),
            'success' => HikvisionSyncLog::where('status', 'success')->count(),
            'failed' => HikvisionSyncLog::where('status', 'failed')->count(),
            'running' => HikvisionSyncLog::where('status', 'running')->count(),
        ];

        return view('backoffice.hikvision.logs.index', compact('logs', 'summary'));
    }
}
