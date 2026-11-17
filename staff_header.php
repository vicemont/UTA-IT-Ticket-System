<?php
/* Shared header, styled like the user dashboard's top bar.
   Include it right after <body> on every page:  <?php include __DIR__ . '/staff_header.php'; ?>
   Needs helpers.php to be loaded first. Uses images/UTA_logomark.png and
   images/hamburger-menu-icon.png, the same images as userdashboard.php. */
$__staff = isStaff();
$__home  = $__staff ? 'IT_Staff_Dashboard.php' : 'userdashboard.php';
// Overdue tickets for the bell (the dashboard may already have computed this)
if (!isset($alerts)) {
    $alerts = $__staff ? overdueAlerts($conn, currentUserName($conn)) : [];
}
?>
<style>
/* Dark mode + the theme switch in the hamburger menu. Loaded by staff_header.php. */

/* keep the top card on the ticket page the same width as the comments */
.ticket-info { box-sizing: border-box; }

/* ---------- Theme switch in the hamburger menu ---------- */
header.topbar .dropdown-menu { min-width: 190px; }
header.topbar .theme-row {
    display: flex; align-items: center; justify-content: space-between; gap: 12px;
    width: 100%; box-sizing: border-box; margin: 0 0 8px; padding: 8px 10px;
    background-color: var(--bg-main); color: var(--text-main);
    border: none; border-radius: 6px; cursor: pointer; font-family: inherit; font-size: 14px;
}
header.topbar .theme-row:hover { background-color: var(--border-color); }
header.topbar .theme-switch {
    position: relative; flex-shrink: 0; width: 44px; height: 24px; border-radius: 999px;
    background-color: #cbd5e1; transition: background-color .25s ease;
}
header.topbar .theme-thumb {
    position: absolute; top: 3px; left: 3px; width: 18px; height: 18px; border-radius: 50%;
    background-color: #fff; box-shadow: 0 1px 3px rgba(0, 0, 0, 0.3); transition: transform .25s ease;
}
:root.dark-mode header.topbar .theme-switch { background-color: #1d4b81; }
:root.dark-mode header.topbar .theme-thumb { transform: translateX(20px); }

/* ---------- Dark mode (same palette as the user dashboard) ---------- */
:root.dark-mode {
    color-scheme: dark;                       /* dark date pickers, scrollbars, form controls */
    --bg-main: #121212;
    --text-main: #f5f5f5;
    --card-bg: #1f1e1e;
    --accent: #1d4b81;
    --accent-hover: #173960;
    --border-color: #334155;
    --brand-orange: #944221;
}
:root.dark-mode body { background-color: #121212; color: #f5f5f5; }

/* header */
:root.dark-mode header.topbar .page-name { color: #7ab8ee; }
:root.dark-mode header.topbar .three-lines img { filter: invert(1); }
:root.dark-mode header.topbar .dropdown-menu { box-shadow: 0 4px 14px rgba(0, 0, 0, 0.6); }
:root.dark-mode header.topbar .notif-item { background: #2b2013; color: #f5f5f5; }
:root.dark-mode header.topbar .notif-item.notif-high   { background: #2a1717; }
:root.dark-mode header.topbar .notif-item.notif-medium { background: #2b2013; }
:root.dark-mode header.topbar .notif-item.notif-low    { background: #2a2610; }
:root.dark-mode header.topbar .notif-item span,
:root.dark-mode header.topbar .notif-empty { color: #9aa4b2; }

/* tabs and tables */
:root.dark-mode .tab button { background-color: #1f1e1e; color: #f5f5f5; }
:root.dark-mode .tab button:hover { background-color: #2c2c2c; }
:root.dark-mode .tab button.active { background-color: #334155; }
:root.dark-mode .tabcontent { border-color: #334155; }
:root.dark-mode .tabcontent th, :root.dark-mode .tabcontent td,
:root.dark-mode .test-table th, :root.dark-mode .test-table td { border-color: #334155; }

/* cards */
:root.dark-mode .card { background: #1f1e1e; color: #f5f5f5; box-shadow: 0 8px 15px rgba(0, 0, 0, 0.55); }
:root.dark-mode .card.priority-high   { background: #2a1717; border-left-color: #e0605b; }
:root.dark-mode .card.priority-medium { background: #2b2013; border-left-color: #f0902e; }
:root.dark-mode .card.priority-low    { background: #2a2610; border-left-color: #e8c52f; }
:root.dark-mode .card h3 small, :root.dark-mode .ticket-info .meta { color: #9aa4b2; }
:root.dark-mode .back-link { color: #9ec5ee; }

/* search, forms, buttons */
:root.dark-mode .search-form input,
:root.dark-mode .comment-form input, :root.dark-mode .comment-form textarea,
:root.dark-mode .new-ticket-form select, :root.dark-mode .new-ticket-form input[type="datetime-local"] {
    background: #1f1e1e; color: #f5f5f5; border-color: #475569;
}
:root.dark-mode .search-form button, :root.dark-mode .comment-form button, :root.dark-mode .status-form button,
:root.dark-mode .button-link, :root.dark-mode .test-actions button { background: #1d4b81; }
:root.dark-mode .search-form button:hover, :root.dark-mode .comment-form button:hover, :root.dark-mode .status-form button:hover,
:root.dark-mode .button-link:hover, :root.dark-mode .test-actions button:hover { background: #173960; }
:root.dark-mode .test-actions button.secondary { background: #475569; }
:root.dark-mode .readonly-field, :root.dark-mode .closed-note { background: #262626; color: #d6dbe2; }
:root.dark-mode .form-error { color: #ff8a8a; }

/* comments */
:root.dark-mode .comment { background: #262626; border-left-color: #64748b; }
:root.dark-mode .comment-it { background: #14283f; border-left-color: #4a90e2; }
:root.dark-mode .comment-system { background: #2b2510; border-left-color: #e0a800; }
:root.dark-mode .comment-system .comment-head strong { color: #f0c24a; }
:root.dark-mode .comment-head .time { color: #9aa4b2; }

/* overdue box, pop-ups, test page */
:root.dark-mode .alerts { background: #2a1717; border-left-color: #c0392b; }
:root.dark-mode .alerts h2, :root.dark-mode .alerts a { color: #f1948a; }
:root.dark-mode .toast { background: #2b2013; color: #f5f5f5; box-shadow: 0 6px 18px rgba(0, 0, 0, 0.6); }
:root.dark-mode .toast.toast-high { background: #2a1717; }
:root.dark-mode .toast.toast-low  { background: #2a2610; }
:root.dark-mode .test-table .ok   { color: #6fcf8b; }
:root.dark-mode .test-table .warn, :root.dark-mode .test-result.warn { color: #f0b45a; }
:root.dark-mode .test-result.ok { color: #6fcf8b; }
</style>
<script>
    // apply the saved light/dark choice right away, before the page draws
    (function () { try { if (localStorage.getItem('staffTheme') === 'dark') document.documentElement.classList.add('dark-mode'); } catch (e) {} })();
</script>
<header class="topbar" data-poll-ms="<?= $__staff ? (notificationTestActive() ? 1000 : 30000) : 0 ?>" data-poll-url="get-notifications.php">
    <a class="brand" href="<?= e($__home) ?>" aria-label="UTA IT Support Home">
        <span class="page-name"><strong>UT<img width="16" height="16" src="images/UTA_logomark.png" alt=""> IT Support <?= $__staff ? 'Staff Portal' : 'Portal' ?></strong></span>
    </a>

    <!-- hamburger menu -->
    <div class="top-actions">
        <button id="hamburger" class="three-lines" type="button" aria-label="Menu">
            <img src="images/hamburger-menu-icon.png" width="24" height="24" alt="Menu">
        </button>
    </div>

    <!-- dropdown menu -->
    <div class="dropdown-menu" id="headerMenu">
        <!-- dark mode switch -->
        <button id="themeToggle" class="theme-row" type="button" role="switch" aria-checked="false" aria-label="Toggle dark mode">
            <span class="theme-label">&#127769; Dark mode</span>
            <span class="theme-switch"><span class="theme-thumb"></span></span>
        </button>
        <?php if ($__staff): ?>
            <!-- notification bell: overdue tickets -->
            <button id="notificationsButton" class="bell-button" type="button" aria-expanded="false" aria-label="View notifications">
                <span class="bell-icon">&#128276;</span>
                <span class="count-badge"><?= count($alerts) ?></span>
            </button>
            <div id="notificationsPanel" class="notif-panel" hidden>
                <?php if (!$alerts): ?>
                    <p class="notif-empty">No overdue tickets.</p>
                <?php endif; ?>
                <?php foreach ($alerts as $a): ?>
                    <a class="notif-item notif-<?= e($a['priority']) ?>" data-id="<?= (int)$a['id'] ?>" href="ticket.php?id=<?= (int)$a['id'] ?>">
                        <strong>#<?= (int)$a['id'] ?> <?= e($a['title']) ?></strong>
                        <span><?= e(ucfirst($a['priority'])) ?> priority &middot; <?= e(formatOverdue($a['overdue_secs'])) ?> past due<?= $a['assignedTo'] ? '' : ' &middot; Unassigned' ?></span>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        <a class="logout-button" href="logout.php">Logout</a>
    </div>
</header>
<div id="toastArea" class="toast-area" aria-live="polite"></div>
<script>
    // dark mode switch in the hamburger menu; the choice is remembered in this browser
    (function () {
        var button = document.getElementById('themeToggle');
        if (!button) return;
        var root = document.documentElement;
        function sync() { button.setAttribute('aria-checked', String(root.classList.contains('dark-mode'))); }
        sync();
        // capture + stopImmediatePropagation: if an older copy of the theme code is still on the server,
        // it must not toggle a second time and cancel this one out
        button.addEventListener('click', function (e) {
            e.stopImmediatePropagation();
            var dark = !root.classList.contains('dark-mode');
            root.classList.toggle('dark-mode', dark);
            try { localStorage.setItem('staffTheme', dark ? 'dark' : 'light'); } catch (err) {}
            sync();
        }, true);
    })();
</script>
<script src="staff-header.js" defer></script>