{{--
    Center selector — included at the top of every CRM page.

    Expects (provided by CrmController::view()):
      $crmCenters       : Collection<Site>
      $crmCurrentStore  : int|null  (selected strStoreId)
      $crmCurrentSite   : Site|null (resolved row, for the label)
--}}

<div class="card mb-3">
    <div class="card-body py-3">
        <form method="POST" action="{{ route('backoffice.crm.set-center') }}" class="row g-2 align-items-end">
            @csrf
            <div class="col-12 col-sm-auto">
                <label class="form-label fw-semibold mb-1 small">
                    <i class="ti ti-building me-1 text-primary"></i> Centre actif
                </label>
            </div>
            <div class="col-12 col-sm">
                <select name="crm_store_id" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="">— Tous les centres autorisés —</option>
                    @foreach($crmCenters as $site)
                        <option value="{{ $site->crm_store_id }}"
                            {{ (int) $crmCurrentStore === (int) $site->crm_store_id ? 'selected' : '' }}>
                            {{ $site->name }}@if($site->city) — {{ $site->city }}@endif
                        </option>
                    @endforeach
                </select>
            </div>
            @if($crmCurrentStore)
                <div class="col-12 col-sm-auto">
                    <button type="submit" name="crm_store_id" value="" class="btn btn-sm btn-outline-secondary">
                        <i class="ti ti-x me-1"></i> Effacer
                    </button>
                </div>
            @endif
            <div class="col-12 col-sm-auto">
                @if($crmCurrentSite)
                    <span class="badge bg-light-primary text-primary">
                        <i class="ti ti-filter me-1"></i> Filtré : {{ $crmCurrentSite->name }}
                    </span>
                @else
                    <span class="badge bg-light text-muted">
                        <i class="ti ti-world me-1"></i> Tous les centres
                    </span>
                @endif
            </div>
        </form>
    </div>
</div>
