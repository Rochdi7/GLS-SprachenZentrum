<?php

namespace App\Mail;

use App\Models\AttestationRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class AttestationRequestRefusedMail extends Mailable
{
    use Queueable, SerializesModels;

    public AttestationRequest $request;

    public function __construct(AttestationRequest $request)
    {
        $this->request = $request;
    }

    public function build()
    {
        return $this->subject('Suite à votre demande d\'attestation')
            ->markdown('emails.attestation-request-refused')
            ->with(['request' => $this->request]);
    }
}
