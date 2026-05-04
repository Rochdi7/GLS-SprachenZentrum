<?php

namespace App\Mail;

use App\Mail\Concerns\EmbedsBrandLogo;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ContactMessageMail extends Mailable
{
    use Queueable, SerializesModels, EmbedsBrandLogo;

    public $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function build()
    {
        return $this->subject('Nouveau message du formulaire de contact')
                    ->view('emails.contact-message')
                    ->withSymfonyMessage($this->embedBrandLogo());
    }
}
