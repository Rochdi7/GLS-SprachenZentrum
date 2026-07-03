<?php

namespace App\Http\Controllers\Backoffice;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

// Models GLS
use App\Models\{
    BlogPost,
    BlogCategory,
    Site,
    Teacher,
    Group,
    GroupLevelFollowup,
    Certificate,
    Studienkolleg,
    GlsInscription,
    Consultation,
    NewsletterSubscriber,
    GroupApplication,
    Quiz,
    QuizQuestion,
    User
};

class DashboardController extends Controller
{
    public function index()
    {
        // Branch:
        //   • Super Admin → global dashboard (this view).
        //   • Everyone else (Admin = centre responsible, Réception, …) → the
        //     centre-scoped dashboard. Admin gets a management variant, other
        //     staff a front-desk variant — both limited to their centre(s).
        $auth = auth()->user();

        // Professors have no backoffice dashboard — send them straight to their
        // own read-only payroll history.
        if ($auth && $auth->isProfessor()) {
            return redirect()->route('backoffice.payroll.professor.index');
        }

        if ($auth && ! $auth->hasRole('Super Admin')) {
            return redirect()->route('backoffice.dashboard.staff');
        }

        $now = Carbon::now();

        /* ===============================
         * GLOBAL STATS
         * =============================== */
        $stats = [
            // Centres
            'totalSites' => Site::count(),

            // Teachers & Groups
            'totalTeachers' => Teacher::count(),
            'totalGroups'   => Group::count(),
            'activeGroups'  => Group::where('status', 'active')->count(),

            // Blog
            'totalPosts'      => BlogPost::count(),
            'publishedPosts'  => BlogPost::where('status', 'published')->count(),
            'totalCategories' => BlogCategory::count(),

            // Academic
            'totalCertificates' => Certificate::count(),

            // Inscriptions
            'totalInscriptions'     => GlsInscription::count(),
            'inscriptionsThisMonth' => GlsInscription::whereBetween('created_at', [
                $now->copy()->startOfMonth(),
                $now->copy()->endOfMonth(),
            ])->count(),

            // Consultations
            'totalConsultations'     => Consultation::count(),
            'consultationsThisMonth' => Consultation::whereBetween('created_at', [
                $now->copy()->startOfMonth(),
                $now->copy()->endOfMonth(),
            ])->count(),

            // Newsletter
            'totalSubscribers'     => NewsletterSubscriber::count(),
            'subscribersThisMonth' => NewsletterSubscriber::whereBetween('created_at', [
                $now->copy()->startOfMonth(),
                $now->copy()->endOfMonth(),
            ])->count(),

            // Group applications
            'totalGroupApps'   => GroupApplication::count(),
            'pendingGroupApps' => GroupApplication::where('status', 'pending')->count(),

            // Studienkollegs
            'totalStudienkollegs'    => Studienkolleg::count(),
            'featuredStudienkollegs' => Studienkolleg::where('featured', true)->count(),

            // Quizzes
            'totalQuizzes'      => Quiz::count(),
            'activeQuizzes'     => Quiz::where('is_active', true)->count(),
            'totalQuestions'    => QuizQuestion::count(),

            // Users
            'totalUsers' => User::count(),
        ];

        /* ===============================
         * MICRO ANALYTICS (PEITY) - last 8 months
         * =============================== */
        $analytics = [
            'sitesTrend'         => $this->trendCountByMonth('sites', 8),
            'teachersTrend'      => $this->trendCountByMonth('teachers', 8),
            'inscriptionsTrend'  => $this->trendCountByMonth('gls_inscriptions', 8),
            'consultationsTrend' => $this->trendCountByMonth('consultations', 8),
            'newsletterTrend'    => $this->trendCountByMonth('newsletter_subscribers', 8),
        ];

        /* ===============================
         * CHARTS (APEX) - last 12 months
         * We return collections like: ["Jan 2026" => 5, ...]
         * =============================== */

        $postsByMonth = $this->chartCountByMonth('blog_posts', 'created_at', 12, function ($q) {
            $q->where('status', 'published');
        });

        $certificatesByMonth = $this->chartCountByMonth('certificates', 'created_at', 12);

        $inscriptionsByMonth = $this->chartCountByMonth('gls_inscriptions', 'created_at', 12);

        $consultationsByMonth = $this->chartCountByMonth('consultations', 'created_at', 12);

        // Group applications by status (donut)
        $groupAppsByStatus = [
            'En attente' => GroupApplication::where('status', 'pending')->count(),
            'Approuvées' => GroupApplication::where('status', 'approved')->count(),
            'Rejetées'   => GroupApplication::where('status', 'rejected')->count(),
        ];

        /* ===============================
         * SUIVI NIVEAU (Rappels)
         * =============================== */
        $levelFollowupsDue = GroupLevelFollowup::query()
            ->with(['group.teacher'])
            ->where('status', 'pending')
            ->whereDate('due_date', '<=', $now->toDateString())
            ->orderBy('due_date')
            ->get()
            // de-duplicate: keep earliest due followup per group
            ->groupBy('group_id')
            ->map(fn ($items) => $items->sortBy('due_date')->first())
            ->values();

        $groupIds = $levelFollowupsDue->pluck('group_id')->unique()->values();

        $levelFollowupsByGroup = $groupIds->isNotEmpty()
            ? GroupLevelFollowup::query()
                ->whereIn('group_id', $groupIds)
                ->get()
                ->groupBy('group_id')
            : collect();

        return view('backoffice.dashboard.index', compact(
            'stats',
            'analytics',
            'postsByMonth',
            'certificatesByMonth',
            'inscriptionsByMonth',
            'consultationsByMonth',
            'groupAppsByStatus',
            'levelFollowupsDue',
            'levelFollowupsByGroup'
        ));
    }

    private function trendCountByMonth(string $table, int $months = 8): array
    {
        $now = Carbon::now();
        $data = [];

        for ($i = $months - 1; $i >= 0; $i--) {
            $start = $now->copy()->subMonths($i)->startOfMonth();
            $end   = $now->copy()->subMonths($i)->endOfMonth();

            $data[] = DB::table($table)
                ->whereBetween('created_at', [$start, $end])
                ->count();
        }

        return $data ?: array_fill(0, $months, 0);
    }

    private function chartCountByMonth(string $table, string $dateColumn, int $months = 12, ?\Closure $filter = null): \Illuminate\Support\Collection
    {
        $now = Carbon::now();
        $result = collect();

        for ($i = $months - 1; $i >= 0; $i--) {
            $start = $now->copy()->subMonths($i)->startOfMonth();
            $end   = $now->copy()->subMonths($i)->endOfMonth();

            $q = DB::table($table)->whereBetween($dateColumn, [$start, $end]);

            if ($filter) {
                $filter($q);
            }

            $label = $start->format('M Y');
            $result->put($label, $q->count());
        }

        return $result;
    }
}
