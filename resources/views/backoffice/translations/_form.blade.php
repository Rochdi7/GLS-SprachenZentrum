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
      method="POST" id="{{ $formId }}" class="translation-form">
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
                <div class="col-md-5">
                    <label class="form-label small mb-1">Nom étudiant <span class="text-danger">*</span></label>
                    <input type="text" name="student_name" required
                           class="form-control"
                           placeholder="Ahmed Alami"
                           value="{{ old('student_name', $isEdit ? $translation->student_name : '') }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label small mb-1">Téléphone</label>
                    <input type="text" name="phone" class="form-control" placeholder="06…"
                           value="{{ old('phone', $isEdit ? $translation->phone : '') }}">
                </div>
            </div>
        </div>
    </div>

    {{-- DOCUMENTS (line items) --}}
    <div class="card mb-3">
        <div class="card-header d-flex align-items-center justify-content-between">
            <h6 class="mb-0"><i class="ti ti-files text-primary"></i> Documents à traduire</h6>
            <button type="button" class="btn btn-sm btn-outline-primary btn-add-item">
                <i class="ti ti-plus"></i> Ajouter un document
            </button>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table mb-0 align-middle items-table">
                    <thead class="table-light">
                        <tr>
                            <th style="width:40px" class="text-center">#</th>
                            <th>Document / Type</th>
                            <th style="width:110px" class="text-center">Pages</th>
                            <th style="width:140px" class="text-center">Prix / page (DH)</th>
                            <th style="width:140px" class="text-end">Total ligne</th>
                            <th style="width:60px" class="text-center"></th>
                        </tr>
                    </thead>
                    <tbody class="items-body">
                        @foreach($items as $idx => $item)
                            <tr class="item-row">
                                <td class="text-center text-muted row-index">{{ $idx + 1 }}</td>
                                <td>
                                    @if(!empty($item->id))
                                        <input type="hidden" name="items[{{ $idx }}][id]" value="{{ $item->id }}">
                                    @endif
                                    <input type="text" name="items[{{ $idx }}][doc_type]"
                                           class="form-control" placeholder="Ex : Bac, Relevés, Diplôme…"
                                           value="{{ old('items.'.$idx.'.doc_type', $item->doc_type) }}">
                                </td>
                                <td>
                                    <input type="number" name="items[{{ $idx }}][page_count]"
                                           min="1" required
                                           class="form-control text-center fw-bold item-pages"
                                           value="{{ old('items.'.$idx.'.page_count', $item->page_count) }}">
                                </td>
                                <td>
                                    <input type="number" name="items[{{ $idx }}][price_per_page]"
                                           min="0" required
                                           class="form-control text-center item-price"
                                           value="{{ old('items.'.$idx.'.price_per_page', $item->price_per_page) }}">
                                </td>
                                <td class="text-end fw-semibold text-primary item-total">
                                    {{ number_format((int)$item->page_count * (int)$item->price_per_page, 0, ',', ' ') }} DH
                                </td>
                                <td class="text-center">
                                    <button type="button" class="btn btn-sm btn-link text-danger p-0 btn-remove-item" title="Supprimer">
                                        <i class="ti ti-trash f-18"></i>
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="table-light">
                        <tr>
                            <td colspan="4" class="text-end text-uppercase small text-muted fw-bold">Total commande</td>
                            <td class="text-end fw-bold text-primary fs-5 grand-total">0 DH</td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
            @error('items')
                <div class="text-danger small px-3 py-2">{{ $message }}</div>
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
            row.querySelectorAll('input[name^="items["]').forEach(inp => {
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
        const tr  = document.createElement('tr');
        tr.className = 'item-row';
        tr.innerHTML = `
            <td class="text-center text-muted row-index">${idx + 1}</td>
            <td><input type="text" name="items[${idx}][doc_type]" class="form-control" placeholder="Ex : Bac, Relevés, Diplôme…"></td>
            <td><input type="number" name="items[${idx}][page_count]" min="1" required class="form-control text-center fw-bold item-pages" value="1"></td>
            <td><input type="number" name="items[${idx}][price_per_page]" min="0" required class="form-control text-center item-price" value="${defaultPrice}"></td>
            <td class="text-end fw-semibold text-primary item-total">0 DH</td>
            <td class="text-center">
                <button type="button" class="btn btn-sm btn-link text-danger p-0 btn-remove-item" title="Supprimer">
                    <i class="ti ti-trash f-18"></i>
                </button>
            </td>`;
        body.appendChild(tr);
        recompute();
    }

    btnAdd.addEventListener('click', addRow);

    body.addEventListener('input', e => {
        if (e.target.matches('.item-pages, .item-price')) recompute();
    });

    body.addEventListener('click', e => {
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
