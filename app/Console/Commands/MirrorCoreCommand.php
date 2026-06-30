<?php

namespace App\Console\Commands;

use App\Models\CrmAttendance;
use App\Models\CrmClass;
use App\Models\CrmRegistration;
use App\Models\CrmStudent;
use App\Models\Site;
use App\Models\Teacher;
use App\Services\Crm\Crm;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class MirrorCoreCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'homeschool:mirror-core {--full : Sync all data instead of just recent changes} {--months=2 : How many months back to sync attendance}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Synchronize core CRM metadata (Classes, Students, Registrations) to the local mirror database';

    public function __construct(protected Crm $crm)
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting Core CRM Mirror Synchronization...');

        $this->syncClasses();
        $this->syncTeachers();
        $this->syncStudents();
        $this->syncRegistrations();
        // Attendance is handled by crm:sync-attendance (bulk endpoint, 500/page)
        // Do NOT sync here — the per-class standard endpoint hangs on shared hosting

        $this->info('Core CRM Mirror Synchronization Completed.');
    }

    protected function syncClasses()
    {
        $this->info('Syncing Classes...');

        // Two passes: active groups (history=N, default) and historical/finished
        // groups (history=Y). Historical groups power the "Groupes terminés" tab.
        // We pass history=Y explicitly on the second pass; the API returns the
        // closed/past groups that the default call omits.
        $this->syncClassesPass(null);   // active / current
        $this->syncClassesPass('Y');    // include finished / historical
    }

    /**
     * Page-walk /bulk/groups/classes for one history mode and upsert each row.
     * $history = null → active only (API default); 'Y' → include finished groups.
     */
    protected function syncClassesPass(?string $history): void
    {
        $page = 0;
        $size = 100;
        $label = $history === 'Y' ? 'history' : 'active';
        $seen = 0;
        $finished = 0;

        do {
            $this->comment("Fetching classes ({$label}) page {$page}...");
            $response = $this->crm->groups()->bulkClasses($page, $size, extra: array_filter([
                'history' => $history,
            ], fn ($v) => $v !== null));

            if (!($response['success'] ?? false)) {
                $this->error("Failed to fetch classes ({$label}) from API");
                break;
            }

            foreach ($response['data'] as $item) {
                $seen++;
                if (($item['STATUS_NAME'] ?? null) === 'Terminé') {
                    $finished++;
                }
                CrmClass::updateOrCreate(
                    ['crm_id' => $item['ID']],
                    [
                        'class_id'       => $item['CLASS_ID'] ?? null,
                        'name'           => $item['NAME'] ?? $item['REFERENCE'],
                        'crm_teacher_id' => $item['EMPLOYEE_TEACHER_ID'] ?? null,
                        'level'          => $item['SCHOOL_LEVEL_NAME'] ?? null,
                        'site_id'        => $item['STR_STORE_ID'] ?? null,
                        'raw_data'       => $item,
                        'last_synced_at' => now(),
                    ]
                );
            }

            $page++;
            $hasNext = $response['pagination']['hasMore'] ?? false;
        } while ($hasNext);

        $this->info("Classes ({$label}): {$seen} fetched, {$finished} with status 'Terminé'.");
    }

    protected function syncTeachers()
    {
        $this->info('Syncing Teachers from CRM Classes...');

        // Map crm_store_id → local site.id
        $siteMap = Site::whereNotNull('crm_store_id')->where('crm_store_id', '>', 0)->pluck('id', 'crm_store_id');

        // Fallback: use any site_id so the NOT NULL constraint is satisfied
        $fallbackSiteId = Site::value('id');

        $synced = 0;
        CrmClass::all()
            ->filter(fn($c) => !empty($c->raw_data['EMPLOYEE_TEACHER_ID']))
            ->map(fn($c) => [
                'crm_teacher_id' => $c->raw_data['EMPLOYEE_TEACHER_ID'],
                'name'           => trim($c->raw_data['EMPLOYEE_TEACHER_FULL_NAME'] ?? ''),
                // $c->site_id is crm_store_id on crm_classes; map to local site PK
                'site_id'        => $siteMap[$c->site_id] ?? $fallbackSiteId,
            ])
            ->filter(fn($t) => !empty($t['name']) && !empty($t['site_id']))
            ->unique('crm_teacher_id')
            ->each(function ($t) use (&$synced) {
                $baseSlug = Str::slug($t['name']);

                // Priority 1: find by crm_teacher_id (already linked)
                $teacher = Teacher::where('crm_teacher_id', $t['crm_teacher_id'])->first();

                // Priority 2: find by slug (manually created teacher, not yet linked)
                if (!$teacher) {
                    $teacher = Teacher::where('slug', $baseSlug)
                        ->whereNull('crm_teacher_id')
                        ->first();
                }

                // Priority 3: find by name (slug may differ slightly)
                if (!$teacher) {
                    $teacher = Teacher::where('name', $t['name'])
                        ->whereNull('crm_teacher_id')
                        ->first();
                }

                if ($teacher) {
                    // Update the existing teacher row — stamp crm_teacher_id on it
                    $teacher->crm_teacher_id = $t['crm_teacher_id'];
                    $teacher->site_id        = $t['site_id'];
                    $teacher->save();
                } else {
                    // Brand new teacher from CRM — generate a unique slug
                    $slug   = $baseSlug;
                    $suffix = 1;
                    while (Teacher::where('slug', $slug)->exists()) {
                        $slug = $baseSlug . '-' . $suffix++;
                    }

                    Teacher::create([
                        'crm_teacher_id' => $t['crm_teacher_id'],
                        'name'           => $t['name'],
                        'site_id'        => $t['site_id'],
                        'slug'           => $slug,
                    ]);
                }

                $synced++;
            });

        $this->info("Teachers synced: {$synced}");
    }

    protected function syncStudents()
    {
        $this->info('Syncing Students (Bulk)...');
        $page = 0;
        $size = 500; // Increased size for bulk

        do {
            $this->comment("Fetching students bulk page {$page}...");
            $response = $this->crm->attendance()->students($page, $size);

            if (!$response['success']) {
                $this->error('Failed to fetch students from bulk API');
                break;
            }

            foreach ($response['data'] as $item) {
                CrmStudent::updateOrCreate(
                    ['crm_id' => $item['ID']],
                    [
                        'first_name' => $item['FIRST_NAME'] ?? 'Unknown',
                        'last_name' => $item['LAST_NAME'] ?? 'Unknown',
                        'email' => $item['EMAIL'] ?? null,
                        'phone' => $item['PHONE_NUMBER'] ?? null,
                        'raw_data' => $item,
                        'last_synced_at' => now(),
                    ]
                );
            }

            $page++;
            $hasNext = $response['pagination']['hasMore'] ?? false;
        } while ($hasNext);
    }

    protected function syncRegistrations()
    {
        $this->info('Syncing Registrations (Bulk)...');
        $page = 0;
        $size = 500;

        do {
            $this->comment("Fetching registrations bulk page {$page}...");
            $response = $this->crm->attendance()->registrations($page, $size);

            if (!$response['success']) {
                $this->error('Failed to fetch registrations from bulk API');
                break;
            }

            foreach ($response['data'] as $item) {
                if (empty($item['CLASS_ID'])) {
                    continue;
                }

                $rawDc = $item['DATE_CREATION'] ?? null;
                $dateCreation = null;
                if ($rawDc && $rawDc !== 'null') {
                    try {
                        $dateCreation = \Carbon\Carbon::parse($rawDc)->toDateString();
                    } catch (\Throwable) {}
                }

                CrmRegistration::updateOrCreate(
                    ['crm_id' => $item['ID']],
                    [
                        'crm_student_id' => $item['STUDENT_ID'],
                        'crm_class_id'   => $item['CLASS_ID'],
                        'crm_store_id'   => $item['STR_STORE_ID'] ?? null,
                        'status'         => $item['STATUS_NAME'] ?? 'Active',
                        'date_creation'  => $dateCreation,
                        'status_label'   => $item['REGISTRATION_STATUS_NAME'] ?? $item['STATUS_NAME'] ?? null,
                        'raw_data'       => $item,
                        'last_synced_at' => now(),
                    ]
                );
            }

            $page++;
            $hasNext = $response['pagination']['hasMore'] ?? false;
        } while ($hasNext);
    }

    protected function syncAttendance(): void
    {
        $months = (int) ($this->option('months') ?? 2);
        $dateFrom = now()->subMonths($months)->startOfMonth()->toDateString();
        $dateTo   = now()->toDateString();

        $this->info("Syncing Attendance ({$dateFrom} → {$dateTo})...");

        $classes = CrmClass::whereNotNull('class_id')
            ->where(fn($q) => $q->whereNull('raw_data->STATUS_NAME')
                ->orWhereRaw("JSON_UNQUOTE(JSON_EXTRACT(raw_data, '$.STATUS_NAME')) = 'En formation'"))
            ->get(['id', 'crm_id', 'class_id', 'name']);

        $total = 0;

        foreach ($classes as $i => $class) {
            $this->comment("Attendance for: {$class->name} (class_id={$class->class_id})");
            if ($i > 0 && $i % 5 === 0) {
                $this->comment("  Rate-limit pause...");
                sleep(3);
            }

            $page = 0;
            $size = 500;

            do {
                $response = $this->crm->client()->get('/api/external/v1/bulk/session-presence', [
                    'classId'  => $class->class_id,
                    'dateFrom' => $dateFrom,
                    'dateTo'   => $dateTo,
                    'page'     => $page,
                    'size'     => $size,
                ]);

                if (empty($response['success'])) {
                    $this->warn("  Failed page {$page} for {$class->name}");
                    break;
                }

                foreach ($response['data'] as $row) {
                    $sessionDate = $row['SESSION_DATE'] ?? null;
                    $studentId   = $row['STUDENT_ID'] ?? null;

                    if (!$sessionDate || !$studentId) continue;

                    try {
                        $date = Carbon::parse($sessionDate)
                            ->setTimezone('Africa/Casablanca')
                            ->toDateString();
                    } catch (\Throwable) {
                        continue;
                    }

                    $isPresent = ($row['PRESENCE'] ?? 'N') === 'Y'
                        || ($row['PRESENCE_STATUS'] ?? 0) == 1;

                    CrmAttendance::updateOrCreate(
                        [
                            'crm_class_id'   => $class->crm_id,
                            'crm_student_id' => $studentId,
                            'date'           => $date,
                        ],
                        [
                            'crm_id'         => $row['SESSION_ID'] ?? null,
                            'is_present'     => $isPresent,
                            'raw_data'       => $row,
                            'last_synced_at' => now(),
                        ]
                    );
                    $total++;
                }

                $page++;
                $hasMore = $response['pagination']['hasMore'] ?? false;
            } while ($hasMore);
        }

        $this->info("Attendance synced: {$total} records across {$classes->count()} classes.");
    }
}
