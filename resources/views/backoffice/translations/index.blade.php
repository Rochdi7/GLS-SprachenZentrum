@extends('layouts.main')

@section('title', 'Suivi des Traductions')
@section('breadcrumb-item', 'Ecole')
@section('breadcrumb-item-active', 'Traductions')

@section('css')
    <link rel="stylesheet" href="{{ URL::asset('build/css/plugins/style.css') }}">
    <style>
        .status-pending     { background:#f1f3f5; color:#495057; border:1px solid #dee2e6; }
        .status-translator  { background:#fff4d6; color:#a06700; border:1px solid #ffe69c; }
        .status-delivered   { background:#cfe2ff; color:#0a58ca; border:1px solid #9ec5fe; font-weight:600; }
        .status-pill        { padding:.35rem .9rem; border-radius:999px; font-size:.78rem; cursor:pointer; display:inline-block; min-width:160px; text-align:center; }
        .status-pill:hover  { filter:brightness(.96); }
        td.amount, th.amount{ text-align:right; font-variant-numeric: tabular-nums; }
        .items-mini        { font-size:.78rem; color:#6c757d; }
        .items-mini li     { margin:0; padding:0; line-height:1.4; }
        .items-mini .doc   { color:#212529; font-weight:500; }
        .filter-status     { width:230px !important; min-width:230px; }
        .filter-search     { width:240px !important; min-width:240px; }
    </style>
@endsection

@section('content')

    {{-- Toast Notifications --}}
    @if (session('toast') || session('success') || session('error'))
        <div class="position-fixed top-0 end-0 p-3" style="z-index: 99999">
            <div id="liveToast" class="toast hide" role="alert" aria-live="assertive" aria-atomic="true">
                <div class="toast-header">
                    <img src="{{ asset('assets/images/favicon/favicon.svg') }}" class="img-fluid me-2" alt="favicon" style="width: 17px">
                    <strong class="me-auto">GLS Backoffice</strong>
                    <small>Just now</small>
                    <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
                </div>
                <div class="toast-body">
                    {{ session('toast') ?? (session('success') ?? session('error')) }}
                </div>
            </div>
        </div>
    @endif

    {{-- Validation errors (when modal submission fails server-side) --}}
    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show">
            <strong>Erreur lors de l'enregistrement :</strong>
            <ul class="mb-0">
                @foreach ($errors->all() as $err)<li>{{ $err }}</li>@endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- HEADER --}}
    <div class="card mb-3">
        <div class="card-body py-3">
            <div class="d-flex flex-wrap align-items-center gap-3 justify-content-between">
                <div class="me-auto">
                    <h5 class="mb-0">Suivi des Traductions</h5>
                    <small class="text-muted">Portail interne — Maroc / Allemagne</small>
                </div>

                <div class="d-flex flex-wrap align-items-center gap-2">
                    <form method="GET" action="{{ route('backoffice.translations.index') }}" class="d-flex gap-2 align-items-center">
                        <input type="text" name="q" value="{{ $q }}" class="form-control form-control-sm filter-search" placeholder="CIN, nom, document…">
                        <select name="status" class="form-select form-select-sm filter-status" onchange="this.form.submit()">
                            <option value="">Tous statuts ({{ $counts['all'] }})</option>
                            <option value="pending"    {{ $currentStatus === 'pending'    ? 'selected' : '' }}>Reçu (GLS) ({{ $counts['pending'] }})</option>
                            <option value="translator" {{ $currentStatus === 'translator' ? 'selected' : '' }}>Chez Traducteur ({{ $counts['translator'] }})</option>
                            <option value="delivered"  {{ $currentStatus === 'delivered'  ? 'selected' : '' }}>Rendu ({{ $counts['delivered'] }})</option>
                        </select>
                        <a href="{{ route('backoffice.translations.index') }}" class="btn btn-sm btn-outline-secondary text-nowrap">
                            <i class="ti ti-refresh"></i> Reset
                        </a>
                    </form>

                    @can('translations.create')
                    <button type="button" class="btn btn-sm btn-primary text-nowrap"
                            data-bs-toggle="modal" data-bs-target="#createTranslationModal">
                        <i class="ti ti-plus"></i> Nouvelle commande
                    </button>
                    @endcan
                </div>
            </div>
        </div>
    </div>

    {{-- TABLE --}}
    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th style="width:40px">#</th>
                            <th>CIN</th>
                            <th>Étudiant</th>
                            <th>Documents</th>
                            <th class="text-center">Pages</th>
                            <th class="amount">Total</th>
                            <th>Dépôt</th>
                            <th style="width:170px">Date Remise</th>
                            <th class="text-center" style="width:200px">Statut</th>
                            <th class="text-center" style="width:140px">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($translations as $i => $t)
                            <tr>
                                <td class="text-muted">{{ $i + 1 }}</td>
                                <td><code class="text-dark">{{ $t->cin }}</code></td>
                                <td>
                                    <div class="fw-semibold">{{ $t->student_name }}</div>
                                    @if($t->phone)<small class="text-muted">{{ $t->phone }}</small>@endif
                                </td>
                                <td>
                                    @if($t->items->isEmpty())
                                        <small class="text-muted fst-italic">Aucun document</small>
                                    @else
                                        <ul class="items-mini list-unstyled mb-0">
                                            @foreach($t->items as $it)
                                                <li>
                                                    <span class="doc">{{ $it->doc_type }}</span>
                                                    <span class="text-muted">— {{ $it->page_count }} p × {{ $it->price_per_page }} DH = <strong>{{ number_format($it->line_total, 0, ',', ' ') }} DH</strong></span>
                                                </li>
                                            @endforeach
                                        </ul>
                                    @endif
                                </td>
                                <td class="text-center fw-bold">{{ $t->totalPages() }}</td>
                                <td class="amount fw-semibold text-primary">{{ number_format($t->total_cost, 0, ',', ' ') }} DH</td>
                                <td><small>{{ optional($t->date_received)->format('d/m/Y') ?? '-' }}</small></td>
                                <td>
                                    @can('translations.edit')
                                    <form method="POST" action="{{ route('backoffice.translations.handover', $t) }}">
                                        @csrf @method('PATCH')
                                        <input type="date" name="date_handed_over"
                                               value="{{ optional($t->date_handed_over)->format('Y-m-d') }}"
                                               class="form-control form-control-sm"
                                               onchange="this.form.submit()">
                                    </form>
                                    @else
                                        <small>{{ optional($t->date_handed_over)->format('d/m/Y') ?? '-' }}</small>
                                    @endcan
                                </td>
                                <td class="text-center">
                                    @can('translations.edit')
                                    <form method="POST" action="{{ route('backoffice.translations.status', $t) }}" class="d-inline">
                                        @csrf
                                        <button type="submit" class="status-pill status-{{ $t->status }} border-0">
                                            {{ $t->statusLabel() }}
                                        </button>
                                    </form>
                                    @else
                                    <span class="status-pill status-{{ $t->status }}">{{ $t->statusLabel() }}</span>
                                    @endcan
                                </td>
                                <td class="text-center">
                                    @can('translations.edit')
                                    <button type="button" class="btn btn-sm btn-link text-secondary p-1"
                                            data-bs-toggle="modal" data-bs-target="#editTranslationModal-{{ $t->id }}" title="Éditer">
                                        <i class="ti ti-edit f-18"></i>
                                    </button>
                                    @endcan
                                    @can('translations.delete')
                                    <form method="POST" action="{{ route('backoffice.translations.destroy', $t) }}" class="d-inline"
                                          onsubmit="return confirm('Supprimer cette commande et tous ses documents ?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-link text-danger p-1" title="Supprimer">
                                            <i class="ti ti-trash f-18"></i>
                                        </button>
                                    </form>
                                    @endcan
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="text-center text-muted py-4 fst-italic">Aucune commande pour ce filtre.</td>
                            </tr>
                        @endforelse
                    </tbody>
                    <tfoot class="table-light">
                        <tr>
                            <td colspan="5" class="text-end text-uppercase small text-muted">Total filtré</td>
                            <td class="amount fw-bold text-primary">{{ number_format($grandTotal, 0, ',', ' ') }} DH</td>
                            <td colspan="4"></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>

    {{-- CREATE MODAL --}}
    @can('translations.create')
        <div class="modal fade" id="createTranslationModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
            <div class="modal-dialog modal-xl">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title"><i class="ti ti-plus-circle text-primary"></i> Nouvelle commande de traduction</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        @include('backoffice.translations._form', [
                            'translation'  => null,
                            'defaultPrice' => $defaultPrice,
                            'formId'       => 'createTranslationForm',
                        ])
                    </div>
                </div>
            </div>
        </div>
    @endcan

    {{-- EDIT MODALS --}}
    @can('translations.edit')
        @foreach($translations as $t)
            <div class="modal fade" id="editTranslationModal-{{ $t->id }}" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
                <div class="modal-dialog modal-xl">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">
                                <i class="ti ti-edit text-primary"></i>
                                Modifier — {{ $t->student_name }} <small class="text-muted">(CIN : {{ $t->cin }})</small>
                            </h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            @include('backoffice.translations._form', [
                                'translation'  => $t,
                                'defaultPrice' => 0,
                                'formId'       => 'editTranslationForm-' . $t->id,
                            ])
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    @endcan

@endsection

@section('scripts')
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const toastEl = document.getElementById('liveToast');
            if (toastEl) {
                const toast = new bootstrap.Toast(toastEl);
                toast.show();
            }

            // Re-open the modal on validation errors so the user sees the messages.
            @if ($errors->any() && old('_form_kind') === 'create')
                const cm = new bootstrap.Modal(document.getElementById('createTranslationModal'));
                cm.show();
            @elseif ($errors->any() && old('_form_kind') && str_starts_with(old('_form_kind'), 'edit:'))
                @php $editId = (int) str_replace('edit:', '', old('_form_kind')); @endphp
                const em = document.getElementById('editTranslationModal-{{ $editId }}');
                if (em) (new bootstrap.Modal(em)).show();
            @endif
        });
    </script>
@endsection
