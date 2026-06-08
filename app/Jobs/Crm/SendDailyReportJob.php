<?php

namespace App\Jobs\Crm;

use App\Mail\Reports\DailyCeoReportMail;
use App\Models\CrmDailyReport;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendDailyReportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(public CrmDailyReport $report)
    {
    }

    public function handle(): void
    {
        $recipient = config('reports.ceo_email', 'rochdi.karouali1234@gmail.com');

        Mail::to($recipient)->send(new DailyCeoReportMail($this->report));

        $this->report->update(['email_sent_at' => now()]);

        Log::info('Daily CEO report sent', [
            'date'      => $this->report->report_date->toDateString(),
            'to'        => $recipient,
            'sent_at'   => now()->toDateTimeString(),
        ]);
    }
}
