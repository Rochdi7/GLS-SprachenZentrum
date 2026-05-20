{{--
    Generic filter bar.

    Expects:
      $fields : array<string, array{
          label: string,
          type?: string,            // 'text' (default) | 'number' | 'date' | 'select' | 'student-autocomplete'
          placeholder?: string,
          options?: array<int|string, array{id:int|string, name:string}>,
                                    // required when type === 'select'
          empty?: string,           // optional placeholder for select (default: "— Choisir —")
      }>

    Filter values come from request()->query() — same as before.
    Adding a select option:
        'paymentTypeId' => [
            'label'   => 'Type de paiement',
            'type'    => 'select',
            'options' => $lovPaymentTypes,         // [['id'=>1,'name'=>'Carte'], ...]
            'empty'   => '— Tous les types —',
        ],

    Adding a student autocomplete:
        'studentId' => [
            'label' => 'Étudiant',
            'type'  => 'student-autocomplete',
        ],
--}}

@php
    $hasAnyFilter = collect(array_keys($fields))->contains(fn ($k) => request()->filled($k));
@endphp

<form method="GET" class="card mb-3 crm-filter-bar">
    <div class="card-body py-3">
        <div class="d-flex align-items-center mb-2">
            <h6 class="mb-0 small text-muted text-uppercase">
                <i class="ti ti-filter me-1"></i> Filtres
            </h6>
            @if($hasAnyFilter)
                <span class="badge bg-light-primary text-primary ms-2">{{ collect($fields)->keys()->filter(fn ($k) => request()->filled($k))->count() }} actif(s)</span>
            @endif
        </div>
        <div class="row g-2">
            @foreach($fields as $name => $cfg)
                @php
                    $type    = $cfg['type'] ?? 'text';
                    $current = request()->query($name);
                @endphp
                <div class="col-12 col-sm-6 col-md-4 col-lg-3">
                    <label class="form-label small mb-1 text-muted">{{ $cfg['label'] }}</label>

                    @if($type === 'select')
                        @if(!empty($cfg['options']))
                            <select name="{{ $name }}" class="form-select form-select-sm">
                                <option value="">{{ $cfg['empty'] ?? '— Tous —' }}</option>
                                @foreach($cfg['options'] as $opt)
                                    <option value="{{ $opt['id'] }}" {{ (string) $current === (string) $opt['id'] ? 'selected' : '' }}>
                                        {{ $opt['name'] }}
                                    </option>
                                @endforeach
                            </select>
                        @else
                            {{-- Fallback: LOV unavailable (API error, empty list). User can still type an ID. --}}
                            <input type="number" name="{{ $name }}" value="{{ $current }}"
                                   placeholder="Liste indisponible — saisir l'ID"
                                   class="form-control form-control-sm">
                        @endif

                    @elseif($type === 'student-autocomplete')
                        @php
                            $widgetId = 'student-ac-' . $name . '-' . uniqid();
                            $preloadedName = $cfg['selected_name'] ?? null;
                        @endphp
                        <div class="crm-student-ac position-relative" data-widget-id="{{ $widgetId }}">
                            <input type="hidden" name="{{ $name }}" value="{{ $current }}" data-role="value">
                            <input type="text"
                                   class="form-control form-control-sm crm-student-ac-search"
                                   placeholder="Tapez un nom (≥ 2 caractères)…"
                                   value="{{ $preloadedName ?: ($current ? ('#' . $current) : '') }}"
                                   autocomplete="off"
                                   data-role="search">
                            <div class="crm-student-ac-menu list-group shadow-sm" data-role="menu"
                                 style="display:none; position:absolute; top:100%; left:0; right:0; z-index:1050; max-height:280px; overflow-y:auto;"></div>
                        </div>

                    @else
                        <input
                            type="{{ $type }}"
                            name="{{ $name }}"
                            value="{{ $current }}"
                            placeholder="{{ $cfg['placeholder'] ?? '' }}"
                            class="form-control form-control-sm"
                        >
                    @endif
                </div>
            @endforeach
        </div>
        <div class="mt-2 d-flex gap-2">
            <button type="submit" class="btn btn-sm btn-primary">
                <i class="ti ti-search me-1"></i> Appliquer
            </button>
            @if($hasAnyFilter)
                <a href="{{ url()->current() }}" class="btn btn-sm btn-outline-secondary">
                    <i class="ti ti-x me-1"></i> Réinitialiser
                </a>
            @endif
        </div>
    </div>
</form>

@include('backoffice.crm.partials._student_autocomplete')
