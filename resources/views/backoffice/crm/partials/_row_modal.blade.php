{{--
    Reusable Bootstrap modal that displays a CRM row in a human-friendly format.

    The table partial injects a `data-row-json="…"` attribute into each row's
    eye button. The JS at the bottom of this partial:
      1. Catches clicks on .crm-row-view buttons
      2. Parses the JSON
      3. Re-renders Classes / Payments / Students rows into clean tabs/lists
      4. Falls back to a generic property list for unknown row shapes
--}}

<div class="modal fade" id="crmRowModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-light">
                <h5 class="modal-title" id="crmRowModalTitle">
                    <i class="ti ti-info-circle text-primary me-1"></i>
                    Détails
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
            </div>
            <div class="modal-body" id="crmRowModalBody">
                <div class="text-center text-muted py-4">
                    <i class="ti ti-loader spin"></i> Chargement...
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Fermer</button>
            </div>
        </div>
    </div>
</div>

{{-- Fullscreen matrix modal — "Statistique de groupe" per class, mirrors the
     reference CRM screenshot: rows = active students, cols = subscription
     service labels (months), cells = paid/partial/unpaid/n.a. --}}
<div class="modal fade" id="crmPaymentMatrixModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-fullscreen modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header bg-light">
                <h5 class="modal-title" id="crmPaymentMatrixTitle">
                    <i class="ti ti-table text-success me-1"></i> Statistique de groupe
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
            </div>
            <div class="modal-body" id="crmPaymentMatrixBody">
                <div class="crm-matrix-loader">
                    <div class="crm-matrix-loader__spinner" aria-hidden="true"></div>
                    <div class="crm-matrix-loader__title">
                        Chargement de la matrice<span class="crm-matrix-loader__dots"><span>.</span><span>.</span><span>.</span></span>
                    </div>
                    <div class="crm-matrix-loader__sub">Préparation...</div>
                    <div class="crm-matrix-loader__bar" aria-hidden="true"></div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-sm btn-success" id="crmPaymentMatrixExport">
                    <i class="ti ti-file-spreadsheet me-1"></i> Télécharger EXCEL
                </button>
                <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Fermer</button>
            </div>
        </div>
    </div>
</div>

{{-- Dedicated XL modal for the "click on a count pill" student-table view --}}
<div class="modal fade" id="crmStudentsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-light">
                <h5 class="modal-title" id="crmStudentsModalTitle">
                    <i class="ti ti-users text-primary me-1"></i> La liste d'étudiants
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
            </div>
            <div class="modal-body" id="crmStudentsModalBody"></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Fermer</button>
            </div>
        </div>
    </div>
</div>

{{-- Styles extracted to public/assets/css/backoffice/crm-row-modal.css.
     @once ensures the <link> is emitted only on the first include — this
     partial is included from multiple CRM pages but the asset is identical. --}}
@once
    <link rel="stylesheet" href="{{ asset('assets/css/backoffice/crm-row-modal.css') }}">
@endonce

{{-- Script extracted to public/assets/js/backoffice/crm-row-modal.js.
     Three values must reach JS at render time:
       - CRM_DEFAULT_AVATAR     : asset URL the JS uses when avatars 404
       - CRM_STORE_ID_NAMES     : storeId → human centre name (from the sites table)
       - CRM_PAYMENT_MATRIX_URL : matrix endpoint with {id} placeholder

     We expose them as window.* globals BEFORE the external file loads.
     The inline bootstrap is tiny and contains only @json output — the rest
     of the logic lives in the cacheable static .js file. --}}
@once
    <script>
        window.CRM_DEFAULT_AVATAR     = @json(asset('build/images/user/avatar-1.jpg'));
        window.CRM_STORE_ID_NAMES     = @json(app(\App\Services\Crm\CenterContext::class)->storeIdToName());
        window.CRM_PAYMENT_MATRIX_URL = @json(rtrim(url('/'), '/')) + '/backoffice/crm/groups/classes/{id}/payment-matrix';
    </script>
    <script src="{{ asset('assets/js/backoffice/crm-row-modal.js') }}" defer></script>
@endonce

