<?php

namespace Tests\Feature\Crm;

use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

/**
 * Benchmark test for CRM heavy-data endpoints.
 *
 * Run:
 *   php artisan test --filter=CrmEndpointBenchmark --no-coverage
 *
 * Each test hits the route, measures wall-clock time, and dumps the result
 * so you can see: status, response size, and how many milliseconds each
 * endpoint took — both cold (cache busted) and warm (cached).
 *
 * IMPORTANT: Requires a logged-in session with crm.view permission.
 * Set CRM_BENCH_USER_ID in .env (or phpunit.xml) to the admin user id.
 */
class CrmEndpointBenchmarkTest extends TestCase
{
    private int $userId;

    protected function setUp(): void
    {
        parent::setUp();

        $this->userId = (int) (env('CRM_BENCH_USER_ID', 1));

        // Act as the user without loading full auth scaffolding
        $user = \App\Models\User::find($this->userId);
        if ($user) {
            $this->actingAs($user);
        }
    }

    // -------------------------------------------------------------------------
    // Helper
    // -------------------------------------------------------------------------

    private function bench(string $label, string $url, string $method = 'GET', array $data = []): array
    {
        // --- Cold run (bust cache first) ---
        Cache::flush();
        $t0       = hrtime(true);
        $response = $method === 'POST'
            ? $this->post($url, $data)
            : $this->get($url);
        $coldMs   = (hrtime(true) - $t0) / 1_000_000;
        $coldStatus   = $response->status();
        $coldSize     = strlen($response->content());

        // --- Warm run (from cache) ---
        $t1       = hrtime(true);
        $response2 = $method === 'POST'
            ? $this->post($url, $data)
            : $this->get($url);
        $warmMs   = (hrtime(true) - $t1) / 1_000_000;

        $result = [
            'endpoint'    => $label,
            'url'         => $url,
            'cold_ms'     => round($coldMs, 1),
            'warm_ms'     => round($warmMs, 1),
            'status'      => $coldStatus,
            'size_kb'     => round($coldSize / 1024, 1),
            'speedup_x'   => $warmMs > 0 ? round($coldMs / $warmMs, 1) : '∞',
        ];

        // Print immediately so it's visible in test output
        $this->printRow($result);

        return $result;
    }

    private function printRow(array $r): void
    {
        $flag = $r['status'] >= 400 ? ' ❌' : ($r['cold_ms'] > 5000 ? ' 🐢' : ($r['cold_ms'] > 2000 ? ' ⚠️ ' : ' ✅'));
        echo sprintf(
            "  %-45s  HTTP %d  cold=%6.0f ms  warm=%5.0f ms  speedup=%-5s  size=%6.1f KB%s\n",
            $r['endpoint'],
            $r['status'],
            $r['cold_ms'],
            $r['warm_ms'],
            $r['speedup_x'] . 'x',
            $r['size_kb'],
            $flag,
        );
    }

    // -------------------------------------------------------------------------
    // Presence Suivi
    // -------------------------------------------------------------------------

    /** @test */
    public function presence_suivi_index(): void
    {
        echo "\n\n========== CRM ENDPOINT BENCHMARK ==========\n";
        echo sprintf("  %-45s  %s\n", 'ENDPOINT', 'COLD → WARM');
        echo str_repeat('-', 100) . "\n";

        $this->bench(
            'presence-suivi (current month)',
            '/backoffice/crm/presence-suivi',
        );
        $this->assertTrue(true);
    }

    /** @test */
    public function presence_suivi_details_saisie(): void
    {
        $this->bench(
            'presence-suivi/details?status=saisie',
            '/backoffice/crm/presence-suivi/details?status=saisie',
        );
        $this->assertTrue(true);
    }

    /** @test */
    public function presence_suivi_details_draft(): void
    {
        $this->bench(
            'presence-suivi/details?status=draft',
            '/backoffice/crm/presence-suivi/details?status=draft',
        );
        $this->assertTrue(true);
    }

    // -------------------------------------------------------------------------
    // Statistiques (encaissement + recouvrement + inscriptions)
    // -------------------------------------------------------------------------

    /** @test */
    public function statistiques_3_months(): void
    {
        $this->bench(
            'statistiques?months=3',
            '/backoffice/crm/statistiques?months=3',
        );
        $this->assertTrue(true);
    }

    /** @test */
    public function statistiques_6_months(): void
    {
        $this->bench(
            'statistiques?months=6',
            '/backoffice/crm/statistiques?months=6',
        );
        $this->assertTrue(true);
    }

    /** @test */
    public function statistiques_12_months(): void
    {
        $this->bench(
            'statistiques?months=12',
            '/backoffice/crm/statistiques?months=12',
        );
        $this->assertTrue(true);
    }

    // -------------------------------------------------------------------------
    // Collections (recouvrement dashboard)
    // -------------------------------------------------------------------------

    /** @test */
    public function collections_dashboard(): void
    {
        $this->bench(
            'collections (dashboard)',
            '/backoffice/crm/collections',
        );
        $this->assertTrue(true);
    }

    // -------------------------------------------------------------------------
    // Groups / Classes (big JOIN with payment matrix)
    // -------------------------------------------------------------------------

    /** @test */
    public function groups_classes(): void
    {
        $this->bench(
            'groups/classes',
            '/backoffice/crm/groups/classes',
        );
        $this->assertTrue(true);
    }

    /** @test */
    public function groups_level_sessions(): void
    {
        $this->bench(
            'groups/level-sessions',
            '/backoffice/crm/groups/level-sessions',
        );
        $this->assertTrue(true);
    }

    // -------------------------------------------------------------------------
    // Payments
    // -------------------------------------------------------------------------

    /** @test */
    public function payments_list(): void
    {
        $this->bench(
            'payments (list)',
            '/backoffice/crm/payments',
        );
        $this->assertTrue(true);
    }

    /** @test */
    public function payment_checks(): void
    {
        $this->bench(
            'payment-checks',
            '/backoffice/crm/payment-checks',
        );
        $this->assertTrue(true);
    }

    /** @test */
    public function payment_collection(): void
    {
        $this->bench(
            'payment-collection',
            '/backoffice/crm/payment-collection',
        );
        $this->assertTrue(true);
    }

    // -------------------------------------------------------------------------
    // Insights / Analytics (heaviest queries)
    // -------------------------------------------------------------------------

    /** @test */
    public function insights_reconciliation(): void
    {
        $this->bench(
            'insights/reconciliation',
            '/backoffice/crm/insights/reconciliation',
        );
        $this->assertTrue(true);
    }

    /** @test */
    public function insights_retention(): void
    {
        $this->bench(
            'insights/retention',
            '/backoffice/crm/insights/retention',
        );
        $this->assertTrue(true);
    }

    /** @test */
    public function insights_forecast(): void
    {
        $this->bench(
            'insights/forecast',
            '/backoffice/crm/insights/forecast',
        );
        $this->assertTrue(true);
    }

    /** @test */
    public function insights_payment_activity(): void
    {
        $this->bench(
            'insights/payment-activity',
            '/backoffice/crm/insights/payment-activity',
        );
        $this->assertTrue(true);
    }

    /** @test */
    public function insights_advances(): void
    {
        $this->bench(
            'insights/advances',
            '/backoffice/crm/insights/advances',
        );
        $this->assertTrue(true);
    }

    /** @test */
    public function group_evolution(): void
    {
        $this->bench(
            'group-evolution',
            '/backoffice/crm/insights/group-evolution',
        );
        $this->assertTrue(true);
    }

    // -------------------------------------------------------------------------
    // Daily Reports
    // -------------------------------------------------------------------------

    /** @test */
    public function reports_index(): void
    {
        $this->bench(
            'reports (index)',
            '/backoffice/crm/reports',
        );
        $this->assertTrue(true);
    }

    // -------------------------------------------------------------------------
    // Summary table (runs all above, printed last)
    // -------------------------------------------------------------------------

    /** @test */
    public function full_summary(): void
    {
        echo "\n\n============================================================\n";
        echo "  FULL BENCHMARK SUMMARY — all CRM heavy-data endpoints\n";
        echo "============================================================\n";
        echo sprintf("  %-45s  %7s  %7s  %6s  %7s\n", 'ENDPOINT', 'COLD ms', 'WARM ms', 'SPEED', 'SIZE KB');
        echo str_repeat('-', 90) . "\n";

        $endpoints = [
            // [label, url]
            ['presence-suivi (current month)',        '/backoffice/crm/presence-suivi'],
            ['presence-suivi/details saisie',         '/backoffice/crm/presence-suivi/details?status=saisie'],
            ['presence-suivi/details draft',          '/backoffice/crm/presence-suivi/details?status=draft'],
            ['statistiques?months=3',                 '/backoffice/crm/statistiques?months=3'],
            ['statistiques?months=6',                 '/backoffice/crm/statistiques?months=6'],
            ['statistiques?months=12',                '/backoffice/crm/statistiques?months=12'],
            ['collections (dashboard)',               '/backoffice/crm/collections'],
            ['groups/classes',                        '/backoffice/crm/groups/classes'],
            ['groups/level-sessions',                 '/backoffice/crm/groups/level-sessions'],
            ['payments (list)',                       '/backoffice/crm/payments'],
            ['payment-checks',                        '/backoffice/crm/payment-checks'],
            ['payment-collection',                    '/backoffice/crm/payment-collection'],
            ['insights/reconciliation',               '/backoffice/crm/insights/reconciliation'],
            ['insights/retention',                    '/backoffice/crm/insights/retention'],
            ['insights/forecast',                     '/backoffice/crm/insights/forecast'],
            ['insights/payment-activity',             '/backoffice/crm/insights/payment-activity'],
            ['insights/advances',                     '/backoffice/crm/insights/advances'],
            ['group-evolution',                       '/backoffice/crm/insights/group-evolution'],
            ['reports (index)',                       '/backoffice/crm/reports'],
        ];

        $results = [];
        foreach ($endpoints as [$label, $url]) {
            $results[] = $this->bench($label, $url);
        }

        echo str_repeat('=', 90) . "\n";

        // Sort by cold_ms descending to highlight the slowest
        usort($results, fn ($a, $b) => $b['cold_ms'] <=> $a['cold_ms']);

        echo "\n  TOP SLOWEST (cold, no cache):\n";
        foreach (array_slice($results, 0, 5) as $i => $r) {
            $medal = ['🥇', '🥈', '🥉', '4.', '5.'][$i];
            echo sprintf("  %s %-45s  %6.0f ms\n", $medal, $r['endpoint'], $r['cold_ms']);
        }

        $avgCold = array_sum(array_column($results, 'cold_ms')) / count($results);
        $avgWarm = array_sum(array_column($results, 'warm_ms')) / count($results);

        echo sprintf("\n  Average cold: %.0f ms | Average warm: %.0f ms\n", $avgCold, $avgWarm);
        echo "============================================================\n";

        $this->assertTrue(true);
    }
}
