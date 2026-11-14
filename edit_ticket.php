<?php
require 'helpers.php';
requireStaff(); // only IT staff can edit tickets

$id = (int)($_GET['id'] ?? 0);

$ticket = qOne($conn,
    "SELECT t.*, COALESCE(NULLIF(u.name, ''), u.email, 'Unknown') AS created_by
     FROM tickets t LEFT JOIN users_staff u ON u.id = t.user_id
     WHERE t.id = ?", 'i', [$id]
);
if (!$ticket) { http_response_code(404); exit('Ticket not found.'); }

$priorities = ['low', 'medium', 'high'];

$staff = staffNames($conn);
if ($ticket['assignedTo'] && !in_array($ticket['assignedTo'], $staff, true)) {
    $staff[] = $ticket['assignedTo']; // keep an existing assignee even if no longer a staff account
}

if (empty($_SESSION['ticket_csrf_token'])) {
    $_SESSION['ticket_csrf_token'] = bin2hex(random_bytes(32));
}

$errors = [];
$f = [
    'priority'   => $ticket['priority'],
    'assignedTo' => (string)$ticket['assignedTo'],
    'dueDate'    => $ticket['dueDate'] ? date('Y-m-d\TH:i', strtotime($ticket['dueDate'])) : '',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!hash_equals($_SESSION['ticket_csrf_token'], (string)($_POST['csrf_token'] ?? ''))) {
        $errors[] = 'Your session expired. Please try again.';
    }

    foreach ($f as $k => $_) $f[$k] = trim((string)($_POST[$k] ?? ''));

    if (!in_array($f['priority'], $priorities, true)) $errors[] = 'Choose a priority.';
    if ($f['assignedTo'] !== '' && !in_array($f['assignedTo'], $staff, true)) $errors[] = 'Choose a valid staff member.';

    $due = null;
    $ts = $f['dueDate'] === '' ? false : strtotime($f['dueDate']);
    if ($ts === false) $errors[] = 'Choose a due date.';
    else $due = date('Y-m-d H:i:s', $ts);

    // Title and description are intentionally not editable here.
    if (!$errors) {
        $oldAssignee = (string)$ticket['assignedTo'];
        $newAssignee = $f['assignedTo'];
        qRun($conn,
            'UPDATE tickets SET priority = ?, assignedTo = ?, dueDate = ? WHERE id = ?',
            'sssi', [$f['priority'], $f['assignedTo'] === '' ? null : mb_substr($f['assignedTo'], 0, 50), $due, $id]
        );
        // Leave a note on the ticket when it is reassigned
        if ($oldAssignee !== $newAssignee) {
            addTicketNote($conn, $id, sprintf(
                'Ticket reassigned from %s to %s.',
                $oldAssignee === '' ? 'Unassigned' : $oldAssignee,
                $newAssignee === '' ? 'Unassigned' : $newAssignee
            ));
        }
        header("Location: ticket.php?id=$id");
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Ticket #<?= $id ?> - UTA IT Support</title>
    <link rel="stylesheet" href="ITstaffstyles.css">
</head>
<body>
<?php include 'staff_header.php'; ?>

<div class="ticket-detail">
    <a href="ticket.php?id=<?= $id ?>" class="back-link">&larr; Back to ticket</a>

    <div class="card ticket-info">
        <h2>Edit Ticket #<?= $id ?></h2>
        <p class="meta">Created by <strong><?= e($ticket['created_by']) ?></strong> &middot; Opened <?= e($ticket['created_at']) ?></p>

        <?php foreach ($errors as $err): ?>
            <p class="form-error"><?= e($err) ?></p>
        <?php endforeach; ?>

        <form method="post" class="comment-form new-ticket-form">
            <input type="hidden" name="csrf_token" value="<?= e($_SESSION['ticket_csrf_token']) ?>">

            <label>Title</label>
            <p class="readonly-field"><?= e($ticket['title']) ?></p>

            <label>Description</label>
            <p class="readonly-field"><?= nl2br(e($ticket['description'])) ?></p>

            <div class="form-row">
                <div class="form-col">
                    <label for="priority">Priority</label>
                    <select id="priority" name="priority">
                        <?php foreach ($priorities as $p): ?>
                            <option value="<?= $p ?>" <?= $f['priority'] === $p ? 'selected' : '' ?>><?= ucfirst($p) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-col">
                    <label for="assignedTo">Assigned to</label>
                    <select id="assignedTo" name="assignedTo">
                        <option value="">Unassigned</option>
                        <?php foreach ($staff as $name): ?>
                            <option value="<?= e($name) ?>" <?= $f['assignedTo'] === $name ? 'selected' : '' ?>><?= e($name) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <label for="dueDate">Due date</label>
            <input type="datetime-local" id="dueDate" name="dueDate" value="<?= e($f['dueDate']) ?>" required>

            <button type="submit">Save Changes</button>
        </form>
    </div>
</div>
</body>
</html>