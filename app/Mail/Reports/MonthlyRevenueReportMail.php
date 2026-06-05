<?php

namespace App\Mail\Reports;

use App\Mail\Concerns\EmbedsBrandLogo;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class MonthlyRevenueReportMail extends Mailable
{
    use Queueable, SerializesModels, EmbedsBrandLogo;

    public function __construct(public array $reportData) {}

    public function build(): static
    {
        return $this
            ->subject('Rapport mensuel — Revenus | ' . $this->reportData['month_label'])
            ->view('emails.reports.monthly-revenue')
            ->withSymfonyMessage($this->embedBrandLogo());
    }
}
