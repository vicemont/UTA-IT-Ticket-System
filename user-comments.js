// Adds a "Comments" section to every ticket on the user dashboard.
// Connect it with ONE line in userdashboard.php:
//     <script src="user-comments.js" defer></script>
(function () {
    'use strict';

    var list = document.getElementById('ticketList');
    if (!list) return;

    // everything lives in the same folder; if the php files are in a subfolder, add data-php="php/" to the script tag
    var me = document.currentScript || document.querySelector('script[src*="user-comments.js"]');
    var php = (me && me.dataset.php) || '';
    var cssHref = me && me.src ? me.src.replace(/user-comments\.js.*$/, 'user-comments.css') : 'user-comments.css';
    var link = document.createElement('link');
    link.rel = 'stylesheet';
    link.href = cssHref;
    document.head.appendChild(link);

    /* ---------- which status is showing (used only if a ticket's id is not in the page) ---------- */
    var currentStatus = null;
    function rememberStatus(e) {
        var card = e.target && e.target.closest ? e.target.closest('.summary-card') : null;
        if (card && card.dataset.status) currentStatus = card.dataset.status;
    }
    document.addEventListener('click', rememberStatus, true);
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Enter' || e.key === ' ') rememberStatus(e);
    }, true);

    var ticketCache = {};
    // the ticket list endpoint can be next to this page or in a php/ folder: try both
    function tryFetch(urls) {
        var rest = urls.slice(1);
        return fetch(urls[0], { credentials: 'same-origin' })
            .then(function (r) { return r.ok ? r.json() : (rest.length ? tryFetch(rest) : []); })
            .catch(function () { return rest.length ? tryFetch(rest) : []; });
    }
    function fetchTickets(status) {
        if (!ticketCache[status]) {
            var q = '?status=' + encodeURIComponent(status);
            var urls = [php + 'get-tickets.php' + q, 'get-tickets.php' + q, 'php/get-tickets.php' + q]
                .filter(function (u, i, a) { return a.indexOf(u) === i; });
            ticketCache[status] = tryFetch(urls);
        }
        return ticketCache[status];
    }

    /* ---------- finding each ticket's id ---------- */
    function idFromPage(item) {
        function pick(node) {
            if (!node || !node.dataset) return 0;
            var keys = ['id', 'ticketId', 'ticket'];
            for (var k = 0; k < keys.length; k++) {
                var v = parseInt(node.dataset[keys[k]], 10);
                if (v > 0) return v;
            }
            return 0;
        }
        var found = pick(item);
        if (found) return found;

        var nodes = item.querySelectorAll('[data-id], [data-ticket-id], [data-ticket]');
        for (var i = 0; i < nodes.length; i++) {
            found = pick(nodes[i]);
            if (found) return found;
        }
        var input = item.querySelector('input[name="id"], input[name="ticket_id"]');
        if (input && parseInt(input.value, 10) > 0) return parseInt(input.value, 10);

        var m = /(\d+)$/.exec(item.id || '');
        return m ? parseInt(m[1], 10) : 0;
    }

    function ticketItems() {
        var items = list.querySelectorAll('.ticket-item');
        if (items.length) return Array.prototype.slice.call(items);
        return Array.prototype.filter.call(list.children, function (n) { return n.nodeType === 1; });
    }

    function usedIds() {
        var ids = {};
        Array.prototype.forEach.call(list.querySelectorAll('.ticket-comments'), function (w) { ids[w.dataset.ticketId] = true; });
        return ids;
    }

    function enhance() {
        var items = ticketItems().filter(function (el) { return !el.querySelector('.ticket-comments'); });
        if (!items.length) return;

        var pending = [];
        items.forEach(function (el) {
            var id = idFromPage(el);
            if (id) attach(el, id); else pending.push(el);
        });
        if (!pending.length) return;

        // no ids in the page: match each ticket to the list php/get-tickets.php returns (same order, same titles)
        var statuses = currentStatus ? [currentStatus] : ['open', 'in_progress', 'resolved'];
        Promise.all(statuses.map(fetchTickets)).then(function (lists) {
            var tickets = [].concat.apply([], lists);
            var all = ticketItems();
            var used = usedIds();
            pending.forEach(function (el) {
                if (el.querySelector('.ticket-comments')) return;
                var text = el.textContent;
                var match = null;
                var byPosition = currentStatus ? tickets[all.indexOf(el)] : null;
                if (byPosition && text.indexOf(byPosition.title) !== -1 && !used[byPosition.id]) {
                    match = byPosition;
                } else {
                    for (var i = 0; i < tickets.length; i++) {
                        if (text.indexOf(tickets[i].title) !== -1 && !used[tickets[i].id]) { match = tickets[i]; break; }
                    }
                }
                if (match) {
                    used[match.id] = true;
                    attach(el, parseInt(match.id, 10));
                }
            });
        });
    }

    /* ---------- the comments section ---------- */
    function attach(item, id) {
        var wrap = document.createElement('div');
        wrap.className = 'ticket-comments';
        wrap.dataset.ticketId = id;
        wrap.innerHTML =
            '<button type="button" class="comments-toggle" aria-expanded="false">Comments <span class="comments-count"></span></button>' +
            '<div class="comments-panel" hidden>' +
                '<div class="comments-list"></div>' +
                '<p class="comments-note" hidden>This ticket is closed, so new comments can\'t be added.</p>' +
                '<form class="comments-form">' +
                    '<textarea name="body" rows="3" maxlength="3000" placeholder="Write a comment..." required></textarea>' +
                    '<button type="submit" class="comments-send">Post Comment</button>' +
                '</form>' +
                '<p class="comments-error"></p>' +
            '</div>';
        item.appendChild(wrap);

        var toggle = wrap.querySelector('.comments-toggle');
        var count = wrap.querySelector('.comments-count');
        var panel = wrap.querySelector('.comments-panel');
        var box = wrap.querySelector('.comments-list');
        var note = wrap.querySelector('.comments-note');
        var form = wrap.querySelector('.comments-form');
        var area = form.querySelector('textarea');
        var send = form.querySelector('button');
        var error = wrap.querySelector('.comments-error');
        var csrf = '';

        function render(comments, closed) {
            box.textContent = '';
            if (!comments.length) {
                var empty = document.createElement('p');
                empty.className = 'comments-empty';
                empty.textContent = 'No comments yet.';
                box.appendChild(empty);
            }
            comments.forEach(function (c) {
                var entry = document.createElement('div');
                entry.className = 'comment-entry ' + c.type;

                var head = document.createElement('div');
                head.className = 'comment-head';
                var who = document.createElement('strong');
                who.textContent = c.author;
                head.appendChild(who);
                if (c.type === 'staff') {
                    var badge = document.createElement('span');
                    badge.className = 'comment-badge';
                    badge.textContent = 'IT Staff';
                    head.appendChild(badge);
                }
                var time = document.createElement('span');
                time.className = 'comment-time';
                time.textContent = String(c.created_at || '').slice(0, 16);
                head.appendChild(time);

                var text = document.createElement('p');
                text.className = 'comment-body';
                text.textContent = c.body;

                entry.appendChild(head);
                entry.appendChild(text);
                box.appendChild(entry);
            });
            count.textContent = comments.length ? '(' + comments.length + ')' : '';
            form.hidden = closed;
            note.hidden = !closed;
        }

        function load() {
            return fetch(php + 'get-comments.php?ticket_id=' + id, { credentials: 'same-origin', cache: 'no-store' })
                .then(function (r) { if (!r.ok) throw new Error('load'); return r.json(); })
                .then(function (data) {
                    csrf = data.csrf;
                    error.textContent = '';
                    render(data.comments, !!data.closed);
                })
                .catch(function () { error.textContent = 'Could not load comments.'; });
        }

        toggle.addEventListener('click', function () {
            panel.hidden = !panel.hidden;
            toggle.setAttribute('aria-expanded', String(!panel.hidden));
            if (!panel.hidden) load();
        });

        form.addEventListener('submit', function (e) {
            e.preventDefault();
            var body = area.value.trim();
            if (!body) return;
            send.disabled = true;
            error.textContent = '';

            fetch(php + 'add-comment.php', {
                method: 'POST',
                credentials: 'same-origin',
                body: new URLSearchParams({ ticket_id: id, body: body, csrf_token: csrf })
            })
                .then(function (r) { return r.json().then(function (j) { return { ok: r.ok, data: j }; }); })
                .then(function (res) {
                    if (res.ok) area.value = '';
                    return load().then(function () {
                        if (!res.ok) error.textContent = res.data.error || 'Could not post the comment.';
                    });
                })
                .catch(function () { error.textContent = 'Could not post the comment.'; })
                .then(function () { send.disabled = false; });
        });
    }

    /* ---------- keep up with the dashboard re-drawing its ticket list ---------- */
    function insideWidget(node) {
        var el = node && node.nodeType === 1 ? node : (node ? node.parentElement : null);
        return !!(el && el.closest && el.closest('.ticket-comments'));
    }
    var scheduled = false;
    new MutationObserver(function (mutations) {
        if (mutations.every(function (m) { return insideWidget(m.target); })) return;
        if (scheduled) return;
        scheduled = true;
        setTimeout(function () {
            scheduled = false;
            ticketCache = {};
            enhance();
        }, 50);
    }).observe(list, { childList: true, subtree: true });

    enhance();
})();
