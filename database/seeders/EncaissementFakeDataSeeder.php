<?php

namespace Database\Seeders;

use App\Models\Site;
use App\Models\User;
use App\Models\Encaissement;
use App\Models\SiteExpense;
use App\Models\Impaye;
use App\Models\ImpayeImport;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Seeds realistic fake encaissement/impayes/expenses data from Jan 2023 to now,
 * for all active sites, with varying performance per center.
 */
class EncaissementFakeDataSeeder extends Seeder
{
    private array $firstNames = [
        'Aya', 'Mohamed', 'Fatima', 'Ahmed', 'Salma', 'Youssef', 'Sara', 'Hamza',
        'Nour', 'Ali', 'Zineb', 'Omar', 'Meryem', 'Khalid', 'Leila', 'Karim',
        'Hiba', 'Rachid', 'Amal', 'Mehdi', 'Nadia', 'Othmane', 'Imane', 'Yassine',
        'Chaimae', 'Anas', 'Laila', 'Adil', 'Sanae', 'Tarik', 'Hanae', 'Marouane',
        'Kaoutar', 'Issam', 'Dounia', 'Hassan', 'Samira', 'Abdelilah', 'Hajar', 'Soufiane',
    ];

    private array $lastNames = [
        'Alaoui', 'Bennani', 'Chraibi', 'Tazi', 'Fassi', 'Mansouri', 'El Idrissi',
        'Benjelloun', 'Cherkaoui', 'Sebti', 'Berrada', 'Naciri', 'Lahlou', 'Kettani',
        'Benkiran', 'El Hassani', 'Bouchareb', 'Amrani', 'Saidi', 'El Amrani',
        'Kabbaj', 'Filali', 'Ziani', 'Zniber', 'Bensalem', 'El Mansouri', 'Khalil',
        'Hamdouchi', 'Benabdallah', 'El Ghazi', 'Rami', 'Daoudi', 'Sefrioui', 'Moussaoui',
    ];

    private array $paymentMethods = ['especes', 'especes', 'especes', 'especes', 'tpe', 'tpe', 'virement', 'cheque'];

    private array $feeTypes = [
        ['type' => 'mensualite', 'desc' => 'Frais de %s', 'min' => 300, 'max' => 1600],
        ['type' => 'inscription_a1', 'desc' => "Frais d'inscription A1/A2/B1", 'min' => 300, 'max' => 300],
        ['type' => 'inscription_b2', 'desc' => "Frais d'inscription B2", 'min' => 200, 'max' => 200],
    ];

    private array $monthNamesFr = [
        1 => 'Janvier', 2 => 'Février', 3 => 'Mars', 4 => 'Avril', 5 => 'Mai', 6 => 'Juin',
        7 => 'Juillet', 8 => 'Août', 9 => 'Septembre', 10 => 'Octobre', 11 => 'Novembre', 12 => 'Décembre',
    ];

    // Performance profile per site: higher = more revenue, lower impayé rate
    private array $sitePerformance = [
        1 => ['volume_factor' => 1.0,  'impaye_rate' => 0.10, 'name_short' => 'Marrakech'],   // Strong
        2 => ['volume_factor' => 0.85, 'impaye_rate' => 0.15, 'name_short' => 'Rabat'],        // Good
        4 => ['volume_factor' => 0.50, 'impaye_rate' => 0.25, 'name_short' => 'Kenitra'],      // Average
        5 => ['volume_factor' => 0.55, 'impaye_rate' => 0.18, 'name_short' => 'Agadir'],       // Average
        6 => ['volume_factor' => 0.90, 'impaye_rate' => 0.40, 'name_short' => 'Casablanca'],   // High volume, high impayé
        7 => ['volume_factor' => 0.60, 'impaye_rate' => 0.22, 'name_short' => 'Sale'],         // Average
    ];

    public function run(): void
    {
        $this->command->info('🌱 Seeding fake encaissement data from 2023 to now...');

        // Ensure employees exist for each site
        $this->seedEmployees();

        $sites = Site::where('is_active', true)->whereIn('id', array_keys($this->sitePerformance))->get();
        $startDate = Carbon::create(2023, 1, 1);
        $endDate = now();

        foreach ($sites as $site) {
            $this->command->info("  → Seeding site: {$site->name}");
            $this->seedSiteData($site, $startDate, $endDate);
        }

        $this->command->info('✓ Fake data seeded successfully.');
        $this->command->info('Encaissements: ' . Encaissement::count());
        $this->command->info('Expenses: ' . SiteExpense::count());
        $this->command->info('Impayés: ' . Impaye::count());
    }

    private function seedEmployees(): void
    {
        $roles = ['Conseiller Administration', 'Commercial', 'Coordination'];
        foreach ($this->sitePerformance as $siteId => $perf) {
            $count = User::where('site_id', $siteId)->whereNotNull('staff_role')->count();
            if ($count >= 3) continue;

            foreach ($roles as $role) {
                $exists = User::where('site_id', $siteId)->where('staff_role', $role)->exists();
                if (! $exists) {
                    User::create([
                        'site_id'           => $siteId,
                        'name'              => $this->randomName(),
                        'staff_role'        => $role,
                        'phone'             => '06' . rand(10000000, 99999999),
                        'email'             => strtolower($perf['name_short']) . '.' . strtolower(\Illuminate\Support\Str::slug($role)) . '.' . \Illuminate\Support\Str::random(4) . '@gls.ma',
                        'password'          => \Illuminate\Support\Str::random(32),
                        'is_active'         => true,
                        'hired_at'          => Carbon::create(2022, rand(1, 12), rand(1, 28)),
                        'email_verified_at' => now(),
                    ]);
                }
            }
        }
    }

    private function seedSiteData(Site $site, Carbon $startDate, Carbon $endDate): void
    {
        $perf = $this->sitePerformance[$site->id];
        $volumeFactor = $perf['volume_factor'];
        $impayeRate = $perf['impaye_rate'];

        // Get operators for this site
        $operators = User::where('site_id', $site->id)
            ->where('is_active', true)
            ->whereIn('staff_role', ['Conseiller Administration', 'Commercial'])
            ->pluck('name')
            ->toArray();
        if (empty($operators)) $operators = ['Latifa Abouelfath', 'Mustapha Benmoussa'];

        // Generate encaissements per month
        $current = $startDate->copy();
        while ($current <= $endDate) {
            $monthSource = $current->year <= 2024 ? 'old_crm' : 'new_crm';
            $schoolYear = $this->schoolYearFor($current);

            // How many encaissements per month (proportional to volume_factor)
            // Strong site = 80-150/month, weak = 30-60/month
            $baseCount = (int) round(80 * $volumeFactor);
            $variation = (int) round($baseCount * 0.3);
            $count = $baseCount + rand(-$variation, $variation);
            if ($count < 10) $count = 10;

            $batch = [];
            for ($i = 0; $i < $count; $i++) {
                $row = $this->generateEncaissement($site->id, $current, $monthSource, $schoolYear, $operators, $i + 1);
                $batch[] = $row;

                // Batch insert every 500
                if (count($batch) >= 500) {
                    Encaissement::insert($batch);
                    $batch = [];
                }
            }
            if (!empty($batch)) Encaissement::insert($batch);

            // Generate charges (site_expenses) for this month
            $this->seedExpensesForMonth($site->id, $current, $volumeFactor);

            $current->addMonth();
        }

        // Generate impayés snapshot (latest state)
        $this->seedImpayes($site->id, $endDate, $impayeRate, $volumeFactor);
    }

    private function generateEncaissement(int $siteId, Carbon $month, string $source, string $schoolYear, array $operators, int $orderNum): array
    {
        // Spread across the month
        $day = rand(1, min($month->daysInMonth, now()->month === $month->month && now()->year === $month->year ? now()->day : $month->daysInMonth));
        $collectedAt = $month->copy()->day($day);

        $studentName = $this->randomName();
        $feeType = $this->randomFeeType($month);
        $monthNameFr = $this->monthNamesFr[$month->month];

        if ($feeType['type'] === 'mensualite') {
            $feeDescription = sprintf($feeType['desc'], $monthNameFr);
            $amount = rand(3, 13) * 100; // 300-1300
            if (rand(1, 100) < 20) $amount += 300; // occasional higher amounts
        } else {
            $feeDescription = $feeType['desc'];
            $amount = $feeType['min'];
        }

        $paymentMethod = $this->paymentMethods[array_rand($this->paymentMethods)];

        return [
            'site_id' => $siteId,
            'reference' => $source === 'new_crm' ? 'P' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT) : str_pad(rand(100, 9999), 4, '0', STR_PAD_LEFT),
            'source_system' => $source,
            'student_name' => $studentName,
            'payer_name' => null,
            'amount' => $amount,
            'payment_method' => $paymentMethod,
            'fee_type' => $feeType['type'],
            'fee_month' => $feeType['type'] === 'mensualite' ? $month->format('Y-m-01') : null,
            'fee_description' => $feeDescription,
            'group_name' => $this->randomGroupName(),
            'school_year' => $schoolYear,
            'collected_at' => $collectedAt->format('Y-m-d'),
            'operator_name' => $operators[array_rand($operators)],
            'order_number' => $orderNum,
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    private function randomFeeType(Carbon $month): array
    {
        // September = high inscription rate, other months = mostly mensualité
        if ($month->month === 9 && rand(1, 100) < 40) {
            return rand(1, 100) < 80 ? $this->feeTypes[1] : $this->feeTypes[2]; // A1 or B2
        }
        if (rand(1, 100) < 10) {
            return rand(1, 100) < 70 ? $this->feeTypes[1] : $this->feeTypes[2];
        }
        return $this->feeTypes[0]; // Mensualité
    }

    private function seedExpensesForMonth(int $siteId, Carbon $month, float $volumeFactor): void
    {
        // Fixed expenses per month (loyer, internet, eau+elec)
        $expenses = [
            ['type' => 'loyer', 'label' => 'Loyer mensuel', 'amount' => 8000 * $volumeFactor],
            ['type' => 'internet', 'label' => 'Internet + télécom', 'amount' => 600],
            ['type' => 'eau_electricite', 'label' => 'Eau et Électricité', 'amount' => 800 + rand(-200, 200)],
            ['type' => 'paiement_prof', 'label' => 'Paiement professeurs', 'amount' => 25000 * $volumeFactor],
            ['type' => 'fournitures', 'label' => 'Fournitures bureau', 'amount' => 500 + rand(-100, 400)],
            ['type' => 'impots_taxes', 'label' => 'Impôts et taxes', 'amount' => 2000 * $volumeFactor],
        ];

        foreach ($expenses as $e) {
            SiteExpense::create([
                'site_id' => $siteId,
                'type' => $e['type'],
                'label' => $e['label'],
                'amount' => round($e['amount'], 2),
                'month' => $month->format('Y-m-01'),
                'expense_date' => $month->copy()->day(rand(1, 28))->format('Y-m-d'),
                'payment_method' => 'especes',
            ]);
        }
    }

    private function seedImpayes(int $siteId, Carbon $snapshotDate, float $impayeRate, float $volumeFactor): void
    {
        // Calculate total encaissement to compute realistic impayés
        $lastMonthEnc = Encaissement::where('site_id', $siteId)
            ->whereBetween('collected_at', [
                $snapshotDate->copy()->startOfMonth(),
                $snapshotDate->copy()->endOfMonth()
            ])
            ->sum('amount');

        // Target impayé amount = X% of monthly encaissement
        $targetImpayeAmount = $lastMonthEnc * $impayeRate / (1 - $impayeRate);

        // Number of impayés
        $avgAmount = 1000;
        $count = (int) round($targetImpayeAmount / $avgAmount);
        if ($count < 5) $count = 5;

        $import = ImpayeImport::create([
            'site_id' => $siteId,
            'file_name' => 'fake-impayes-' . $snapshotDate->format('Ymd') . '.xlsx',
            'file_path' => 'fake/seeder.xlsx',
            'file_type' => 'excel',
            'month' => $snapshotDate->format('Y-m'),
            'snapshot_date' => $snapshotDate->format('Y-m-d'),
            'total_rows' => $count,
            'success_rows' => $count,
            'error_rows' => 0,
            'total_amount' => 0,
            'status' => 'completed',
            'notes' => 'Données générées pour test',
        ]);

        $totalAmount = 0;
        for ($i = 0; $i < $count; $i++) {
            $amount = rand(2, 15) * 100;
            $studentName = $this->randomName();
            $feeDescription = rand(1, 100) < 70
                ? "Frais de " . $this->monthNamesFr[rand(1, 12)]
                : "Frais d'inscription " . (rand(0, 1) ? 'A1/A2/B1' : 'B2');

            Impaye::create([
                'site_id' => $siteId,
                'impaye_import_id' => $import->id,
                'order_number' => $i + 1,
                'reference' => rand(1, 999) . 'SL' . rand(125, 126),
                'dedup_key' => Impaye::buildDedupKey($siteId, $studentName, $feeDescription, $amount),
                'student_name' => $studentName,
                'phone' => '2126' . rand(10000000, 99999999),
                'group_name' => 'Herr ' . ['Nizar', 'Driss', 'Hanafi', 'Kawtar', 'Abdelhadi'][array_rand(['a','b','c','d','e'])] . ' ' . rand(10, 19) . 'H',
                'fee_description' => $feeDescription,
                'amount_due' => $amount,
                'month' => $snapshotDate->format('Y-m'),
                'status' => 'pending',
            ]);
            $totalAmount += $amount;
        }

        $import->update(['total_amount' => $totalAmount]);
    }

    private function randomName(): string
    {
        return $this->firstNames[array_rand($this->firstNames)] . ' ' . $this->lastNames[array_rand($this->lastNames)];
    }

    private function randomGroupName(): string
    {
        $profs = ['NIZAR', 'DRISS', 'HANAFI', 'KAWTAR', 'ABDELHADI', 'YOUSSEF'];
        return 'P. ' . $profs[array_rand($profs)] . ' ' . rand(10, 19) . 'H';
    }

    private function schoolYearFor(Carbon $date): string
    {
        if ($date->month >= 9) {
            return $date->year . '/' . ($date->year + 1);
        }
        return ($date->year - 1) . '/' . $date->year;
    }
}
