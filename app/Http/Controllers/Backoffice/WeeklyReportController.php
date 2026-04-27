<?php

namespace App\Http\Controllers\Backoffice;

use App\Http\Controllers\Controller;
use App\Models\Teacher;
use App\Models\WeeklyReport;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class WeeklyReportController extends Controller
{
    /**
     * Calendar view — default to current week.
     */
    public function index(Request $request)
    {
        $teachers = Teacher::with(['groups' => function ($q) {
            $q->orderBy('name');
        }])->orderBy('name')->get();

        // Build a JS-friendly map: { teacherId: [{id, label}, ...] }
        $teacherGroupsMap = $teachers->mapWithKeys(function ($t) {
            return [$t->id => $t->groups->map(fn ($g) => [
                'id'    => $g->id,
                'label' => $g->name ?: ($g->name_fr ?: 'Groupe #'.$g->id),
            ])->values()];
        });

        $date = $request->filled('week')
            ? Carbon::parse($request->input('week'))->startOfWeek(Carbon::MONDAY)
            : Carbon::now()->startOfWeek(Carbon::MONDAY);

        $weekDays = collect();
        for ($i = 0; $i < 5; $i++) {
            $weekDays->push($date->copy()->addDays($i));
        }

        $reports = WeeklyReport::with(['teacher', 'group'])
            ->whereBetween('report_date', [$weekDays->first(), $weekDays->last()])
            ->orderBy('created_at')
            ->get()
            ->groupBy(fn ($r) => $r->report_date->format('Y-m-d'));

        return view('backoffice.weekly-reports.index', compact('teachers', 'teacherGroupsMap', 'weekDays', 'reports', 'date'));
    }

    /**
     * Create a new report (multiple per teacher/day allowed).
     * Optionally accepts a PDF attachment.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'teacher_id'  => 'required|exists:teachers,id',
            'report_date' => 'required|date',
            'notes'       => 'required|string|max:5000',
            'attachment'  => 'nullable|file|mimes:pdf|max:10240',
        ]);

        $payload = [
            'teacher_id'  => $data['teacher_id'],
            'report_date' => $data['report_date'],
            'notes'       => $data['notes'],
            'created_by'  => auth()->id(),
        ];

        if ($request->hasFile('attachment')) {
            $file = $request->file('attachment');
            $payload['attachment_path'] = $file->store('weekly-reports', 'public');
            $payload['attachment_original_name'] = $file->getClientOriginalName();
        }

        $report = WeeklyReport::create($payload);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'report'  => $report->load('teacher'),
            ]);
        }

        return back()->with('success', 'Rapport enregistré avec succès.');
    }

    /**
     * Update an existing report. Replaces or removes attachment as requested.
     */
    public function update(Request $request, WeeklyReport $weeklyReport)
    {
        $data = $request->validate([
            'teacher_id'        => 'required|exists:teachers,id',
            'report_date'       => 'required|date',
            'notes'             => 'required|string|max:5000',
            'attachment'        => 'nullable|file|mimes:pdf|max:10240',
            'remove_attachment' => 'nullable|boolean',
        ]);

        $weeklyReport->teacher_id = $data['teacher_id'];
        $weeklyReport->report_date = $data['report_date'];
        $weeklyReport->notes = $data['notes'];

        if (!empty($data['remove_attachment']) && $weeklyReport->attachment_path) {
            Storage::disk('public')->delete($weeklyReport->attachment_path);
            $weeklyReport->attachment_path = null;
            $weeklyReport->attachment_original_name = null;
        }

        if ($request->hasFile('attachment')) {
            if ($weeklyReport->attachment_path) {
                Storage::disk('public')->delete($weeklyReport->attachment_path);
            }
            $file = $request->file('attachment');
            $weeklyReport->attachment_path = $file->store('weekly-reports', 'public');
            $weeklyReport->attachment_original_name = $file->getClientOriginalName();
        }

        $weeklyReport->save();

        return back()->with('success', 'Rapport mis à jour avec succès.');
    }

    /**
     * Delete a report and its attachment.
     */
    public function destroy(WeeklyReport $weeklyReport)
    {
        if ($weeklyReport->attachment_path) {
            Storage::disk('public')->delete($weeklyReport->attachment_path);
        }

        $weeklyReport->delete();

        if (request()->expectsJson()) {
            return response()->json(['success' => true]);
        }

        return back()->with('success', 'Rapport supprimé.');
    }

    /**
     * Export the current week's reports as PDF.
     */
    public function exportPdf(Request $request)
    {
        $date = $request->filled('week')
            ? Carbon::parse($request->input('week'))->startOfWeek(Carbon::MONDAY)
            : Carbon::now()->startOfWeek(Carbon::MONDAY);

        $weekDays = collect();
        for ($i = 0; $i < 5; $i++) {
            $weekDays->push($date->copy()->addDays($i));
        }

        $weekStart = $weekDays->first();
        $weekEnd = $weekDays->last();

        $reports = WeeklyReport::with(['teacher', 'group'])
            ->whereBetween('report_date', [$weekStart, $weekEnd])
            ->orderBy('report_date')
            ->orderBy('created_at')
            ->get()
            ->groupBy(fn ($r) => $r->report_date->format('Y-m-d'));

        $reportsByTeacher = WeeklyReport::with(['teacher', 'group'])
            ->whereBetween('report_date', [$weekStart, $weekEnd])
            ->orderBy('report_date')
            ->orderBy('created_at')
            ->get()
            ->groupBy(fn ($r) => $r->teacher->name)
            ->sortKeys();

        $pdf = Pdf::loadView('backoffice.pdf.weekly-report', compact(
            'weekDays', 'weekStart', 'weekEnd', 'reports', 'reportsByTeacher'
        ))->setPaper('A4', 'landscape');

        $filename = 'rapport_semaine_' . $weekStart->format('Y-m-d') . '_' . $weekEnd->format('Y-m-d') . '.pdf';

        return $pdf->download($filename);
    }

    /**
     * Return all reports for a single day (used by the multi-row modal).
     */
    public function forDay(Request $request)
    {
        $request->validate(['date' => 'required|date']);

        $reports = WeeklyReport::with(['teacher', 'group'])
            ->whereDate('report_date', $request->date)
            ->orderBy('created_at')
            ->get()
            ->map(fn ($r) => [
                'id'              => $r->id,
                'teacher_id'      => $r->teacher_id,
                'group_id'        => $r->group_id,
                'group_label'     => $r->group?->name,
                'notes'           => $r->notes,
                'attachment_url'  => $r->attachment_url,
                'attachment_name' => $r->attachment_original_name,
            ]);

        return response()->json(['reports' => $reports]);
    }

    /**
     * Batch sync all reports for a single day.
     * Accepts a list of rows (existing + new). Anything missing from the payload
     * for that day gets deleted. New rows get created, existing rows updated,
     * with optional file replacement / removal.
     */
    public function batchSync(Request $request)
    {
        $data = $request->validate([
            'report_date'                  => 'required|date',
            'rows'                         => 'array',
            'rows.*.id'                    => 'nullable|integer|exists:weekly_reports,id',
            'rows.*.teacher_id'            => 'required|exists:teachers,id',
            'rows.*.group_id'              => 'nullable|integer|exists:groups,id',
            'rows.*.notes'                 => 'required|string|max:5000',
            'rows.*.remove_attachment'     => 'nullable|boolean',
            'rows.*.attachment'            => 'nullable|file|mimes:pdf|max:10240',
        ]);

        // Enforce: each group must belong to the selected teacher
        foreach (($data['rows'] ?? []) as $i => $row) {
            if (!empty($row['group_id'])) {
                $belongs = \App\Models\Group::where('id', $row['group_id'])
                    ->where('teacher_id', $row['teacher_id'])
                    ->exists();
                if (!$belongs) {
                    return back()->withErrors([
                        "rows.$i.group_id" => "Le groupe sélectionné n'appartient pas à cet enseignant.",
                    ])->withInput();
                }
            }
        }

        $rows = $data['rows'] ?? [];
        $reportDate = $data['report_date'];
        $keepIds = [];

        foreach ($rows as $row) {
            if (!empty($row['id'])) {
                $report = WeeklyReport::where('id', $row['id'])
                    ->whereDate('report_date', $reportDate)
                    ->first();

                if (!$report) continue;

                $report->teacher_id = $row['teacher_id'];
                $report->group_id = $row['group_id'] ?? null;
                $report->notes = $row['notes'];

                if (!empty($row['remove_attachment']) && $report->attachment_path) {
                    Storage::disk('public')->delete($report->attachment_path);
                    $report->attachment_path = null;
                    $report->attachment_original_name = null;
                }

                if (isset($row['attachment']) && $row['attachment']) {
                    if ($report->attachment_path) {
                        Storage::disk('public')->delete($report->attachment_path);
                    }
                    $file = $row['attachment'];
                    $report->attachment_path = $file->store('weekly-reports', 'public');
                    $report->attachment_original_name = $file->getClientOriginalName();
                }

                $report->save();
                $keepIds[] = $report->id;
            } else {
                $payload = [
                    'teacher_id'  => $row['teacher_id'],
                    'group_id'    => $row['group_id'] ?? null,
                    'report_date' => $reportDate,
                    'notes'       => $row['notes'],
                    'created_by'  => auth()->id(),
                ];

                if (isset($row['attachment']) && $row['attachment']) {
                    $file = $row['attachment'];
                    $payload['attachment_path'] = $file->store('weekly-reports', 'public');
                    $payload['attachment_original_name'] = $file->getClientOriginalName();
                }

                $created = WeeklyReport::create($payload);
                $keepIds[] = $created->id;
            }
        }

        // Delete reports for that day that were removed in the modal
        $toDelete = WeeklyReport::whereDate('report_date', $reportDate)
            ->when(!empty($keepIds), fn ($q) => $q->whereNotIn('id', $keepIds))
            ->get();

        foreach ($toDelete as $report) {
            if ($report->attachment_path) {
                Storage::disk('public')->delete($report->attachment_path);
            }
            $report->delete();
        }

        return back()->with('success', 'Rapports enregistrés avec succès.');
    }

    /**
     * Return reports for a given week as JSON (for AJAX calendar refresh).
     */
    public function events(Request $request)
    {
        $request->validate([
            'start' => 'required|date',
            'end'   => 'required|date',
        ]);

        $reports = WeeklyReport::with(['teacher', 'group'])
            ->whereBetween('report_date', [$request->start, $request->end])
            ->orderBy('created_at')
            ->get()
            ->map(fn ($r) => [
                'id'             => $r->id,
                'teacher_id'     => $r->teacher_id,
                'teacher_name'   => $r->teacher->name,
                'group_id'       => $r->group_id,
                'group_name'     => $r->group?->name,
                'report_date'    => $r->report_date->format('Y-m-d'),
                'notes'          => $r->notes,
                'attachment_url' => $r->attachment_url,
                'attachment_name'=> $r->attachment_original_name,
            ]);

        return response()->json($reports);
    }
}
