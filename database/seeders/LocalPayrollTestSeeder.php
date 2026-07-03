<?php

namespace Database\Seeders;

use App\Models\Group;
use App\Models\PayrollStatusLog;
use App\Models\PresenceImport;
use App\Models\PresenceImportStudent;
use App\Models\PresenceRecord;
use App\Models\Site;
use App\Models\Teacher;
use App\Models\User;
use App\Services\Payroll\HourlyPaymentCalculationService;
use App\Services\Payroll\PeriodPaymentCalculationService;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

/**
 * LOCAL-ONLY test data for the professor payment / payroll module.
 *
 * Creates a teacher + a professor login, two groups, and imports covering:
 *   - all 3 modes: period, hourly, weekly (legacy)
 *   - all 4 lifecycle states: draft, validated, paid, locked
 * with real presence records so the calculation services actually run.
 *
 * Run:  php artisan db:seed --class=LocalPayrollTestSeeder
 *
 * Safe to run repeatedly (idempotent-ish: it wipes only the objects it owns,
 * keyed by the sentinel names/emails below).
 */
class LocalPayrollTestSeeder extends Seeder
{
    /** Sentinel values so we only ever touch our own test rows. */
    private const PROF_EMAIL   = 'prof@local.test';
    private const TEACHER_NAME = 'Prof Test (LOCAL)';
    private const SITE_SLUG    = 'local-test-site';
    private const GROUP_PREFIX = '[LOCAL] ';

    public function run(): void
    {
        // ── HARD SAFETY GUARD ──────────────────────────────────────────
        if (! app()->environment('local')) {
            $this->command->error('LocalPayrollTestSeeder only runs in the LOCAL environment. Aborting.');

            return;
        }

        $periodCalc = app(PeriodPaymentCalculationService::class);
        $hourlyCalc = app(HourlyPaymentCalculationService::class);

        $this->cleanup();

        // ── Site + teacher + professor login ───────────────────────────
        $site = Site::firstOrCreate(
            ['slug' => self::SITE_SLUG],
            ['name' => 'Centre Test (LOCAL)', 'city' => 'Casablanca', 'is_active' => true]
        );

        $teacher = Teacher::create([
            'name'                => self::TEACHER_NAME,
            'slug'                => 'prof-test-local-' . uniqid(),
            'site_id'             => $site->id,
            'email'               => self::PROF_EMAIL,
            'payment_per_student' => 500,
        ]);

        $profRole = Role::firstOrCreate(['name' => 'Professeur', 'guard_name' => 'web']);
        $prof = User::create([
            'name'              => self::TEACHER_NAME,
            'email'             => self::PROF_EMAIL,
            'password'          => Hash::make('password'),
            'site_id'           => $site->id,
            'teacher_id'        => $teacher->id,
            'email_verified_at' => now(),
            'is_active'         => true,
        ]);
        $prof->assignRole($profRole);

        // An admin actor for the lifecycle "who" stamps (fall back to any user).
        $actor = User::whereHas('roles', fn ($q) => $q->whereIn('name', ['Super Admin', 'Admin']))->first() ?? $prof;

        // ── Two groups belonging to the teacher ────────────────────────
        // crm_class_id is set so the groups appear on the payroll dashboard
        // (which filters whereNotNull('crm_class_id')). High values avoid
        // colliding with any real synced CRM classes.
        $groupA = Group::create([
            'site_id' => $site->id, 'teacher_id' => $teacher->id,
            'crm_class_id' => 999901,
            'name' => self::GROUP_PREFIX . 'Groupe A1 Matin', 'level' => 'A1',
            'time_range' => '09:00 - 11:00', 'status' => 'active', 'crm_only' => true,
        ]);
        $groupB = Group::create([
            'site_id' => $site->id, 'teacher_id' => $teacher->id,
            'crm_class_id' => 999902,
            'name' => self::GROUP_PREFIX . 'Groupe B1 Soir', 'level' => 'B1',
            'time_range' => '18:00 - 20:00', 'status' => 'active', 'crm_only' => true,
        ]);

        $version = 1;

        // ── PERIOD imports — one per lifecycle state (Group A) ──────────
        // December = Month 1, January = Month 2 (proves no Sept assumption).
        $states = [
            ['status' => 'draft',     'month' => 12, 'year' => 2025, 'monthNo' => 1],
            ['status' => 'validated', 'month' => 1,  'year' => 2026, 'monthNo' => 2],
            ['status' => 'paid',      'month' => 2,  'year' => 2026, 'monthNo' => 3],
            ['status' => 'locked',    'month' => 3,  'year' => 2026, 'monthNo' => 4],
        ];

        foreach ($states as $s) {
            $import = $this->makePeriodImport($groupA, $version++, $s, $teacher);
            $periodCalc->calculate($import);
            $this->applyLifecycle($import, $s['status'], $actor);
        }

        // ── HOURLY imports — draft + paid (Group B) ────────────────────
        $h1 = $this->makeHourlyImport($groupB, $version++, 12, 2025, 1, 100, 20, $teacher);
        $hourlyCalc->calculate($h1);

        $h2 = $this->makeHourlyImport($groupB, $version++, 1, 2026, 2, 120, 18, $teacher);
        $hourlyCalc->calculate($h2);
        $this->applyLifecycle($h2, 'paid', $actor);

        // ── WEEKLY legacy import (Group B) — stays 'weekly', renders old view
        $this->makeWeeklyImport($groupB, $version++, $teacher);

        $this->summary($prof, $groupA, $groupB);
    }

    /* ================================================================== */

    private function makePeriodImport(Group $group, int $version, array $s, Teacher $teacher): PresenceImport
    {
        $start = Carbon::create($s['year'], $s['month'], 1)->startOfMonth();
        $end   = $start->copy()->endOfMonth();

        $import = PresenceImport::create([
            'group_id'           => $group->id,
            'version'            => $version,
            'month'              => $start,
            'date_start'         => $start,
            'date_end'           => $end,
            'total_days'         => 20,
            'file_name'          => 'LOCAL_SEED',
            'file_path'          => 'local_seed',
            'is_crm_api'         => true,
            'crm_teacher_name'   => $teacher->name,
            'month_label'        => $start->locale('fr')->isoFormat('MMMM YYYY') . " (Mois {$s['monthNo']})",
            'payment_mode'       => PresenceImport::MODE_PERIOD,
            'status'             => PresenceImport::STATUS_DRAFT, // lifecycle applied after calc
            'base_price'         => 500,
            'period_tiers_json'  => PeriodPaymentCalculationService::currentTiersSnapshot(),
            'attached_month'     => $s['month'],
            'attached_year'      => $s['year'],
            'group_month_number' => $s['monthNo'],
        ]);

        // 5 students with varied presence counts → hits every tier (0/125/250/500)
        $presenceCounts = [4, 5, 8, 12, 6];
        foreach ($presenceCounts as $idx => $count) {
            $student = PresenceImportStudent::create([
                'presence_import_id' => $import->id,
                'row_number'         => $idx + 1,
                'student_name'       => "Étudiant " . chr(65 + $idx) . " — G{$group->id}",
                'total_present'      => $count,
                'total_absent'       => 20 - $count,
                'status'             => 'active',
            ]);
            $this->makeRecords($student, $start, $count, 20 - $count);
        }

        return $import;
    }

    private function makeHourlyImport(Group $group, int $version, int $month, int $year, int $monthNo, float $rate, float $hours, Teacher $teacher): PresenceImport
    {
        $start = Carbon::create($year, $month, 1)->startOfMonth();

        return PresenceImport::create([
            'group_id'           => $group->id,
            'version'            => $version,
            'month'              => $start,
            'date_start'         => $start,
            'date_end'           => $start->copy()->endOfMonth(),
            'total_days'         => 0,
            'file_name'          => 'LOCAL_SEED',
            'file_path'          => 'local_seed',
            'crm_teacher_name'   => $teacher->name,
            'month_label'        => $start->locale('fr')->isoFormat('MMMM YYYY') . " (Mois {$monthNo})",
            'payment_mode'       => PresenceImport::MODE_HOURLY,
            'status'             => PresenceImport::STATUS_DRAFT,
            'attached_month'     => $month,
            'attached_year'      => $year,
            'group_month_number' => $monthNo,
            'hourly_rate'        => $rate,
            'total_hours'        => $hours,
        ]);
    }

    private function makeWeeklyImport(Group $group, int $version, Teacher $teacher): PresenceImport
    {
        $start = Carbon::create(2025, 11, 1)->startOfMonth();

        // Legacy weekly import: payment_mode defaults to 'weekly', keeps old view.
        $import = PresenceImport::create([
            'group_id'          => $group->id,
            'version'           => $version,
            'month'             => $start,
            'date_start'        => $start,
            'date_end'          => $start->copy()->endOfMonth(),
            'total_days'        => 20,
            'payment_per_student' => 500,
            'weekly_threshold'  => 3,
            'weekly_rate_percent' => 25,
            'file_name'         => 'LOCAL_SEED',
            'file_path'         => 'local_seed',
            'is_crm_api'        => true,
            'crm_teacher_name'  => $teacher->name,
            'month_label'       => 'Novembre 2025 (hebdo legacy)',
            'payment_mode'      => PresenceImport::MODE_WEEKLY,
            'status'            => PresenceImport::STATUS_DRAFT,
        ]);

        foreach ([[4, 3, 4, 5], [3, 0, 3, 4], [5, 5, 5, 5]] as $idx => $weeks) {
            $student = PresenceImportStudent::create([
                'presence_import_id' => $import->id,
                'row_number'         => $idx + 1,
                'student_name'       => "Étudiant W" . ($idx + 1),
                'total_present'      => array_sum($weeks),
                'total_absent'       => 0,
                'status'             => 'active',
                'week_1_presence'    => $weeks[0],
                'week_2_presence'    => $weeks[1],
                'week_3_presence'    => $weeks[2],
                'week_4_presence'    => $weeks[3],
            ]);
            // Spread present days across the month so the weekly calculator can bucket them
            $this->makeRecords($student, $start, array_sum($weeks), 0);
        }

        return $import;
    }

    /**
     * Create present/absent records on consecutive weekdays.
     */
    private function makeRecords(PresenceImportStudent $student, Carbon $monthStart, int $present, int $absent): void
    {
        $cursor = $monthStart->copy();
        $made = 0;
        $target = $present + $absent;

        while ($made < $target) {
            // Skip weekends (school runs Mon–Fri)
            if ($cursor->isWeekend()) {
                $cursor->addDay();
                continue;
            }
            PresenceRecord::create([
                'presence_import_student_id' => $student->id,
                'date'   => $cursor->toDateString(),
                'status' => $made < $present ? 'present' : 'absent',
            ]);
            $made++;
            $cursor->addDay();
        }
    }

    /**
     * Advance an import to the requested lifecycle state, writing the "who/when"
     * stamps and an audit log row for each step (mirrors PayrollLifecycleService).
     */
    private function applyLifecycle(PresenceImport $import, string $target, User $actor): void
    {
        if ($target === PresenceImport::STATUS_DRAFT) {
            return;
        }

        // draft → validated
        $import->update([
            'status'       => PresenceImport::STATUS_VALIDATED,
            'validated_by' => $actor->id,
            'validated_at' => now()->subDays(5),
        ]);
        $this->log($import, $actor, PayrollStatusLog::ACTION_VALIDATE, 'draft', 'validated', now()->subDays(5));

        if ($target === PresenceImport::STATUS_VALIDATED) {
            return;
        }

        // validated → paid
        $import->update([
            'status'            => PresenceImport::STATUS_PAID,
            'payment_date'      => now()->subDays(3)->toDateString(),
            'payment_method'    => PresenceImport::PAY_TRANSFER,
            'payment_reference' => 'TX-LOCAL-' . $import->id,
            'payment_notes'     => 'Paiement de test (seed local).',
            'paid_by'           => $actor->id,
            'paid_at'           => now()->subDays(3),
        ]);
        $this->log($import, $actor, PayrollStatusLog::ACTION_MARK_PAID, 'validated', 'paid', now()->subDays(3));

        if ($target === PresenceImport::STATUS_PAID) {
            return;
        }

        // paid → locked
        $import->update([
            'status'    => PresenceImport::STATUS_LOCKED,
            'locked_by' => $actor->id,
            'locked_at' => now()->subDay(),
        ]);
        $this->log($import, $actor, PayrollStatusLog::ACTION_LOCK, 'paid', 'locked', now()->subDay());
    }

    private function log(PresenceImport $import, User $actor, string $action, string $from, string $to, Carbon $at): void
    {
        PayrollStatusLog::create([
            'presence_import_id' => $import->id,
            'user_id'            => $actor->id,
            'action'             => $action,
            'from_status'        => $from,
            'to_status'          => $to,
            'comment'            => 'Seed local',
            'created_at'         => $at,
            'updated_at'         => $at,
        ]);
    }

    /**
     * Remove any data from a previous run of this seeder (by sentinels only).
     */
    private function cleanup(): void
    {
        $groupIds = Group::where('name', 'like', self::GROUP_PREFIX . '%')->pluck('id');

        if ($groupIds->isNotEmpty()) {
            $importIds = PresenceImport::whereIn('group_id', $groupIds)->pluck('id');
            PayrollStatusLog::whereIn('presence_import_id', $importIds)->delete();
            PresenceRecord::whereIn(
                'presence_import_student_id',
                PresenceImportStudent::whereIn('presence_import_id', $importIds)->pluck('id')
            )->delete();
            PresenceImportStudent::whereIn('presence_import_id', $importIds)->delete();
            \App\Models\PresencePaymentSummary::whereIn('presence_import_id', $importIds)->delete();
            PresenceImport::whereIn('id', $importIds)->delete();
            Group::whereIn('id', $groupIds)->delete();
        }

        // Detach the professor login so teacher can be removed cleanly
        User::where('email', self::PROF_EMAIL)->delete();
        Teacher::where('name', self::TEACHER_NAME)->delete();
    }

    private function summary(User $prof, Group $groupA, Group $groupB): void
    {
        $this->command->info('');
        $this->command->info('✅ LOCAL payroll test data seeded.');
        $this->command->info('────────────────────────────────────────────');
        $this->command->info("Professor login:  {$prof->email}  /  password");
        $this->command->info("Group A (period, 4 lifecycle states): #{$groupA->id}");
        $this->command->info("Group B (hourly x2 + weekly legacy):  #{$groupB->id}");
        $this->command->info('');
        $this->command->info('Admin view:  /backoffice/payroll/crm/legacy/group/' . $groupA->id . '/imports');
        $this->command->info('Prof view:   log in as the professor → /backoffice/payroll/professor');
        $this->command->info('────────────────────────────────────────────');
    }
}
