@extends('layouts.main')

@section('title', 'Attestations de participation')
@section('breadcrumb-item', 'Ecole')
@section('breadcrumb-item-active', 'Attestations')

@section('css')
    <link rel="stylesheet" href="{{ URL::asset('build/css/plugins/style.css') }}">
@endsection

@section('content')

    @if (session('success') || session('error'))
        <div class="position-fixed top-0 end-0 p-3" style="z-index: 99999">
            <div id="liveToast" class="toast hide" role="alert" aria-live="assertive" aria-atomic="true">
                <div class="toast-header">
                    <img src="{{ asset('assets/images/favicon/favicon.svg') }}" class="img-fluid me-2" alt="favicon" style="width: 17px">
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

    <div class="row">
        <div class="col-12">
            <div class="card table-card">
                <div class="card-header">
                    <div class="d-sm-flex align-items-center justify-content-between">
                        <h5 class="mb-3 mb-sm-0">Attestations de participation</h5>
                        @can('attestations.create')
                            <a href="{{ route('backoffice.attestations.create') }}" class="btn btn-primary">
                                Ajouter une attestation
                            </a>
                        @endcan
                    </div>
                </div>

                <div class="card-body pt-3">
                    @if($sites->count() > 1)
                        <form method="GET" class="row g-2 mb-3 align-items-end">
                            <div class="col-md-4">
                                <label class="form-label fw-bold mb-1">Filtrer par centre</label>
                                <select name="site_id" class="form-select" onchange="this.form.submit()">
                                    <option value="">Tous les centres</option>
                                    @foreach($sites as $s)
                                        <option value="{{ $s->id }}" {{ (string) $requestedSiteId === (string) $s->id ? 'selected' : '' }}>
                                            {{ $s->name }}@if($s->city) — {{ $s->city }}@endif
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            @if($requestedSiteId)
                                <div class="col-md-2">
                                    <a href="{{ route('backoffice.attestations.index') }}" class="btn btn-link">Réinitialiser</a>
                                </div>
                            @endif
                        </form>
                    @endif

                    @include('backoffice.attestations.table')
                </div>
            </div>
        </div>
    </div>

@endsection

@section('scripts')
    <script type="module">
        import { DataTable } from "/build/js/plugins/module.js";
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
