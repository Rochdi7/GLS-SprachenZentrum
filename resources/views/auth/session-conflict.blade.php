@extends('layouts.AuthLayout')

@section('title', 'Session active sur un autre appareil')

@section('css')
<style>
    .conflict-card {
        max-width: 480px;
        margin: 0 auto;
    }

    .device-info-box {
        background: #f8f9fa;
        border: 1px solid #e9ecef;
        border-radius: 12px;
        padding: 1.25rem 1.5rem;
    }

    .device-icon-wrap {
        width: 52px;
        height: 52px;
        border-radius: 50%;
        background: #fff3cd;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    .device-icon-wrap i {
        font-size: 1.5rem;
        color: #e6a817;
    }

    .badge-dot {
        display: inline-block;
        width: 8px;
        height: 8px;
        border-radius: 50%;
        background: #e53935;
        margin-right: 6px;
        animation: pulse-dot 1.4s infinite;
    }

    @keyframes pulse-dot {
        0%, 100% { opacity: 1; }
        50%       { opacity: 0.3; }
    }

    .btn-kick {
        background: #e53935;
        border-color: #e53935;
        color: #fff;
        font-weight: 600;
    }

    .btn-kick:hover {
        background: #c62828;
        border-color: #c62828;
        color: #fff;
    }

    .btn-selfout {
        border: 1.5px solid #dee2e6;
        background: #fff;
        color: #495057;
        font-weight: 500;
    }

    .btn-selfout:hover {
        background: #f8f9fa;
        border-color: #adb5bd;
        color: #212529;
    }

    .meta-row {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        font-size: 0.85rem;
        color: #6c757d;
        margin-bottom: 0.35rem;
    }

    .meta-row i {
        font-size: 1rem;
        color: #adb5bd;
        width: 18px;
        text-align: center;
    }

    .meta-row strong {
        color: #343a40;
        font-weight: 600;
    }
</style>
@endsection

@section('content')
<div class="auth-form conflict-card">
    <div class="card my-5 shadow-sm border-0">
        <div class="card-body p-4">

            {{-- Header --}}
            <div class="text-center mb-4">
                <img src="{{ URL::asset('assets/images/logo/gls.png') }}" alt="GLS Logo" style="width:100px;object-fit:contain;" class="mb-3">
                <div class="mb-2">
                    <span class="badge-dot"></span>
                    <span class="text-danger fw-semibold" style="font-size:.8rem;letter-spacing:.04em;">SESSION ACTIVE DÉTECTÉE</span>
                </div>
                <h5 class="fw-bold mb-1">Votre compte est connecté ailleurs</h5>
                <p class="text-muted mb-0" style="font-size:.875rem;">
                    Une session active a été détectée sur un autre appareil.<br>
                    Que souhaitez-vous faire ?
                </p>
            </div>

            {{-- Device info box --}}
            <div class="device-info-box mb-4 d-flex align-items-start gap-3">
                <div class="device-icon-wrap">
                    <i class="ti ti-device-desktop"></i>
                </div>
                <div class="flex-grow-1">
                    <div class="fw-semibold mb-2" style="font-size:.8rem;text-transform:uppercase;letter-spacing:.06em;color:#adb5bd;">Autre appareil connecté</div>

                    <div class="meta-row">
                        <i class="ti ti-device-laptop"></i>
                        <span><strong>{{ $device }}</strong></span>
                    </div>

                    <div class="meta-row">
                        <i class="ti ti-map-pin"></i>
                        <span>IP : <strong>{{ $ip }}</strong></span>
                    </div>

                    @if($sessionAt)
                    <div class="meta-row">
                        <i class="ti ti-clock"></i>
                        <span>Connecté le <strong>{{ \Carbon\Carbon::parse($sessionAt)->setTimezone('Africa/Casablanca')->format('d/m/Y à H:i') }}</strong></span>
                    </div>
                    @endif
                </div>
            </div>

            {{-- Actions --}}
            <div class="d-grid gap-2">

                {{-- Kick other, keep current --}}
                <form method="POST" action="{{ route('session.keep') }}">
                    @csrf
                    <button type="submit" class="btn btn-kick w-100 d-flex align-items-center justify-content-center gap-2">
                        <i class="ti ti-plug-connected-x"></i>
                        Déconnecter l'autre appareil et continuer
                    </button>
                </form>

                {{-- Logout self --}}
                <form method="POST" action="{{ route('session.logout') }}">
                    @csrf
                    <button type="submit" class="btn btn-selfout w-100 d-flex align-items-center justify-content-center gap-2">
                        <i class="ti ti-logout"></i>
                        Me déconnecter de cet appareil
                    </button>
                </form>

            </div>

        </div>
    </div>
</div>
@endsection
