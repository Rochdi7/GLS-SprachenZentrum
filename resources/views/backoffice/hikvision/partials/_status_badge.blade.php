@php
    $normalized = \Illuminate\Support\Str::lower((string) ($value ?? 'inconnu'));
    $class = match ($normalized) {
        'online', 'active', 'connected', 'processed', 'success', 'resolved', 'closed', 'enabled' => 'bg-light-success text-success',
        'offline', 'disconnected', 'failed', 'error', 'critical', 'high', 'disabled', 'inactive' => 'bg-light-danger text-danger',
        'pending', 'received', 'running', 'warning' => 'bg-light-warning text-warning',
        default => 'bg-light-secondary text-secondary',
    };
@endphp

<span class="badge {{ $class }}">{{ $label ?? ($value ?? 'N/A') }}</span>
