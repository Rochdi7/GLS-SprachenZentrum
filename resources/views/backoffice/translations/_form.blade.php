@php
    $isEdit = isset($translation) && $translation && $translation->exists;
    $defaultPrice = $defaultPrice ?? 0;
    $items = $isEdit
        ? $translation->items
        : collect([(object)[
            'id'             => null,
            'doc_type'       => '',
            'page_count'     => 1,
            'price_per_page' => $defaultPrice,
        ]]);
    $statuses     = \App\Models\Translation::statuses();
    $formId       = $formId ?? ($isEdit ? 'editTranslationForm-'.$translation->id : 'createTranslationForm');
    $formKind     = $isEdit ? 'edit:'.$translation->id : 'create';
@endphp

<form action="{{ $isEdit ? route('backoffice.translations.update', $translation) : route('backoffice.translations.store') }}"
      method="POST" id="{{ $formId }}" class="translation-form" enctype="multipart/form-data">
    @csrf
    @if($isEdit) @method('PUT') @endif
    <input type="hidden" name="_form_kind" value="{{ $formKind }}">

    {{-- ÉTUDIANT --}}
    <div class="card mb-3">
        <div class="card-header"><h6 class="mb-0"><i class="ti ti-user text-primary"></i> Étudiant</h6></div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-3">
                    <label class="form-label small mb-1">CIN <span class="text-danger">*</span></label>
                    <input type="text" name="cin" required
                           class="form-control text-uppercase"
                           placeholder="AB123456"
                           value="{{ old('cin', $isEdit ? $translation->cin : '') }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label small mb-1">Nom étudiant <span class="text-danger">*</span></label>
                    <input type="text" name="student_name" required
                           class="form-control"
                           placeholder="Ahmed Alami"
                           value="{{ old('student_name', $isEdit ? $translation->student_name : '') }}">
                </div>
                <div class="col-md-5">
                    <label class="form-label small mb-1">Email <small class="text-muted">(pour la notification « prêt »)</small></label>
                    <input type="email" name="email" class="form-control" placeholder="etudiant@email.com"
                           value="{{ old('email', $isEdit ? $translation->email : '') }}">
                </div>
            </div>
            <div class="row g-3 mt-0">
                <div class="col-md-4">
                    <label class="form-label small mb-1">Téléphone <small class="text-muted">(WhatsApp)</small></label>
                    <input type="text" name="phone" class="form-control" placeholder="06…"
                           value="{{ old('phone', $isEdit ? $translation->phone : '') }}">
                </div>
            </div>
        </div>
    </div>

    {{-- DOCUMENTS (line items) --}}
    <div class="card mb-3">
        <div class="card-header d-flex align-items-center justify-content-between py-2">
            <h6 class="mb-0"><i class="ti ti-files text-primary"></i> Documents à traduire</h6>
            <button type="button" class="btn btn-sm btn-outline-primary btn-add-item">
                <i class="ti ti-plus"></i> Ajouter un document
            </button>
        </div>
        <div class="card-body p-2">
            <div class="items-body d-flex flex-column gap-2">
                @foreach($items as $idx => $item)
                    @php $scans = !empty($item->id) ? $item->getMedia('originals') : collect(); @endphp
                    <div class="item-row border rounded p-2 bg-light-subtle">
                        @if(!empty($item->id))
                            <input type="hidden" name="items[{{ $idx }}][id]" value="{{ $item->id }}">
                        @endif
                        <div class="row g-2 align-items-end">
                            <div class="col-auto text-muted small fw-bold row-index pt-4">{{ $idx + 1 }}</div>
                            <div class="col">
                                <label class="form-label small mb-1 text-muted">Document / Type</label>
                                <input type="text" name="items[{{ $idx }}][doc_type]"
                                       class="form-control form-control-sm" placeholder="Ex : Bac, Relevés, Diplôme…"
                                       value="{{ old('items.'.$idx.'.doc_type', $item->doc_type) }}">
                            </div>
                            <div class="col-2" style="min-width:80px">
                                <label class="form-label small mb-1 text-muted">Pages</label>
                                <input type="number" name="items[{{ $idx }}][page_count]"
                                       min="1" required
                                       class="form-control form-control-sm text-center fw-bold item-pages"
                                       value="{{ old('items.'.$idx.'.page_count', $item->page_count) }}">
                            </div>
                            <div class="col-2" style="min-width:100px">
                                <label class="form-label small mb-1 text-muted">Prix/page</label>
                                <input type="number" name="items[{{ $idx }}][price_per_page]"
                                       min="0" required
                                       class="form-control form-control-sm text-center item-price"
                                       value="{{ old('items.'.$idx.'.price_per_page', $item->price_per_page) }}">
                            </div>
                            <div class="col-auto text-end" style="min-width:90px">
                                <label class="form-label small mb-1 text-muted d-block">Total</label>
                                <span class="fw-semibold text-primary item-total">
                                    {{ number_format((int)$item->page_count * (int)$item->price_per_page, 0, ',', ' ') }} DH
                                </span>
                            </div>
                            <div class="col-auto">
                                <button type="button" class="btn btn-sm btn-outline-danger btn-remove-item" title="Supprimer ce document">
                                    <i class="ti ti-trash"></i>
                                </button>
                            </div>
                        </div>

                        {{-- SCANS / originaux --}}
                        <div class="mt-2">
                            <label class="scan-drop d-flex align-items-center gap-2 px-2 py-1 rounded border border-dashed small text-muted mb-0" style="cursor:pointer">
                                <i class="ti ti-paperclip"></i>
                                <span>Joindre le(s) scan(s) de l'original — PDF, JPG, PNG (max 10 Mo)</span>
                                <input type="file" name="items[{{ $idx }}][scans][]" class="d-none scan-input"
                                       accept=".pdf,image/*" multiple>
                            </label>
                            <div class="scan-chips d-flex flex-wrap gap-1 mt-1"></div>

                            @if($scans->isNotEmpty())
                                <div class="existing-scans d-flex flex-wrap gap-1 mt-1">
                                    @foreach($scans as $media)
                                        <span class="badge bg-light text-dark border d-inline-flex align-items-center gap-1" data-media="{{ $media->id }}">
                                            <i class="ti {{ str_contains($media->mime_type, 'pdf') ? 'ti-file-type-pdf text-danger' : 'ti-photo text-info' }}"></i>
                                            <a href="{{ $media->getUrl() }}" target="_blank" class="text-decoration-none text-dark text-truncate" style="max-width:140px">{{ $media->file_name }}</a>
                                            <button type="button" class="btn-close btn-close-sm ms-1 btn-remove-scan" data-media="{{ $media->id }}" title="Supprimer ce scan" style="font-size:.55rem"></button>
                                        </span>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="d-flex justify-content-end align-items-center gap-2 mt-2 px-1">
                <span class="text-uppercase small text-muted fw-bold">Total commande</span>
                <span class="fw-bold text-primary fs-5 grand-total">0 DH</span>
            </div>

            @error('items')
                <div class="text-danger small px-1">{{ $message }}</div>
            @enderror
        </div>
    </div>

    {{-- WORKFLOW --}}
    <div class="card mb-3">
        <div class="card-header"><h6 class="mb-0"><i class="ti ti-clipboard-check text-primary"></i> Suivi</h6></div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-3">
                    <label class="form-label small mb-1">Date dépôt</label>
                    <input type="date" name="date_received" class="form-control"
                           value="{{ old('date_received', $isEdit ? optional($translation->date_received)->format('Y-m-d') : now()->toDateString()) }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label small mb-1">Date remise</label>
                    <input type="date" name="date_handed_over" class="form-control"
                           value="{{ old('date_handed_over', $isEdit ? optional($translation->date_handed_over)->format('Y-m-d') : '') }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label small mb-1">Statut {!! $isEdit ? '<span class="text-danger">*</span>' : '' !!}</label>
                    <select name="status" class="form-select" {{ $isEdit ? 'required' : '' }}>
                        @foreach($statuses as $k => $label)
                            <option value="{{ $k }}"
                                {{ old('status', $isEdit ? $translation->status : 'pending') === $k ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12">
                    <label class="form-label small mb-1">Notes internes</label>
                    <textarea name="notes" rows="2" class="form-control" placeholder="Optionnel">{{ old('notes', $isEdit ? $translation->notes : '') }}</textarea>
                </div>
            </div>
        </div>
    </div>

    <div class="d-flex justify-content-end gap-2">
        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Annuler</button>
        <button type="submit" class="btn btn-primary px-4">
            <i class="ti ti-device-floppy me-1"></i>
            {{ $isEdit ? 'Mettre à jour' : 'Enregistrer la commande' }}
        </button>
    </div>
</form>

<script>
(function () {
    const form = document.getElementById(@json($formId));
    if (!form || form.dataset.bound === '1') return;
    form.dataset.bound = '1';

    const body         = form.querySelector('.items-body');
    const grandTotalEl = form.querySelector('.grand-total');
    const btnAdd       = form.querySelector('.btn-add-item');
    const defaultPrice = {{ (int) $defaultPrice }};

    function fmt(n) { return new Intl.NumberFormat('fr-FR').format(n) + ' DH'; }

    function reindexRows() {
        body.querySelectorAll('.item-row').forEach((row, i) => {
            row.querySelector('.row-index').textContent = i + 1;
            row.querySelectorAll('[name^="items["]').forEach(inp => {
                inp.name = inp.name.replace(/items\[\d+\]/, `items[${i}]`);
            });
        });
    }

    function recompute() {
        let grand = 0;
        body.querySelectorAll('.item-row').forEach(row => {
            const pages = parseInt(row.querySelector('.item-pages').value || 0, 10);
            const price = parseInt(row.querySelector('.item-price').value || 0, 10);
            const total = pages * price;
            row.querySelector('.item-total').textContent = fmt(total);
            grand += total;
        });
        grandTotalEl.textContent = fmt(grand);
    }

    function addRow() {
        const idx = body.querySelectorAll('.item-row').length;
        const div = document.createElement('div');
        div.className = 'item-row border rounded p-2 bg-light-subtle';
        div.innerHTML = `
            <div class="row g-2 align-items-end">
                <div class="col-auto text-muted small fw-bold row-index pt-4">${idx + 1}</div>
                <div class="col">
                    <label class="form-label small mb-1 text-muted">Document / Type</label>
                    <input type="text" name="items[${idx}][doc_type]" class="form-control form-control-sm" placeholder="Ex : Bac, Relevés, Diplôme…">
                </div>
                <div class="col-2" style="min-width:80px">
                    <label class="form-label small mb-1 text-muted">Pages</label>
                    <input type="number" name="items[${idx}][page_count]" min="1" required class="form-control form-control-sm text-center fw-bold item-pages" value="1">
                </div>
                <div class="col-2" style="min-width:100px">
                    <label class="form-label small mb-1 text-muted">Prix/page</label>
                    <input type="number" name="items[${idx}][price_per_page]" min="0" required class="form-control form-control-sm text-center item-price" value="${defaultPrice}">
                </div>
                <div class="col-auto text-end" style="min-width:90px">
                    <label class="form-label small mb-1 text-muted d-block">Total</label>
                    <span class="fw-semibold text-primary item-total">0 DH</span>
                </div>
                <div class="col-auto">
                    <button type="button" class="btn btn-sm btn-outline-danger btn-remove-item" title="Supprimer ce document">
                        <i class="ti ti-trash"></i>
                    </button>
                </div>
            </div>
            <div class="mt-2">
                <label class="scan-drop d-flex align-items-center gap-2 px-2 py-1 rounded border border-dashed small text-muted mb-0" style="cursor:pointer">
                    <i class="ti ti-paperclip"></i>
                    <span>Joindre le(s) scan(s) de l'original — PDF, JPG, PNG (max 10 Mo)</span>
                    <input type="file" name="items[${idx}][scans][]" class="d-none scan-input" accept=".pdf,image/*" multiple>
                </label>
                <div class="scan-chips d-flex flex-wrap gap-1 mt-1"></div>
            </div>`;
        body.appendChild(div);
        recompute();
    }

    function renderChips(input) {
        const wrap = input.closest('.item-row').querySelector('.scan-chips');
        if (!wrap) return;
        // Revoke any object URLs from a previous selection to avoid leaks.
        wrap.querySelectorAll('a[data-objurl]').forEach(a => URL.revokeObjectURL(a.href));
        wrap.innerHTML = '';
        Array.from(input.files).forEach(f => {
            const isPdf = /pdf$/i.test(f.type) || /\.pdf$/i.test(f.name);
            const url   = URL.createObjectURL(f);
            const chip  = document.createElement('a');
            chip.href = url;
            chip.target = '_blank';
            chip.rel = 'noopener';
            chip.dataset.objurl = '1';
            chip.title = 'Cliquer pour voir « ' + f.name + ' »';
            chip.className = 'badge bg-primary-subtle text-primary border d-inline-flex align-items-center gap-1 text-decoration-none';
            chip.innerHTML = `<i class="ti ${isPdf ? 'ti-file-type-pdf' : 'ti-photo'}"></i><span class="text-truncate" style="max-width:140px">${f.name}</span><i class="ti ti-external-link"></i>`;
            wrap.appendChild(chip);
        });
    }

    btnAdd.addEventListener('click', addRow);

    body.addEventListener('input', e => {
        if (e.target.matches('.item-pages, .item-price')) recompute();
    });

    body.addEventListener('change', e => {
        if (e.target.matches('.scan-input')) renderChips(e.target);
    });

    body.addEventListener('click', e => {
        // Remove an existing (already saved) scan → flag it for server-side deletion.
        const rm = e.target.closest('.btn-remove-scan');
        if (rm) {
            const id = rm.dataset.media;
            const hidden = document.createElement('input');
            hidden.type = 'hidden';
            hidden.name = 'remove_scans[]';
            hidden.value = id;
            form.appendChild(hidden);
            rm.closest('[data-media]').remove();
            return;
        }

        const btn = e.target.closest('.btn-remove-item');
        if (!btn) return;
        if (body.querySelectorAll('.item-row').length <= 1) {
            alert('Une commande doit contenir au moins un document.');
            return;
        }
        btn.closest('.item-row').remove();
        reindexRows();
        recompute();
    });

    recompute();
})();
</script>
