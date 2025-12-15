<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class GlsInscriptionMail extends Mailable
{
    use Queueable, SerializesModels;

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
        return $this->subject('Nouvelle inscription GLS')
                    ->view('emails.gls-inscription')
                    ->with([
                        'data' => $this->data,
                        'centre' => $this->centre,
                        'group' => $this->group,
                    ]);
    }
}
