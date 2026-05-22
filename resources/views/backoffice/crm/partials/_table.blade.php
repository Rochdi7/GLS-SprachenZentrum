{{--
    Generic dynamic table for any CRM API payload.

    Expects:
      $payload : array|null   — full API response (success, data, pagination, meta)
      $error   : CrmException|null
      $rowKind : string|null  — optional. 'class' enables the rich class modal renderer.
                                If omitted we default to a generic property list.

    Rendering strategy:
      - Hide noisy / Arabic / internal columns (keep tables clean for non-tech users)
      - For known column types, render a pretty badge / formatted date / amount
      - Long JSON blobs are NOT shown inline anymore — they are summarised as
        pill counts ("En formation 17 · Archivés 5 · Annulés 8") and the full
        detail is available via the 👁️ action that opens a modal.
--}}

@php
    $rowKind = $rowKind ?? null;

    // Pretty French labels for known columns.
    $labelFor = function (string $col): string {
        $renames = [
            'STR_STORE_ID'              => 'Centre',
            'SMALL_AVATAR_PATH'         => 'Photo',
            'TNT_MODULE_ID'             => 'Module ID',
            'ID'                        => 'ID',
            'CLASS_ID'                  => 'Class ID',
            'REFERENCE'                 => 'Référence',
            'NAME'                      => 'Nom',
            'FIRST_NAME'                => 'Prénom',
            'LAST_NAME'                 => 'Nom',
            'PHONE_NUMBER'              => 'Téléphone',
            'WHATSAPP_NUMBER'           => 'WhatsApp',
            'EMAIL'                     => 'Email',
            'BIRTHDAY'                  => 'Naissance',
            'SEXE'                      => 'Sexe',
            'ACTIVE'                    => 'Actif',
            'AMOUNT'                    => 'Montant',
            'OPEN_AMOUNT'               => 'Reste',
            'TOTAL_PRICE'               => 'Total',
            'REST_AMOUNT'               => 'Reste',
            'START_DATE'                => 'Début',
            'END_DATE'                  => 'Fin',
            'EFFECTIVE_DATE'            => 'Date',
            'DATE_CREATION'             => 'Créé le',
            'DATE_UPDATE'               => 'MAJ le',
            'STUDENT_FULL_NAME'         => 'Étudiant',
            'STUDENT_ID'                => 'Student ID',
            'EMPLOYEE_TEACHER_FULL_NAME'=> 'Enseignant',
            'EMPLOYEE_TEACHER_ID'       => 'Teacher ID',
            'CLASSIFICATION_NAME'       => 'Niveau',
            'STATUS_NAME'               => 'Statut',
            'SCHOOL_LEVEL_NAME'         => 'Formation',
            'PAYMENT_METHOD_NAME'       => 'Méthode',
            'PAYMENT_TYPE_NAME'         => 'Type',
            'PAYMENTS_STATUS_NAME'      => 'Statut',
            'CATEGORY_NAME'             => 'Catégorie',
            'USER_CREATION_FULL_NAME'   => 'Créé par',
            'USER_UPDATE_FULL_NAME'     => 'MAJ par',
            'CASH_BOX_ACCOUNT_DESIGNATION' => 'Compte caisse',
            // Subscription services
            'SERVICE_TYPE_NAME'         => 'Service',
            'DUE_DATE'                  => 'Échéance',
            'SUBSCRIPTION_SERVICE_STATUS_NAME' => 'Statut service',
            'LEVEL_SESSION_PACKAGE_NAME'=> 'Package',
            // Employee calculated salary classes
            'EMPLOYEE_ID'               => 'ID employé',
            'CLASS_ID'                  => 'ID classe',
            'OPERATION_YEAR'            => 'Année',
            'OPERATION_MONTH'           => 'Mois',
            'MONTHLY_SALARY'            => 'Salaire mensuel',
            'REMAINING_AMOUNT'          => 'Reste à verser',
            'PK'                        => 'Clé',
        ];
        if (isset($renames[$col])) return $renames[$col];
        $isAr = str_ends_with($col, '_AR');
        $base = $isAr ? substr($col, 0, -3) : $col;
        $words = ucwords(strtolower(str_replace('_', ' ', $base)));
        return $isAr ? "{$words} (AR)" : $words;
    };

    // Default avatar served from public/build/ — used when the API row has no
    // SMALL_AVATAR_PATH or when the remote image fails to load.
    $defaultAvatar = asset('build/images/user/avatar-1.jpg');

    // Columns to suppress from every table (still in row data, available in modal).
    $hiddenAlways = [
        'TNT_MODULE_ID', 'USER_CREATION', 'USER_UPDATE',
        'USER_CREATION_FULL_NAME_AR', 'USER_UPDATE_FULL_NAME_AR',
        // JSON blobs — surfaced as pills + modal instead
        'LIST_STUDENT', 'LIST_STUDENT_ACTIVE', 'LIST_STUDENT_ARCHIVED', 'LIST_STUDENT_CANCELED',
        'SERVICE_LIST',
        // Counts shown as pills already
        'CLASS_COUNT_STUDENTS_ACTIVE', 'CLASS_COUNT_STUDENTS_ARCHIVED', 'CLASS_COUNT_STUDENTS_CANCLED',
        // Arabic duplicate name columns (we render only the non-AR version)
        'NAME_AR', 'FIRST_NAME_AR', 'LAST_NAME_AR',
        'SCHOOL_LEVEL_NAME_AR', 'EMPLOYEE_TEACHER_FULL_NAME_AR',
        'CLASSIFICATION_NAME_AR', 'STATUS_NAME_AR', 'CATEGORY_NAME_AR',
        'STUDENT_FULL_NAME_AR', 'TUTOR_LEGAL_FULL_NAME_AR',
        'PAYMENT_METHOD_NAME_AR', 'PAYMENT_TYPE_NAME_AR',
        'PAYMENTS_STATUS_NAME_AR', 'ITEMS_NAME_AR',
        'CASH_BOX_ACCOUNT_DESIGNATION_AR',
    ];
@endphp

{{-- Avatar styling extracted to public/assets/css/backoffice/crm-table.css.
     @once so it's not duplicated when this partial is included multiple
     times (which would be once per CRM list page). --}}
@once
    <link rel="stylesheet" href="{{ asset('assets/css/backoffice/crm-table.css') }}">
@endonce

@if($error)
    @php
        $status    = $error->status;
        $body      = $error->body ?? [];
        $errorCode = $body['errorCode'] ?? null;
        $apiMsg    = $body['message']   ?? null;
        $requestId = $body['requestId'] ?? null;
        $details   = $body['details']   ?? null;

        // Translate known errors into a human-friendly French message.
        $friendly = match (true) {
            $status === 401 || $errorCode === 'AUTH_INVALID_TOKEN'
                => ['title' => 'Token API invalide', 'detail' => 'Le token Homeschool est rejeté. Vérifiez la valeur de CRM_API_TOKEN dans la configuration.'],
            $status === 403 || $errorCode === 'AUTH_SCOPE_DENIED'
                => ['title' => 'Accès refusé', 'detail' => "Le token n'a pas le scope nécessaire pour cet endpoint. Contactez Homeschool pour étendre les permissions."],
            $status === 429
                => ['title' => 'Trop de requêtes', 'detail' => 'Limite de requêtes atteinte. Réessayez dans quelques instants.'],
            $errorCode === 'CONNECTION_ERROR' || $status === 0
                => ['title' => 'API CRM injoignable', 'detail' => 'Impossible de contacter le serveur Homeschool. Vérifiez votre connexion internet ou l\'état du service.'],
            $status === 500 && is_string($details) && str_contains($details, 'bad SQL grammar')
                => ['title' => 'Bug serveur côté Homeschool', 'detail' => "Cet endpoint contient une erreur SQL côté Homeschool, pas une erreur de configuration locale. Communiquez-leur le numéro de requête ci-dessous et ils pourront corriger leur requête."],
            $status >= 500
                => ['title' => 'Erreur serveur Homeschool', 'detail' => "L'API a renvoyé une erreur interne. Communiquez le numéro de requête à Homeschool."],
            default
                => ['title' => 'Erreur API CRM', 'detail' => $apiMsg ?: 'Une erreur inattendue s\'est produite.'],
        };
    @endphp

    <div class="alert alert-warning border-0 shadow-sm">
        <div class="d-flex align-items-start gap-3">
            <div class="display-6 text-warning lh-1"><i class="ti ti-alert-triangle"></i></div>
            <div class="flex-grow-1">
                <h6 class="alert-heading mb-1">
                    {{ $friendly['title'] }}
                    @if($status) <span class="badge bg-warning text-dark ms-1">HTTP {{ $status }}</span> @endif
                </h6>
                <p class="small mb-2 text-muted">{{ $friendly['detail'] }}</p>

                @if($requestId)
                    <div class="small mb-2">
                        <span class="text-muted">Numéro de requête (à transmettre à Homeschool) :</span>
                        <code class="user-select-all">{{ $requestId }}</code>
                    </div>
                @endif

                <details class="small text-muted">
                    <summary style="cursor:pointer;">Détails techniques (pour le développeur)</summary>
                    <div class="mt-2 mb-1">{{ $error->getMessage() }}</div>
                    @if($body)
                        <pre class="bg-white border rounded p-2 mt-1 mb-0" style="max-height: 240px; overflow:auto;">{{ json_encode($body, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                    @endif
                </details>
            </div>
        </div>
    </div>
@elseif(!$payload || empty($payload['data']))
    <div class="alert alert-info mb-0">
        <i class="ti ti-info-circle me-1"></i> Aucune donnée renvoyée par l'API.
    </div>
@else
    @php
        $rows = $payload['data'];
        $allCols = is_array($rows[0] ?? null) ? array_keys($rows[0]) : [];
        $cols = array_values(array_filter($allCols, fn ($c) => !in_array($c, $hiddenAlways, true)));
        $pagination = $payload['pagination'] ?? null;
        $centerCtx = app(\App\Services\Crm\CenterContext::class);

        // Is this a Classes-style payload? (used to show "Étudiants" pill column)
        $isClass = $rowKind === 'class'
            || (is_array($rows[0] ?? null) && array_key_exists('CLASS_COUNT_STUDENTS_ACTIVE', $rows[0]));
    @endphp

    <div class="mb-2 small text-muted">
        <i class="ti ti-database me-1"></i>
        {{ count($rows) }} ligne(s)
        @if($pagination)
            — page {{ ($pagination['page'] ?? 0) + 1 }}
            @if(isset($pagination['totalPages'])) / {{ $pagination['totalPages'] }} @endif
            @if(isset($pagination['totalElements'])) ({{ $pagination['totalElements'] }} au total) @endif
        @endif
    </div>

    {{-- Stash full row data in a single JSON script tag, keyed by row index.
         Buttons reference rows by index — keeps DOM attributes small. --}}
    <script type="application/json" id="crm-rows-data-{{ $tableId = uniqid() }}">{!! json_encode($rows, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) !!}</script>

    <div class="table-responsive">
        <table class="table table-sm table-hover align-middle" data-crm-table-id="{{ $tableId }}">
            <thead class="table-light">
                <tr>
                    @foreach($cols as $c)
                        <th class="text-nowrap small">{{ $labelFor($c) }}</th>
                    @endforeach
                    @if($isClass)
                        <th class="small text-nowrap">Étudiants</th>
                    @endif
                    <th class="small text-end" style="width: 120px;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($rows as $rowIndex => $row)
                    <tr>
                        @foreach($cols as $c)
                            @php $v = $row[$c] ?? null; @endphp
                            <td class="small">
                                @if($c === 'SMALL_AVATAR_PATH')
                                    <span class="crm-avatar">
                                        <img src="{{ $v ?: $defaultAvatar }}"
                                             onerror="this.onerror=null;this.src='{{ $defaultAvatar }}';"
                                             alt="avatar">
                                    </span>
                                @elseif($c === 'STR_STORE_ID')
                                    @php $name = $centerCtx->nameForStoreId($v); @endphp
                                    @if($name)
                                        <span class="badge bg-light-primary text-primary">
                                            <i class="ti ti-building me-1"></i>{{ $name }}
                                        </span>
                                    @else
                                        <span class="badge bg-light text-muted">#{{ $v }}</span>
                                    @endif
                                @elseif(in_array($c, ['ACTIVE', 'IS_AVANCE']) && in_array($v, ['Y', 'N'], true))
                                    <span class="badge bg-{{ $v === 'Y' ? 'light-success text-success' : 'light-secondary text-muted' }}">
                                        {{ $v === 'Y' ? 'Oui' : 'Non' }}
                                    </span>
                                @elseif($c === 'SEXE' && in_array($v, ['M', 'F'], true))
                                    <span class="badge bg-{{ $v === 'F' ? 'light-danger text-danger' : 'light-info text-info' }}">{{ $v }}</span>
                                @elseif(
                                    (
                                        str_ends_with($c, '_DATE')
                                        || str_starts_with($c, 'DATE_')
                                        || in_array($c, ['BIRTHDAY', 'DUE_DATE', 'EFFECTIVE_DATE_PAYMENT', 'EFFECTIVE_DATE_PAYMENT_ALLOCATION', 'SESSION_DATE'], true)
                                        || (is_string($v) && preg_match('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}/', $v))
                                    )
                                    && $v
                                )
                                    @php
                                        try { $d = \Carbon\Carbon::parse($v)->setTimezone('Africa/Casablanca'); }
                                        catch (\Throwable $e) { $d = null; }
                                        // Show time only when the value carries a real (non-zero) clock.
                                        $hasTime = $d && (int) $d->format('His') !== 0;
                                    @endphp
                                    @if($d)
                                        <span class="text-nowrap">{{ $d->format($hasTime ? 'd/m/Y H:i' : 'd/m/Y') }}</span>
                                    @else
                                        {{ $v }}
                                    @endif
                                @elseif(in_array($c, ['AMOUNT', 'OPEN_AMOUNT', 'TOTAL_PRICE', 'REST_AMOUNT', 'PRICE', 'MONTHLY_SALARY', 'REMAINING_AMOUNT']) && is_numeric($v))
                                    <span class="text-nowrap fw-medium">{{ number_format((float) $v, 2, ',', ' ') }}</span>
                                @elseif(is_array($v) || is_object($v))
                                    <span class="text-muted small">—</span>
                                @elseif(is_string($v) && strlen($v) > 200 && (str_starts_with(trim($v), '[') || str_starts_with(trim($v), '{')))
                                    <span class="text-muted small">—</span>
                                @elseif(is_bool($v))
                                    <span class="badge bg-{{ $v ? 'success' : 'secondary' }}">{{ $v ? 'true' : 'false' }}</span>
                                @elseif($v === null || $v === '')
                                    <span class="text-muted">—</span>
                                @else
                                    {{ $v }}
                                @endif
                            </td>
                        @endforeach

                        {{-- Aggregated student pills column (only for Classes) --}}
                        @if($isClass)
                            <td class="text-nowrap">
                                <button type="button" class="badge bg-light-success text-success border-0 crm-students-view"
                                        data-table-id="{{ $tableId }}" data-row-index="{{ $rowIndex }}" data-bucket="active"
                                        title="Voir les étudiants en formation">
                                    <i class="ti ti-user-check me-1"></i>{{ $row['CLASS_COUNT_STUDENTS_ACTIVE'] ?? 0 }}
                                </button>
                                <button type="button" class="badge bg-light-secondary text-muted border-0 crm-students-view"
                                        data-table-id="{{ $tableId }}" data-row-index="{{ $rowIndex }}" data-bucket="archived"
                                        title="Voir les étudiants archivés">
                                    <i class="ti ti-archive me-1"></i>{{ $row['CLASS_COUNT_STUDENTS_ARCHIVED'] ?? 0 }}
                                </button>
                                <button type="button" class="badge bg-light-danger text-danger border-0 crm-students-view"
                                        data-table-id="{{ $tableId }}" data-row-index="{{ $rowIndex }}" data-bucket="canceled"
                                        title="Voir les étudiants annulés">
                                    <i class="ti ti-x me-1"></i>{{ $row['CLASS_COUNT_STUDENTS_CANCLED'] ?? 0 }}
                                </button>
                            </td>
                        @endif

                        {{-- Action: open modal with friendly detail view --}}
                        <td class="text-end text-nowrap">
                            @if($isClass)
                                <button type="button"
                                        class="btn btn-sm btn-light-success text-success crm-payment-matrix me-1"
                                        data-class-id="{{ $row['CLASS_ID'] ?? $row['ID'] ?? '' }}"
                                        data-class-name="{{ $row['NAME'] ?? '' }}"
                                        data-table-id="{{ $tableId }}"
                                        data-row-index="{{ $rowIndex }}"
                                        title="Statistique de groupe (paiements)">
                                    <i class="ti ti-table"></i>
                                </button>
                            @endif
                            <button type="button"
                                    class="btn btn-sm btn-light crm-row-view"
                                    data-row-kind="{{ $rowKind ?? ($isClass ? 'class' : 'generic') }}"
                                    data-table-id="{{ $tableId }}"
                                    data-row-index="{{ $rowIndex }}"
                                    title="Voir les détails">
                                <i class="ti ti-eye"></i>
                            </button>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    @if($pagination)
        @include('backoffice.crm.partials._pagination', ['pagination' => $pagination])
    @endif

    {{-- Modal markup + JS (only emitted once per page) --}}
    @once
        @include('backoffice.crm.partials._row_modal')
    @endonce
@endif
