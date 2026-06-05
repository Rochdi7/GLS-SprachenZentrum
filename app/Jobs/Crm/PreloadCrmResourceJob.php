<?php

namespace App\Jobs\Crm;

use App\Services\Crm\Crm;
use App\Services\Crm\CrmException;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class PreloadCrmResourceJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @param array<string, mixed> $query
     */
    public function __construct(
        protected string $path,
        protected array $query,
        protected ?string $token,
        protected int $startPage = 0,
        protected int $pageCount = 3,
    ) {
    }

    /**
     * Execute the job.
     */
    public function handle(Crm $crm): void
    {
        $scopedCrm = $crm->withToken($this->token);
        $client = $scopedCrm->client();

        try {
            for ($p = 0; $p < $this->pageCount; $p++) {
                $page = $this->startPage + $p;
                
                // We just call get() which will automatically store the result in cache
                // because of WimschoolClient's built-in caching logic.
                $client->get($this->path, array_merge($this->query, [
                    'page' => $page,
                ]), fresh: true);
            }
        } catch (CrmException $e) {
            Log::warning("PreloadCrmResourceJob failed for {$this->path}: " . $e->getMessage());
        }
    }
}
