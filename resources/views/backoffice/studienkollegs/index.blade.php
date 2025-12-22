@extends('layouts.main')

@section('title', 'Gestion des Studienkollegs')
@section('breadcrumb-item', 'Gestion GLS')
@section('breadcrumb-item-active', 'Studienkollegs')

@section('css')
    <link rel="stylesheet" href="{{ URL::asset('build/css/plugins/style.css') }}">
@endsection

@section('content')

    {{-- Toast Notifications --}}
    @if (session('toast') || session('success') || session('error'))
        <div class="position-fixed top-0 end-0 p-3" style="z-index: 99999">
            <div id="liveToast" class="toast hide" role="alert">
                <div class="toast-header">
                    <img src="{{ asset('assets/images/favicon/favicon.svg') }}"
                         class="img-fluid me-2" alt="favicon" style="width: 17px">
                    <strong class="me-auto">GLS Backoffice</strong>
                    <small>Just now</small>
                    <button type="button" class="btn-close" data-bs-dismiss="toast"></button>
                </div>
                <div class="toast-body">
                    {{ session('toast') ?? (session('success') ?? session('error')) }}
                </div>
            </div>
        </div>
    @endif

    <div class="row">
        <div class="col-12">

            <div class="card table-card">

                <div class="card-header">
                    <div class="d-sm-flex align-items-center justify-content-between">
                        <h5 class="mb-3 mb-sm-0">Studienkollegs</h5>
                        <a href="{{ route('backoffice.studienkollegs.create') }}"
                           class="btn btn-primary">
                            Ajouter Studienkolleg
                        </a>
                    </div>
                </div>

                <div class="card-body pt-3">
                    @include('backoffice.studienkollegs.table')
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
        document.addEventListener("DOMContentLoaded", function () {
            const toastEl = document.getElementById('liveToast');
            if (toastEl) {
                new bootstrap.Toast(toastEl).show();
            }
        });
    </script>
@endsection
