<?php
require 'db.php';

$stmt = $pdo->query(
    "SELECT id, title, created_by, priority, status
     FROM tickets
     ORDER BY FIELD(priority, 'high', 'medium', 'low'), created_at DESC"
);
$open = $closed = [];
foreach ($stmt as $t) {
    if ($t['status'] === 'open') $open[] = $t; else $closed[] = $t;
}

// Renders a list of ticket cards, each linking to its detail page
function renderCards(array $list) {
    if (!$list) { echo '<p>No tickets.</p>'; return; }
    foreach ($list as $t): ?>
        <a class="card-link" href="ticket.php?id=<?= (int)$t['id'] ?>">
            <div class="card clickable">
                <h3><?= e($t['title']) ?></h3>
                <p>Created By: <?= e($t['created_by']) ?></p>
                <p>Priority: <?= e($t['priority']) ?></p>
            </div>
        </a>
    <?php endforeach;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>IT Ticket System</title>
    <link rel="stylesheet" href="ITstaffstyles.css">
    <script src="staff.js"></script>
</head>
<body>

<header>
    <h1>UTA IT Support</h1>
</header>

<!-- Tab links -->
<div class="tab">
  <button class="tablinks" onclick="openCity(event, 'Open')">Open Tickets</button>
  <button class="tablinks" onclick="openCity(event, 'Closed')">Closed Tickets</button>
  <button class="tablinks" onclick="openCity(event, 'Search')">Search</button>
</div>

<!-- Tab content -->
<div id="Open" class="tabcontent">
  <div class="cards">
    <h2>Open Tickets</h2>
    <?php renderCards($open); ?>
  </div>
</div>

<div id="Closed" class="tabcontent">
  <div class="cards">
    <h2>Closed Tickets</h2>
    <?php renderCards($closed); ?>
  </div>
</div>

<div id="Search" class="tabcontent">
  <h3>Search</h3>
</div>

</body>
</html>
