<?php

namespace App\Jobs\Crm;

use App\Models\CrmDailyReport;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendDailyReportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(public CrmDailyReport $report)
    {
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // Log the report generation. Wire actual email dispatch here when ready.
        Log::info('Daily CRM report generated', [
            'date'             => $this->report->report_date->toDateString(),
            'revenue'          => $this->report->revenue_yesterday,
            'registrations'    => $this->report->new_registrations,
            'at_risk'          => $this->report->students_at_risk,
            'best_center'      => $this->report->best_center,
            'generated_at'     => $this->report->generated_at?->toDateTimeString(),
        ]);

        // Mark as sent (log-only for now — update to actual timestamp after email is wired)
        $this->report->update(['email_sent_at' => now()]);
    }
}
