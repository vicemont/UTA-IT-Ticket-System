<?php
require 'helpers.php';
requireLogin();
$backUrl = isStaff() ? 'IT_Staff_Dashboard.php' : 'userdashboard.php';

$id = (int)($_GET['id'] ?? 0);

$ticket = qOne($conn,
    "SELECT t.*, COALESCE(NULLIF(u.name, ''), u.email, 'Unknown') AS created_by
     FROM tickets t LEFT JOIN users_staff u ON u.id = t.user_id
     WHERE t.id = ?", 'i', [$id]
);
if (!$ticket) { http_response_code(404); exit('Ticket not found.'); }

// Regular users can only open their own tickets; staff can open any
if (!isStaff() && (int)$ticket['user_id'] !== currentUserId()) {
    http_response_code(403);
    exit('You do not have access to this ticket.');
}

$isClosed = isClosedStatus($ticket['status']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? 'comment';

    if ($action === 'comment' && !$isClosed) { // closed tickets reject new comments
        $body = trim($_POST['body'] ?? '');
        if ($body !== '') {
            qRun($conn,
                'INSERT INTO ticket_comments (ticket_id, user_id, author_role, body) VALUES (?, ?, ?, ?)',
                'iiss', [$id, currentUserId(), isStaff() ? 'staff' : 'user', mb_substr($body, 0, 3000)]
            );
        }
    } elseif ($action === 'close' && isStaff() && !$isClosed) {
        qRun($conn, "UPDATE tickets SET status = 'resolved' WHERE id = ?", 'i', [$id]);
    } elseif ($action === 'reopen' && isStaff() && $isClosed) {
        qRun($conn, "UPDATE tickets SET status = 'open' WHERE id = ?", 'i', [$id]);
        addTicketNote($conn, $id, 'Ticket reopened.'); // leave a note on the ticket
    }
    header("Location: ticket.php?id=$id"); // avoid resubmit on refresh
    exit;
}

$comments = q($conn,
    "SELECT c.*, COALESCE(NULLIF(u.name, ''), u.email, 'Deleted user') AS author_name
     FROM ticket_comments c LEFT JOIN users_staff u ON u.id = c.user_id
     WHERE c.ticket_id = ? ORDER BY c.created_at ASC, c.id ASC", 'i', [$id]
);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($ticket['title']) ?> - UTA IT Support</title>
    <link rel="stylesheet" href="ITstaffstyles.css">
</head>
<body>
<?php include 'staff_header.php'; ?>

<div class="ticket-detail">
    <a href="<?= e($backUrl) ?>" class="back-link">&larr; Back to tickets</a>

    <div class="card ticket-info priority-<?= e($ticket['priority']) ?>">
        <h2><?= e($ticket['title']) ?></h2>
        <p class="meta">
            Created by <strong><?= e($ticket['created_by']) ?></strong>
            &middot; Category: <strong><?= e($ticket['category']) ?></strong>
            &middot; Priority: <strong><?= e($ticket['priority']) ?></strong>
            &middot; Status: <strong><?= e(statusLabel($ticket['status'])) ?></strong>
        </p>
        <p class="meta">
            Opened: <?= e($ticket['created_at']) ?>
            <?php if ($ticket['dueDate']): ?>&middot; Due: <?= e($ticket['dueDate']) ?><?php endif; ?>
            <?php if ($ticket['assignedTo']): ?>&middot; Assigned to: <?= e($ticket['assignedTo']) ?><?php endif; ?>
        </p>
        <h3>Description</h3>
        <p><?= nl2br(e($ticket['description'])) ?></p>

        <?php if (isStaff()): ?>
            <div class="staff-actions">
            <a class="button-link" href="edit_ticket.php?id=<?= $id ?>">Edit Ticket</a>
            <form method="post" class="status-form">
                <?php if (!$isClosed): ?>
                    <input type="hidden" name="action" value="close">
                    <button type="submit">Close Ticket</button>
                <?php else: ?>
                    <input type="hidden" name="action" value="reopen">
                    <button type="submit">Reopen Ticket</button>
                <?php endif; ?>
            </form>
            </div>
        <?php endif; ?>
    </div>

    <h3>Comments</h3>
    <div class="comments">
        <?php if (!$comments): ?>
            <p>No comments.</p>
        <?php endif; ?>
        <?php foreach ($comments as $cm):
            $byStaff  = $cm['author_role'] === 'staff';
            $isSystem = $byStaff && $cm['user_id'] === null; // automatic notes: shown as "IT"
            $class    = $isSystem ? 'comment-system' : ($byStaff ? 'comment-it' : 'comment-user');
        ?>
            <div class="comment <?= $class ?>">
                <div class="comment-head">
                    <?php if ($isSystem): ?>
                        <strong>IT</strong>
                    <?php else: ?>
                        <strong><?= e($cm['author_name']) ?></strong>
                        <?php if ($byStaff): ?><span class="badge">IT Staff</span><?php endif; ?>
                    <?php endif; ?>
                    <span class="time"><?= e($cm['created_at']) ?></span>
                </div>
                <p><?= nl2br(e($cm['body'])) ?></p>
            </div>
        <?php endforeach; ?>
    </div>

    <?php if ($isClosed): ?>
        <p class="closed-note">This ticket is closed, so new comments can't be added.</p>
    <?php else: ?>
        <form method="post" class="comment-form">
            <input type="hidden" name="action" value="comment">
            <textarea name="body" rows="4" placeholder="Write a comment..." required></textarea>
            <button type="submit">Post Comment</button>
        </form>
    <?php endif; ?>
</div>
</body>
</html>