<?php

namespace App\Mail;

use App\Mail\Concerns\EmbedsBrandLogo;
use App\Models\Consultation;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ConsultationConfirmationMail extends Mailable
{
    use Queueable, SerializesModels, EmbedsBrandLogo;

    public Consultation $consultation;

    public function __construct(Consultation $consultation)
    {
        $this->consultation = $consultation;
    }

    public function build()
    {
        return $this->subject('Confirmation de votre demande de consultation')
            ->view('emails.consultation-confirmation')
            ->withSymfonyMessage($this->embedBrandLogo());
    }
}
