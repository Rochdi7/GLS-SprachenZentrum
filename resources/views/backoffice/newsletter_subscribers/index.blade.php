@extends('layouts.main')

@section('title', 'Abonnés Newsletter')
@section('breadcrumb-item', 'Admissions & leads')
@section('breadcrumb-item-active', 'Newsletter')

@section('css')
    <link rel="stylesheet" href="{{ URL::asset('build/css/plugins/style.css') }}">
@endsection

@section('content')

    {{-- Toast Notifications --}}
    @if (session('success') || session('error'))
        <div class="position-fixed top-0 end-0 p-3" style="z-index: 99999">
            <div id="liveToast" class="toast hide" role="alert" aria-live="assertive" aria-atomic="true">
                <div class="toast-header">
                    <img src="{{ asset('assets/images/favicon/favicon.svg') }}" class="img-fluid me-2" alt="favicon"
                        style="width: 17px">
                    <strong class="me-auto">GLS Backoffice</strong>
                    <small>Just now</small>
                    <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
                </div>
                <div class="toast-body">
                    {{ session('success') ?? session('error') }}
                </div>
            </div>
        </div>
    @endif

    {{-- Stats --}}
    <div class="row">
        <div class="col-md-4">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="avtar avtar-s bg-light-primary">
                                <i class="ph-duotone ph-envelope-simple f-22"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <p class="mb-0 text-muted">Total abonnés</p>
                            <h4 class="mb-0">{{ $stats['total'] }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="avtar avtar-s bg-light-success">
                                <i class="ph-duotone ph-calendar-check f-22"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <p class="mb-0 text-muted">Aujourd'hui</p>
                            <h4 class="mb-0">{{ $stats['today'] }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="avtar avtar-s bg-light-warning">
                                <i class="ph-duotone ph-trend-up f-22"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <p class="mb-0 text-muted">Cette semaine</p>
                            <h4 class="mb-0">{{ $stats['this_week'] }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card table-card">
                <div class="card-header">
                    <div class="d-sm-flex align-items-center justify-content-between">
                        <h5 class="mb-3 mb-sm-0">Abonnés Newsletter</h5>

                        <form method="GET" action="{{ route('backoffice.newsletter_subscribers.index') }}"
                            class="d-flex gap-2 flex-wrap">
                            <input type="text" name="q" value="{{ request('q') }}"
                                class="form-control form-control-sm" placeholder="Rechercher email..."
                                style="min-width: 200px;">

                            <select name="locale" class="form-select form-select-sm" style="min-width: 120px;">
                                <option value="">Toutes langues</option>
                                @foreach ($locales as $loc)
                                    <option value="{{ $loc }}" @selected(request('locale') === $loc)>
                                        {{ strtoupper($loc) }}
                                    </option>
                                @endforeach
                            </select>

                            <select name="source" class="form-select form-select-sm" style="min-width: 120px;">
                                <option value="">Toutes sources</option>
                                @foreach ($sources as $src)
                                    <option value="{{ $src }}" @selected(request('source') === $src)>
                                        {{ ucfirst($src) }}
                                    </option>
                                @endforeach
                            </select>

                            <button type="submit" class="btn btn-sm btn-primary">Filtrer</button>
                            @if (request()->hasAny(['q', 'locale', 'source']))
                                <a href="{{ route('backoffice.newsletter_subscribers.index') }}"
                                    class="btn btn-sm btn-outline-secondary">Réinitialiser</a>
                            @endif
                        </form>
                    </div>
                </div>

                <div class="card-body pt-3">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle" id="pc-dt-simple">
                            <thead>
                                <tr>
                                    <th>#ID</th>
                                    <th>Email</th>
                                    <th>Langue</th>
                                    <th>Source</th>
                                    <th>Date d'abonnement</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($subscribers as $subscriber)
                                    <tr>
                                        <td>{{ $subscriber->id }}</td>
                                        <td>
                                            <a href="mailto:{{ $subscriber->email }}" class="link-primary">
                                                {{ $subscriber->email }}
                                            </a>
                                        </td>
                                        <td>
                                            @if ($subscriber->locale)
                                                <span class="badge bg-light-info text-info">
                                                    {{ strtoupper($subscriber->locale) }}
                                                </span>
                                            @else
                                                <span class="text-muted">—</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if ($subscriber->source)
                                                <span class="badge bg-light-secondary text-secondary">
                                                    {{ ucfirst($subscriber->source) }}
                                                </span>
                                            @else
                                                <span class="text-muted">—</span>
                                            @endif
                                        </td>
                                        <td>
                                            {{ $subscriber->subscribed_at?->format('Y-m-d H:i') ?? $subscriber->created_at->format('Y-m-d H:i') }}
                                        </td>
                                        <td>
                                            @can('newsletter_subscribers.delete')
                                                <form action="{{ route('backoffice.newsletter_subscribers.destroy', $subscriber) }}"
                                                    method="POST" class="d-inline-block">
                                                    @csrf @method('DELETE')
                                                    <button type="submit"
                                                        class="avtar avtar-xs btn-link-secondary border-0 bg-transparent p-0"
                                                        onclick="return confirm('Supprimer cet abonné ?')"
                                                        title="Supprimer" aria-label="Supprimer">
                                                        <i class="ti ti-trash f-20"></i>
                                                    </button>
                                                </form>
                                            @endcan
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center text-muted py-4">
                                            Aucun abonné trouvé.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection

@section('scripts')
    <script type="module">
        import {
            DataTable
        } from "/build/js/plugins/module.js";
        window.dt = new DataTable("#pc-dt-simple");
    </script>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const toastEl = document.getElementById('liveToast');
            if (toastEl) {
                const toast = new bootstrap.Toast(toastEl);
                toast.show();
            }
        });
    </script>
@endsection
