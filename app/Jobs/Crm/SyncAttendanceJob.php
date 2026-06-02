<?php

namespace App\Jobs\Crm;

use App\Models\CrmAttendance;
use App\Services\Crm\Crm;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SyncAttendanceJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(
        protected ?int $classId = null,
        protected ?string $dateFrom = null,
        protected ?string $dateTo = null,
        protected int $page = 0
    ) {
    }

    /**
     * Execute the job.
     */
    public function handle(Crm $crm)
    {
        Log::info("Syncing attendance for ClassID: {$this->classId}, Date: {$this->dateFrom} to {$this->dateTo}, Page: {$this->page}");

        $response = $crm->attendance()->sessionPresence(
            page: $this->page,
            size: 100,
            dateFrom: $this->dateFrom,
            dateTo: $this->dateTo,
            classId: $this->classId
        );

        if (!$response['success']) {
            Log::error("Failed to sync attendance: " . json_encode($response));
            return;
        }

        foreach ($response['data'] as $item) {
            // CRM IDs are usually consistent across endpoints
            CrmAttendance::updateOrCreate(
                [
                    'crm_class_id' => $item['CLASS_ID'],
                    'crm_student_id' => $item['STUDENT_ID'],
                    'date' => Carbon::parse($item['SESSION_DATE'])->format('Y-m-d'),
                ],
                [
                    'crm_id' => $item['ID'] ?? null, // Some items might not have a global unique ID for the specific record
                    'is_present' => ($item['PRESENCE_STATUS'] ?? 0) == 1,
                    'raw_data' => $item,
                    'last_synced_at' => now(),
                ]
            );
        }

        // Dispatch next page if exists
        if (isset($response['pagination']['hasNext']) && $response['pagination']['hasNext']) {
            self::dispatch($this->classId, $this->dateFrom, $this->dateTo, $this->page + 1)
                ->delay(now()->addSeconds(2)); // Slight delay to respect rate limits
        }
    }
}
