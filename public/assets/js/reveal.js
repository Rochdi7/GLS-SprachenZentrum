document.addEventListener("DOMContentLoaded", () => {
    const elements = document.querySelectorAll('.reveal, .fade-blur-title');

    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('visible');
                observer.unobserve(entry.target);
            }
        });
    }, { threshold: 0.1, rootMargin: '0px 0px -40px 0px' });

    elements.forEach(el => {
        const rect = el.getBoundingClientRect();
        if (rect.top < window.innerHeight && rect.bottom > 0) {
            el.classList.add('visible');
        } else {
            observer.observe(el);
        }
    });
});

// ===============================
document.addEventListener('DOMContentLoaded', () => {

  // Safety check: Bootstrap available
  if (typeof bootstrap === 'undefined' || !bootstrap.Modal) {
    console.error('Bootstrap Modal not loaded. Make sure bootstrap.bundle.js is included.');
    return;
  }

  const modalEl = document.getElementById('groupsSiteModal');
  if (!modalEl) return;

  const modalInstance = bootstrap.Modal.getOrCreateInstance(modalEl);

  // Open modal from cards
  document.querySelectorAll('[data-action="open-groups-site-modal"]').forEach(trigger => {
    trigger.addEventListener('click', (e) => {
      e.preventDefault();
      modalInstance.show();
    });
  });

  // Close modal when a site is selected (navigation continues)
  modalEl.querySelectorAll('.gls-site-pill').forEach(link => {
    link.addEventListener('click', () => {
      modalInstance.hide();
    });
  });

});