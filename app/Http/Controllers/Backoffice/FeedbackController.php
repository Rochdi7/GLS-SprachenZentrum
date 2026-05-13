<?php

namespace App\Http\Controllers\Backoffice;

use App\Http\Controllers\Controller;
use App\Models\Feedback;
use Illuminate\Http\Request;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class FeedbackController extends Controller
{
    /**
     * TEMP: while feedbacks is in beta, only these emails may access it.
     * Delete this method and its calls when opening the module to all users.
     */
    private const BETA_TESTERS = [
        'ichrak.fakroune@glszentrum.com',
        'rochdi.karouali@glszentrum.com',
    ];

    private function ensureBetaTester(): void
    {
        if (!in_array(optional(auth()->user())->email, self::BETA_TESTERS, true)) {
            abort(403);
        }
    }

    public function index(Request $request)
    {
        $this->ensureBetaTester();

        $filter = $request->query('filter');

        $query = Feedback::query()->with('site')->latest();

        if ($filter === 'unread') {
            $query->where('is_read', false);
        } elseif ($filter === 'read') {
            $query->where('is_read', true);
        }

        $feedbacks = $query->get();

        $counts = [
            'all'    => Feedback::count(),
            'unread' => Feedback::where('is_read', false)->count(),
            'read'   => Feedback::where('is_read', true)->count(),
        ];

        return view('backoffice.feedbacks.index', [
            'feedbacks'    => $feedbacks,
            'currentFilter' => $filter,
            'counts'       => $counts,
        ]);
    }

    public function show(string $id)
    {
        $this->ensureBetaTester();

        $feedback = Feedback::with('site')->findOrFail($id);

        if (!$feedback->is_read) {
            $feedback->update(['is_read' => true]);
        }

        return view('backoffice.feedbacks.show', [
            'feedback' => $feedback,
        ]);
    }

    public function destroy(string $id)
    {
        $this->ensureBetaTester();

        Feedback::findOrFail($id)->delete();

        return redirect()->route('backoffice.feedbacks.index')
            ->with('success', 'Avis supprimé.');
    }

    /**
     * QR code page — generates a scannable QR pointing to the public feedback form.
     */
    public function qr()
    {
        $this->ensureBetaTester();

        $url = route('front.feedback.create');

        $qrSvg = QrCode::format('svg')
            ->size(320)
            ->margin(1)
            ->errorCorrection('H')
            ->generate($url);

        return view('backoffice.feedbacks.qr', [
            'url'   => $url,
            'qrSvg' => $qrSvg,
        ]);
    }
}
