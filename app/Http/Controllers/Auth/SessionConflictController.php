<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class SessionConflictController extends Controller
{
    public function show(Request $request)
    {
        $user = Auth::user();

        return view('auth.session-conflict', [
            'device'    => $user->session_device ?? 'Appareil inconnu',
            'ip'        => $user->session_ip ?? '—',
            'sessionAt' => $user->session_at,
        ]);
    }

    /**
     * Current user keeps their session — kick the other device by issuing a new token.
     */
    public function keep(Request $request)
    {
        $user  = Auth::user();
        $token = Str::random(60);
        $ua    = $request->userAgent() ?? '';

        $user->update([
            'session_token'  => $token,
            'session_ip'     => $request->ip(),
            'session_device' => $this->parseDevice($ua),
            'session_at'     => now(),
        ]);

        $request->session()->put('session_token', $token);

        return redirect()->intended(route('backoffice.dashboard'));
    }

    /**
     * Current user logs themselves out — let the other device stay.
     */
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->flush();
        $request->session()->regenerate(true);

        return redirect()->route('login');
    }

    private function parseDevice(string $ua): string
    {
        $ua = strtolower($ua);

        $os = match (true) {
            str_contains($ua, 'windows')   => 'Windows',
            str_contains($ua, 'macintosh') => 'macOS',
            str_contains($ua, 'iphone')    => 'iPhone',
            str_contains($ua, 'ipad')      => 'iPad',
            str_contains($ua, 'android')   => 'Android',
            str_contains($ua, 'linux')     => 'Linux',
            default                        => 'Appareil inconnu',
        };

        $browser = match (true) {
            str_contains($ua, 'edg')     => 'Edge',
            str_contains($ua, 'opr')     => 'Opera',
            str_contains($ua, 'chrome')  => 'Chrome',
            str_contains($ua, 'firefox') => 'Firefox',
            str_contains($ua, 'safari')  => 'Safari',
            default                      => 'Navigateur inconnu',
        };

        return "$browser / $os";
    }
}
