const center = document.getElementById('liveNotificationCenter');

if (center) {
    const list = document.getElementById('liveNotificationList');
    const badge = document.getElementById('liveNotificationBadge');
    const status = document.getElementById('liveNotificationStatus');
    const toastStack = document.getElementById('liveNotificationToasts');
    const readAll = document.getElementById('markAllNotificationsRead');
    const known = new Set([...list.querySelectorAll('[data-notification-id]')].map((item) => String(item.dataset.notificationId)));
    const fallbackInterval = 60000;
    let fallbackTimer;
    let refreshTimer;
    let fetching = false;
    let realtimeConnected = false;
    let stopped = false;

    const escape = (value = '') => String(value).replace(/[&<>'"]/g, (character) => ({'&': '&amp;', '<': '&lt;', '>': '&gt;', "'": '&#39;', '"': '&quot;'}[character]));
    const setStatus = (label, className = '') => {
        status.textContent = label;
        status.className = `live-notification-status ${className}`.trim();
    };
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
            ? payload.notifications.map((item) => `<button type="button" class="live-notification-item ${item.read ? '' : 'is-unread'}" data-notification-id="${escape(item.id)}" data-read-url="${escape(item.read_url)}"><strong>${escape(item.title)}</strong><span>${escape(item.message)}</span><small>${escape(item.created_label)}</small></button>`).join('')
            : '<div class="live-notification-empty">No notifications yet.</div>';
        bindItems();
        for (const item of [...payload.notifications].reverse()) {
            const id = String(item.id);
            if (!known.has(id)) {
                known.add(id);
                toast(item);
                window.dispatchEvent(new CustomEvent('academic:notification', {detail: item}));
            }
        }
    };

    const receiveRealtimeNotification = (item) => {
        const id = String(item.id);
        if (known.has(id)) return;
        known.add(id);

        list.querySelector('.live-notification-empty')?.remove();
        list.insertAdjacentHTML('afterbegin', `<button type="button" class="live-notification-item is-unread" data-notification-id="${escape(id)}" data-read-url="${escape(item.read_url)}"><strong>${escape(item.title)}</strong><span>${escape(item.message)}</span><small>${escape(item.created_label ?? 'Just now')}</small></button>`);
        while (list.querySelectorAll('[data-notification-id]').length > 10) {
            list.querySelector('[data-notification-id]:last-of-type')?.remove();
        }
        const currentUnread = Number.parseInt(badge.textContent, 10) || 0;
        badge.textContent = currentUnread >= 99 ? '99+' : currentUnread + 1;
        badge.classList.remove('d-none');
        bindItems();
        toast(item);
        window.dispatchEvent(new CustomEvent('academic:notification', {detail: item}));
    };

    const refreshNotifications = async () => {
        if (fetching || stopped) return;
        fetching = true;
        try {
            const response = await window.axios.get(center.dataset.feedUrl, {headers: {Accept: 'application/json'}});
            render(response.data);
        } catch (error) {
            if (error.response?.status === 401 || error.response?.status === 403) stopped = true;
        } finally {
            fetching = false;
        }
    };

    const scheduleFallback = (delay = fallbackInterval) => {
        window.clearTimeout(fallbackTimer);
        if (realtimeConnected || stopped) return;
        fallbackTimer = window.setTimeout(async () => {
            if (!document.hidden) await refreshNotifications();
            scheduleFallback();
        }, delay);
    };
    const useFallback = () => {
        realtimeConnected = false;
        setStatus('Reconnecting · safe fallback active', 'is-fallback');
        scheduleFallback(5000);
    };

    readAll?.addEventListener('click', async () => {
        readAll.disabled = true;
        try {
            await window.axios.post(center.dataset.readAllUrl, {}, {headers: {Accept: 'application/json'}});
            await refreshNotifications();
        } finally {
            readAll.disabled = false;
        }
    });
    document.addEventListener('visibilitychange', () => {
        if (!document.hidden && !realtimeConnected) {
            refreshNotifications();
            scheduleFallback();
        }
    });

    bindItems();

    if (window.Echo && center.dataset.userId) {
        const channel = window.Echo.private(`users.${center.dataset.userId}.notifications`);
        channel.notification(receiveRealtimeNotification);
        channel.error(() => useFallback());

        const connection = window.Echo.connector?.pusher?.connection;
        connection?.bind('connected', () => {
            realtimeConnected = true;
            window.clearTimeout(fallbackTimer);
            setStatus('Live', 'is-live');
            refreshNotifications();
        });
        connection?.bind('connecting', () => setStatus('Connecting...'));
        connection?.bind('disconnected', useFallback);
        connection?.bind('unavailable', useFallback);
        connection?.bind('failed', useFallback);
        scheduleFallback(10000);
    } else {
        useFallback();
    }
}
