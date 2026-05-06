/* =========================================================
   GLS Custom Form Fields — shared JS
   - AttSelectInit:    progressive-enhanced custom <select>
   - AttDatepickerInit: progressive-enhanced custom date input
   Native inputs are kept in the DOM for form submission and
   accessibility. On any error, the native control is revealed
   and the custom UI hidden (failsafe).
   ========================================================= */
(function () {
    if (window.AttSelectInit && window.AttDatepickerInit) return;

    /* ---------------- Custom Select ---------------- */
    var AttSelect = (function () {
        function init(root) {
            if (!root || root.__attSelectBound) return;
            if (root.classList.contains('att-select--no-enhance')) return;
            try {
                var native = root.querySelector('.att-select__native');
                var btn    = root.querySelector('.att-select__btn');
                var value  = root.querySelector('.att-select__value');
                var menu   = root.querySelector('.att-select__menu');
                if (!native || !btn || !menu || !value) return;
                root.__attSelectBound = true;

                if (!value.dataset.placeholder) {
                    value.dataset.placeholder = value.textContent.trim();
                }

                var opts = [];
                var activeIdx = -1;
                var rebuilding = false;

                function rebuildMenu() {
                    if (rebuilding) return;
                    rebuilding = true;
                    try {
                        menu.innerHTML = '';
                        opts = [];
                        Array.prototype.forEach.call(native.options, function (o) {
                            // Skip empty placeholder options (no label or value)
                            if (o.value === '' && (!o.textContent || !o.textContent.trim())) return;
                            // Skip explicitly hidden options
                            if (o.hidden || (o.style && o.style.display === 'none')) return;
                            // Skip the empty placeholder when there are real options
                            if (o.value === '' && native.options.length > 1) {
                                // Keep the placeholder text for the button label only
                                return;
                            }
                            var li = document.createElement('li');
                            li.className = 'att-select__opt';
                            li.setAttribute('role', 'option');
                            li.dataset.value = o.value;
                            li.tabIndex = -1;
                            li.setAttribute('aria-selected', native.value === o.value && o.value !== '' ? 'true' : 'false');
                            if (o.disabled) li.setAttribute('aria-disabled', 'true');
                            var dot = document.createElement('span');
                            dot.className = 'att-select__dot';
                            dot.setAttribute('aria-hidden', 'true');
                            var label = document.createElement('span');
                            label.className = 'att-select__opt-label';
                            label.textContent = o.textContent;
                            li.appendChild(dot);
                            li.appendChild(label);
                            (function (idx) {
                                li.addEventListener('mouseenter', function () { setActive(idx); });
                            })(opts.length);
                            (function (el, optEl) {
                                li.addEventListener('click', function () { if (!optEl.disabled) pick(el); });
                            })(li, o);
                            menu.appendChild(li);
                            opts.push(li);
                        });
                        syncFromNative();
                    } finally {
                        rebuilding = false;
                    }
                }

                function syncFromNative() {
                    var val = native.value;
                    var selectedOpt = null;
                    Array.prototype.forEach.call(native.options, function (o) {
                        if (o.value === val) selectedOpt = o;
                    });
                    opts.forEach(function (o) {
                        o.setAttribute('aria-selected', o.dataset.value === val && val !== '' ? 'true' : 'false');
                    });
                    if (selectedOpt && selectedOpt.value !== '') {
                        value.textContent = selectedOpt.textContent;
                        value.classList.remove('att-select__value--placeholder');
                    } else {
                        // Use the empty option's text if present (preserves "Loading…" / "Select…" feedback)
                        var placeholder = value.dataset.placeholder;
                        Array.prototype.forEach.call(native.options, function (o) {
                            if (o.value === '' && o.textContent && o.textContent.trim()) {
                                placeholder = o.textContent;
                            }
                        });
                        value.textContent = placeholder;
                        value.classList.add('att-select__value--placeholder');
                    }
                    // Reflect disabled state visually
                    if (native.disabled) btn.setAttribute('aria-disabled', 'true');
                    else btn.removeAttribute('aria-disabled');
                }

                function positionMenu() {
                    var rect = btn.getBoundingClientRect();
                    var vh = window.innerHeight || document.documentElement.clientHeight;
                    var vw = document.documentElement.clientWidth;
                    menu.style.position = 'fixed';
                    // Clamp width and left so menu never extends beyond viewport edges
                    var width = Math.min(rect.width, vw - 16);
                    var left  = rect.left;
                    if (left + width > vw - 8) left = vw - 8 - width;
                    if (left < 8) left = 8;
                    menu.style.left   = left + 'px';
                    menu.style.width  = width + 'px';
                    menu.style.right  = 'auto';
                    menu.style.maxWidth = (vw - 16) + 'px';
                    var spaceBelow = vh - rect.bottom - 8;
                    var spaceAbove = rect.top - 8;
                    var minBelow = 120;
                    var maxH = 320;
                    if (spaceBelow < minBelow && spaceAbove > spaceBelow + 60) {
                        menu.style.top    = 'auto';
                        menu.style.bottom = (vh - rect.top + 8) + 'px';
                        menu.style.maxHeight = Math.max(160, Math.min(maxH, spaceAbove)) + 'px';
                    } else {
                        menu.style.top    = (rect.bottom + 8) + 'px';
                        menu.style.bottom = 'auto';
                        menu.style.maxHeight = Math.max(160, Math.min(maxH, spaceBelow)) + 'px';
                    }
                }
                var onReposition = function () { if (!menu.hidden) positionMenu(); };

                var menuHome = menu.parentNode;
                var menuNext = menu.nextSibling;

                function open() {
                    if (native.disabled) return;
                    if (!opts.length) rebuildMenu();
                    if (!opts.length) return;
                    root.classList.add('is-open');
                    btn.setAttribute('aria-expanded', 'true');
                    // Portal the menu into <body> so it cannot affect parent layout
                    if (menu.parentNode !== document.body) {
                        document.body.appendChild(menu);
                    }
                    menu.hidden = false;
                    positionMenu();
                    window.addEventListener('scroll', onReposition, true);
                    window.addEventListener('resize', onReposition);
                    var selectedIdx = opts.findIndex(function (o) { return o.getAttribute('aria-selected') === 'true'; });
                    setActive(selectedIdx >= 0 ? selectedIdx : 0);
                    document.addEventListener('mousedown', onDocClick, true);
                    document.addEventListener('keydown', onKeydown, true);
                }
                function close() {
                    root.classList.remove('is-open');
                    btn.setAttribute('aria-expanded', 'false');
                    menu.hidden = true;
                    setActive(-1);
                    window.removeEventListener('scroll', onReposition, true);
                    window.removeEventListener('resize', onReposition);
                    document.removeEventListener('mousedown', onDocClick, true);
                    document.removeEventListener('keydown', onKeydown, true);
                    // Restore menu to its original location
                    if (menu.parentNode !== menuHome) {
                        if (menuNext && menuNext.parentNode === menuHome) {
                            menuHome.insertBefore(menu, menuNext);
                        } else {
                            menuHome.appendChild(menu);
                        }
                    }
                }
                function toggle() { root.classList.contains('is-open') ? close() : open(); }

                function setActive(idx) {
                    opts.forEach(function (o) { o.classList.remove('is-active'); });
                    activeIdx = idx;
                    if (idx >= 0 && opts[idx]) {
                        opts[idx].classList.add('is-active');
                        opts[idx].scrollIntoView({ block: 'nearest' });
                    }
                }

                function pick(opt) {
                    native.value = opt.dataset.value;
                    native.dispatchEvent(new Event('input',  { bubbles: true }));
                    native.dispatchEvent(new Event('change', { bubbles: true }));
                    syncFromNative();
                    close();
                    btn.focus();
                }

                function onDocClick(e) {
                    if (root.contains(e.target)) return;
                    if (menu.contains(e.target)) return;
                    close();
                }
                function onKeydown(e) {
                    if (e.key === 'Escape')      { e.preventDefault(); close(); btn.focus(); }
                    else if (e.key === 'ArrowDown') { e.preventDefault(); setActive(Math.min(activeIdx + 1, opts.length - 1)); }
                    else if (e.key === 'ArrowUp')   { e.preventDefault(); setActive(Math.max(activeIdx - 1, 0)); }
                    else if (e.key === 'Home')      { e.preventDefault(); setActive(0); }
                    else if (e.key === 'End')       { e.preventDefault(); setActive(opts.length - 1); }
                    else if (e.key === 'Enter' || e.key === ' ') {
                        if (activeIdx >= 0) { e.preventDefault(); pick(opts[activeIdx]); }
                    }
                }

                btn.addEventListener('click', function (e) { e.preventDefault(); toggle(); });
                btn.addEventListener('keydown', function (e) {
                    if (!root.classList.contains('is-open') && (e.key === 'ArrowDown' || e.key === 'Enter' || e.key === ' ')) {
                        e.preventDefault(); open();
                    }
                });

                try {
                    var mo = new MutationObserver(function () { rebuildMenu(); });
                    mo.observe(native, { childList: true, subtree: true, attributes: true, attributeFilter: ['disabled'] });
                } catch (_) { /* noop */ }
                native.addEventListener('change', syncFromNative);

                rebuildMenu();
            } catch (err) {
                fail(root, err, 'att-select');
            }
        }
        function fail(root, err, kind) {
            try {
                root.classList.add(kind + '--no-enhance');
                if (window.console && console.warn) console.warn(kind + ' fallback to native:', err);
            } catch (_) {}
        }
        function initAll() { document.querySelectorAll('[data-att-select]').forEach(init); }
        return { init: init, initAll: initAll };
    })();

    /* ---------------- Custom Datepicker ---------------- */
    var AttDatepicker = (function () {
        function init(root) {
            if (!root || root.__attDpBound) return;
            if (root.classList.contains('att-datepicker--no-enhance')) return;
            try {
                var native     = root.querySelector('.att-datepicker__native');
                var btn        = root.querySelector('.att-datepicker__btn');
                var valueEl    = root.querySelector('.att-datepicker__value');
                var panel      = root.querySelector('.att-datepicker__panel');
                var weekdaysEl = root.querySelector('.att-datepicker__weekdays');
                var gridEl     = root.querySelector('.att-datepicker__grid');
                var monthBtn   = root.querySelector('[data-pick="month"]');
                var yearBtn    = root.querySelector('[data-pick="year"]');
                var prevBtn    = root.querySelector('[data-nav="prev"]');
                var nextBtn    = root.querySelector('[data-nav="next"]');
                var clearBtn   = root.querySelector('[data-action="clear"]');
                var todayBtn   = root.querySelector('[data-action="today"]');
                if (!native || !btn || !panel || !gridEl || !monthBtn || !yearBtn) return;

                var locale = root.dataset.locale || document.documentElement.lang || 'fr';
                var today  = new Date(); today.setHours(0,0,0,0);

                function parseISO(s) {
                    if (!s) return null;
                    var p = s.split('-'); if (p.length !== 3) return null;
                    var d = new Date(+p[0], +p[1] - 1, +p[2]); d.setHours(0,0,0,0);
                    return isNaN(d) ? null : d;
                }
                function toISO(d) {
                    return d.getFullYear() + '-' + String(d.getMonth()+1).padStart(2,'0') + '-' + String(d.getDate()).padStart(2,'0');
                }
                function formatLong(d) {
                    try { return d.toLocaleDateString(locale, { day:'2-digit', month:'long', year:'numeric' }); }
                    catch (_) { return toISO(d); }
                }
                function monthName(m, y) {
                    try { return new Date(y, m, 1).toLocaleDateString(locale, { month:'long' }); } catch(_) { return String(m+1); }
                }
                function shortWeekday(i) {
                    try {
                        var d = new Date(2024, 0, 1 + i);
                        return d.toLocaleDateString(locale, { weekday:'short' }).replace('.', '');
                    } catch(_) { return ['Mo','Tu','We','Th','Fr','Sa','Su'][i]; }
                }

                var selected = parseISO(native.value);
                var view = selected ? new Date(selected) : new Date(today);
                view.setDate(1);
                var mode = 'days';

                weekdaysEl.innerHTML = '';
                for (var i = 0; i < 7; i++) {
                    var s = document.createElement('span');
                    s.textContent = shortWeekday(i);
                    weekdaysEl.appendChild(s);
                }

                if (!valueEl.dataset.original) valueEl.dataset.original = valueEl.textContent;

                // Optional min/max from native
                function bounds() {
                    return {
                        min: parseISO(native.min || ''),
                        max: parseISO(native.max || '')
                    };
                }
                function disabledFor(date) {
                    var b = bounds();
                    if (b.min && date < b.min) return true;
                    if (b.max && date > b.max) return true;
                    return false;
                }

                function renderHeader() {
                    monthBtn.textContent = monthName(view.getMonth(), view.getFullYear());
                    yearBtn.textContent  = view.getFullYear();
                }

                function renderDays() {
                    gridEl.className = 'att-datepicker__grid';
                    gridEl.innerHTML = '';
                    var year = view.getFullYear(), month = view.getMonth();
                    var firstDay = new Date(year, month, 1);
                    var startOffset = (firstDay.getDay() + 6) % 7; // Mon=0
                    var daysInMonth = new Date(year, month + 1, 0).getDate();
                    var prevDays = new Date(year, month, 0).getDate();
                    var totalCells = Math.ceil((startOffset + daysInMonth) / 7) * 7;

                    for (var c = 0; c < totalCells; c++) {
                        var dayNum, isOther = false, dt;
                        if (c < startOffset) {
                            dayNum = prevDays - startOffset + 1 + c;
                            dt = new Date(year, month - 1, dayNum); isOther = true;
                        } else if (c >= startOffset + daysInMonth) {
                            dayNum = c - startOffset - daysInMonth + 1;
                            dt = new Date(year, month + 1, dayNum); isOther = true;
                        } else {
                            dayNum = c - startOffset + 1;
                            dt = new Date(year, month, dayNum);
                        }
                        dt.setHours(0,0,0,0);
                        var b = document.createElement('button');
                        b.type = 'button';
                        b.className = 'att-datepicker__day';
                        if (isOther) b.classList.add('is-other');
                        if (dt.getTime() === today.getTime()) b.classList.add('is-today');
                        if (selected && dt.getTime() === selected.getTime()) b.classList.add('is-selected');
                        if (disabledFor(dt)) b.disabled = true;
                        b.textContent = dayNum;
                        (function (date) { b.addEventListener('click', function () { pick(date); }); })(dt);
                        gridEl.appendChild(b);
                    }
                }

                function renderMonths() {
                    gridEl.className = 'att-datepicker__picker att-datepicker__picker--months';
                    gridEl.innerHTML = '';
                    for (var m = 0; m < 12; m++) {
                        var b = document.createElement('button');
                        b.type = 'button';
                        b.textContent = monthName(m, view.getFullYear());
                        if (m === view.getMonth()) b.classList.add('is-current');
                        (function (mm) {
                            b.addEventListener('click', function () { view.setMonth(mm); mode = 'days'; render(); });
                        })(m);
                        gridEl.appendChild(b);
                    }
                }

                function renderYears() {
                    gridEl.className = 'att-datepicker__picker att-datepicker__picker--years';
                    gridEl.innerHTML = '';
                    var current = view.getFullYear();
                    var start = current - 60;
                    var end = current + 5;
                    for (var y = start; y <= end; y++) {
                        var b = document.createElement('button');
                        b.type = 'button';
                        b.textContent = y;
                        if (y === current) b.classList.add('is-current');
                        (function (yy) {
                            b.addEventListener('click', function () { view.setFullYear(yy); mode = 'months'; render(); });
                        })(y);
                        gridEl.appendChild(b);
                    }
                    var cur = gridEl.querySelector('.is-current');
                    if (cur) cur.scrollIntoView({ block: 'center' });
                }

                function render() {
                    renderHeader();
                    if (mode === 'days') renderDays();
                    else if (mode === 'months') renderMonths();
                    else renderYears();
                }

                function syncBtn() {
                    if (selected) {
                        valueEl.textContent = formatLong(selected);
                        valueEl.classList.remove('att-datepicker__value--placeholder');
                    } else {
                        valueEl.textContent = valueEl.dataset.original;
                        valueEl.classList.add('att-datepicker__value--placeholder');
                    }
                }
                if (selected) syncBtn();

                function pick(date) {
                    if (disabledFor(date)) return;
                    selected = new Date(date); selected.setHours(0,0,0,0);
                    native.value = toISO(selected);
                    native.dispatchEvent(new Event('input',  { bubbles: true }));
                    native.dispatchEvent(new Event('change', { bubbles: true }));
                    syncBtn();
                    view = new Date(selected); view.setDate(1);
                    close(); btn.focus();
                }

                function positionPanel() {
                    var rect = btn.getBoundingClientRect();
                    var vh = window.innerHeight || document.documentElement.clientHeight;
                    var vw = window.innerWidth  || document.documentElement.clientWidth;
                    panel.style.position = 'fixed';
                    var pw = panel.offsetWidth || 320;
                    // Prefer aligning to button left, but clamp to viewport
                    var left = Math.min(rect.left, vw - pw - 12);
                    if (left < 12) left = 12;
                    panel.style.left = left + 'px';
                    panel.style.right = 'auto';
                    var spaceBelow = vh - rect.bottom - 8;
                    var spaceAbove = rect.top - 8;
                    // Datepicker panel is ~360px tall — flip up only when it really can't fit below
                    if (spaceBelow < 220 && spaceAbove > spaceBelow + 80) {
                        panel.style.top    = 'auto';
                        panel.style.bottom = (vh - rect.top + 8) + 'px';
                    } else {
                        panel.style.top    = (rect.bottom + 8) + 'px';
                        panel.style.bottom = 'auto';
                    }
                }
                var onPanelReposition = function () { if (!panel.hidden) positionPanel(); };

                var panelHome = panel.parentNode;
                var panelNext = panel.nextSibling;

                function open() {
                    if (native.disabled) return;
                    root.classList.add('is-open');
                    btn.setAttribute('aria-expanded', 'true');
                    if (panel.parentNode !== document.body) {
                        document.body.appendChild(panel);
                    }
                    panel.hidden = false;
                    mode = 'days';
                    view = selected ? new Date(selected) : new Date(today);
                    view.setDate(1);
                    render();
                    positionPanel();
                    window.addEventListener('scroll', onPanelReposition, true);
                    window.addEventListener('resize', onPanelReposition);
                    document.addEventListener('mousedown', onDocClick, true);
                    document.addEventListener('keydown', onKeydown, true);
                }
                function close() {
                    root.classList.remove('is-open');
                    btn.setAttribute('aria-expanded', 'false');
                    panel.hidden = true;
                    window.removeEventListener('scroll', onPanelReposition, true);
                    window.removeEventListener('resize', onPanelReposition);
                    document.removeEventListener('mousedown', onDocClick, true);
                    document.removeEventListener('keydown', onKeydown, true);
                    if (panel.parentNode !== panelHome) {
                        if (panelNext && panelNext.parentNode === panelHome) {
                            panelHome.insertBefore(panel, panelNext);
                        } else {
                            panelHome.appendChild(panel);
                        }
                    }
                }
                function toggle() { root.classList.contains('is-open') ? close() : open(); }



                btn.addEventListener('click', function (e) { e.preventDefault(); toggle(); });
                prevBtn.addEventListener('click', function () {
                    if (mode === 'days')        { view.setMonth(view.getMonth() - 1); }
                    else if (mode === 'months') { view.setFullYear(view.getFullYear() - 1); }
                    else                        { view.setFullYear(view.getFullYear() - 12); }
                    render();
                });
                nextBtn.addEventListener('click', function () {
                    if (mode === 'days')        { view.setMonth(view.getMonth() + 1); }
                    else if (mode === 'months') { view.setFullYear(view.getFullYear() + 1); }
                    else                        { view.setFullYear(view.getFullYear() + 12); }
                    render();
                });
                monthBtn.addEventListener('click', function () { mode = mode === 'months' ? 'days' : 'months'; render(); });
                yearBtn .addEventListener('click', function () { mode = mode === 'years'  ? 'days' : 'years';  render(); });

                if (clearBtn) clearBtn.addEventListener('click', function () {
                    selected = null;
                    native.value = '';
                    native.dispatchEvent(new Event('input',  { bubbles: true }));
                    native.dispatchEvent(new Event('change', { bubbles: true }));
                    syncBtn();
                    render();
                });
                if (todayBtn) todayBtn.addEventListener('click', function () { pick(today); });

                native.addEventListener('change', function () {
                    var d = parseISO(native.value);
                    selected = d;
                    syncBtn();
                });

                root.__attDpBound = true;
            } catch (err) {
                try {
                    root.classList.add('att-datepicker--no-enhance');
                    if (window.console && console.warn) console.warn('att-datepicker fallback to native:', err);
                } catch (_) {}
            }
        }
        function initAll() { document.querySelectorAll('[data-att-datepicker]').forEach(init); }
        return { init: init, initAll: initAll };
    })();

    /* Public API */
    window.AttSelectInit     = AttSelect;
    window.AttDatepickerInit = AttDatepicker;

    function bootAll() { AttSelect.initAll(); AttDatepicker.initAll(); }
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', bootAll);
    } else {
        bootAll();
    }
})();
