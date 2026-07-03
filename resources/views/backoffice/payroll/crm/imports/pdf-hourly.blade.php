<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 10px; color: #1a1a2e; background: #fff; }
        .page { padding: 22px 26px; }

        .header { display: table; width: 100%; border-bottom: 3px solid #1a3a6b; padding-bottom: 12px; margin-bottom: 18px; }
        .header-left { display: table-cell; vertical-align: middle; width: 120px; }
        .header-mid { display: table-cell; vertical-align: middle; text-align: center; }
        .header-right { display: table-cell; vertical-align: middle; text-align: right; width: 150px; }
        .logo { width: 95px; }
        .doc-title { font-size: 16px; font-weight: 700; color: #1a3a6b; letter-spacing: .04em; text-transform: uppercase; }
        .doc-subtitle { font-size: 10px; color: #555; margin-top: 3px; }
        .badge-crm { display: inline-block; background: #1a3a6b; color: #fff; font-size: 8px; padding: 2px 8px; border-radius: 10px; letter-spacing: .05em; font-weight: 700; }
        .badge-v { display: inline-block; background: #e8ecf5; color: #1a3a6b; font-size: 8px; padding: 2px 8px; border-radius: 10px; font-weight: 700; margin-left: 3px; }
        .badge-status { display: inline-block; color: #fff; font-size: 8px; padding: 2px 8px; border-radius: 10px; font-weight: 700; margin-left: 3px; }

        .meta-grid { display: table; width: 100%; margin-bottom: 18px; border-collapse: collapse; }
        .meta-block { display: table-cell; width: 33.33%; background: #f4f6fb; border: 1px solid #dde3f0; padding: 9px 12px; vertical-align: top; }
        .meta-block + .meta-block { border-left: none; }
        .meta-label { font-size: 8px; color: #888; text-transform: uppercase; letter-spacing: .05em; margin-bottom: 3px; }
        .meta-value { font-size: 12px; font-weight: 700; color: #1a3a6b; }
        .meta-sub { font-size: 8.5px; color: #666; margin-top: 2px; }

        .section-title { font-size: 9px; font-weight: 700; color: #1a3a6b; text-transform: uppercase; letter-spacing: .06em; border-left: 3px solid #1a3a6b; padding-left: 7px; margin: 18px 0 8px; }

        table.calc { width: 60%; border-collapse: collapse; font-size: 11px; }
        table.calc td { padding: 8px 12px; border-bottom: 1px solid #e5e9f2; }
        table.calc td.lbl { color: #555; }
        table.calc td.val { text-align: right; font-weight: 700; color: #1a1a2e; }
        table.calc tr.total td { border-top: 2px solid #1a3a6b; border-bottom: none; font-size: 14px; color: #1a3a6b; padding-top: 10px; }

        .pay-info { margin-top: 18px; border: 1px solid #dde3f0; background: #f4f6fb; padding: 10px 12px; display: table; width: 100%; }
        .pay-info .pi { display: table-cell; width: 25%; font-size: 9px; }
        .pi-label { color: #888; text-transform: uppercase; font-size: 7px; letter-spacing: .04em; }
        .pi-value { font-weight: 700; color: #1a3a6b; }

        .signature-block { margin-top: 40px; display: table; width: 100%; }
        .sig-left, .sig-right { display: table-cell; width: 50%; }
        .sig-right { text-align: right; }
        .sig-line { border-top: 1px solid #bbb; width: 180px; margin-top: 30px; padding-top: 5px; font-size: 8px; color: #888; }
        .sig-right .sig-line { margin-left: auto; }
        .page-footer { border-top: 2px solid #1a3a6b; margin-top: 24px; padding-top: 8px; display: table; width: 100%; }
        .footer-left { display: table-cell; font-size: 8px; color: #888; vertical-align: bottom; }
        .footer-right { display: table-cell; text-align: right; font-size: 8px; color: #888; vertical-align: bottom; }
    </style>
</head>

<body>
    <div class="page">

        <div class="header">
            <div class="header-left"><img src="data:image/png;base64,{{ $logoBase64 }}" class="logo" alt="GLS"></div>
            <div class="header-mid">
                <div class="doc-title">Fiche de Paiement Professeur</div>
                <div class="doc-subtitle">GLS Sprachenzentrum — Paiement par heures</div>
                <div style="margin-top:6px">
                    <span class="badge-crm">HORAIRE</span>
                    <span class="badge-v">Version {{ $import->version }}</span>
                    <span class="badge-status" style="background:{{ $statusColor }}">{{ strtoupper($statusLabel) }}</span>
                </div>
            </div>
            <div class="header-right">
                <div style="font-size:8px; color:#888">Généré le</div>
                <div style="font-size:10px; font-weight:700; color:#1a3a6b">{{ now()->format('d/m/Y à H:i') }}</div>
                <div style="font-size:8px; color:#888; margin-top:4px">Réf. {{ strtoupper(str($group->name)->slug()) }}-V{{ $import->version }}</div>
            </div>
        </div>

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
                <div class="meta-label">Mois rattaché @if($import->group_month_number)· Mois {{ $import->group_month_number }}@endif</div>
                <div class="meta-value">
                    @if($import->attached_month && $import->attached_year)
                        {{ \Carbon\Carbon::create()->month($import->attached_month)->locale('fr')->isoFormat('MMMM') }} {{ $import->attached_year }}
                    @else — @endif
                </div>
                @if($import->month_label)<div class="meta-sub">{{ $import->month_label }}</div>@endif
            </div>
        </div>

        <div class="section-title">Détail du calcul</div>
        <table class="calc">
            <tr><td class="lbl">Taux horaire</td><td class="val">{{ number_format((float)$import->hourly_rate, 2) }} DH</td></tr>
            <tr><td class="lbl">Total heures</td><td class="val">{{ number_format((float)$import->total_hours, 2) }} h</td></tr>
            <tr class="total"><td class="lbl" style="font-weight:700">Total final
                ({{ number_format((float)$import->hourly_rate, 2) }} × {{ number_format((float)$import->total_hours, 2) }})</td>
                <td class="val">{{ number_format((float)$import->final_total, 2) }} DH</td></tr>
        </table>

        @if($import->payment_date)
            <div class="pay-info">
                <div class="pi"><div class="pi-label">Date de paiement</div><div class="pi-value">{{ $import->payment_date->format('d/m/Y') }}</div></div>
                <div class="pi"><div class="pi-label">Mode</div><div class="pi-value">{{ $import->paymentMethodLabel() ?? '—' }}</div></div>
                <div class="pi"><div class="pi-label">Référence</div><div class="pi-value">{{ $import->payment_reference ?: '—' }}</div></div>
                <div class="pi"><div class="pi-label">Statut</div><div class="pi-value">{{ $statusLabel }}</div></div>
            </div>
        @endif

        @if($import->notes)
            <div style="margin-top:14px; font-size:9px; color:#555"><strong>Notes :</strong> {{ $import->notes }}</div>
        @endif

        <div class="signature-block">
            <div class="sig-left"><div class="sig-line">Signature du Professeur</div></div>
            <div class="sig-right"><div class="sig-line">Cachet et Signature GLS</div></div>
        </div>

        <div class="page-footer">
            <div class="footer-left">GLS Sprachenzentrum — Document généré automatiquement · Non modifiable</div>
            <div class="footer-right">www.gls-sprachzentrum.ma · contact@glszentrum.com</div>
        </div>

    </div>
</body>

</html>
