<?php

namespace App\Http\Controllers\Backoffice;

use App\Http\Controllers\Controller;
use App\Models\Feedback;
use Illuminate\Http\Request;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class FeedbackController extends Controller
{
    public function index(Request $request)
    {
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
        Feedback::findOrFail($id)->delete();

        return redirect()->route('backoffice.feedbacks.index')
            ->with('success', 'Avis supprimé.');
    }

    /**
     * QR code page — generates a scannable QR pointing to the public feedback form.
     */
    public function qr()
    {
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
