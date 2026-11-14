<?php
require 'helpers.php';
requireStaff();
$alerts = overdueAlerts($conn, currentUserName($conn));

$order = "ORDER BY FIELD(t.priority, 'high', 'medium', 'low'), t.created_at DESC";
$base  = "SELECT t.id, t.title, COALESCE(NULLIF(u.name, ''), u.email, 'Unknown') AS created_by, t.priority, t.status
          FROM tickets t LEFT JOIN users_staff u ON u.id = t.user_id";

// Open tab = open + in progress; Closed tab = resolved + closed
$open = $closed = [];
foreach (q($conn, "$base $order") as $t) {
    if (isClosedStatus($t['status'])) $closed[] = $t; else $open[] = $t;
}

// Search across ALL tickets (open and closed)
$q = trim($_GET['q'] ?? '');
$results = [];
if ($q !== '') {
    // escape LIKE wildcards so a typed % or _ is searched literally
    $like = '%' . addcslashes($q, '\\%_') . '%';
    $results = q($conn,
        "$base
         WHERE t.title LIKE ? OR t.description LIKE ? OR t.category LIKE ?
            OR u.name LIKE ? OR t.id = ?
         $order",
        'ssssi', [$like, $like, $like, $like, ctype_digit($q) ? (int)$q : 0]
    );
}

// Same card markup as the original page, wrapped in a link to the detail page
function renderCards(array $list, bool $showStatus = false) {
    foreach ($list as $ticket): ?>
        <a class="card-link" href="ticket.php?id=<?= (int)$ticket['id'] ?>">
        <div class="card priority-<?php echo e($ticket["priority"]); ?>">

            <h3>
                <?php echo e($ticket["title"]); ?>
                <?php if ($showStatus): ?>
                    <small>(<?php echo e(statusLabel($ticket["status"])); ?>)</small>
                <?php endif; ?>
            </h3>

            <p>
                Created By:
                <?php echo e($ticket["created_by"]); ?>
            </p>

            <p>
                <?php echo e($ticket["priority"]); ?>
            </p>

        </div>
        </a>
    <?php endforeach;
}
?>
<DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>IT Ticket System</title>

    <link rel="stylesheet" href="ITstaffstyles.css">
    <script src="staff.js"></script>
    <style>
        /* The starting tab is shown by the server, so a tab is always open without clicking */
        .tabcontent.show-default { display: block; }
    </style>
</head>

<body>


<?php include 'staff_header.php'; ?>

<?php if ($alerts): ?>
<div class="alerts" id="alerts">
    <h2>&#9888; <?= count($alerts) ?> overdue ticket<?= count($alerts) === 1 ? ' needs' : 's need' ?> attention</h2>
    <ul>
    <?php foreach ($alerts as $a): ?>
        <li>
            <a href="ticket.php?id=<?= (int)$a['id'] ?>">
                #<?= (int)$a['id'] ?> <?= e($a['title']) ?>
            </a>
            &mdash; <strong><?= e($a['priority']) ?></strong> priority,
            <?= e(formatOverdue($a['overdue_secs'])) ?> past due
            (<?= $a['assignedTo'] ? 'assigned to ' . e($a['assignedTo']) : 'unassigned' ?>)
        </li>
    <?php endforeach; ?>
    </ul>
</div>
<?php endif; ?>
<!-- Tab links -->
<div class="tab">
  <button class="tablinks<?= $q === '' ? ' active' : '' ?>" id="openTab" onclick="openCity(event, 'Open')">Open Tickets</button>
  <button class="tablinks" onclick="openCity(event, 'Closed')">Closed Tickets</button>
  <button class="tablinks<?= $q !== '' ? ' active' : '' ?>" id="searchTab" onclick="openCity(event, 'Search')">Search</button>
</div>

<!-- Tab content -->
<div id="Open" class="tabcontent<?= $q === '' ? ' show-default' : '' ?>">
  <div class="cards">

    <h2>Open Tickets</h2>

        <?php renderCards($open); ?>

    </div>

</div>

<div id="Closed" class="tabcontent">
  <div class="Cards">
  <h2>Closed Tickets</h2>

        <?php renderCards($closed); ?>

    </div>

</div>

<div id="Search" class="tabcontent<?= $q !== '' ? ' show-default' : '' ?>">
  <h3>Search</h3>

  <form method="get" action="IT_Staff_Dashboard.php" class="search-form">
      <input type="text" name="q" value="<?= e($q) ?>" placeholder="Search by title, description, category, name or ticket number" required>
      <button type="submit">Search</button>
  </form>

  <?php if ($q !== ''): ?>
      <div class="cards">
          <h2><?= count($results) ?> result<?= count($results) === 1 ? '' : 's' ?> for "<?= e($q) ?>"</h2>
          <?php renderCards($results, true); ?>
      </div>
  <?php endif; ?>

</div>


</body>
</html>