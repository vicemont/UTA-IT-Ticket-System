// Header menu (hamburger dropdown + notification bell) for the staff/ticket pages
(function () {
    var button = document.getElementById('hamburger');
    var menu = document.getElementById('headerMenu');
    if (!button || !menu) return;

    button.addEventListener('click', function (e) { e.stopPropagation(); menu.classList.toggle('show'); });
    document.addEventListener('click', function (e) { if (!menu.contains(e.target)) menu.classList.remove('show'); });

    // bell: show / hide the list of overdue tickets
    var bell = document.getElementById('notificationsButton');
    var panel = document.getElementById('notificationsPanel');
    if (!bell || !panel) return;

    bell.addEventListener('click', function () {
        panel.hidden = !panel.hidden;
        bell.setAttribute('aria-expanded', String(!panel.hidden));
    });

    /* ---------- live updates: check for new overdue tickets every so often ---------- */
    var header = document.querySelector('header.topbar');
    var pollMs = header ? parseInt(header.dataset.pollMs, 10) : 0;
    var pollUrl = header ? header.dataset.pollUrl : '';
    if (!(pollMs > 0) || !pollUrl) return;

    var badge = bell.querySelector('.count-badge');
    var toasts = document.getElementById('toastArea');
    var seen = {};   // tickets we already told the user about (the ones on the page when it loaded)
    Array.prototype.forEach.call(panel.querySelectorAll('.notif-item'), function (a) { seen[a.dataset.id] = true; });

    function cap(s) { return s.charAt(0).toUpperCase() + s.slice(1); }

    function renderPanel(alerts) {
        panel.textContent = '';
        if (!alerts.length) {
            var empty = document.createElement('p');
            empty.className = 'notif-empty';
            empty.textContent = 'No overdue tickets.';
            panel.appendChild(empty);
        }
        alerts.forEach(function (a) {
            var item = document.createElement('a');
            item.className = 'notif-item notif-' + a.priority;
            item.dataset.id = a.id;
            item.href = 'ticket.php?id=' + a.id;
            var title = document.createElement('strong');
            title.textContent = '#' + a.id + ' ' + a.title;
            var info = document.createElement('span');
            info.textContent = cap(a.priority) + ' priority \u00b7 ' + a.overdue_text + ' past due' + (a.assignedTo ? '' : ' \u00b7 Unassigned');
            item.appendChild(title);
            item.appendChild(info);
            panel.appendChild(item);
        });
    }

    function toast(a) {
        if (!toasts) return;
        var t = document.createElement('a');
        t.className = 'toast toast-' + a.priority;
        t.href = 'ticket.php?id=' + a.id;
        var head = document.createElement('strong');
        head.textContent = '\ud83d\udd14 Ticket overdue';
        var body = document.createElement('span');
        body.textContent = '#' + a.id + ' ' + a.title + ' (' + cap(a.priority) + ' priority)';
        t.appendChild(head);
        t.appendChild(body);
        toasts.appendChild(t);
        setTimeout(function () { t.classList.add('toast-hide'); setTimeout(function () { t.remove(); }, 400); }, 8000);
    }

    function poll() {
        fetch(pollUrl, { credentials: 'same-origin', cache: 'no-store' })
            .then(function (r) { return r.ok ? r.json() : null; })
            .then(function (data) {
                if (!data) return;
                if (badge) badge.textContent = data.count;
                renderPanel(data.alerts);
                data.alerts.forEach(function (a) {
                    if (!seen[a.id]) { seen[a.id] = true; toast(a); }
                });
                document.dispatchEvent(new CustomEvent('notifications:updated', { detail: data }));
            })
            .catch(function () { /* try again on the next check */ });
    }

    setInterval(poll, pollMs);
    poll();
})();
