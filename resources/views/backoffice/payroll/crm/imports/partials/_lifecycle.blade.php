{{--
    Payment info card (optional). No status lifecycle — imports are managed by
    admins/super-admins only and are always editable. This card lets an admin
    record how/when the professor was paid.

    Expects: $import, $group.
--}}
<div class="card shadow-sm mb-3">
    <div class="card-header bg-white d-flex align-items-center justify-content-between">
        <h6 class="mb-0 fw-bold"><i class="ph-duotone ph-currency-circle-dollar me-1"></i>Informations de paiement</h6>
        @if($import->payment_date)
            <span class="badge bg-success"><i class="ph-duotone ph-check-circle me-1"></i>Payé</span>
        @else
            <span class="badge bg-light-secondary text-muted">Non renseigné</span>
        @endif
    </div>
    <div class="card-body">
        <form method="POST" action="{{ route('backoffice.payroll.crm.legacy.import.payment-info', ['group'=>$group->id,'import'=>$import->id]) }}">
            @csrf
            <div class="row g-3">
                <div class="col-md-3">
                    <label class="form-label small fw-semibold">Date de paiement</label>
                    <input type="date" name="payment_date" class="form-control form-control-sm"
                           value="{{ old('payment_date', optional($import->payment_date)->toDateString()) }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-semibold">Mode de paiement</label>
                    <select name="payment_method" class="form-select form-select-sm">
                        <option value="">—</option>
                        @foreach(\App\Models\PresenceImport::PAYMENT_METHOD_LABELS as $val => $lbl)
                            <option value="{{ $val }}" {{ old('payment_method', $import->payment_method) === $val ? 'selected' : '' }}>{{ $lbl }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-semibold">Référence / N° transaction</label>
                    <input type="text" name="payment_reference" class="form-control form-control-sm" maxlength="100"
                           value="{{ old('payment_reference', $import->payment_reference) }}" placeholder="Optionnel">
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-semibold">Notes</label>
                    <input type="text" name="payment_notes" class="form-control form-control-sm" maxlength="1000"
                           value="{{ old('payment_notes', $import->payment_notes) }}" placeholder="Optionnel">
                </div>
            </div>

            <div class="d-flex align-items-center justify-content-between mt-3">
                <small class="text-muted">
                    @if($import->paid_at)
                        <i class="ph-duotone ph-user me-1"></i>Dernière mise à jour : {{ $import->paidBy?->name ?? '—' }} · {{ $import->paid_at->format('d/m/Y H:i') }}
                    @else
                        Renseignez la date pour marquer ce paiement comme payé.
                    @endif
                </small>
                <button class="btn btn-success btn-sm"><i class="ph-duotone ph-floppy-disk me-1"></i>Enregistrer</button>
            </div>
        </form>
    </div>
</div>
