<?php

namespace App\Mail\Reports;

use App\Mail\Concerns\EmbedsBrandLogo;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class WeeklyProfPaymentReportMail extends Mailable
{
    use Queueable, SerializesModels, EmbedsBrandLogo;

    public function __construct(public array $reportData) {}

    public function build(): static
    {
        return $this
            ->subject('Rapport hebdomadaire — Paiements professeurs | ' . $this->reportData['period_label'])
            ->view('emails.reports.weekly-prof-payment')
            ->withSymfonyMessage($this->embedBrandLogo());
    }
}
