<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * ➤ Données sensibles à ne JAMAIS flasher en session
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * ➤ Enregistrer les callbacks de reporting
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            // Laravel utilisera son reporting par défaut
        });
    }

    /**
     * ➤ Rendu des exceptions HTTP (404, 500, 503...)
     * Laravel s’en occupe automatiquement ici
     */
    public function render($request, Throwable $exception)
    {
        return parent::render($request, $exception);
    }
}
