<?php

namespace App\Console\Commands;

use App\Services\Crm\Stats\WeeklyReportService;
use Illuminate\Console\Command;

class GenerateWeeklyReportCommand extends Command
{
    protected $signature = 'crm:weekly-report
                            {--date= : Any date inside the target week (yyyy-mm-dd). Defaults to last week.}';

    protected $description = 'Generate the weekly CEO CRM report (Mon–Sun) and store it in crm_weekly_reports';

    public function handle(WeeklyReportService $service): int
    {
        $date = $this->option('date') ?: null;

        $this->info('Generating weekly CRM report' . ($date ? " for week of {$date}" : ' (last week)') . '...');

        try {
            $report = $service->generate($date);

            $this->info("Report stored (id={$report->id}, week={$report->week_label}).");
            $this->line("  Revenue:       " . number_format((float) $report->total_revenue, 2) . " MAD");
            $this->line("  Registrations: " . $report->new_registrations);
            $this->line("  Best center:   " . ($report->best_center ?? '—'));
        } catch (\Throwable $e) {
            $this->error('Failed: ' . $e->getMessage());
            return self::FAILURE;
        }

        return self::SUCCESS;
    }
}
