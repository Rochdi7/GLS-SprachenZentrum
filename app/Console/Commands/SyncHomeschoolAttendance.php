<?php

namespace App\Console\Commands;

use App\Models\Group;
use App\Services\Payroll\HomeschoolAttendanceService;
use Carbon\Carbon;
use Illuminate\Console\Command;

class SyncHomeschoolAttendance extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'homeschool:sync-attendance {--group=*} {--date-start=} {--date-end=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Synchronise attendance from Homeschool API and calculates professor payment';

    public function __construct(protected HomeschoolAttendanceService $attendanceService)
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $groupIds = $this->option('group');
        $dateStart = $this->option('date-start') ? Carbon::parse($this->option('date-start')) : now()->startOfMonth();
        $dateEnd = $this->option('date-end') ? Carbon::parse($this->option('date-end')) : now()->endOfMonth();

        $query = Group::whereNotNull('crm_class_id');

        if (!empty($groupIds)) {
            $query->whereIn('id', $groupIds);
        }

        $groups = $query->get();

        $this->info("Found {$groups->count()} groups to sync");

        foreach ($groups as $group) {
            $this->info("Syncing group: {$group->name}");
            try {
                $result = $this->attendanceService->syncAndCalculate(
                    group: $group,
                    dateStart: $dateStart,
                    dateEnd: $dateEnd
                );
                $this->info("Successfully synced {$result['records_synced']} records");
            } catch (\Exception $e) {
                $this->error("Sync failed for group {$group->name}: {$e->getMessage()}");
            }
        }

        $this->info("Sync completed");
    }
}
