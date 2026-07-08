<?php

namespace App\Http\Controllers\Backoffice;

use App\Http\Controllers\Concerns\ScopesToUserSites;
use App\Http\Controllers\Controller;
use App\Models\Consultation;
use App\Models\GlsInscription;
use App\Models\GroupApplication;
use App\Models\Site;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class LeadController extends Controller
{
    use ScopesToUserSites;

    public function index(Request $request)
    {
        $tab = $request->get('tab', 'consultations');
        $centreFilter = $request->get('centre', 'all');

        // Get sites for the filter dropdown (exclude "Online" — handled separately as "En ligne")
        $sites = $this->accessibleSites()
            ->reject(fn ($s) => str_contains(strtolower($s->slug ?? $s->name), 'online'))
            ->values();

        // Build queries with optional centre filter
        $consultationsQuery = Consultation::latest();
        $inscriptionsQuery = GlsInscription::with('site')->latest();
        $applicationsQuery = GroupApplication::with(['group.site'])->latest();

        // Apply centre access scope (non-admins see only their affected centres)
        $allowedSiteIds = $this->accessibleSiteIds();
        if ($allowedSiteIds !== null) {
            // Inscriptions: `centre` = site_id (0 = online). Allow online + accessible.
            $inscriptionsQuery->where(function ($q) use ($allowedSiteIds) {
                $q->whereIn('centre', $allowedSiteIds)->orWhere('centre', 0)->orWhere('type_cours', 'en_ligne');
            });
            $applicationsQuery->whereHas('group', fn ($q) => $q->whereIn('site_id', $allowedSiteIds));

            // Consultations have no site_id — only a free-text `city` field from
            // the frontoffice form. Approximate the scope by matching city
            // against the cities of the sites this user can access.
            $this->scopeConsultationsToCities($consultationsQuery, $allowedSiteIds);
        }

        if ($centreFilter !== 'all') {
            if ($centreFilter === 'online') {
                $inscriptionsQuery->where(function ($q) {
                    $q->where('centre', 0)->orWhere('type_cours', 'en_ligne');
                });
                // Applications & consultations don't have an "online" concept, so return empty
                $applicationsQuery->whereRaw('1 = 0');
                $consultationsQuery->whereRaw('1 = 0');
            } else {
                $resolved = $this->resolveRequestedSiteId((int) $centreFilter);
                if ($resolved === null) {
                    // Requested a centre the user can't access → empty result
                    $inscriptionsQuery->whereRaw('1 = 0');
                    $applicationsQuery->whereRaw('1 = 0');
                    $consultationsQuery->whereRaw('1 = 0');
                } else {
                    $inscriptionsQuery->where('centre', $resolved);
                    $applicationsQuery->whereHas('group', fn ($q) => $q->where('site_id', $resolved));
                    $this->scopeConsultationsToCities($consultationsQuery, [$resolved]);
                }
            }
        }

        $consultations = $consultationsQuery->get();
        $inscriptions = $inscriptionsQuery->get();
        $applications = $applicationsQuery->get();

        // Counts per centre for inscriptions
        $centreCounts = GlsInscription::selectRaw('centre, COUNT(*) as total')
            ->groupBy('centre')
            ->pluck('total', 'centre');

        return view('backoffice.leads.index', compact(
            'tab',
            'consultations',
            'inscriptions',
            'applications',
            'sites',
            'centreFilter',
            'centreCounts',
        ));
    }

    public function stats(Request $request)
    {
        $centreFilter = $request->get('centre', 'all');
        $sites = $this->accessibleSites()
            ->reject(fn ($s) => str_contains(strtolower($s->slug ?? $s->name), 'online'))
            ->values();
        $allowedSiteIds = $this->accessibleSiteIds();

        // Counts per centre
        $centreCounts = GlsInscription::selectRaw('centre, COUNT(*) as total')
            ->groupBy('centre')
            ->pluck('total', 'centre');

        // Monthly stats (last 12 months)
        $monthlyStats = [];
        for ($i = 11; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $label = $date->translatedFormat('M Y');

            $inscQuery = GlsInscription::whereYear('created_at', $date->year)
                ->whereMonth('created_at', $date->month);
            $consQuery = Consultation::whereYear('created_at', $date->year)
                ->whereMonth('created_at', $date->month);
            $appQuery = GroupApplication::whereYear('created_at', $date->year)
                ->whereMonth('created_at', $date->month);

            // First apply centre-access scope for non-admins
            if ($allowedSiteIds !== null) {
                $inscQuery->where(function ($q) use ($allowedSiteIds) {
                    $q->whereIn('centre', $allowedSiteIds)->orWhere('centre', 0)->orWhere('type_cours', 'en_ligne');
                });
                $appQuery->whereHas('group', fn ($q) => $q->whereIn('site_id', $allowedSiteIds));
                $this->scopeConsultationsToCities($consQuery, $allowedSiteIds);
            }

            if ($centreFilter !== 'all') {
                if ($centreFilter === 'online') {
                    $inscQuery->where(function ($q) {
                        $q->where('centre', 0)->orWhere('type_cours', 'en_ligne');
                    });
                    $appQuery->whereRaw('1 = 0');
                    $consQuery->whereRaw('1 = 0');
                } else {
                    $resolved = $this->resolveRequestedSiteId((int) $centreFilter);
                    if ($resolved === null) {
                        $inscQuery->whereRaw('1 = 0');
                        $appQuery->whereRaw('1 = 0');
                        $consQuery->whereRaw('1 = 0');
                    } else {
                        $inscQuery->where('centre', $resolved);
                        $appQuery->whereHas('group', fn ($q) => $q->where('site_id', $resolved));
                        $this->scopeConsultationsToCities($consQuery, [$resolved]);
                    }
                }
            }

            $monthlyStats[] = [
                'label' => $label,
                'inscriptions' => $inscQuery->count(),
                'consultations' => $consQuery->count(),
                'applications' => $appQuery->count(),
            ];
        }

        // Per-centre breakdown (exclude the "Online" site to avoid duplicate with "En ligne")
        $centreStats = [];
        $centreStats[] = [
            'name' => 'En ligne',
            'total' => $centreCounts[0] ?? 0,
        ];
        foreach ($sites as $site) {
            if (str_contains(strtolower($site->slug ?? $site->name), 'online')) {
                continue;
            }
            $centreStats[] = [
                'name' => $site->name,
                'total' => $centreCounts[$site->id] ?? 0,
            ];
        }

        // Totals
        $totalInscriptions = GlsInscription::count();
        $totalConsultations = Consultation::count();
        $totalApplications = GroupApplication::count();

        return view('backoffice.leads.stats', compact(
            'sites',
            'centreFilter',
            'centreCounts',
            'monthlyStats',
            'centreStats',
            'totalInscriptions',
            'totalConsultations',
            'totalApplications',
        ));
    }

    /**
     * Consultations have no site_id — only a free-text `city` field from the
     * frontoffice form. Approximate a centre scope by matching that city
     * against the given sites' `city` column (case-insensitive).
     */
    private function scopeConsultationsToCities($query, array $siteIds): void
    {
        $cities = Site::whereIn('id', $siteIds)->pluck('city')->filter()->all();

        if (empty($cities)) {
            $query->whereRaw('1 = 0');
            return;
        }

        $query->where(function ($q) use ($cities) {
            foreach ($cities as $city) {
                $q->orWhereRaw('LOWER(city) = ?', [mb_strtolower($city)]);
            }
        });
    }

    public function destroyConsultation(Consultation $consultation)
    {
        $consultation->delete();

        return redirect()
            ->route('backoffice.leads.index', ['tab' => 'consultations'])
            ->with('success', 'Consultation supprimée avec succès.');
    }

    public function destroyInscription(GlsInscription $inscription)
    {
        $inscription->delete();

        return redirect()
            ->route('backoffice.leads.index', ['tab' => 'inscriptions'])
            ->with('success', 'Inscription supprimée avec succès.');
    }

    public function destroyApplication(GroupApplication $application)
    {
        $application->delete();

        return redirect()
            ->route('backoffice.leads.index', ['tab' => 'applications'])
            ->with('success', 'Application supprimée avec succès.');
    }
}
