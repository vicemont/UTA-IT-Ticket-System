<?php
require 'db.php';

$order = "ORDER BY FIELD(priority, 'high', 'medium', 'low'), created_at DESC";

$stmt = $pdo->query("SELECT id, title, created_by, priority, status FROM tickets $order");
$open = $closed = [];
foreach ($stmt as $t) {
    if ($t['status'] === 'open') $open[] = $t; else $closed[] = $t;
}

// Search across ALL tickets (open and closed)
$q = trim($_GET['q'] ?? '');
$results = [];
if ($q !== '') {
    // escape LIKE wildcards so a typed % or _ is searched literally
    $like = '%' . addcslashes($q, '\\%_') . '%';
    $s = $pdo->prepare(
        "SELECT id, title, created_by, priority, status FROM tickets
         WHERE title LIKE ? OR description LIKE ? OR created_by LIKE ? OR id = ?
         $order"
    );
    $s->execute([$like, $like, $like, ctype_digit($q) ? (int)$q : 0]);
    $results = $s->fetchAll();
}

// Same card markup as the original page, wrapped in a link to the detail page
function renderCards(array $list, bool $showStatus = false) {
    foreach ($list as $ticket): ?>
        <a class="card-link" href="ticket.php?id=<?= (int)$ticket['id'] ?>">
        <div class="card">

            <h3>
                <?php echo e($ticket["title"]); ?>
                <?php if ($showStatus): ?>
                    <small>(<?php echo e($ticket["status"]); ?>)</small>
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
</head>

<body>


<header>
    <h1>UTA IT Support</h1>
</header>
<!-- Tab links -->
<div class="tab">
  <button class="tablinks" onclick="openCity(event, 'Open')">Open Tickets</button>
  <button class="tablinks" onclick="openCity(event, 'Closed')">Closed Tickets</button>
  <button class="tablinks" id="searchTab" onclick="openCity(event, 'Search')">Search</button>
</div>

<!-- Tab content -->
<div id="Open" class="tabcontent">
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

<div id="Search" class="tabcontent">
  <h3>Search</h3>

  <form method="get" action="index.php" class="search-form">
      <input type="text" name="q" value="<?= e($q) ?>" placeholder="Search by title, description, name or ticket number" required>
      <button type="submit">Search</button>
  </form>

  <?php if ($q !== ''): ?>
      <div class="cards">
          <h2><?= count($results) ?> result<?= count($results) === 1 ? '' : 's' ?> for "<?= e($q) ?>"</h2>
          <?php renderCards($results, true); ?>
      </div>
  <?php endif; ?>

</div>

<?php if ($q !== ''): ?>
<script>
  // After a search, reopen the Search tab automatically
  document.getElementById('searchTab').click();
</script>
<?php endif; ?>

</body>
</html>