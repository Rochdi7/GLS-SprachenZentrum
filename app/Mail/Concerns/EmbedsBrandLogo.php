<?php

namespace App\Mail\Concerns;

use Symfony\Component\Mime\Email;

trait EmbedsBrandLogo
{
    public string $logoCid = '';

    /**
     * Computes a deterministic CID for the GLS logo, exposes it to the view via
     * $this->logoCid (auto-shared), and returns a Symfony message callback that
     * actually attaches the image inline under the same CID.
     *
     * Usage in a Mailable build():
     *   return $this->subject(...)
     *       ->view('emails.foo')
     *       ->withSymfonyMessage($this->embedBrandLogo());
     */
    protected function embedBrandLogo(): \Closure
    {
        $logoPath = public_path('assets/images/logo/gls.png');
        $cid      = 'gls-logo-' . substr(md5($logoPath), 0, 10);

        $this->logoCid = 'cid:' . $cid;

        return function (Email $email) use ($logoPath, $cid): void {
            if (is_file($logoPath)) {
                $email->embedFromPath($logoPath, $cid, 'image/png');
            }
        };
    }
}
