@extends('layouts.main')

@section('title', 'Détail Rapport — ' . $teacher->name)
@section('breadcrumb-item', 'Rapport Semaine')
@section('breadcrumb-item-active', 'Détail')

@section('css')
<style>
    .detail-page-header {
        background: linear-gradient(135deg, #1e3a5f 0%, #2d4d7a 100%);
        color: #fff !important;
        padding: 22px 28px;
        border-radius: 10px 10px 0 0;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        flex-wrap: wrap;
    }
    .detail-page-header .title-block { flex: 1 1 auto; min-width: 0; }
    .detail-page-header * { color: #fff !important; }
    .detail-page-header h2 {
        margin: 0 0 6px;
        font-size: 1.4rem;
        font-weight: 700;
        color: #fff !important;
        line-height: 1.2;
    }
    .detail-page-header h2 i { color: rgba(255, 255, 255, 0.85) !important; }
    .detail-page-header .meta {
        font-size: .95rem;
        opacity: .9;
        text-transform: capitalize;
        color: rgba(255, 255, 255, 0.92) !important;
    }
    .detail-page-header .actions {
        display: flex;
        gap: 8px;
        flex-shrink: 0;
    }
    .detail-page-header .actions .btn {
        font-size: .82rem;
        padding: 7px 14px;
        white-space: nowrap;
    }
    /* Action buttons stay readable */
    .detail-page-header .actions .btn-light { color: #1e3a5f !important; }
    .detail-page-header .actions .btn-light i { color: #1e3a5f !important; }
    .detail-page-header .actions .btn-danger,
    .detail-page-header .actions .btn-danger i { color: #fff !important; }

    /* Mobile: stack title above buttons, full-width buttons */
    @media (max-width: 575.98px) {
        .detail-page-header {
            padding: 16px 18px;
            flex-direction: column;
            align-items: stretch;
        }
        .detail-page-header h2 { font-size: 1.15rem; }
        .detail-page-header .meta { font-size: .82rem; }
        .detail-page-header .actions { width: 100%; }
        .detail-page-header .actions .btn { flex: 1 1 0; padding: 9px 12px; }
    }

    .info-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 12px;
        padding: 18px 28px;
        background: #f8fafc;
        border-bottom: 1px solid #e2e8f0;
    }
    @media (max-width: 575.98px) {
        .info-grid {
            grid-template-columns: 1fr 1fr;
            gap: 8px;
            padding: 14px 16px;
        }
        .info-grid .info-item { padding: 8px 10px; }
        .info-grid .info-value { font-size: .9rem; }
    }
    .info-grid .info-item {
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        padding: 10px 14px;
    }
    .info-grid .info-label {
        font-size: .7rem;
        text-transform: uppercase;
        letter-spacing: .4px;
        color: #6c757d;
        font-weight: 600;
        margin-bottom: 3px;
    }
    .info-grid .info-value {
        font-size: 1rem;
        color: #1e3a5f;
        font-weight: 600;
    }
    .info-grid .info-value .gpill {
        display: inline-block;
        background: #fff3cd;
        color: #856404;
        padding: 3px 10px;
        border-radius: 4px;
        font-size: .82rem;
    }

    .detail-body {
        padding: 22px 28px 28px;
    }
    @media (max-width: 575.98px) {
        .detail-body { padding: 16px 14px 20px; }
    }

    .skills-table {
        width: 100%;
        border-collapse: collapse;
        font-size: .9rem;
    }
    .skills-table th {
        background: #1e3a5f;
        color: #fff;
        padding: 10px 14px;
        font-size: .78rem;
        text-transform: uppercase;
        letter-spacing: .5px;
        text-align: left;
    }
    .skills-table td {
        border: 1px solid #e9ecef;
        padding: 12px 14px;
        vertical-align: top;
    }
    .skills-table tr:nth-child(even) td { background: #fafbfc; }
    .skills-table .skill-cell { width: 160px; }
    @media (max-width: 575.98px) {
        .skills-table { font-size: .85rem; }
        .skills-table th { padding: 8px 10px; font-size: .7rem; }
        .skills-table td { padding: 10px; }
        .skills-table .skill-cell { width: 90px; }
        .skill-pill { font-size: .65rem; padding: 4px 8px; }
    }
    .skill-pill {
        display: inline-block;
        background: #1e3a5f;
        color: #fff;
        font-size: .72rem;
        font-weight: 700;
        padding: 5px 12px;
        border-radius: 5px;
        text-transform: uppercase;
        letter-spacing: .4px;
    }
    .skills-table .empty-skill { color: #adb5bd; font-style: italic; }
    .notes-text {
        white-space: pre-wrap;
        word-break: break-word;
        line-height: 1.5;
        color: #333;
    }
    .pdf-attached {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        margin-top: 10px;
        padding: 6px 12px;
        background: #fff5f5;
        border: 1px solid #fed7d7;
        border-radius: 6px;
        color: #c53030;
        text-decoration: none;
        font-size: .82rem;
        font-weight: 500;
    }
    .pdf-attached:hover { background: #fed7d7; color: #9b2c2c; text-decoration: none; }
    .pdf-attached i { font-size: 1.05rem; }

    .freeform-block {
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        padding: 16px 20px;
        margin-bottom: 12px;
    }

    .empty-state {
        text-align: center;
        padding: 40px 20px;
        color: #adb5bd;
    }
    .empty-state i { font-size: 3rem; opacity: .4; }
</style>
@endsection

@section('content')

<div class="row">
    <div class="col-12">
        <div class="card border-0 shadow-sm overflow-hidden">

            {{-- Header --}}
            <div class="detail-page-header">
                <div class="title-block">
                    <h2><i class="ph-duotone ph-calendar-check me-2"></i> Rapport de la Semaine</h2>
                    <div class="meta">
                        {{ ucfirst($date->locale('fr')->isoFormat('D MMM')) }}
                        &mdash;
                        {{ ucfirst($date->copy()->addDays(4)->locale('fr')->isoFormat('D MMMM YYYY')) }}
                    </div>
                </div>
                <div class="actions">
                    <a href="{{ url()->previous() }}" class="btn btn-light btn-sm">
                        <i class="ph-duotone ph-arrow-left me-1"></i> Retour
                    </a>
                    <a href="{{ route('backoffice.weekly_reports.export_single_pdf', $exportParams) }}"
                       class="btn btn-danger btn-sm" target="_blank">
                        <i class="ph-duotone ph-file-pdf me-1"></i> Exporter PDF
                    </a>
                </div>
            </div>

            {{-- Info grid --}}
            <div class="info-grid">
                <div class="info-item">
                    <div class="info-label">Enseignant</div>
                    <div class="info-value">{{ $teacher->name }}</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Groupe</div>
                    <div class="info-value">
                        @if ($group)
                            <span class="gpill">{{ $group->name }}</span>
                        @else
                            <em class="text-muted" style="font-weight:normal; font-size:.85rem;">Aucun groupe / général</em>
                        @endif
                    </div>
                </div>
                <div class="info-item">
                    <div class="info-label">Semaine</div>
                    <div class="info-value" style="font-size:.85rem;">
                        {{ $date->format('d/m') }} – {{ $date->copy()->addDays(4)->format('d/m/Y') }}
                    </div>
                </div>
                <div class="info-item">
                    <div class="info-label">Entrées</div>
                    <div class="info-value">{{ $reports->count() }}</div>
                </div>
            </div>

            {{-- Body --}}
            <div class="detail-body">
                @php
                    $hasSkills = $reports->whereNotNull('skill')->isNotEmpty();
                    $bySkill = $reports->keyBy('skill');
                @endphp

                @if ($hasSkills)
                    <table class="skills-table">
                        <thead>
                            <tr>
                                <th class="skill-cell">Compétence</th>
                                <th>Activité / Contenu</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach (\App\Models\WeeklyReport::SKILLS as $skillKey => $skillLabel)
                                @php $report = $bySkill->get($skillKey); @endphp
                                <tr>
                                    <td class="skill-cell">
                                        <span class="skill-pill">{{ $skillLabel }}</span>
                                    </td>
                                    <td>
                                        @if ($report && trim($report->notes) !== '')
                                            <div class="notes-text">{{ $report->notes }}</div>
                                            @if ($report->attachment_path)
                                                <a href="{{ $report->attachment_url }}" target="_blank" rel="noopener" class="pdf-attached">
                                                    <i class="ph-duotone ph-file-pdf"></i>
                                                    {{ $report->attachment_original_name ?? 'document.pdf' }}
                                                </a>
                                            @endif
                                            @foreach ($report->attachments as $att)
                                                <a href="{{ $att->url }}" target="_blank" rel="noopener" class="pdf-attached">
                                                    <i class="ph-duotone ph-file-pdf"></i>
                                                    {{ $att->original_name ?? 'document.pdf' }}
                                                </a>
                                            @endforeach
                                        @else
                                            <span class="empty-skill">— Aucune activité enregistrée pour cette compétence —</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @elseif ($reports->isNotEmpty())
                    @foreach ($reports as $report)
                        <div class="freeform-block">
                            <div class="notes-text">{{ $report->notes }}</div>
                            @if ($report->attachment_path)
                                <a href="{{ $report->attachment_url }}" target="_blank" rel="noopener" class="pdf-attached">
                                    <i class="ph-duotone ph-file-pdf"></i>
                                    {{ $report->attachment_original_name ?? 'document.pdf' }}
                                </a>
                            @endif
                            @foreach ($report->attachments as $att)
                                <a href="{{ $att->url }}" target="_blank" rel="noopener" class="pdf-attached">
                                    <i class="ph-duotone ph-file-pdf"></i>
                                    {{ $att->original_name ?? 'document.pdf' }}
                                </a>
                            @endforeach
                        </div>
                    @endforeach
                @else
                    <div class="empty-state">
                        <i class="ph-duotone ph-folder-open"></i>
                        <p class="mt-2">Aucun rapport pour cette sélection.</p>
                    </div>
                @endif
            </div>

        </div>
    </div>
</div>

@endsection
