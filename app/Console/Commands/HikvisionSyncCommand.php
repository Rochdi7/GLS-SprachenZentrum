<?php

namespace App\Console\Commands;

use App\Models\Hikvision\HikvisionAlarm;
use App\Models\Hikvision\HikvisionAttendance;
use App\Models\Hikvision\HikvisionDevice;
use App\Models\Hikvision\HikvisionPerson;
use App\Models\Hikvision\HikvisionSyncLog;
use App\Services\Hikvision\HikvisionClient;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Throwable;

class HikvisionSyncCommand extends Command
{
    protected $signature = 'hikvision:sync {channel? : device|persons|attendance|alarms (default: all)}';

    protected $description = 'Pull devices, persons, attendance and alarms from the local Hikvision terminal via ISAPI';

    public function handle(HikvisionClient $client): int
    {
        if (! $client->isConfigured()) {
            $this->error('Hikvision is not configured: set HIKVISION_BASE_URL, HIKVISION_USERNAME and HIKVISION_PASSWORD in .env.');

            return self::FAILURE;
        }

        $channel = $this->argument('channel');
        $channels = $channel ? [$channel] : ['device', 'persons', 'attendance', 'alarms'];

        $exitCode = self::SUCCESS;

        foreach ($channels as $name) {
            $method = 'sync' . ucfirst($name);

            if (! method_exists($this, $method)) {
                $this->error("Unknown channel: {$name}");
                $exitCode = self::FAILURE;

                continue;
            }

            if (! $this->runChannel($client, $name, $method)) {
                $exitCode = self::FAILURE;
            }
        }

        return $exitCode;
    }

    private function runChannel(HikvisionClient $client, string $name, string $method): bool
    {
        $log = HikvisionSyncLog::create([
            'channel' => $name,
            'action' => 'sync',
            'status' => 'running',
            'started_at' => now(),
        ]);

        $this->info("Syncing {$name}...");

        try {
            $result = $this->{$method}($client);

            $log->update([
                'status' => 'success',
                'records_total' => $result['total'],
                'records_success' => $result['success'],
                'records_failed' => $result['failed'],
                'completed_at' => now(),
            ]);

            $this->info("  {$name}: {$result['success']}/{$result['total']} synced");

            return $result['failed'] === 0;
        } catch (Throwable $e) {
            $log->update([
                'status' => 'failed',
                'error_message' => $e->getMessage(),
                'completed_at' => now(),
            ]);

            $this->error("  {$name} failed: {$e->getMessage()}");

            return false;
        }
    }

    /**
     * @return array{total: int, success: int, failed: int}
     */
    private function syncDevice(HikvisionClient $client): array
    {
        $response = $client->pendingRequest()
            ->get('/ISAPI/System/deviceInfo', ['format' => 'json']);

        $response->throw();

        $info = $response->json('DeviceInfo') ?? $response->json() ?? [];

        $externalId = (string) ($info['serialNumber'] ?? $client->baseUrl());

        HikvisionDevice::updateOrCreate(
            ['external_id' => $externalId],
            [
                'name' => $info['deviceName'] ?? $externalId,
                'serial_number' => $info['serialNumber'] ?? null,
                'ip_address' => parse_url($client->baseUrl(), PHP_URL_HOST),
                'status' => 'online',
                'firmware_version' => $info['firmwareVersion'] ?? null,
                'last_seen_at' => now(),
                'raw_data' => $info,
                'last_synced_at' => now(),
            ]
        );

        return ['total' => 1, 'success' => 1, 'failed' => 0];
    }

    /**
     * @return array{total: int, success: int, failed: int}
     */
    private function syncPersons(HikvisionClient $client): array
    {
        $total = 0;
        $success = 0;
        $failed = 0;
        $position = 0;
        $pageSize = 30;

        do {
            $response = $client->pendingRequest()
                ->post('/ISAPI/AccessControl/UserInfo/Search?format=json', [
                    'UserInfoSearchCond' => [
                        'searchID' => (string) str()->uuid(),
                        'searchResultPosition' => $position,
                        'maxResults' => $pageSize,
                    ],
                ]);

            $response->throw();

            $result = $response->json('UserInfoSearch') ?? [];
            $records = $result['UserInfo'] ?? [];
            $total += count($records);

            foreach ($records as $record) {
                try {
                    $name = trim((string) ($record['name'] ?? ''));
                    [$firstName, $lastName] = $this->splitName($name);

                    HikvisionPerson::updateOrCreate(
                        ['external_id' => (string) ($record['employeeNo'] ?? $record['id'] ?? '')],
                        [
                            'employee_no' => $record['employeeNo'] ?? null,
                            'first_name' => $firstName ?: ($record['employeeNo'] ?? 'N/A'),
                            'last_name' => $lastName,
                            'status' => ($record['Valid']['enable'] ?? true) ? 'active' : 'inactive',
                            'raw_data' => $record,
                            'last_synced_at' => now(),
                        ]
                    );

                    $success++;
                } catch (Throwable) {
                    $failed++;
                }
            }

            $position += $pageSize;
            $responseStatusStrg = $result['responseStatusStrg'] ?? 'NO MATCH';
        } while ($responseStatusStrg === 'MORE' && count($records) > 0);

        return ['total' => $total, 'success' => $success, 'failed' => $failed];
    }

    /**
     * @return array{total: int, success: int, failed: int}
     */
    private function syncAttendance(HikvisionClient $client): array
    {
        $device = HikvisionDevice::query()->latest('last_synced_at')->first();

        $total = 0;
        $success = 0;
        $failed = 0;
        $position = 0;
        $pageSize = 30;
        $startTime = now()->subDay()->format('Y-m-d\TH:i:s');
        $endTime = now()->format('Y-m-d\TH:i:s');

        do {
            $response = $client->pendingRequest()
                ->post('/ISAPI/AccessControl/AcsEvent?format=json', [
                    'AcsEventCond' => [
                        'searchID' => (string) str()->uuid(),
                        'searchResultPosition' => $position,
                        'maxResults' => $pageSize,
                        'major' => 0,
                        'minor' => 0,
                        'startTime' => $startTime,
                        'endTime' => $endTime,
                    ],
                ]);

            $response->throw();

            $result = $response->json('AcsEvent') ?? [];
            $records = $result['InfoList'] ?? [];
            $total += count($records);

            foreach ($records as $record) {
                try {
                    $occurredAt = isset($record['time'])
                        ? Carbon::parse($record['time'])
                        : now();

                    HikvisionAttendance::updateOrCreate(
                        ['external_id' => $this->attendanceExternalId($record)],
                        [
                            'device_external_id' => (string) ($record['serialNo'] ?? $device?->external_id),
                            'hikvision_device_id' => $device?->id,
                            'person_external_id' => isset($record['employeeNoString'])
                                ? (string) $record['employeeNoString']
                                : null,
                            'hikvision_person_id' => isset($record['employeeNoString'])
                                ? HikvisionPerson::where('external_id', (string) $record['employeeNoString'])->value('id')
                                : null,
                            'direction' => $this->mapDirection($record['attendanceStatus'] ?? null),
                            'verification_mode' => $record['currentVerifyMode'] ?? null,
                            'status' => 'recorded',
                            'occurred_at' => $occurredAt,
                            'raw_data' => $record,
                            'last_synced_at' => now(),
                        ]
                    );

                    $success++;
                } catch (Throwable) {
                    $failed++;
                }
            }

            $position += $pageSize;
            $responseStatusStrg = $result['responseStatusStrg'] ?? 'NO MATCH';
        } while ($responseStatusStrg === 'MORE' && count($records) > 0);

        return ['total' => $total, 'success' => $success, 'failed' => $failed];
    }

    /**
     * @return array{total: int, success: int, failed: int}
     */
    private function syncAlarms(HikvisionClient $client): array
    {
        $device = HikvisionDevice::query()->latest('last_synced_at')->first();

        $total = 0;
        $success = 0;
        $failed = 0;
        $position = 0;
        $pageSize = 30;
        $startTime = now()->subDay()->format('Y-m-d\TH:i:s');
        $endTime = now()->format('Y-m-d\TH:i:s');

        do {
            $response = $client->pendingRequest()
                ->post('/ISAPI/Event/notification/alertStream/search?format=json', [
                    'AcsEventCond' => [
                        'searchID' => (string) str()->uuid(),
                        'searchResultPosition' => $position,
                        'maxResults' => $pageSize,
                        'major' => 5,
                        'minor' => 0,
                        'startTime' => $startTime,
                        'endTime' => $endTime,
                    ],
                ]);

            $response->throw();

            $result = $response->json('AcsEvent') ?? [];
            $records = $result['InfoList'] ?? [];
            $total += count($records);

            foreach ($records as $record) {
                try {
                    $triggeredAt = isset($record['time'])
                        ? Carbon::parse($record['time'])
                        : now();

                    HikvisionAlarm::updateOrCreate(
                        ['external_id' => $this->attendanceExternalId($record)],
                        [
                            'device_external_id' => (string) ($record['serialNo'] ?? $device?->external_id),
                            'hikvision_device_id' => $device?->id,
                            'alarm_type' => (string) ($record['minor'] ?? 'unknown'),
                            'severity' => 'unknown',
                            'status' => 'open',
                            'triggered_at' => $triggeredAt,
                            'raw_data' => $record,
                            'last_synced_at' => now(),
                        ]
                    );

                    $success++;
                } catch (Throwable) {
                    $failed++;
                }
            }

            $position += $pageSize;
            $responseStatusStrg = $result['responseStatusStrg'] ?? 'NO MATCH';
        } while ($responseStatusStrg === 'MORE' && count($records) > 0);

        return ['total' => $total, 'success' => $success, 'failed' => $failed];
    }

    /**
     * @return array{0: string, 1: ?string}
     */
    private function splitName(string $name): array
    {
        if ($name === '') {
            return ['', null];
        }

        $parts = explode(' ', $name, 2);

        return [$parts[0], $parts[1] ?? null];
    }

    private function mapDirection(?string $attendanceStatus): ?string
    {
        return match ($attendanceStatus) {
            'checkIn' => 'in',
            'checkOut' => 'out',
            default => null,
        };
    }

    private function attendanceExternalId(array $record): string
    {
        return (string) ($record['serialNo'] ?? '') . '-' . (string) ($record['time'] ?? uniqid());
    }
}
