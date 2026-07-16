<?php

namespace App\Http\Controllers\Backoffice;

use App\Http\Controllers\Concerns\ScopesToUserSites;
use App\Http\Controllers\Controller;
use App\Models\Teacher;
use App\Models\WeeklyReport;
use App\Models\WeeklyReportAttachment;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class WeeklyReportController extends Controller
{
    use ScopesToUserSites;

    /**
     * Calendar view — default to current week.
     */
    public function index(Request $request)
    {
        $teachersQuery = Teacher::with(['groups' => function ($q) {
            $q->orderBy('name');
        }])->orderBy('name');

        // Non-admins: only teachers tied to their accessible centres
        $allowedSiteIds = $this->accessibleSiteIds();
        if ($allowedSiteIds !== null) {
            if (empty($allowedSiteIds)) {
                $teachersQuery->whereRaw('1 = 0');
            } else {
                $teachersQuery->whereIn('site_id', $allowedSiteIds);
            }
        }
        $teachers = $teachersQuery->get();

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

        $reportsQuery = WeeklyReport::with(['teacher', 'group'])
            ->whereBetween('report_date', [$weekDays->first(), $weekDays->last()])
            ->orderBy('created_at');

        if ($allowedSiteIds !== null) {
            if (empty($allowedSiteIds)) {
                $reportsQuery->whereRaw('1 = 0');
            } else {
                $reportsQuery->whereHas('teacher', fn ($q) => $q->whereIn('site_id', $allowedSiteIds));
            }
        }

        $reports = $reportsQuery->get()
            ->groupBy(fn ($r) => $r->report_date->format('Y-m-d'));

        // Réception (front-desk) clicks a chip to read the detail page; Admins go
        // straight to the edit modal since they manage the data.
        $canEditReports = auth()->user()?->hasAnyRole(['Super Admin', 'Admin']) ?? false;

        return view('backoffice.weekly-reports.index', compact('teachers', 'teacherGroupsMap', 'weekDays', 'reports', 'date', 'canEditReports'));
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
        foreach ($weeklyReport->attachments as $att) {
            Storage::disk('public')->delete($att->path);
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

        $reports = WeeklyReport::with(['teacher', 'group', 'attachments'])
            ->whereBetween('report_date', [$weekStart, $weekEnd])
            ->orderBy('report_date')
            ->orderBy('created_at')
            ->get()
            ->groupBy(fn ($r) => $r->report_date->format('Y-m-d'));

        $reportsByTeacher = WeeklyReport::with(['teacher', 'group', 'attachments'])
            ->whereBetween('report_date', [$weekStart, $weekEnd])
            ->orderBy('report_date')
            ->orderBy('created_at')
            ->get()
            ->groupBy(fn ($r) => $r->teacher->name)
            ->sortKeys();

        $pdf = Pdf::loadView('backoffice.pdf.weekly-report', compact(
            'weekDays', 'weekStart', 'weekEnd', 'reports', 'reportsByTeacher'
        ))
            ->setPaper('A4', 'landscape')
            ->setOption('isHtml5ParserEnabled', true)
            ->setOption('isPhpEnabled', true);

        $filename = 'rapport_semaine_' . $weekStart->format('Y-m-d') . '_' . $weekEnd->format('Y-m-d') . '.pdf';

        return $pdf->download($filename);
    }

    /**
     * Show a detail page for a (teacher, group?, week) tuple — opened by the eye button.
     * Accepts ?week=YYYY-MM-DD (any date in the week) or legacy ?date=YYYY-MM-DD.
     */
    public function show(Request $request)
    {
        $data = $request->validate([
            'week'       => 'nullable|date',
            'date'       => 'nullable|date',
            'teacher_id' => 'required|exists:teachers,id',
            'group_id'   => 'nullable|integer|exists:groups,id',
        ]);

        // Support both ?week= (new) and ?date= (legacy)
        $anchor = $data['week'] ?? $data['date'] ?? now()->toDateString();
        $monday = Carbon::parse($anchor)->startOfWeek(Carbon::MONDAY);
        $friday = $monday->copy()->addDays(4);

        // Non-admins: block viewing a teacher outside their accessible centres.
        $allowedSiteIds = $this->accessibleSiteIds();
        if ($allowedSiteIds !== null) {
            $teacherAllowed = !empty($allowedSiteIds) && Teacher::where('id', $data['teacher_id'])
                ->whereIn('site_id', $allowedSiteIds)
                ->exists();
            if (!$teacherAllowed) {
                abort(403);
            }
        }

        $reports = WeeklyReport::with(['teacher', 'group', 'attachments'])
            ->whereBetween('report_date', [$monday, $friday])
            ->where('teacher_id', $data['teacher_id'])
            ->when(true, function ($q) use ($data) {
                $gid = $data['group_id'] ?? null;
                if ($gid === null || $gid === '') {
                    $q->whereNull('group_id');
                } else {
                    $q->where('group_id', $gid);
                }
            })
            ->orderBy('report_date')
            ->orderBy('skill')
            ->orderBy('created_at')
            ->get();

        if ($reports->isEmpty()) {
            return redirect()
                ->route('backoffice.weekly_reports.index', ['week' => $anchor])
                ->with('error', 'Aucun rapport trouvé pour cette sélection.');
        }

        $teacher = $reports->first()->teacher;
        $group   = $reports->first()->group;
        $date    = $monday; // used by the view for the week header

        $exportParams = [
            'week'       => $monday->format('Y-m-d'),
            'teacher_id' => $data['teacher_id'],
        ];
        if (!empty($data['group_id'])) {
            $exportParams['group_id'] = $data['group_id'];
        }

        return view('backoffice.weekly-reports.show', compact(
            'reports', 'teacher', 'group', 'date', 'exportParams'
        ));
    }

    /**
     * Export a single (teacher, group?, week) detail as PDF — used by the "eye" button in the modal.
     * Accepts ?week= (new) or legacy ?date=.
     */
    public function exportSinglePdf(Request $request)
    {
        $data = $request->validate([
            'week'       => 'nullable|date',
            'date'       => 'nullable|date',
            'teacher_id' => 'required|exists:teachers,id',
            'group_id'   => 'nullable|integer|exists:groups,id',
        ]);

        $anchor = $data['week'] ?? $data['date'] ?? now()->toDateString();
        $monday = Carbon::parse($anchor)->startOfWeek(Carbon::MONDAY);
        $friday = $monday->copy()->addDays(4);

        $reports = WeeklyReport::with(['teacher', 'group', 'attachments'])
            ->whereBetween('report_date', [$monday, $friday])
            ->where('teacher_id', $data['teacher_id'])
            ->when(true, function ($q) use ($data) {
                $gid = $data['group_id'] ?? null;
                if ($gid === null || $gid === '') {
                    $q->whereNull('group_id');
                } else {
                    $q->where('group_id', $gid);
                }
            })
            ->orderBy('report_date')
            ->orderBy('skill')
            ->orderBy('created_at')
            ->get();

        if ($reports->isEmpty()) {
            return back()->with('error', 'Aucun rapport trouvé pour cette sélection.');
        }

        $teacher = $reports->first()->teacher;
        $group   = $reports->first()->group;
        $date    = $monday;

        $pdf = Pdf::loadView('backoffice.pdf.weekly-report-single', compact(
            'reports', 'teacher', 'group', 'date'
        ))
            ->setPaper('A4', 'portrait')
            ->setOption('isHtml5ParserEnabled', true)
            ->setOption('isPhpEnabled', true);

        $slug = str()->slug($teacher->name) . ($group ? '_' . str()->slug($group->name) : '');
        $filename = 'rapport_semaine_' . $slug . '_' . $monday->format('Y-m-d') . '_' . $friday->format('Y-m-d') . '.pdf';

        return $pdf->download($filename);
    }

    /**
     * Return all reports for a full week (Mon–Fri) — used by the per-week modal.
     * Accepts ?week=YYYY-MM-DD (any date in the week; normalised to Monday).
     */
    public function forWeek(Request $request)
    {
        $request->validate(['week' => 'required|date']);

        $monday = Carbon::parse($request->week)->startOfWeek(Carbon::MONDAY);
        $friday = $monday->copy()->addDays(4);

        $reportsQuery = WeeklyReport::with(['teacher', 'group', 'attachments'])
            ->whereBetween('report_date', [$monday, $friday])
            ->orderBy('report_date')
            ->orderBy('created_at');

        // Non-admins: only reports for teachers tied to their accessible centres.
        $allowedSiteIds = $this->accessibleSiteIds();
        if ($allowedSiteIds !== null) {
            if (empty($allowedSiteIds)) {
                $reportsQuery->whereRaw('1 = 0');
            } else {
                $reportsQuery->whereHas('teacher', fn ($q) => $q->whereIn('site_id', $allowedSiteIds));
            }
        }

        $reports = $reportsQuery->get()
            ->map(fn ($r) => [
                'id'              => $r->id,
                'teacher_id'      => $r->teacher_id,
                'group_id'        => $r->group_id,
                'group_label'     => $r->group?->name,
                'skill'           => $r->skill,
                'notes'           => $r->notes,
                'report_date'     => $r->report_date->format('Y-m-d'),
                'attachment_url'  => $r->attachment_url,
                'attachment_name' => $r->attachment_original_name,
                'attachments'     => $r->attachments->map(fn ($a) => [
                    'id'   => $a->id,
                    'url'  => $a->url,
                    'name' => $a->original_name ?: 'PDF',
                ])->values(),
            ]);

        return response()->json([
            'reports' => $reports,
            'friday'  => $friday->format('Y-m-d'),
        ]);
    }

    /**
     * Return all reports for a single day (used by the multi-row modal).
     */
    public function forDay(Request $request)
    {
        $request->validate(['date' => 'required|date']);

        $reportsQuery = WeeklyReport::with(['teacher', 'group', 'attachments'])
            ->whereDate('report_date', $request->date)
            ->orderBy('created_at');

        $allowedSiteIds = $this->accessibleSiteIds();
        if ($allowedSiteIds !== null) {
            if (empty($allowedSiteIds)) {
                $reportsQuery->whereRaw('1 = 0');
            } else {
                $reportsQuery->whereHas('teacher', fn ($q) => $q->whereIn('site_id', $allowedSiteIds));
            }
        }

        $reports = $reportsQuery->get()
            ->map(fn ($r) => [
                'id'              => $r->id,
                'teacher_id'      => $r->teacher_id,
                'group_id'        => $r->group_id,
                'group_label'     => $r->group?->name,
                'skill'           => $r->skill,
                'notes'           => $r->notes,
                // Legacy single-file (kept for back-compat with old data not yet migrated)
                'attachment_url'  => $r->attachment_url,
                'attachment_name' => $r->attachment_original_name,
                // New multi-file array
                'attachments'     => $r->attachments->map(fn ($a) => [
                    'id'   => $a->id,
                    'url'  => $a->url,
                    'name' => $a->original_name ?: 'PDF',
                ])->values(),
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
        // Detect when POST exceeds php.ini limits (silent payload truncation)
        if (empty($_POST) && empty($_FILES) && $request->server('CONTENT_LENGTH') > 0) {
            $maxPost = ini_get('post_max_size');
            return back()
                ->withErrors(['rows' => "La taille des données envoyées dépasse la limite du serveur (post_max_size = {$maxPost}). Réduisez la taille des PDFs ou contactez l'administrateur."])
                ->withInput();
        }

        $data = $request->validate([
            'report_date'                       => 'required|date',
            'scope_teacher_id'                  => 'nullable|integer|exists:teachers,id',
            'scope_group_id'                    => 'nullable|integer|exists:groups,id',
            'rows'                              => 'array',
            'rows.*.id'                         => 'nullable|integer|exists:weekly_reports,id',
            'rows.*.teacher_id'                 => 'required|exists:teachers,id',
            'rows.*.group_id'                   => 'nullable|integer|exists:groups,id',
            'rows.*.skill'                      => 'nullable|string|in:' . implode(',', array_keys(WeeklyReport::SKILLS)),
            'rows.*.notes'                      => 'required|string|max:5000',
            // Legacy single-file fields (kept for backward compat with old payloads)
            'rows.*.remove_attachment'          => 'nullable|boolean',
            'rows.*.attachment'                 => 'nullable|file|mimes:pdf|max:10240',
            // New multi-file fields
            'rows.*.attachments'                => 'nullable|array',
            'rows.*.attachments.*'              => 'file|mimes:pdf|max:10240',
            'rows.*.remove_attachment_ids'      => 'nullable|array',
            'rows.*.remove_attachment_ids.*'    => 'integer|exists:weekly_report_attachments,id',
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

        try {
            $rows = $data['rows'] ?? [];
            $reportDate = $data['report_date'];
            // Normalise to the Mon–Fri range of this week so we can find records
            // regardless of which day they were originally saved on.
            $weekMonday = Carbon::parse($reportDate)->startOfWeek(Carbon::MONDAY);
            $weekFriday = $weekMonday->copy()->addDays(4);
            $keepIds = [];

            foreach ($rows as $row) {
                if (!empty($row['id'])) {
                    $report = WeeklyReport::where('id', $row['id'])
                        ->whereBetween('report_date', [$weekMonday, $weekFriday])
                        ->first();

                    if (!$report) continue;

                    $report->teacher_id = $row['teacher_id'];
                    $report->group_id = $row['group_id'] ?? null;
                    $report->skill = $row['skill'] ?? null;
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

                    // Multi-file: remove specific existing attachments
                    if (!empty($row['remove_attachment_ids'])) {
                        $toRemove = WeeklyReportAttachment::where('weekly_report_id', $report->id)
                            ->whereIn('id', $row['remove_attachment_ids'])
                            ->get();
                        foreach ($toRemove as $att) {
                            Storage::disk('public')->delete($att->path);
                            $att->delete();
                        }
                    }

                    // Multi-file: add newly uploaded files
                    if (!empty($row['attachments']) && is_array($row['attachments'])) {
                        foreach ($row['attachments'] as $file) {
                            if (!$file) continue;
                            WeeklyReportAttachment::create([
                                'weekly_report_id' => $report->id,
                                'path'             => $file->store('weekly-reports', 'public'),
                                'original_name'    => $file->getClientOriginalName(),
                            ]);
                        }
                    }

                    $keepIds[] = $report->id;
                } else {
                    $payload = [
                        'teacher_id'  => $row['teacher_id'],
                        'group_id'    => $row['group_id'] ?? null,
                        'skill'       => $row['skill'] ?? null,
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

                    // Multi-file: attach all uploaded files to the new report
                    if (!empty($row['attachments']) && is_array($row['attachments'])) {
                        foreach ($row['attachments'] as $file) {
                            if (!$file) continue;
                            WeeklyReportAttachment::create([
                                'weekly_report_id' => $created->id,
                                'path'             => $file->store('weekly-reports', 'public'),
                                'original_name'    => $file->getClientOriginalName(),
                            ]);
                        }
                    }

                    $keepIds[] = $created->id;
                }
            }

            // Delete reports removed in the modal. When the modal was opened scoped to a
            // single teacher/group (e.g. clicking a chip), only clean up within that scope —
            // otherwise this would wipe out every other teacher's reports for the week.
            $scopeTeacherId = $data['scope_teacher_id'] ?? null;
            $hasGroupScope = array_key_exists('scope_group_id', $data);
            $scopeGroupId = $data['scope_group_id'] ?? null;

            $toDelete = WeeklyReport::whereBetween('report_date', [$weekMonday, $weekFriday])
                ->when($scopeTeacherId, function ($q) use ($scopeTeacherId, $hasGroupScope, $scopeGroupId) {
                    $q->where('teacher_id', $scopeTeacherId);
                    if ($hasGroupScope) {
                        $scopeGroupId ? $q->where('group_id', $scopeGroupId) : $q->whereNull('group_id');
                    }
                })
                ->when(!empty($keepIds), fn ($q) => $q->whereNotIn('id', $keepIds))
                ->get();

            foreach ($toDelete as $report) {
                if ($report->attachment_path) {
                    Storage::disk('public')->delete($report->attachment_path);
                }
                // Delete files for multi-attachments (FK cascade removes DB rows but not files)
                foreach ($report->attachments as $att) {
                    Storage::disk('public')->delete($att->path);
                }
                $report->delete();
            }
        } catch (\Throwable $e) {
            \Log::error('WeeklyReport batchSync failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return back()
                ->withErrors(['rows' => "Erreur lors de l'enregistrement : " . $e->getMessage()])
                ->withInput();
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

        $reportsQuery = WeeklyReport::with(['teacher', 'group'])
            ->withCount('attachments')
            ->whereBetween('report_date', [$request->start, $request->end])
            ->orderBy('created_at');

        $allowedSiteIds = $this->accessibleSiteIds();
        if ($allowedSiteIds !== null) {
            if (empty($allowedSiteIds)) {
                $reportsQuery->whereRaw('1 = 0');
            } else {
                $reportsQuery->whereHas('teacher', fn ($q) => $q->whereIn('site_id', $allowedSiteIds));
            }
        }

        $reports = $reportsQuery->get()
            ->map(fn ($r) => [
                'id'                => $r->id,
                'teacher_id'        => $r->teacher_id,
                'teacher_name'      => $r->teacher->name,
                'group_id'          => $r->group_id,
                'group_name'        => $r->group?->name,
                'report_date'       => $r->report_date->format('Y-m-d'),
                'notes'             => $r->notes,
                // Calendar uses attachment_url just to display a 📎 icon. Surface true
                // whenever the report has any attached file (legacy single OR multi).
                'attachment_url'    => $r->attachment_url ?: ($r->attachments_count > 0 ? '#' : null),
                'attachment_name'   => $r->attachment_original_name,
                'attachments_count' => $r->attachments_count,
            ]);

        return response()->json($reports);
    }
}
