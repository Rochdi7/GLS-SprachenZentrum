<?php

namespace App\Http\Controllers\Backoffice;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

// Models GLS
use App\Models\{
    BlogPost,
    BlogCategory,
    Site,
    Teacher,
    Group,
    Certificate,
    Studienkolleg,
    GlsInscription
};

class DashboardController extends Controller
{
    public function index()
    {
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
            'totalInscriptions' => GlsInscription::count(),

            // Studienkollegs
            'totalStudienkollegs'    => Studienkolleg::count(),
            'featuredStudienkollegs' => Studienkolleg::where('featured', true)->count(),
        ];

        /* ===============================
         * MICRO ANALYTICS (PEITY)
         * ===============================
         * Safe static trends (can be dynamic later)
         */
        $analytics = [
            'sitesTrend' => Site::select(
                    DB::raw('COUNT(*) as total')
                )
                ->groupBy(DB::raw('MONTH(created_at)'))
                ->orderBy(DB::raw('MONTH(created_at)'))
                ->pluck('total')
                ->take(7)
                ->pad(7, 0)
                ->toArray(),

            'teachersTrend' => Teacher::select(
                    DB::raw('COUNT(*) as total')
                )
                ->groupBy(DB::raw('MONTH(created_at)'))
                ->orderBy(DB::raw('MONTH(created_at)'))
                ->pluck('total')
                ->take(7)
                ->pad(7, 0)
                ->toArray(),
        ];

        /* ===============================
         * CHARTS (APEX)
         * =============================== */

        // Blog posts per month
        $postsByMonth = BlogPost::select(
                DB::raw('DATE_FORMAT(created_at, "%b") as month'),
                DB::raw('COUNT(*) as total')
            )
            ->groupBy('month')
            ->orderByRaw('MIN(created_at)')
            ->pluck('total', 'month');

        // Certificates per month
        $certificatesByMonth = Certificate::select(
                DB::raw('DATE_FORMAT(created_at, "%b") as month'),
                DB::raw('COUNT(*) as total')
            )
            ->groupBy('month')
            ->orderByRaw('MIN(created_at)')
            ->pluck('total', 'month');

        // Groups by level
        $groupsByLevel = Group::select('level', DB::raw('COUNT(*) as total'))
            ->groupBy('level')
            ->pluck('total', 'level');

        return view('backoffice.dashboard.index', compact(
            'stats',
            'analytics',
            'postsByMonth',
            'certificatesByMonth',
            'groupsByLevel'
        ));
    }
}
