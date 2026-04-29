<?php

namespace App\Http\Controllers\Backoffice;

use App\Http\Controllers\Controller;
use App\Models\NewsletterSubscriber;
use Illuminate\Http\Request;

class NewsletterSubscriberController extends Controller
{
    public function index(Request $request)
    {
        $query = NewsletterSubscriber::query();

        if ($search = trim((string) $request->input('q'))) {
            $query->where('email', 'like', '%' . $search . '%');
        }

        if ($locale = $request->input('locale')) {
            $query->where('locale', $locale);
        }

        if ($source = $request->input('source')) {
            $query->where('source', $source);
        }

        $subscribers = $query->orderByDesc('subscribed_at')->orderByDesc('id')->get();

        $stats = [
            'total'     => NewsletterSubscriber::count(),
            'today'     => NewsletterSubscriber::whereDate('subscribed_at', now()->toDateString())->count(),
            'this_week' => NewsletterSubscriber::whereBetween('subscribed_at', [now()->startOfWeek(), now()->endOfWeek()])->count(),
        ];

        $locales = NewsletterSubscriber::query()->whereNotNull('locale')->distinct()->pluck('locale');
        $sources = NewsletterSubscriber::query()->whereNotNull('source')->distinct()->pluck('source');

        return view('backoffice.newsletter_subscribers.index', compact('subscribers', 'stats', 'locales', 'sources'));
    }

    public function destroy(NewsletterSubscriber $subscriber)
    {
        $subscriber->delete();

        return redirect()
            ->route('backoffice.newsletter_subscribers.index')
            ->with('success', 'Abonné supprimé avec succès.');
    }
}
