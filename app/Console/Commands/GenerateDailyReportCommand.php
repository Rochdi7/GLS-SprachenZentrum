<?php

namespace App\Console\Commands;

use App\Jobs\Crm\SendDailyReportJob;
use App\Services\Crm\Stats\DailyReportService;
use Illuminate\Console\Command;

class GenerateDailyReportCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'crm:daily-report
                            {--date= : Date in yyyy-mm-dd format, defaults to yesterday}';

    /**
     * The console command description.
     */
    protected $description = 'Generate the daily CEO CRM report and dispatch the send job';

    public function handle(DailyReportService $service): int
    {
        $date = $this->option('date') ?: null;

        $this->info('Generating daily CRM report' . ($date ? " for {$date}" : ' (yesterday)') . '...');

        try {
            $data   = $service->generate($date);
            $report = $service->store($data);

            $this->info("Report stored (id={$report->id}, date={$report->report_date->toDateString()}).");
            $this->line("  Revenue:       " . number_format((float) $report->revenue_yesterday, 2) . " MAD");
            $this->line("  Registrations: " . $report->new_registrations);
            $this->line("  At risk:       " . $report->students_at_risk);
            $this->line("  Best center:   " . ($report->best_center ?? '—'));

            SendDailyReportJob::dispatch($report);
            $this->info('SendDailyReportJob dispatched.');
        } catch (\Throwable $e) {
            $this->error('Failed: ' . $e->getMessage());
            return self::FAILURE;
        }

        return self::SUCCESS;
    }
}
