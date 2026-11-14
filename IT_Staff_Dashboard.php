<?php
session_start();

if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'staff') {
    header('Location: index.php');
    exit();
}

require_once __DIR__ . '/config.php';

if (empty($_SESSION['staff_dashboard_csrf'])) {
    $_SESSION['staff_dashboard_csrf'] = bin2hex(random_bytes(32));
}

$updateError = '';
$postedTicketId = $_POST['ticket_id'] ?? '';
$selectedTicketId = 0;

if (is_string($postedTicketId) && ctype_digit($postedTicketId)) {
    $selectedTicketId = (int) $postedTicketId;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $submittedToken = $_POST['csrf_token'] ?? '';
    $newStatus = $_POST['status'] ?? '';
    $allowedStatuses = ['open', 'in_progress', 'resolved', 'closed'];

    if (
        !is_string($submittedToken) ||
        !hash_equals($_SESSION['staff_dashboard_csrf'], $submittedToken) ||
        !is_string($newStatus) ||
        !in_array($newStatus, $allowedStatuses, true) ||
        $selectedTicketId < 1
    ) {
        http_response_code(400);
        $updateError = 'The ticket update could not be validated. Please try again.';
    } else {
        $updateStatement = $conn->prepare('UPDATE tickets SET status = ? WHERE id = ?');
        $updateStatement->bind_param('si', $newStatus, $selectedTicketId);
        $updateStatement->execute();

        header('Location: IT_Staff_Dashboard.php?ticket_id=' . $selectedTicketId . '&updated=1');
        exit();
    }
}

$order = "ORDER BY FIELD(t.priority, 'high', 'medium', 'low'), t.created_at DESC";
$ticketFields = "t.id, t.title, u.name AS created_by, t.priority, t.status";
$ticketSource = "FROM tickets t LEFT JOIN users_staff u ON u.id = t.user_id";

$openStatement = $conn->prepare(
    "SELECT $ticketFields $ticketSource
     WHERE t.status IN ('open', 'in_progress') $order"
);
$openStatement->execute();
$open = $openStatement->get_result()->fetch_all(MYSQLI_ASSOC);

$closedStatement = $conn->prepare(
    "SELECT $ticketFields $ticketSource
     WHERE t.status NOT IN ('open', 'in_progress') $order"
);
$closedStatement->execute();
$closed = $closedStatement->get_result()->fetch_all(MYSQLI_ASSOC);

$queryValue = $_GET['q'] ?? '';
$q = is_string($queryValue) ? trim($queryValue) : '';
$results = [];
if ($q !== '') {
    $like = '%' . addcslashes($q, '\\%_') . '%';
    $ticketId = ctype_digit($q) ? (int) $q : 0;
    $searchStatement = $conn->prepare(
        "SELECT $ticketFields $ticketSource
         WHERE (t.title LIKE ? OR t.description LIKE ? OR u.name LIKE ? OR t.id = ?)
         $order"
    );
    $searchStatement->bind_param('sssi', $like, $like, $like, $ticketId);
    $searchStatement->execute();
    $results = $searchStatement->get_result()->fetch_all(MYSQLI_ASSOC);
}

$ticketQueryValue = $_GET['ticket_id'] ?? '';
if (is_string($ticketQueryValue) && ctype_digit($ticketQueryValue)) {
    $selectedTicketId = (int) $ticketQueryValue;
}

$selectedTicket = null;
if ($selectedTicketId > 0) {
    $detailStatement = $conn->prepare(
        'SELECT t.id, t.title, t.category, t.description, t.priority, t.status,
                t.dueDate, t.assignedTo, t.created_at, u.name AS created_by
         FROM tickets t
         LEFT JOIN users_staff u ON u.id = t.user_id
         WHERE t.id = ?'
    );
    $detailStatement->bind_param('i', $selectedTicketId);
    $detailStatement->execute();
    $selectedTicket = $detailStatement->get_result()->fetch_assoc();

    if (!$selectedTicket) {
        http_response_code(404);
    }
}

function escapeHtml(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

function renderCards(array $list, bool $showStatus = false): void
{
    foreach ($list as $ticket): ?>
        <a class="card-link" href="IT_Staff_Dashboard.php?ticket_id=<?= (int) $ticket['id'] ?>">
        <article class="card">

            <h3>
                <?= escapeHtml($ticket['title']) ?>
                <?php if ($showStatus): ?>
                    <small>(<?= escapeHtml($ticket['status']) ?>)</small>
                <?php endif; ?>
            </h3>

            <p>
                Created By:
                <?= escapeHtml($ticket['created_by'] ?? 'Unknown user') ?>
            </p>

            <p>
                <?= escapeHtml($ticket['priority']) ?>
            </p>

        </article>
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

<?php if ($selectedTicket): ?>
    <section class="ticket-detail">
        <a class="back-link" href="IT_Staff_Dashboard.php">&larr; Back to tickets</a>
        <?php if (isset($_GET['updated'])): ?>
            <p role="status">Ticket status updated.</p>
        <?php endif; ?>
        <?php if ($updateError !== ''): ?>
            <p role="alert"><?= escapeHtml($updateError) ?></p>
        <?php endif; ?>
        <article class="card ticket-info">
            <h2><?= escapeHtml($selectedTicket['title']) ?></h2>
            <p>Created by <?= escapeHtml($selectedTicket['created_by'] ?? 'Unknown user') ?></p>
            <p>Category: <?= escapeHtml($selectedTicket['category']) ?></p>
            <p>Priority: <?= escapeHtml($selectedTicket['priority']) ?></p>
            <p>Status: <?= escapeHtml($selectedTicket['status']) ?></p>
            <p>Created: <?= escapeHtml($selectedTicket['created_at']) ?></p>
            <?php if ($selectedTicket['dueDate'] !== null): ?>
                <p>Due: <?= escapeHtml($selectedTicket['dueDate']) ?></p>
            <?php endif; ?>
            <?php if ($selectedTicket['assignedTo'] !== null && $selectedTicket['assignedTo'] !== ''): ?>
                <p>Assigned to: <?= escapeHtml($selectedTicket['assignedTo']) ?></p>
            <?php endif; ?>
            <h3>Description</h3>
            <p><?= nl2br(escapeHtml($selectedTicket['description'])) ?></p>
            <form method="post" action="IT_Staff_Dashboard.php?ticket_id=<?= (int) $selectedTicket['id'] ?>">
                <input type="hidden" name="csrf_token" value="<?= escapeHtml($_SESSION['staff_dashboard_csrf']) ?>">
                <input type="hidden" name="ticket_id" value="<?= (int) $selectedTicket['id'] ?>">
                <label for="ticketStatus">Update status</label>
                <select id="ticketStatus" name="status">
                    <?php foreach (['open', 'in_progress', 'resolved', 'closed'] as $status): ?>
                        <option value="<?= $status ?>" <?= $selectedTicket['status'] === $status ? 'selected' : '' ?>>
                            <?= escapeHtml(ucwords(str_replace('_', ' ', $status))) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <button type="submit">Save status</button>
            </form>
        </article>
    </section>
<?php elseif ($selectedTicketId > 0): ?>
    <p role="alert">Ticket not found.</p>
<?php endif; ?>

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
  <div class="cards">
  <h2>Closed Tickets</h2>

        <?php renderCards($closed); ?>

    </div>

</div>

<div id="Search" class="tabcontent">
  <h3>Search</h3>

  <form method="get" action="IT_Staff_Dashboard.php" class="search-form">
  <input type="text" name="q" value="<?= escapeHtml($q) ?>" placeholder="Search by title, description, name or ticket number" required>
      <button type="submit">Search</button>
  </form>

  <?php if ($q !== ''): ?>
      <div class="cards">
          <h2><?= count($results) ?> result<?= count($results) === 1 ? '' : 's' ?> for "<?= escapeHtml($q) ?>"</h2>
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