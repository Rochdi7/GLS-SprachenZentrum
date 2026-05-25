<?php

namespace App\Jobs\Crm;

use App\Services\Crm\Crm;
use App\Services\Crm\CrmException;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class ComputeCrmTotalSumJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @param array<string, mixed> $filters
     */
    public function __construct(
        protected array $filters,
        protected ?string $token,
        protected string $cacheKey,
    ) {
    }

    /**
     * Execute the job.
     */
    public function handle(Crm $crm): void
    {
        $scopedCrm = $crm->withToken($this->token);
        
        $sumRemaining = 0.0;
        $totalRowsSummed = 0;
        $maxPages = 50;
        $pageSize = 25;
        $amountKeys = ['REST_AMOUNT', 'OPEN_AMOUNT', 'REMAINING_AMOUNT', 'AMOUNT_DUE', 'AMOUNT'];

        try {
            for ($p = 0; $p < $maxPages; $p++) {
                $query = array_merge($this->filters, [
                    'page' => $p,
                    'size' => $pageSize,
                    'includeTotal' => 'false',
                ]);

                $response = $scopedCrm->payments()->collection(...$query);
                $rows = $response['data'] ?? [];
                
                if (empty($rows)) {
                    break;
                }

                foreach ($rows as $row) {
                    foreach ($amountKeys as $k) {
                        if (isset($row[$k]) && is_numeric($row[$k])) {
                            $sumRemaining += (float) $row[$k];
                            $totalRowsSummed++;
                            break;
                        }
                    }
                }

                if (count($rows) < $pageSize) {
                    break;
                }
            }

            Cache::put($this->cacheKey, [
                'sum' => $sumRemaining,
                'count' => $totalRowsSummed,
                'calculated_at' => now()->toIso8601String(),
            ], 3600); // Cache for 1 hour

        } catch (CrmException $e) {
            Log::error("Failed to compute CRM total sum in background: " . $e->getMessage(), [
                'cacheKey' => $this->cacheKey,
                'filters' => $this->filters,
            ]);
        }
    }
}
