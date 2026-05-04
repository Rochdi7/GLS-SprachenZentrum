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
        .pp-toolbar         { background:#eaf2ff; border:1px solid #c7dbff; border-radius:.5rem; padding:.4rem .6rem; }
        .pp-toolbar input   { width:90px; }
        td.amount, th.amount{ text-align:right; font-variant-numeric: tabular-nums; }
    </style>
@endsection

@section('content')

    @if (session('success') || session('error'))
        <div class="alert alert-{{ session('error') ? 'danger' : 'success' }} alert-dismissible fade show" role="alert">
            {{ session('success') ?? session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    {{-- HEADER : filtres + total --}}
    <div class="card mb-3">
        <div class="card-body">
            <div class="d-flex flex-wrap align-items-center gap-3 justify-content-between">
                <div>
                    <h5 class="mb-1">Suivi des Traductions</h5>
                    <small class="text-muted">Portail interne — Maroc / Allemagne</small>
                </div>

                <div class="d-flex flex-wrap align-items-center gap-2">
                    <form method="GET" action="{{ route('backoffice.translations.index') }}" class="d-flex gap-2">
                        <input type="text" name="q" value="{{ $q }}" class="form-control form-control-sm" placeholder="CIN, nom, document…">
                        <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                            <option value="">Tous statuts ({{ $counts['all'] }})</option>
                            <option value="pending"    {{ $currentStatus === 'pending'    ? 'selected' : '' }}>Reçu (GLS) ({{ $counts['pending'] }})</option>
                            <option value="translator" {{ $currentStatus === 'translator' ? 'selected' : '' }}>Chez Traducteur ({{ $counts['translator'] }})</option>
                            <option value="delivered"  {{ $currentStatus === 'delivered'  ? 'selected' : '' }}>Rendu ({{ $counts['delivered'] }})</option>
                        </select>
                        <button class="btn btn-sm btn-outline-primary" type="submit">Filtrer</button>
                    </form>

                    @can('translations.view')
                    <a href="{{ route('backoffice.translations.export') }}" class="btn btn-sm btn-success">
                        <i class="ti ti-file-spreadsheet"></i> Export CSV
                    </a>
                    @endcan
                </div>
            </div>
        </div>
    </div>

    {{-- FORM : nouvelle commande --}}
    @can('translations.create')
    <div class="card mb-3">
        <div class="card-header">
            <h6 class="mb-0"><i class="ti ti-plus-circle text-primary"></i> Nouvelle commande</h6>
        </div>
        <div class="card-body">
            <form action="{{ route('backoffice.translations.store') }}" method="POST" class="row g-2 align-items-end">
                @csrf
                <div class="col-md-2">
                    <label class="form-label small text-muted">CIN *</label>
                    <input type="text" name="cin" class="form-control form-control-sm text-uppercase" placeholder="AB123456" required>
                </div>
                <div class="col-md-2">
                    <label class="form-label small text-muted">Nom étudiant *</label>
                    <input type="text" name="student_name" class="form-control form-control-sm" placeholder="Ahmed Alami" required>
                </div>
                <div class="col-md-2">
                    <label class="form-label small text-muted">Téléphone</label>
                    <input type="text" name="phone" class="form-control form-control-sm" placeholder="06…">
                </div>
                <div class="col-md-2">
                    <label class="form-label small text-muted">Documents</label>
                    <input type="text" name="doc_type" class="form-control form-control-sm" placeholder="Bac, Relevés…">
                </div>
                <div class="col-md-1">
                    <label class="form-label small text-muted">Pages *</label>
                    <input type="number" name="page_count" min="1" value="1" class="form-control form-control-sm text-center fw-bold" required>
                </div>
                <div class="col-md-1">
                    <label class="form-label small text-muted">Prix/page</label>
                    <input type="number" name="price_per_page" min="0" value="{{ $defaultPrice }}" class="form-control form-control-sm" required>
                </div>
                <div class="col-md-1">
                    <label class="form-label small text-muted">Date dépôt</label>
                    <input type="date" name="date_received" value="{{ now()->toDateString() }}" class="form-control form-control-sm">
                </div>
                <div class="col-md-1">
                    <button type="submit" class="btn btn-primary btn-sm w-100">
                        <i class="ti ti-device-floppy"></i> Enregistrer
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endcan

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
                            <th class="amount">Coût</th>
                            <th>Dépôt</th>
                            <th style="width:170px">Date Remise</th>
                            <th class="text-center" style="width:200px">Statut</th>
                            <th class="text-center" style="width:120px">Actions</th>
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
                                <td><small>{{ $t->doc_type }}</small></td>
                                <td class="text-center fw-bold">{{ $t->page_count }}</td>
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
                                            data-bs-toggle="modal" data-bs-target="#editTranslation-{{ $t->id }}" title="Éditer">
                                        <i class="ti ti-edit f-18"></i>
                                    </button>
                                    @endcan
                                    @can('translations.delete')
                                    <form method="POST" action="{{ route('backoffice.translations.destroy', $t) }}" class="d-inline"
                                          onsubmit="return confirm('Supprimer cette commande ?')">
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

    {{-- EDIT MODALS --}}
    @can('translations.edit')
        @foreach($translations as $t)
            <div class="modal fade" id="editTranslation-{{ $t->id }}" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-lg">
                    <form action="{{ route('backoffice.translations.update', $t) }}" method="POST" class="modal-content">
                        @csrf @method('PUT')
                        <div class="modal-header">
                            <h6 class="modal-title">Modifier — {{ $t->student_name }}</h6>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <div class="row g-2">
                                <div class="col-md-3">
                                    <label class="form-label small">CIN *</label>
                                    <input type="text" name="cin" value="{{ $t->cin }}" class="form-control form-control-sm text-uppercase" required>
                                </div>
                                <div class="col-md-5">
                                    <label class="form-label small">Étudiant *</label>
                                    <input type="text" name="student_name" value="{{ $t->student_name }}" class="form-control form-control-sm" required>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label small">Téléphone</label>
                                    <input type="text" name="phone" value="{{ $t->phone }}" class="form-control form-control-sm">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small">Documents</label>
                                    <input type="text" name="doc_type" value="{{ $t->doc_type }}" class="form-control form-control-sm">
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label small">Pages *</label>
                                    <input type="number" name="page_count" min="1" value="{{ $t->page_count }}" class="form-control form-control-sm" required>
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label small">Prix/page</label>
                                    <input type="number" name="price_per_page" min="0" value="{{ $t->price_per_page }}" class="form-control form-control-sm" required>
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label small">Statut</label>
                                    <select name="status" class="form-select form-select-sm">
                                        @foreach(\App\Models\Translation::statuses() as $k => $label)
                                            <option value="{{ $k }}" {{ $t->status === $k ? 'selected' : '' }}>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label small">Date dépôt</label>
                                    <input type="date" name="date_received" value="{{ optional($t->date_received)->format('Y-m-d') }}" class="form-control form-control-sm">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label small">Date remise</label>
                                    <input type="date" name="date_handed_over" value="{{ optional($t->date_handed_over)->format('Y-m-d') }}" class="form-control form-control-sm">
                                </div>
                                <div class="col-12">
                                    <label class="form-label small">Notes internes</label>
                                    <textarea name="notes" rows="2" class="form-control form-control-sm">{{ $t->notes }}</textarea>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Annuler</button>
                            <button type="submit" class="btn btn-sm btn-primary">Enregistrer</button>
                        </div>
                    </form>
                </div>
            </div>
        @endforeach
    @endcan

@endsection
