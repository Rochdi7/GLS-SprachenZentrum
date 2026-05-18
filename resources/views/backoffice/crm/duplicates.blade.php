@extends('layouts.main')

@section('title', 'CRM — Doublons')
@section('breadcrumb-item', 'CRM')
@section('breadcrumb-item-active', 'Détection de doublons')

@section('content')
    @include('backoffice.crm.partials._center')

    @php
        $summary = $report['summary'] ?? [];
        $groups  = $report['groups']  ?? [];

        $sections = [
            'phone'    => ['label' => 'Téléphone',        'icon' => 'ti-phone',           'color' => 'danger', 'hint' => 'Deux étudiants partagent exactement le même numéro de téléphone (probablement la même personne ou un parent ré-inscrit).'],
            'whatsapp' => ['label' => 'WhatsApp',         'icon' => 'ti-brand-whatsapp',  'color' => 'success','hint' => 'Numéro WhatsApp commun (hors téléphone principal). Souvent un parent inscrit plusieurs enfants.'],
            'email'    => ['label' => 'Email',            'icon' => 'ti-mail',            'color' => 'primary','hint' => 'Deux fiches avec la même adresse e-mail.'],
            'cin'      => ['label' => 'CIN',              'icon' => 'ti-id',              'color' => 'warning','hint' => 'Deux fiches portent le même numéro CIN — vrai doublon d\'identité.'],
            'name'     => ['label' => 'Nom complet',      'icon' => 'ti-user-search',     'color' => 'secondary','hint' => 'Même prénom + nom dans le même centre. Vérifier que ce n\'est pas la même personne ré-inscrite.'],
        ];

        $totalGroups = array_sum(array_map(fn ($k) => count($groups[$k] ?? []), array_keys($sections)));
    @endphp

    {{-- Top header --}}
    <div class="card mb-3 border-0 shadow-sm">
        <div class="card-body">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                <div>
                    <h5 class="mb-1">
                        <i class="ti ti-copy-check text-warning me-1"></i>
                        Détection de doublons
                    </h5>
                    <p class="text-muted small mb-0">
                        {{ number_format($report['scanned'] ?? 0, 0, ',', ' ') }} fiches étudiant analysées
                        @if(!empty($report['centers'])) sur {{ $report['centers'] }} centre(s) @endif
                        — dernière analyse le {{ $report['scanned_at'] ?? '?' }}
                    </p>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('backoffice.crm.duplicates', ['refresh' => 1]) }}" class="btn btn-sm btn-outline-secondary">
                        <i class="ti ti-refresh me-1"></i> Rafraîchir
                    </a>
                </div>
            </div>

            @if(!empty($report['error']))
                <div class="alert alert-warning small mt-3 mb-0">
                    <i class="ti ti-alert-triangle me-1"></i>
                    L'analyse s'est arrêtée prématurément : {{ $report['error'] }}
                </div>
            @endif
        </div>
    </div>

    {{-- KPI strip --}}
    <div class="row g-3 mb-3">
        @php
            $kpis = [
                ['Groupes de doublons', $totalGroups, 'ti-copy', 'warning', "Toutes catégories confondues"],
                ['Avec téléphone',      $summary['with_phone'] ?? 0, 'ti-phone', 'primary', "Fiches qui peuvent être comparées par téléphone"],
                ['Avec email',          $summary['with_email'] ?? 0, 'ti-mail',  'info',    "Fiches qui peuvent être comparées par email"],
                ['Avec CIN',            $summary['with_cin']   ?? 0, 'ti-id',    'success', "Fiches identifiables par CIN"],
            ];
        @endphp
        @foreach($kpis as [$label, $value, $icon, $color, $hint])
            <div class="col-12 col-sm-6 col-lg-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body py-3">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <p class="text-muted small mb-1">{{ $label }}</p>
                                <h4 class="mb-0">{{ number_format($value, 0, ',', ' ') }}</h4>
                            </div>
                            <span class="avtar avtar-l bg-light-{{ $color }} rounded">
                                <i class="ti {{ $icon }} f-24 text-{{ $color }}"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    @if($totalGroups === 0 && empty($report['error']))
        <div class="alert alert-success">
            <i class="ti ti-circle-check me-1"></i>
            Aucun doublon détecté pour le centre sélectionné. 🎉
        </div>
    @endif

    {{-- Per-section duplicate groups --}}
    @foreach($sections as $key => $cfg)
        @php $groupList = $groups[$key] ?? []; @endphp
        @if(!empty($groupList))
            <div class="card mb-3 border-0 shadow-sm">
                <div class="card-header bg-light d-flex align-items-center justify-content-between">
                    <h6 class="mb-0">
                        <i class="ti {{ $cfg['icon'] }} text-{{ $cfg['color'] }} me-1"></i>
                        Doublons par {{ $cfg['label'] }}
                        <span class="badge bg-{{ $cfg['color'] }} ms-2">{{ count($groupList) }}</span>
                    </h6>
                </div>
                <div class="card-body">
                    <p class="small text-muted mb-3">{{ $cfg['hint'] }}</p>

                    @foreach($groupList as $group)
                        <div class="border rounded p-2 mb-2">
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <div>
                                    <span class="badge bg-light-{{ $cfg['color'] }} text-{{ $cfg['color'] }}">
                                        <i class="ti {{ $cfg['icon'] }} me-1"></i>
                                        @if($key === 'name')
                                            {{-- name key looks like "50970|firstname lastname" --}}
                                            {{ ucwords(explode('|', $group['key'])[1] ?? $group['key']) }}
                                        @else
                                            {{ $group['key'] }}
                                        @endif
                                    </span>
                                    <span class="small text-muted ms-2">{{ $group['count'] }} fiches partagent cette valeur</span>
                                </div>
                            </div>

                            <div class="table-responsive">
                                <table class="table table-sm align-middle mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th class="small">Référence</th>
                                            <th class="small">Nom complet</th>
                                            <th class="small">Centre</th>
                                            <th class="small">Téléphone</th>
                                            <th class="small">Email</th>
                                            <th class="small">CIN</th>
                                            <th class="small">Créé le</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($group['students'] as $s)
                                            @php
                                                $centerName = app(\App\Services\Crm\CenterContext::class)
                                                    ->nameForStoreId($s['STR_STORE_ID'] ?? null);
                                                $created = $s['DATE_CREATION'] ?? null;
                                                try { $createdF = $created ? \Carbon\Carbon::parse($created)->format('Y-m-d') : '—'; } catch (\Throwable $e) { $createdF = $created ?: '—'; }
                                            @endphp
                                            <tr>
                                                <td><span class="badge bg-light-secondary text-muted">{{ $s['REFERENCE'] ?? '—' }}</span></td>
                                                <td>{{ trim(($s['FIRST_NAME'] ?? '') . ' ' . ($s['LAST_NAME'] ?? '')) ?: '—' }}</td>
                                                <td>
                                                    @if($centerName)
                                                        <span class="badge bg-light-primary text-primary">
                                                            <i class="ti ti-building me-1"></i>{{ $centerName }}
                                                        </span>
                                                    @else
                                                        <span class="text-muted">#{{ $s['STR_STORE_ID'] ?? '—' }}</span>
                                                    @endif
                                                </td>
                                                <td class="small">{{ $s['PHONE_NUMBER'] ?? '—' }}</td>
                                                <td class="small">{{ $s['EMAIL'] ?? '—' }}</td>
                                                <td class="small">{{ $s['CNE'] ?? $s['IDENTITY_ID'] ?? '—' }}</td>
                                                <td class="small text-muted">{{ $createdF }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    @endforeach
@endsection
