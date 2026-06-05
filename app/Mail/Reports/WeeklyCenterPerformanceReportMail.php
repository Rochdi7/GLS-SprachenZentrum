<?php

namespace App\Mail\Reports;

use App\Mail\Concerns\EmbedsBrandLogo;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class WeeklyCenterPerformanceReportMail extends Mailable
{
    use Queueable, SerializesModels, EmbedsBrandLogo;

    public function __construct(public array $reportData) {}

    public function build(): static
    {
        return $this
            ->subject('Rapport hebdomadaire — Performance centres | ' . $this->reportData['period_label'])
            ->view('emails.reports.weekly-center-performance')
            ->withSymfonyMessage($this->embedBrandLogo());
    }
}
