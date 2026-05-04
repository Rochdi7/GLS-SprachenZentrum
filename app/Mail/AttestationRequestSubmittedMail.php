<?php

namespace App\Mail;

use App\Mail\Concerns\EmbedsBrandLogo;
use App\Models\AttestationRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class AttestationRequestSubmittedMail extends Mailable
{
    use Queueable, SerializesModels, EmbedsBrandLogo;

    public AttestationRequest $request;

    public function __construct(AttestationRequest $request)
    {
        $this->request = $request;
    }

    public function build()
    {
        return $this->subject('Nouvelle demande d\'attestation — ' . $this->request->last_name . ' ' . $this->request->first_name)
            ->view('emails.attestation-request-submitted')
            ->with(['request' => $this->request])
            ->withSymfonyMessage($this->embedBrandLogo());
    }
}
