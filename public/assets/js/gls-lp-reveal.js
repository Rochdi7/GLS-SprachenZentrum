/**
 * GLS Landing Page - Scroll reveal animations
 * File: public/assets/js/gls-lp-reveal.js
 *
 * Targets elements inside .lp-meta-scope or .lp-google-scope that have a
 * `data-reveal` attribute. Adds `.lp-in-view` when they enter the viewport.
 * Honors prefers-reduced-motion.
 */

(function () {
  'use strict';

  const scope = document.querySelector('.lp-meta-scope, .lp-google-scope');
  if (!scope) return;

  const prefersReduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  const targets = scope.querySelectorAll('[data-reveal]');
  if (!targets.length) return;

  if (prefersReduce || !('IntersectionObserver' in window)) {
    // No animation: just reveal everything immediately.
    targets.forEach((el) => el.classList.add('lp-in-view'));
    return;
  }

  const io = new IntersectionObserver(
    (entries) => {
      entries.forEach((entry) => {
        if (entry.isIntersecting) {
          entry.target.classList.add('lp-in-view');
          io.unobserve(entry.target);
        }
      });
    },
    {
      threshold: 0.15,
      rootMargin: '0px 0px -8% 0px',
    }
  );

  targets.forEach((el) => io.observe(el));
})();
