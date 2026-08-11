/**
 * Group Apply Modal
 * Scoped to #glsApplyRoot to prevent conflicts with other modals
 */

(function () {
    // Get root container - all queries scoped within this element
    const root = document.getElementById('glsApplyRoot');
    if (!root) return;

    const modalEl = document.getElementById('glsApplyGroupModal');
    if (!modalEl) return;

    // Scoped DOM queries within root
    const form = root.querySelector('#applyGroupForm');
    const headerEl = root.querySelector('#applyGroupFormHeader');
    const errorBox = root.querySelector('#applyGroupErrorMessage');
    const successBox = root.querySelector('#applyGroupSuccessMessage');
    const successText = root.querySelector('#applyGroupSuccessText');

    const inputGroupId = root.querySelector('#applyGroupId');
    const inputLabel = root.querySelector('#applyGroupLabel');
    const inputSchedule = root.querySelector('#applyGroupSchedule');
    const inputLevel = root.querySelector('#applyGroupLevel');

    const groupsDataSelect = document.getElementById('applyGroupsData');

    let submitting = false;

    // Helper functions - scoped state only
    function showError(msg) {
        errorBox.textContent = msg;
        errorBox.classList.add('active');
    }

    function clearError() {
        errorBox.textContent = '';
        errorBox.classList.remove('active');
    }

    function setSuccessMode(message) {
        clearError();
        if (headerEl) headerEl.style.display = 'none';
        form.style.display = 'none';
        if (message && successText) successText.textContent = message;
        successBox.classList.add('active');
    }

    function setFormMode() {
        if (headerEl) headerEl.style.display = '';
        successBox.classList.remove('active');
        form.style.display = '';
    }

    function getGroupDataFromSelect(groupId) {
        if (!groupsDataSelect) return null;
        const opt = [...groupsDataSelect.options].find(o => String(o.value) === String(groupId));
        if (!opt) return null;

        return {
            id: opt.value,
            label: opt.dataset.label || '',
            schedule: opt.dataset.schedule || '',
            level: opt.dataset.level || ''
        };
    }

    function fillGroup(payload) {
        const groupId = (typeof payload === 'object') ? payload.id : payload;
        inputGroupId.value = groupId || '';

        if (!groupId) {
            inputLabel.value = '';
            inputSchedule.value = '';
            inputLevel.value = '';
            return;
        }

        if (typeof payload === 'object') {
            inputLabel.value = payload.label || ('Groupe #' + groupId);
            inputSchedule.value = payload.schedule || '';
            inputLevel.value = payload.level || '';
            return;
        }

        const data = getGroupDataFromSelect(groupId);
        if (data) {
            inputLabel.value = data.label || ('Groupe #' + groupId);
            inputSchedule.value = data.schedule || '';
            inputLevel.value = data.level || '';
            return;
        }

        inputLabel.value = 'Groupe #' + groupId;
        inputSchedule.value = '';
        inputLevel.value = '';
    }

    function openModal() {
        if (window.bootstrap && bootstrap.Modal) {
            const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
            modal.show();
        }
    }

    // Open from button with data attributes
    document.addEventListener('click', function (e) {
        const btn = e.target.closest('.gls-apply-btn');
        if (!btn) return;

        e.preventDefault();

        const payload = {
            id: btn.dataset.groupId || '',
            label: btn.dataset.groupLabel || '',
            level: btn.dataset.groupLevel || '',
            schedule: btn.dataset.groupSchedule || '',
        };

        fillGroup(payload);
        clearError();
        setFormMode();
        openModal();
    });

    // Optional: auto-open from ?group=ID query parameter
    const params = new URLSearchParams(window.location.search);
    const groupFromQuery = params.get('group');
    if (groupFromQuery) {
        fillGroup(groupFromQuery);
        clearError();
        setFormMode();
        openModal();
    }

    // AJAX submit - no page refresh
    form.addEventListener('submit', async function (e) {
        e.preventDefault();
        clearError();

        if (!inputGroupId.value) {
            showError('Group is missing. Please click Apply from a group.');
            return;
        }

        if (submitting) return;
        submitting = true;

        const btn = root.querySelector('#applyGroupSubmitBtn');
        if (btn) btn.disabled = true;

        try {
            const url = form.getAttribute('action');
            const formData = new FormData(form);

            const res = await fetch(url, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                },
            });

            const data = await res.json().catch(() => ({}));

            // Success
            if (res.ok && data.status === 'success') {
                setSuccessMode(data.message || 'Votre demande a bien été envoyée.');
                if (window.glsTrack) {
                    window.glsTrack('group_apply_submit', {
                        event_category: 'Group Application',
                        event_label: inputLabel.value || 'Group Apply Modal'
                    });
                }
                return;
            }

            // Duplicate detection (409)
            if (res.status === 409 && data.status === 'duplicate') {
                showError(data.message || 'Duplication: demande déjà envoyée.');
                // keep form visible, no refresh
                setFormMode();
                return;
            }

            // Validation errors (422)
            if (res.status === 422 && data.errors) {
                const firstField = Object.keys(data.errors)[0];
                const firstMsg = firstField ? data.errors[firstField][0] : 'Erreur de validation.';
                showError(firstMsg);
                setFormMode();
                return;
            }

            // Other errors
            showError(data.message || 'Une erreur est survenue. Merci de réessayer.');
            setFormMode();

        } catch (err) {
            showError('Erreur réseau. Merci de réessayer.');
            setFormMode();
        } finally {
            submitting = false;
            if (btn) btn.disabled = false;
        }
    });

    // Reset modal state on close
    modalEl.addEventListener('hidden.bs.modal', function () {
        clearError();
        setFormMode();
        submitting = false;

        form.reset();
        fillGroup('');

        const btn = root.querySelector('#applyGroupSubmitBtn');
        if (btn) btn.disabled = false;
    });

})();
