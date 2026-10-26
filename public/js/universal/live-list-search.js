document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('[data-live-list-search]').forEach(function (container) {
        const search = container.querySelector('[data-live-list-input]');
        const clear = container.querySelector('[data-live-list-clear]');
        const count = container.parentElement?.querySelector('[data-live-list-count]');
        const rows = Array.from(document.querySelectorAll(container.dataset.rows));
        const emptyState = document.querySelector(container.dataset.empty);
        const noResults = document.querySelector(container.dataset.noResults);
        const label = container.dataset.label || 'records';

        if (!search || !count || !noResults) return;

        const filter = function () {
            const query = search.value.trim().toLowerCase();
            let matches = 0;

            rows.forEach(function (row) {
                const isMatch = !query || (row.dataset.search || '').includes(query);
                row.hidden = !isMatch;
                if (isMatch) matches++;
            });

            noResults.hidden = rows.length === 0 || matches > 0;
            if (emptyState) emptyState.hidden = Boolean(query);
            if (clear) clear.hidden = !query;
            count.textContent = query ? `${matches} of ${rows.length} ${label}` : `${rows.length} ${label}`;
        };

        search.addEventListener('input', filter);
        clear?.addEventListener('click', function () {
            search.value = '';
            filter();
            search.focus();
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
