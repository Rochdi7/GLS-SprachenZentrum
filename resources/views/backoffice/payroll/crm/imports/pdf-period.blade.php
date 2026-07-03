<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 9px; color: #1a1a2e; background: #fff; }
        .page { padding: 18px 20px; }

        /* HEADER */
        .header { display: table; width: 100%; border-bottom: 3px solid #1a3a6b; padding-bottom: 10px; margin-bottom: 14px; }
        .header-left { display: table-cell; vertical-align: middle; width: 120px; }
        .header-mid { display: table-cell; vertical-align: middle; text-align: center; }
        .header-right { display: table-cell; vertical-align: middle; text-align: right; width: 140px; }
        .logo { width: 90px; }
        .doc-title { font-size: 15px; font-weight: 700; color: #1a3a6b; letter-spacing: .04em; text-transform: uppercase; }
        .doc-subtitle { font-size: 9px; color: #555; margin-top: 2px; }
        .badge-crm { display: inline-block; background: #1a3a6b; color: #fff; font-size: 7px; padding: 2px 7px; border-radius: 10px; letter-spacing: .05em; font-weight: 700; }
        .badge-v { display: inline-block; background: #e8ecf5; color: #1a3a6b; font-size: 7px; padding: 2px 7px; border-radius: 10px; font-weight: 700; margin-left: 3px; }
        .badge-status { display: inline-block; color: #fff; font-size: 7px; padding: 2px 7px; border-radius: 10px; font-weight: 700; margin-left: 3px; }

        /* META */
        .meta-grid { display: table; width: 100%; margin-bottom: 14px; border-collapse: collapse; }
        .meta-block { display: table-cell; width: 25%; background: #f4f6fb; border: 1px solid #dde3f0; padding: 7px 10px; vertical-align: top; }
        .meta-block + .meta-block { border-left: none; }
        .meta-label { font-size: 7px; color: #888; text-transform: uppercase; letter-spacing: .05em; margin-bottom: 3px; }
        .meta-value { font-size: 10px; font-weight: 700; color: #1a3a6b; }
        .meta-sub { font-size: 7.5px; color: #666; margin-top: 1px; }

        /* KPI */
        .kpi-row { display: table; width: 100%; margin-bottom: 14px; border-collapse: collapse; }
        .kpi-box { display: table-cell; width: 25%; text-align: center; padding: 8px 6px; border: 1.5px solid #1a3a6b; vertical-align: middle; }
        .kpi-box:nth-child(1) { background: #1a3a6b; color: #fff; }
        .kpi-box:nth-child(n+2) { background: #f0f4ff; }
        .kpi-box + .kpi-box { border-left: none; }
        .kpi-number { font-size: 15px; font-weight: 700; line-height: 1; }
        .kpi-label { font-size: 7px; text-transform: uppercase; letter-spacing: .04em; margin-top: 3px; opacity: .85; }

        .section-title { font-size: 8px; font-weight: 700; color: #1a3a6b; text-transform: uppercase; letter-spacing: .06em; border-left: 3px solid #1a3a6b; padding-left: 6px; margin-bottom: 6px; }

        /* PRESENCE TABLE */
        table.presence { width: 100%; border-collapse: collapse; font-size: 7.5px; }
        table.presence thead tr th { background: #1a3a6b; color: #fff; padding: 4px 3px; text-align: center; font-weight: 700; font-size: 7px; border: 1px solid #14305a; }
        table.presence thead tr th.th-name { text-align: left; padding-left: 6px; min-width: 110px; }
        table.presence tbody tr td { padding: 3px 3px; border: 1px solid #dde3f0; text-align: center; vertical-align: middle; }
        table.presence tbody tr td.td-name { text-align: left; padding-left: 6px; font-weight: 600; }
        table.presence tbody tr:nth-child(even) td { background: #f7f9ff; }
        .cell-p { background: #d1f5dd !important; color: #155724; font-weight: 700; }
        .cell-a { background: #fde8ea !important; color: #842029; font-weight: 700; }
        .cell-dot { background: #f4f4f4 !important; color: #bbb; }
        .tier-cell { font-size: 7px; }
        .amt { font-weight: 700; }
        table.presence tfoot tr td { background: #1a3a6b !important; color: #fff; font-weight: 700; padding: 4px 3px; text-align: center; border: 1px solid #14305a; font-size: 8px; }
        table.presence tfoot tr td.td-name { text-align: left; padding-left: 6px; }

        /* PAYMENT INFO */
        .pay-info { margin-top: 12px; border: 1px solid #dde3f0; background: #f4f6fb; padding: 8px 10px; display: table; width: 100%; }
        .pay-info .pi { display: table-cell; width: 25%; font-size: 8px; }
        .pi-label { color: #888; text-transform: uppercase; font-size: 6.5px; letter-spacing: .04em; }
        .pi-value { font-weight: 700; color: #1a3a6b; }

        /* SIGNATURE / FOOTER */
        .signature-block { margin-top: 16px; display: table; width: 100%; }
        .sig-left, .sig-right { display: table-cell; width: 50%; }
        .sig-right { text-align: right; }
        .sig-line { border-top: 1px solid #bbb; width: 160px; margin-top: 30px; padding-top: 4px; font-size: 7px; color: #888; }
        .sig-right .sig-line { margin-left: auto; }
        .page-footer { border-top: 2px solid #1a3a6b; margin-top: 14px; padding-top: 6px; display: table; width: 100%; }
        .footer-left { display: table-cell; font-size: 7px; color: #888; vertical-align: bottom; }
        .footer-right { display: table-cell; text-align: right; font-size: 7px; color: #888; vertical-align: bottom; }
    </style>
</head>

<body>
    <div class="page">

        {{-- HEADER --}}
        <div class="header">
            <div class="header-left"><img src="data:image/png;base64,{{ $logoBase64 }}" class="logo" alt="GLS"></div>
            <div class="header-mid">
                <div class="doc-title">Fiche de Paiement Professeur</div>
                <div class="doc-subtitle">GLS Sprachenzentrum — Paiement par période</div>
                <div style="margin-top:5px">
                    <span class="badge-crm">PÉRIODE</span>
                    <span class="badge-v">Version {{ $import->version }}</span>
                    <span class="badge-status" style="background:{{ $statusColor }}">{{ strtoupper($statusLabel) }}</span>
                </div>
            </div>
            <div class="header-right">
                <div style="font-size:7px; color:#888">Généré le</div>
                <div style="font-size:9px; font-weight:700; color:#1a3a6b">{{ now()->format('d/m/Y à H:i') }}</div>
                <div style="font-size:7px; color:#888; margin-top:4px">Réf. {{ strtoupper(str($group->name)->slug()) }}-V{{ $import->version }}</div>
            </div>
        </div>

        {{-- META --}}
        <div class="meta-grid">
            <div class="meta-block">
                <div class="meta-label">Professeur</div>
                <div class="meta-value">{{ $profName }}</div>
            </div>
            <div class="meta-block">
                <div class="meta-label">Groupe / Classe</div>
                <div class="meta-value">{{ $group->name }}</div>
                <div class="meta-sub">Niveau : {{ $group->level }}</div>
            </div>
            <div class="meta-block">
                <div class="meta-label">Période @if($import->group_month_number)· Mois {{ $import->group_month_number }}@endif</div>
                <div class="meta-value">
                    @if($import->attached_month && $import->attached_year)
                        {{ \Carbon\Carbon::create()->month($import->attached_month)->locale('fr')->isoFormat('MMMM') }} {{ $import->attached_year }}
                    @else
                        {{ $import->date_start->format('d/m/Y') }} → {{ $import->date_end->format('d/m/Y') }}
                    @endif
                </div>
                <div class="meta-sub">{{ $import->date_start->format('d/m/Y') }} → {{ $import->date_end->format('d/m/Y') }}</div>
            </div>
            <div class="meta-block">
                <div class="meta-label">Prix par étudiant</div>
                <div class="meta-value">{{ number_format((float)($import->base_price ?? 0), 2) }} DH</div>
                <div class="meta-sub">{{ number_format($unit, 2) }} DH / semaine équivalente</div>
            </div>
        </div>

        {{-- KPI --}}
        <div class="kpi-row">
            <div class="kpi-box">
                <div class="kpi-number">{{ number_format($grandTotal, 2) }} DH</div>
                <div class="kpi-label">Total paiement</div>
            </div>
            <div class="kpi-box">
                <div class="kpi-number" style="color:#1a3a6b">{{ $summary?->total_students ?? 0 }} / {{ $import->students->count() }}</div>
                <div class="kpi-label" style="color:#555">Étudiants payés</div>
            </div>
            <div class="kpi-box">
                <div class="kpi-number" style="color:#1a3a6b">{{ $allDates->count() }}</div>
                <div class="kpi-label" style="color:#555">Jours de cours</div>
            </div>
            <div class="kpi-box">
                <div class="kpi-number" style="color:#1a3a6b; font-size:11px">
                    {{ $summary?->count_tier_full ?? 0 }}·{{ $summary?->count_tier_partial ?? 0 }}·{{ $summary?->count_tier_zero ?? 0 }}
                </div>
                <div class="kpi-label" style="color:#555">Complet · Partiel · Zéro</div>
            </div>
        </div>

        {{-- PRESENCE + PERIOD DETAIL --}}
        <div class="section-title">Détail de présence et calcul par période</div>
        <table class="presence">
            <thead>
                <tr>
                    <th style="width:18px">#</th>
                    <th class="th-name">ÉTUDIANT</th>
                    @foreach ($allDates as $date)
                        @php $d = \Carbon\Carbon::parse($date)->locale('fr'); @endphp
                        <th style="width:16px; font-size:6px; padding:3px 1px">
                            {{ strtoupper(substr($d->isoFormat('ddd'), 0, 2)) }}<br><span style="font-weight:400">{{ $d->format('d') }}</span>
                        </th>
                    @endforeach
                    <th style="width:18px; background:#0f6b3b">P</th>
                    <th style="width:18px; background:#6b0f1a">A</th>
                    <th style="width:34px; background:#0d2d5e">PRÉS.</th>
                    <th style="width:44px; background:#0d2d5e">PALIER</th>
                    <th style="width:44px; background:#0d2d5e">AUTO</th>
                    <th style="width:44px; background:#0d2d5e">AJUST.</th>
                    <th style="width:52px; background:#0a2040">FINAL</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($import->students as $student)
                    @php
                        $byDate    = $student->records->keyBy(fn($r) => (string) $r->date);
                        $presences = (int) $student->period_presence_count;
                        $auto      = (float) $student->period_auto_amount;
                        $override  = $student->period_amount_override;
                        $effective = $student->getPeriodEffectiveAmount();
                        $tierLabel = '0 DH';
                        foreach($tiers as $t) {
                            $inRange = $presences >= (int)$t['min'] && ($t['max'] === null || $presences <= (int)$t['max']);
                            if ($inRange) { $tierLabel = $t['weeks'] === 'full' ? 'Complet' : $t['weeks'].' sem.'; break; }
                        }
                    @endphp
                    <tr>
                        <td style="color:#888; font-size:7px">{{ $loop->iteration }}</td>
                        <td class="td-name">{{ $student->student_name }}</td>
                        @foreach ($allDates as $date)
                            @php $rec = $byDate->get($date); $st = $rec?->status; @endphp
                            <td class="{{ $st === 'present' ? 'cell-p' : ($st === 'absent' ? 'cell-a' : 'cell-dot') }}" style="font-size:7px">
                                {{ $st === 'present' ? 'P' : ($st === 'absent' ? 'A' : '·') }}
                            </td>
                        @endforeach
                        <td style="color:#155724; font-weight:700">{{ $student->total_present }}</td>
                        <td style="color:#842029; font-weight:700">{{ $student->total_absent }}</td>
                        <td style="font-weight:700">{{ $presences }}</td>
                        <td class="tier-cell">{{ $tierLabel }}</td>
                        <td style="color:#666">{{ number_format($auto, 2) }}</td>
                        <td class="amt" style="color:{{ $override !== null ? '#b8860b' : '#bbb' }}">
                            {{ $override !== null ? number_format((float)$override, 2) : '—' }}
                        </td>
                        <td class="amt" style="font-size:8.5px; color:#1a3a6b">{{ number_format($effective, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <td></td>
                    <td class="td-name">TOTAL</td>
                    @foreach ($allDates as $_) <td></td> @endforeach
                    <td>—</td><td></td><td></td><td></td><td></td>
                    <td style="font-size:9px">{{ number_format($grandTotal, 2) }} DH</td>
                </tr>
            </tfoot>
        </table>

        {{-- PAYMENT INFO (when paid/locked) --}}
        @if($import->payment_date)
            <div class="pay-info">
                <div class="pi"><div class="pi-label">Date de paiement</div><div class="pi-value">{{ $import->payment_date->format('d/m/Y') }}</div></div>
                <div class="pi"><div class="pi-label">Mode</div><div class="pi-value">{{ $import->paymentMethodLabel() ?? '—' }}</div></div>
                <div class="pi"><div class="pi-label">Référence</div><div class="pi-value">{{ $import->payment_reference ?: '—' }}</div></div>
                <div class="pi"><div class="pi-label">Statut</div><div class="pi-value">{{ $statusLabel }}</div></div>
            </div>
        @endif

        {{-- SIGNATURE --}}
        <div class="signature-block">
            <div class="sig-left"><div class="sig-line">Signature du Professeur</div></div>
            <div class="sig-right"><div class="sig-line">Cachet et Signature GLS</div></div>
        </div>

        {{-- FOOTER --}}
        <div class="page-footer">
            <div class="footer-left">
                GLS Sprachenzentrum — Document généré automatiquement · Non modifiable
                @if ($import->notes)<br>Note : {{ $import->notes }}@endif
            </div>
            <div class="footer-right">www.gls-sprachzentrum.ma · contact@glszentrum.com</div>
        </div>

    </div>
</body>

</html>
