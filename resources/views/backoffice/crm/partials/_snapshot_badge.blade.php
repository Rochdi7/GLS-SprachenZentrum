{{--
    Snapshot freshness badge.
    Usage: @include('backoffice.crm.partials._snapshot_badge', ['snapshotDate' => $snapshotDate])
    $snapshotDate : string|null  (Y-m-d)
--}}
@once
<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(function (el) {
        new bootstrap.Tooltip(el);
    });
});
</script>
@endonce

@if (!empty($snapshotDate))
@php
    $snap     = \Carbon\Carbon::parse($snapshotDate)->setTimezone('Africa/Casablanca');
    $isToday  = $snap->isToday();
    $label    = $isToday
        ? 'Aujourd\'hui'
        : $snap->translatedFormat('d M Y');
    $color    = $isToday  ? 'success' : 'warning';
    $icon     = $isToday  ? 'ph-check-circle' : 'ph-warning';
    $title    = 'Données issues du snapshot du ' . $snap->translatedFormat('d/m/Y') .
                '. Mis à jour chaque nuit à 01h30.';
@endphp
<span class="badge bg-light-{{ $color }} text-{{ $color }} d-inline-flex align-items-center gap-1 px-2 py-1"
      style="font-size:.75rem;font-weight:500;border-radius:6px"
      title="{{ $title }}" data-bs-toggle="tooltip">
    <i class="ph-duotone {{ $icon }} f-14"></i>
    Snapshot : {{ $label }}
</span>
@endif
