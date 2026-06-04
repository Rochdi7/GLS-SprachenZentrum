{{--
    Last CRM sync badge.
    Usage: @include('backoffice.crm.partials._sync_badge')
    Expects $crmLastSync : Carbon|null — injected automatically by BaseCrmController::view().
--}}
@if(!empty($crmLastSync))
    @php
        $syncAge   = $crmLastSync->diffInMinutes(now('Africa/Casablanca'));
        $syncColor = $syncAge < 120 ? 'success' : ($syncAge < 1440 ? 'warning' : 'danger');
        $syncIcon  = $syncAge < 120 ? 'ti-circle-check' : ($syncAge < 1440 ? 'ti-clock-exclamation' : 'ti-alert-triangle');
        $syncLabel = $crmLastSync->isToday()
            ? 'Sync : ' . $crmLastSync->format('H:i')
            : 'Sync : ' . $crmLastSync->translatedFormat('d M H:i');
        $syncTitle = 'Dernière synchronisation CRM : ' . $crmLastSync->translatedFormat('d/m/Y à H:i') . ' (Casablanca)';
    @endphp
    <span class="badge bg-light-{{ $syncColor }} text-{{ $syncColor }} d-inline-flex align-items-center gap-1 px-2 py-1"
          style="font-size:.75rem;font-weight:500;border-radius:6px;cursor:default"
          title="{{ $syncTitle }}" data-bs-toggle="tooltip">
        <i class="ti {{ $syncIcon }} f-14"></i>
        {{ $syncLabel }}
    </span>
@else
    <span class="badge bg-light text-muted d-inline-flex align-items-center gap-1 px-2 py-1"
          style="font-size:.75rem;font-weight:500;border-radius:6px;cursor:default"
          title="Aucune synchronisation enregistrée" data-bs-toggle="tooltip">
        <i class="ti ti-refresh-off f-14"></i>
        Sync : —
    </span>
@endif
