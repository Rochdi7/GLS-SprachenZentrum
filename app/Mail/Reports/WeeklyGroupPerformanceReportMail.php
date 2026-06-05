<?php

namespace App\Mail\Reports;

use App\Mail\Concerns\EmbedsBrandLogo;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class WeeklyGroupPerformanceReportMail extends Mailable
{
    use Queueable, SerializesModels, EmbedsBrandLogo;

    public function __construct(public array $reportData) {}

    public function build(): static
    {
        return $this
            ->subject('Rapport hebdomadaire — Performance groupes | ' . $this->reportData['period_label'])
            ->view('emails.reports.weekly-group-performance')
            ->withSymfonyMessage($this->embedBrandLogo());
    }
}
