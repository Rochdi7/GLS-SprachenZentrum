<?php

namespace App\Services;

use App\Models\Group;
use App\Models\GroupLevelFollowup;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class LevelFollowupGenerator
{
    /**
     * Supported progression order (fixed for this project).
     */
    private array $order = ['A1', 'A2', 'B1', 'B2'];

    /**
     * Generate followups for all active groups (idempotent).
     */
    public function generateAllActive(): void
    {
        Group::query()
            ->where('status', 'active')
            ->whereNotNull('date_debut')
            ->chunkById(200, function ($groups) {
                /** @var Group $group */
                foreach ($groups as $group) {
                    $this->generateForGroup($group);
                }
            });
    }


    /**
     * Default level durations in days (A1=2m, A2=2.5m, B1=2.5m, B2=3m ≈ 10m total).
     */
    private array $defaultDays = ['A1' => 61, 'A2' => 76, 'B1' => 76, 'B2' => 91];

    /**
     * Generate followups for a single group (idempotent).
     */
    public function generateForGroup(Group $group): void
    {
        if (empty($group->date_debut)) {
            return;
        }

        $startDate = Carbon::parse($group->date_debut)->startOfDay();

        $startLevel = $group->level;
        $startIndex = array_search($startLevel, $this->order, true);
        if ($startIndex === false) {
            return;
        }

        $levels = array_slice($this->order, $startIndex);
        $segmentCount = count($levels);

        // Compute per-level day counts — scale proportionally if date_fin is set
        $levelDays = $this->computeLevelDays($levels, $startDate, $group->date_fin);

        // Load existing done records to respect early completions
        $existingByLevel = GroupLevelFollowup::query()
            ->where('group_id', $group->id)
            ->get()
            ->keyBy('level');

        DB::transaction(function () use ($group, $levels, $segmentCount, $startDate, $existingByLevel, $levelDays) {
            $computed = [];
            $segStart = $startDate->copy();

            for ($i = 0; $i < $segmentCount; $i++) {
                $level = $levels[$i];
                $existing = $existingByLevel->get($level);

                // Inclusive end date = start + duration in days - 1
                $segEnd = $segStart->copy()->addDays($levelDays[$level] - 1)->startOfDay();

                // Auto-mark past levels as done; keep manual done_at if exists
                $isPast = $segEnd->copy()->endOfDay()->isPast();
                $status = $isPast ? 'done' : 'pending';
                $doneAt = $isPast ? $segEnd->toDateString() : null;

                if ($existing && $existing->status === 'done' && $existing->done_at) {
                    $status = 'done';
                    $doneAt = $existing->done_at;
                }

                $computed[] = [
                    'level' => $level,
                    'level_start_date' => $segStart->toDateString(),
                    'level_end_date' => $segEnd->toDateString(),
                    'due_date' => $segStart->toDateString(),
                    'status' => $status,
                    'done_at' => $doneAt,
                ];

                // Next level always starts the day after level_end_date
                $segStart = $segEnd->copy()->addDay();
            }

            // date_fin is always set by the form before this runs (auto or manual).
            // Segments are already scaled to fit — no need to overwrite date_fin here.

            $intendedLevels = array_column($computed, 'level');

            // If group.level changed: remove only *pending* followups for levels that are no longer part of the generated path.
            GroupLevelFollowup::query()
                ->where('group_id', $group->id)
                ->where('status', 'pending')
                ->whereNotIn('level', $intendedLevels)
                ->delete();

            // Upsert each computed segment.
            foreach ($computed as $segment) {
                /** @var GroupLevelFollowup|null $existing */
                $existing = GroupLevelFollowup::query()
                    ->where('group_id', $group->id)
                    ->where('level', $segment['level'])
                    ->first();

                if (!$existing) {
                    GroupLevelFollowup::create([
                        'group_id' => $group->id,
                        'level' => $segment['level'],
                        'level_start_date' => $segment['level_start_date'],
                        'level_end_date' => $segment['level_end_date'],
                        'due_date' => $segment['due_date'],
                        'status' => $segment['status'],
                        'done_at' => $segment['done_at'] ?? null,
                    ]);
                    continue;
                }

                $updateData = [
                    'level_start_date' => $segment['level_start_date'],
                    'level_end_date' => $segment['level_end_date'],
                    'due_date' => $segment['due_date'],
                ];

                if (($existing->status ?? 'pending') === 'pending' && $segment['status'] === 'done') {
                    $updateData['status'] = 'done';
                    $updateData['done_at'] = $segment['done_at'];
                }

                $existing->update($updateData);
            }
        });
    }

    /**
     * Compute how many days each level gets.
     * If date_fin is provided, scale the default ratios to fit exactly within
     * (date_debut → date_fin). The last level absorbs any rounding remainder
     * so segments always end exactly on date_fin.
     */
    private function computeLevelDays(array $levels, Carbon $startDate, ?string $dateFin): array
    {
        // Default days for each level in this path
        $defaults = [];
        foreach ($levels as $lvl) {
            $defaults[$lvl] = $this->defaultDays[$lvl] ?? 60;
        }

        if (empty($dateFin)) {
            return $defaults;
        }

        $endDate   = Carbon::parse($dateFin)->startOfDay();
        $totalDays = $startDate->diffInDays($endDate) + 1; // inclusive

        $defaultTotal = array_sum($defaults);
        $result       = [];
        $assigned     = 0;

        $lastLevel = $levels[array_key_last($levels)];

        foreach ($levels as $lvl) {
            if ($lvl === $lastLevel) {
                // Last level gets the remainder to avoid rounding drift
                $result[$lvl] = $totalDays - $assigned;
            } else {
                $days          = (int) round($totalDays * ($defaults[$lvl] / $defaultTotal));
                $result[$lvl]  = max(1, $days);
                $assigned     += $result[$lvl];
            }
        }

        return $result;
    }
}

