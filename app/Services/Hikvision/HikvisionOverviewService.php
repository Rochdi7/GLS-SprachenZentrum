<?php

namespace App\Services\Hikvision;

use App\Models\Hikvision\HikvisionAlarm;
use App\Models\Hikvision\HikvisionAttendance;
use App\Models\Hikvision\HikvisionDevice;
use App\Models\Hikvision\HikvisionPerson;
use App\Models\Hikvision\HikvisionSyncLog;
use App\Models\Hikvision\HikvisionWebhookEvent;

class HikvisionOverviewService
{
    public function __construct(private readonly HikvisionClient $client) {}

    /**
     * @return array<string, mixed>
     */
    public function dashboardPayload(): array
    {
        return [
            'stats' => $this->stats(),
            'apiStatus' => $this->apiStatus(),
            'recentDevices' => HikvisionDevice::query()
                ->latest('last_seen_at')
                ->limit(5)
                ->get(),
            'recentAttendance' => HikvisionAttendance::query()
                ->with(['person', 'device'])
                ->latest('occurred_at')
                ->limit(8)
                ->get(),
            'recentAlarms' => HikvisionAlarm::query()
                ->with('device')
                ->latest('triggered_at')
                ->limit(8)
                ->get(),
            'recentLogs' => HikvisionSyncLog::query()
                ->latest('started_at')
                ->limit(6)
                ->get(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function settingsPayload(): array
    {
        $latestSuccess = HikvisionSyncLog::query()
            ->where('status', 'success')
            ->latest('completed_at')
            ->first();

        return [
            'config' => [
                'configured' => $this->client->isConfigured(),
                'base_url' => $this->client->baseUrl() ?: 'Non configuree',
                'username' => $this->maskValue($this->client->username()),
                'password' => $this->maskValue($this->client->password()),
                'timeout' => $this->client->timeout(),
            ],
            'logSummary' => [
                'total_logs' => HikvisionSyncLog::count(),
                'failed_logs' => HikvisionSyncLog::where('status', 'failed')->count(),
                'pending_webhooks' => HikvisionWebhookEvent::where('status', 'received')->count(),
                'latest_success' => $latestSuccess?->completed_at,
            ],
        ];
    }

    /**
     * @return array<string, int>
     */
    public function stats(): array
    {
        return [
            'devices_total' => HikvisionDevice::count(),
            'devices_online' => HikvisionDevice::whereIn('status', ['online', 'active', 'connected'])->count(),
            'persons_total' => HikvisionPerson::count(),
            'attendance_today' => HikvisionAttendance::whereDate('occurred_at', now()->toDateString())->count(),
            'alarms_open' => HikvisionAlarm::whereNotIn('status', ['resolved', 'closed'])->count(),
            'webhooks_pending' => HikvisionWebhookEvent::where('status', 'received')->count(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function apiStatus(): array
    {
        $lastSync = HikvisionSyncLog::query()->latest('started_at')->first();

        return [
            'configured' => $this->client->isConfigured(),
            'base_url' => $this->client->baseUrl() ?: 'Non configuree',
            'username' => $this->maskValue($this->client->username()),
            'password' => $this->maskValue($this->client->password()),
            'last_sync_status' => $lastSync?->status ?? 'pending',
            'last_sync_at' => $lastSync?->completed_at,
        ];
    }

    private function maskValue(?string $value, int $visible = 4): string
    {
        if (blank($value)) {
            return 'Non configure';
        }

        $value = (string) $value;
        $length = strlen($value);

        if ($length <= $visible * 2) {
            return str_repeat('*', $length);
        }

        return substr($value, 0, $visible)
            . str_repeat('*', max(4, $length - ($visible * 2)))
            . substr($value, -$visible);
    }
}
