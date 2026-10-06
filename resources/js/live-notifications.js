const center = document.getElementById('liveNotificationCenter');

if (center) {
    const list = document.getElementById('liveNotificationList');
    const badge = document.getElementById('liveNotificationBadge');
    const toastStack = document.getElementById('liveNotificationToasts');
    const readAll = document.getElementById('markAllNotificationsRead');
    const known = new Set([...list.querySelectorAll('[data-notification-id]')].map((item) => item.dataset.notificationId));
    let timer;
    let refreshTimer;

    const escape = (value = '') => String(value).replace(/[&<>'"]/g, (character) => ({'&': '&amp;', '<': '&lt;', '>': '&gt;', "'": '&#39;', '"': '&quot;'}[character]));
    const openNotification = async (readUrl) => {
        const response = await window.axios.post(readUrl, {}, {headers: {Accept: 'application/json'}});
        window.location.assign(response.data.redirect_url);
    };
    const bindItems = () => list.querySelectorAll('[data-read-url]').forEach((item) => {
        if (item.dataset.bound) return;
        item.dataset.bound = 'true';
        item.addEventListener('click', () => openNotification(item.dataset.readUrl));
    });
    const toast = (item) => {
        const node = document.createElement('button');
        node.type = 'button';
        node.className = 'live-notification-toast';
        node.innerHTML = `<strong>${escape(item.title)}</strong><span>${escape(item.message)}</span>`;
        node.addEventListener('click', () => openNotification(item.read_url));
        toastStack.append(node);
        requestAnimationFrame(() => node.classList.add('show'));
        setTimeout(() => { node.classList.remove('show'); setTimeout(() => node.remove(), 250); }, 7000);
    };
    const refreshLiveRegion = () => {
        const region = document.querySelector('[data-live-refresh]');
        if (!region) return;
        window.clearTimeout(refreshTimer);
        refreshTimer = window.setTimeout(async () => {
            try {
                const response = await window.axios.get(window.location.href, {headers: {Accept: 'text/html'}});
                const documentCopy = new DOMParser().parseFromString(response.data, 'text/html');
                const updated = documentCopy.querySelector('[data-live-refresh]');
                if (updated) region.innerHTML = updated.innerHTML;
            } catch (_) {
                // The notification remains available even if a page region cannot be refreshed.
            }
        }, 300);
    };
    window.addEventListener('academic:notification', refreshLiveRegion);
    const render = (payload) => {
        badge.textContent = payload.unread_count > 99 ? '99+' : payload.unread_count;
        badge.classList.toggle('d-none', payload.unread_count === 0);
        list.innerHTML = payload.notifications.length
            ? payload.notifications.map((item) => `<button type="button" class="live-notification-item ${item.read ? '' : 'is-unread'}" data-notification-id="${item.id}" data-read-url="${item.read_url}"><strong>${escape(item.title)}</strong><span>${escape(item.message)}</span><small>${escape(item.created_label)}</small></button>`).join('')
            : '<div class="live-notification-empty">No notifications yet.</div>';
        bindItems();
        for (const item of [...payload.notifications].reverse()) {
            if (!known.has(item.id)) {
                known.add(item.id);
                toast(item);
                window.dispatchEvent(new CustomEvent('academic:notification', {detail: item}));
            }
        }
    };
    const poll = async () => {
        try {
            const response = await window.axios.get(center.dataset.feedUrl, {headers: {Accept: 'application/json'}});
            render(response.data);
        } catch (error) {
            if (error.response?.status === 401 || error.response?.status === 403) return;
        }
        timer = window.setTimeout(poll, document.hidden ? 12000 : 3000);
    };
    readAll?.addEventListener('click', async () => {
        await window.axios.post(center.dataset.readAllUrl, {}, {headers: {Accept: 'application/json'}});
        await poll();
    });
    document.addEventListener('visibilitychange', () => {
        if (!document.hidden) { window.clearTimeout(timer); poll(); }
    });
    bindItems();
    timer = window.setTimeout(poll, 3000);
}
