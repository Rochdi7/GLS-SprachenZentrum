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
                        @if(isset($sites) && $sites->count() > 1)
                        <select name="site_id" class="form-select form-select-sm filter-status" onchange="this.form.submit()">
                            <option value="">Tous les centres</option>
                            @foreach($sites as $site)
                                <option value="{{ $site->id }}" {{ (int) $requestedSiteId === $site->id ? 'selected' : '' }}>{{ $site->name }}</option>
                            @endforeach
                        </select>
                        @endif
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
                            <th class="text-center" style="width:180px">Actions</th>
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
                                    <button type="button" class="btn btn-sm btn-link text-info p-1"
                                            data-bs-toggle="modal" data-bs-target="#showTranslationModal-{{ $t->id }}" title="Voir les détails">
                                        <i class="ti ti-eye f-18"></i>
                                    </button>
                                    @if($t->whatsappReadyUrl())
                                    <a href="{{ $t->whatsappReadyUrl() }}" target="_blank" rel="noopener"
                                       class="btn btn-sm btn-link text-success p-1" title="Envoyer le message « prêt » sur WhatsApp">
                                        <i class="ti ti-brand-whatsapp f-18"></i>
                                    </a>
                                    @endif
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
            <div class="modal-dialog modal-lg modal-dialog-scrollable modal-dialog-centered">
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
                <div class="modal-dialog modal-lg modal-dialog-scrollable modal-dialog-centered">
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

    {{-- SHOW (read-only) MODALS --}}
    @foreach($translations as $t)
        <div class="modal fade" id="showTranslationModal-{{ $t->id }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-scrollable modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <i class="ti ti-file-text text-info"></i>
                            {{ $t->student_name }}
                            <span class="status-pill status-{{ $t->status }} ms-2">{{ $t->statusLabel() }}</span>
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">

                        {{-- Étudiant --}}
                        <div class="row g-3 mb-3">
                            <div class="col-md-3">
                                <div class="text-muted small text-uppercase">CIN</div>
                                <div class="fw-semibold"><code class="text-dark">{{ $t->cin }}</code></div>
                            </div>
                            <div class="col-md-3">
                                <div class="text-muted small text-uppercase">Téléphone</div>
                                <div class="fw-semibold">{{ $t->phone ?: '—' }}</div>
                            </div>
                            <div class="col-md-6">
                                <div class="text-muted small text-uppercase">Email</div>
                                <div class="fw-semibold">{{ $t->email ?: '—' }}</div>
                            </div>
                            <div class="col-md-3">
                                <div class="text-muted small text-uppercase">Date dépôt</div>
                                <div class="fw-semibold">{{ optional($t->date_received)->format('d/m/Y') ?: '—' }}</div>
                            </div>
                            <div class="col-md-3">
                                <div class="text-muted small text-uppercase">Date remise</div>
                                <div class="fw-semibold">{{ optional($t->date_handed_over)->format('d/m/Y') ?: '—' }}</div>
                            </div>
                            <div class="col-md-3">
                                <div class="text-muted small text-uppercase">Total pages</div>
                                <div class="fw-semibold">{{ $t->totalPages() }}</div>
                            </div>
                            <div class="col-md-3">
                                <div class="text-muted small text-uppercase">Total commande</div>
                                <div class="fw-bold text-primary">{{ number_format($t->total_cost, 0, ',', ' ') }} DH</div>
                            </div>
                        </div>

                        @if($t->notes)
                            <div class="alert alert-light border small mb-3">
                                <i class="ti ti-note text-muted"></i> {{ $t->notes }}
                            </div>
                        @endif

                        {{-- Documents + scans --}}
                        <h6 class="text-uppercase small text-muted fw-bold mb-2"><i class="ti ti-files"></i> Documents</h6>
                        @forelse($t->items as $it)
                            @php $scans = $it->getMedia('originals'); @endphp
                            <div class="border rounded p-2 mb-2">
                                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                                    <div class="fw-semibold">{{ $it->doc_type }}</div>
                                    <div class="text-muted small">
                                        {{ $it->page_count }} p × {{ $it->price_per_page }} DH =
                                        <strong class="text-primary">{{ number_format($it->line_total, 0, ',', ' ') }} DH</strong>
                                    </div>
                                </div>
                                @if($scans->isNotEmpty())
                                    <div class="d-flex flex-wrap gap-1 mt-2">
                                        @foreach($scans as $media)
                                            @php $isPdf = str_contains($media->mime_type, 'pdf'); @endphp
                                            <a href="{{ $media->getUrl() }}"
                                               @if($isPdf) target="_blank" rel="noopener"
                                               @else data-img-preview="{{ $media->getUrl() }}" data-img-name="{{ $media->file_name }}" @endif
                                               class="badge bg-light text-dark border text-decoration-none d-inline-flex align-items-center gap-1"
                                               style="cursor:pointer">
                                                <i class="ti {{ $isPdf ? 'ti-file-type-pdf text-danger' : 'ti-photo text-info' }}"></i>
                                                <span class="text-truncate" style="max-width:160px">{{ $media->file_name }}</span>
                                            </a>
                                        @endforeach
                                    </div>
                                @else
                                    <div class="small text-warning mt-1"><i class="ti ti-alert-triangle"></i> Aucun scan de l'original</div>
                                @endif
                            </div>
                        @empty
                            <div class="text-muted fst-italic small">Aucun document.</div>
                        @endforelse
                    </div>
                    <div class="modal-footer">
                        @if($t->whatsappReadyUrl())
                            <a href="{{ $t->whatsappReadyUrl() }}" target="_blank" rel="noopener" class="btn btn-success">
                                <i class="ti ti-brand-whatsapp"></i> Message WhatsApp « prêt »
                            </a>
                        @endif
                        @can('translations.edit')
                            <button type="button" class="btn btn-primary" data-bs-dismiss="modal"
                                    data-bs-toggle="modal" data-bs-target="#editTranslationModal-{{ $t->id }}">
                                <i class="ti ti-edit"></i> Éditer
                            </button>
                        @endcan
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Fermer</button>
                    </div>
                </div>
            </div>
        </div>
    @endforeach

    {{-- IMAGE LIGHTBOX (shared) --}}
    <style>
        /* Keep the lightbox above the create/edit modals it can be opened from. */
        #scanLightbox { z-index: 1085; }
    </style>
    <div class="modal fade" id="scanLightbox" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content bg-dark border-0">
                <div class="modal-header border-0 py-2">
                    <span class="modal-title text-white small text-truncate" id="scanLightboxTitle"></span>
                    <div class="ms-auto d-flex gap-2">
                        <a href="#" id="scanLightboxOpen" target="_blank" rel="noopener" class="btn btn-sm btn-outline-light" title="Ouvrir dans un onglet">
                            <i class="ti ti-external-link"></i>
                        </a>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                </div>
                <div class="modal-body text-center p-2">
                    <img id="scanLightboxImg" src="" alt="" style="max-width:100%;max-height:75vh;border-radius:6px;">
                </div>
            </div>
        </div>
    </div>

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

            // --- Shared image lightbox ---------------------------------------
            const lbEl    = document.getElementById('scanLightbox');
            const lb      = bootstrap.Modal.getOrCreateInstance(lbEl);
            const lbImg   = document.getElementById('scanLightboxImg');
            const lbTitle = document.getElementById('scanLightboxTitle');
            const lbOpen  = document.getElementById('scanLightboxOpen');

            document.addEventListener('click', function (e) {
                const trigger = e.target.closest('[data-img-preview]');
                if (!trigger) return;
                e.preventDefault();
                const url  = trigger.getAttribute('data-img-preview');
                const name = trigger.getAttribute('data-img-name') || '';
                lbImg.src        = url;
                lbImg.alt        = name;
                lbTitle.textContent = name;
                lbOpen.href      = url;
                lb.show();
            });

            // When opened on top of another modal, lift its backdrop above that modal.
            lbEl.addEventListener('shown.bs.modal', function () {
                const backdrops = document.querySelectorAll('.modal-backdrop');
                const last = backdrops[backdrops.length - 1];
                if (last) last.style.zIndex = '1084';
            });

            // Free the object URL / image when the lightbox closes.
            lbEl.addEventListener('hidden.bs.modal', function () {
                lbImg.src = '';
                // If a parent modal is still open, Bootstrap removes the scroll lock;
                // restore it so the underlying modal stays scrollable.
                if (document.querySelector('.modal.show')) {
                    document.body.classList.add('modal-open');
                }
            });
        });
    </script>
@endsection
