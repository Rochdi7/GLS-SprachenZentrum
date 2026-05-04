<?php

namespace App\Mail;

use App\Mail\Concerns\EmbedsBrandLogo;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class GlsInscriptionConfirmation extends Mailable
{
    use Queueable, SerializesModels, EmbedsBrandLogo;

    public $data;
    public $centre;
    public $group;

    public function __construct($data, $centre = null, $group = null)
    {
        $this->data = $data;
        $this->centre = $centre;
        $this->group = $group;
    }

    public function build()
    {
        return $this->subject('Confirmation de votre inscription – GLS')
                    ->view('emails.gls-confirmation')
                    ->with([
                        'data' => $this->data,
                        'centre' => $this->centre,
                        'group' => $this->group,
                    ])
                    ->withSymfonyMessage($this->embedBrandLogo());
    }
}
