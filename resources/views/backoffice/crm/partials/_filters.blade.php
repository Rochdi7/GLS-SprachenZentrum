{{--
    Generic filter bar.
    Expects:
      $fields : array<string, array{label:string,type?:string,placeholder?:string}>
--}}

@php
    $hasAnyFilter = collect(array_keys($fields))->contains(fn ($k) => request()->filled($k));
@endphp

<form method="GET" class="card mb-3">
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
                <div class="col-12 col-sm-6 col-md-4 col-lg-3">
                    <label class="form-label small mb-1 text-muted">{{ $cfg['label'] }}</label>
                    <input
                        type="{{ $cfg['type'] ?? 'text' }}"
                        name="{{ $name }}"
                        value="{{ request()->query($name) }}"
                        placeholder="{{ $cfg['placeholder'] ?? '' }}"
                        class="form-control form-control-sm"
                    >
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
