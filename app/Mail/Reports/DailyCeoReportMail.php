<?php

namespace App\Mail\Reports;

use App\Mail\Concerns\EmbedsBrandLogo;
use App\Models\CrmDailyReport;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class DailyCeoReportMail extends Mailable
{
    use Queueable, SerializesModels, EmbedsBrandLogo;

    public function __construct(public CrmDailyReport $report) {}

    public function build(): static
    {
        return $this
            ->subject('Rapport CEO Quotidien — ' . $this->report->report_date->format('d/m/Y'))
            ->view('emails.reports.daily-ceo-report')
            ->withSymfonyMessage($this->embedBrandLogo());
    }
}
