<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

/**
 * Verifies a Google reCAPTCHA v3 token on public form submissions.
 *
 * Invisible: the token is injected into every public form by
 * resources/js/recaptcha.js, so no markup/design changes are needed.
 */
class VerifyRecaptcha
{
    public function handle(Request $request, Closure $next, ?string $action = null): Response
    {
        if (! config('recaptcha.enabled') || ! config('recaptcha.secret_key')) {
            return $next($request);
        }

        $token = $request->input('g-recaptcha-response')
            ?? $request->header('X-Recaptcha-Token');

        if (empty($token)) {
            return $this->reject($request, 'missing-token');
        }

        try {
            $response = Http::asForm()
                ->timeout(config('recaptcha.timeout', 5))
                ->post(config('recaptcha.verify_url'), [
                    'secret'   => config('recaptcha.secret_key'),
                    'response' => $token,
                    'remoteip' => $request->ip(),
                ]);

            $result = $response->json() ?? [];
        } catch (\Throwable $e) {
            Log::warning('reCAPTCHA verification failed to reach Google', [
                'error' => $e->getMessage(),
                'path'  => $request->path(),
            ]);

            // Google unreachable — do not punish real users.
            return config('recaptcha.fail_open', true)
                ? $next($request)
                : $this->reject($request, 'unreachable');
        }

        $success = (bool) ($result['success'] ?? false);
        $score   = (float) ($result['score'] ?? 0);

        // Token expired / duplicate / wrong domain → tell the user to retry.
        if (! $success) {
            Log::info('reCAPTCHA rejected', [
                'path'   => $request->path(),
                'errors' => $result['error-codes'] ?? [],
            ]);

            return $this->reject($request, 'invalid-token');
        }

        if ($score < config('recaptcha.min_score', 0.5)) {
            Log::info('reCAPTCHA low score — likely spam', [
                'path'   => $request->path(),
                'score'  => $score,
                'action' => $result['action'] ?? null,
                'ip'     => $request->ip(),
            ]);

            return $this->reject($request, 'low-score');
        }

        $request->attributes->set('recaptcha_score', $score);

        return $next($request);
    }

    protected function reject(Request $request, string $reason): Response
    {
        $message = __('Votre demande n\'a pas pu être vérifiée. Merci de recharger la page et de réessayer.');

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => false,
                'message' => $message,
                'errors'  => ['recaptcha' => [$message]],
            ], 422);
        }

        return back()
            ->withInput($request->except('g-recaptcha-response'))
            ->withErrors(['recaptcha' => $message]);
    }
}
