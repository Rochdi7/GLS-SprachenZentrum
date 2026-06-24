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

        // Load existing done records to respect early completions
        $existingByLevel = GroupLevelFollowup::query()
            ->where('group_id', $group->id)
            ->get()
            ->keyBy('level');

        DB::transaction(function () use ($group, $levels, $segmentCount, $startDate, $existingByLevel) {
            $computed = [];
            $segStart = $startDate->copy();

            for ($i = 0; $i < $segmentCount; $i++) {
                $level = $levels[$i];
                $existing = $existingByLevel->get($level);

                // Inclusive end date = start + duration - 1 day
                $segEnd = $segStart->copy();
                if ($level === 'A1') {
                    $segEnd->addMonthsNoOverflow(2)->subDay();
                } elseif ($level === 'A2') {
                    $segEnd->addMonthsNoOverflow(2)->addDays(15)->subDay();
                } elseif ($level === 'B1') {
                    $segEnd->addMonthsNoOverflow(2)->addDays(15)->subDay();
                } elseif ($level === 'B2') {
                    $segEnd->addMonthsNoOverflow(3)->subDay();
                }
                $segEnd->startOfDay();

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

            // Update the group's end date from the last level
            if ($segmentCount > 0) {
                $lastEndDate = $computed[$segmentCount - 1]['level_end_date'];
                if ($group->date_fin !== $lastEndDate && $group->status === 'active') {
                    Group::where('id', $group->id)->update(['date_fin' => $lastEndDate]);
                    $group->date_fin = $lastEndDate;
                }
            }

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
}

