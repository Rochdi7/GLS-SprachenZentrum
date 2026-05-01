<?php

namespace App\Mail;

use App\Models\AttestationRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class AttestationRequestAcceptedMail extends Mailable
{
    use Queueable, SerializesModels;

    public AttestationRequest $request;

    public function __construct(AttestationRequest $request)
    {
        $this->request = $request;
    }

    public function build()
    {
        return $this->subject('Votre demande d\'attestation a été acceptée')
            ->markdown('emails.attestation-request-accepted')
            ->with(['request' => $this->request]);
    }
}
