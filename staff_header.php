<?php
/* Shared header, styled like the user dashboard's top bar.
   Include it right after <body> on every page:  <?php include 'staff_header.php'; ?>
   Needs helpers.php to be loaded first. Uses images/UTA_logomark.png and
   images/hamburger-menu-icon.png, the same images as userdashboard.php. */
$__staff = isStaff();
$__home  = $__staff ? 'IT_Staff_Dashboard.php' : 'userdashboard.php';
// Overdue tickets for the bell (the dashboard may already have computed this)
if (!isset($alerts)) {
    $alerts = $__staff ? overdueAlerts($conn, currentUserName($conn)) : [];
}
?>
<header class="topbar">
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
                    <a class="notif-item notif-<?= e($a['priority']) ?>" href="ticket.php?id=<?= (int)$a['id'] ?>">
                        <strong>#<?= (int)$a['id'] ?> <?= e($a['title']) ?></strong>
                        <span><?= e(ucfirst($a['priority'])) ?> priority &middot; <?= e(formatOverdue($a['overdue_secs'])) ?> past due<?= $a['assignedTo'] ? '' : ' &middot; Unassigned' ?></span>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        <a class="logout-button" href="php/logout.php">Logout</a>
    </div>
</header>
<script>
    (function () {
        var button = document.getElementById('hamburger');
        var menu = document.getElementById('headerMenu');
        button.addEventListener('click', function (e) { e.stopPropagation(); menu.classList.toggle('show'); });
        document.addEventListener('click', function (e) { if (!menu.contains(e.target)) menu.classList.remove('show'); });

        // bell: show / hide the list of overdue tickets
        var bell = document.getElementById('notificationsButton');
        var panel = document.getElementById('notificationsPanel');
        if (bell && panel) {
            bell.addEventListener('click', function () {
                panel.hidden = !panel.hidden;
                bell.setAttribute('aria-expanded', String(!panel.hidden));
            });
        }
    })();
</script>
