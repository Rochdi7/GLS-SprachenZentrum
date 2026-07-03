<?php

namespace App\Http\Controllers\Backoffice\Hikvision;

use App\Http\Controllers\Controller;
use App\Models\Hikvision\HikvisionWebhookEvent;
use Illuminate\Http\Request;

class WebhookController extends Controller
{
    public function index(Request $request)
    {
        $query = HikvisionWebhookEvent::query();

        if ($eventType = trim((string) $request->input('event_type'))) {
            $query->where('event_type', 'like', "%{$eventType}%");
        }

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        $events = $query->latest('received_at')->paginate(20)->withQueryString();

        $summary = [
            'total' => HikvisionWebhookEvent::count(),
            'received' => HikvisionWebhookEvent::where('status', 'received')->count(),
            'processed' => HikvisionWebhookEvent::where('status', 'processed')->count(),
            'failed' => HikvisionWebhookEvent::where('status', 'failed')->count(),
        ];

        return view('backoffice.hikvision.webhooks.index', compact('events', 'summary'));
    }
}
