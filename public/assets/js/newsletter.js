document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('newsletterForm');
            if (!form) return;

            const msg = document.getElementById('newsletterMsg');
            const btn = document.getElementById('newsletterBtn');
            const emailInput = document.getElementById('newsletterEmail');

            form.addEventListener('submit', async function(e) {
                e.preventDefault();

                msg.textContent = '';
                msg.className = 'small mt-2';
                btn.disabled = true;

                if (window.glsTrack) {
                    window.glsTrack('Newsletter_Send_Clicked', { event_category: 'Newsletter', event_label: window.location.pathname });
                }

                try {
                    const formData = new FormData(form);

                    const res = await fetch(form.action, {
                        method: 'POST',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': form.querySelector('input[name="_token"]').value,
                            'Accept': 'application/json',
                        },
                        body: formData,
                    });

                    const data = await res.json();

                    if (!res.ok) {
                        const firstError = (data && data.errors && data.errors.email && data.errors
                                .email[0]) ?
                            data.errors.email[0] :
                            (data && data.message ? data.message : 'Error');

                        msg.textContent = firstError;
                        msg.classList.add('text-danger');
                        return;
                    }

                    msg.textContent = data.message || 'OK';
                    msg.classList.add('text-success');

                    if (data.status === 'subscribed') {
                        emailInput.value = '';
                        if (window.glsTrack) {
                            window.glsTrack('Newsletter_Signup_Completed', { event_category: 'Newsletter', event_label: window.location.pathname });
                        }
                    }
                } catch (err) {
                    msg.textContent = "{{ __('footer.newsletter.error') }}";
                    msg.classList.add('text-danger');
                } finally {
                    btn.disabled = false;
                }
            });
        });

        // Back to Top Button
        document.addEventListener('DOMContentLoaded', function() {
            const btn = document.getElementById('backToTop');
            if (!btn) return;

            window.addEventListener('scroll', () => {
                if (window.scrollY > 300) {
                    btn.classList.add('show');
                } else {
                    btn.classList.remove('show');
                }
            });

            btn.addEventListener('click', () => {
                window.scrollTo({
                    top: 0,
                    behavior: 'smooth'
                });
            });
        });