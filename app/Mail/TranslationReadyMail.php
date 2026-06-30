<?php

namespace App\Mail;

use App\Mail\Concerns\EmbedsBrandLogo;
use App\Models\Translation;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class TranslationReadyMail extends Mailable
{
    use Queueable, SerializesModels, EmbedsBrandLogo;

    public Translation $translation;

    public function __construct(Translation $translation)
    {
        $this->translation = $translation;
    }

    public function build()
    {
        return $this->subject('Vos documents traduits sont prêts — GLS Sprachenzentrum')
            ->view('emails.translation-ready')
            ->with(['translation' => $this->translation])
            ->withSymfonyMessage($this->embedBrandLogo());
    }
}
