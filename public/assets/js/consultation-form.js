(function () {
    // Get root container - all queries scoped within this element
    const root = document.getElementById('consultationRoot');
    if (!root) return;

    // Scoped DOM queries within root
    const form = root.querySelector('#consultationForm');
    const errorBox = root.querySelector('#consultationErrorMessage');
    const successBox = root.querySelector('#consultationSuccessMessage');
    const submitBtn = root.querySelector('#consultationSubmitBtn');
    const modalEl = document.getElementById('consultationModal');
    const headerEl = root.querySelector('.form-header');

    function showError(message) {
        if (!errorBox) return;
        errorBox.textContent = message;
        errorBox.classList.add('active', 'error');
    }

    function clearError() {
        if (!errorBox) return;
        errorBox.textContent = '';
        errorBox.classList.remove('active', 'error');
    }

    function setHeaderVisible(visible) {
        if (!headerEl) return;
        headerEl.style.display = visible ? '' : 'none';
    }

    function setSuccessVisible(visible) {
        if (!successBox) return;
        successBox.classList.toggle('active', visible);
    }

    function setFormVisible(visible) {
        if (!form) return;
        form.style.display = visible ? '' : 'none';
    }

    function resetFormUI() {
        setFormVisible(true);
        if (form) form.reset();
        clearError();
        setSuccessVisible(false);
        setHeaderVisible(true);
        if (submitBtn) submitBtn.disabled = false;
    }

    async function safeJson(response) {
        const ct = response.headers.get('content-type') || '';
        if (ct.includes('application/json')) return response.json();
        const text = await response.text();
        return { message: text || 'Réponse non valide du serveur.' };
    }

    if (!form) return;

    form.addEventListener('submit', async function (e) {
        e.preventDefault();
        clearError();

        if (submitBtn) submitBtn.disabled = true;

        try {
            const response = await fetch(form.action, {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                },
                body: new FormData(form)
            });

            const data = await safeJson(response);

            if (!response.ok) {
                if (data && data.errors) {
                    const firstKey = Object.keys(data.errors)[0];
                    showError(data.errors[firstKey][0] || 'Veuillez vérifier vos informations.');
                } else {
                    showError(data.message || 'Une erreur est survenue. Réessayez.');
                }
                if (submitBtn) submitBtn.disabled = false;
                return;
            }

            setFormVisible(false);
            setHeaderVisible(false);
            clearError();
            setSuccessVisible(true);

            if (window.glsTrack) {
                window.glsTrack('consultation_submit', { event_category: 'Consultation', event_label: 'Consultation Modal' });
            }

        } catch (err) {
            showError('Impossible d\'envoyer la demande. Vérifiez votre connexion.');
            if (submitBtn) submitBtn.disabled = false;
        }
    });

    if (modalEl) {
        modalEl.addEventListener('hidden.bs.modal', function () {
            resetFormUI();
        });
    }
})();
