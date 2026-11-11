document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('[data-live-list-search]').forEach(function (container) {
        const search = container.querySelector('[data-live-list-input]');
        const clear = container.querySelector('[data-live-list-clear]');
        const count = container.parentElement?.querySelector('[data-live-list-count]');
        const rows = Array.from(document.querySelectorAll(container.dataset.rows));
        const emptyState = document.querySelector(container.dataset.empty);
        const noResults = document.querySelector(container.dataset.noResults);
        const label = container.dataset.label || 'records';

        const toolbar = container.closest('.card')?.querySelector('.table-filter-toolbar');
        const filterSelects = toolbar ? Array.from(toolbar.querySelectorAll('[data-filter-field]')) : [];
        const pillBtns = toolbar ? Array.from(toolbar.querySelectorAll('[data-pill-field]')) : [];
        const resetBtn = toolbar ? toolbar.querySelector('[data-filter-reset]') : null;

        if (!search || !count || !noResults) return;

        const filter = function () {
            const query = search.value.trim().toLowerCase();
            const activeFilters = filterSelects.map(sel => ({ field: sel.dataset.filterField, value: sel.value })).filter(f => f.value);

            let matches = 0;

            rows.forEach(function (row) {
                const matchesSearch = !query || (row.dataset.search || '').includes(query);
                const matchesSelects = activeFilters.every(f => (row.dataset[f.field] || '') === f.value);
                const isMatch = matchesSearch && matchesSelects;

                row.hidden = !isMatch;
                if (isMatch) {
                    matches++;
                    const serialCell = row.querySelector('.row-serial');
                    if (serialCell) serialCell.textContent = matches;
                }
            });

            noResults.hidden = rows.length === 0 || matches > 0;
            if (emptyState) emptyState.hidden = Boolean(query || activeFilters.length);
            if (clear) clear.hidden = !query;

            const isFiltered = Boolean(query || activeFilters.length);
            count.textContent = isFiltered ? `${matches} of ${rows.length} ${label}` : `${rows.length} ${label}`;

            // Update active state on pill buttons
            pillBtns.forEach(pill => {
                const field = pill.dataset.pillField;
                const val = pill.dataset.pillValue;
                const targetSelect = filterSelects.find(s => s.dataset.filterField === field);
                if (targetSelect) {
                    pill.classList.toggle('is-active', (targetSelect.value || '') === val);
                }
            });
        };

        search.addEventListener('input', filter);
        clear?.addEventListener('click', function () {
            search.value = '';
            filter();
            search.focus();
        });

        filterSelects.forEach(sel => sel.addEventListener('change', filter));

        pillBtns.forEach(pill => {
            pill.addEventListener('click', function () {
                const field = this.dataset.pillField;
                const val = this.dataset.pillValue;
                const targetSelect = filterSelects.find(s => s.dataset.filterField === field);
                if (targetSelect) {
                    targetSelect.value = (targetSelect.value === val ? '' : val);
                    filter();
                }
            });
        });

        resetBtn?.addEventListener('click', function () {
            search.value = '';
            filterSelects.forEach(sel => sel.value = '');
            filter();
        });
    });

    document.querySelectorAll('[data-auto-dismiss]').forEach(function (message) {
        setTimeout(function () {
            message.style.transition = 'opacity .35s ease';
            message.style.opacity = '0';
            setTimeout(() => message.remove(), 350);
        }, 2800);
    });
});
