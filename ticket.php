<?php
require 'db.php';

$id = (int)($_GET['id'] ?? 0);

$stmt = $pdo->prepare('SELECT * FROM tickets WHERE id = ?');
$stmt->execute([$id]);
$ticket = $stmt->fetch();
if (!$ticket) { http_response_code(404); exit('Ticket not found.'); }

$isClosed = $ticket['status'] === 'closed';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? 'comment';

    if ($action === 'comment' && !$isClosed) { // closed tickets reject new comments
        $name = trim($_POST['name'] ?? '');
        $body = trim($_POST['body'] ?? '');
        $role = isStaff() ? 'it' : 'user';
        if ($name !== '' && $body !== '') {
            $ins = $pdo->prepare(
                'INSERT INTO ticket_comments (ticket_id, author_name, author_role, body)
                 VALUES (?, ?, ?, ?)'
            );
            $ins->execute([$id, mb_substr($name, 0, 100), $role, mb_substr($body, 0, 3000)]);
        }
    } elseif (($action === 'close' || $action === 'reopen') && isStaff()) {
        $upd = $pdo->prepare('UPDATE tickets SET status = ? WHERE id = ?');
        $upd->execute([$action === 'close' ? 'closed' : 'open', $id]);
    }
    header("Location: ticket.php?id=$id"); // avoid resubmit on refresh
    exit;
}

$c = $pdo->prepare('SELECT * FROM ticket_comments WHERE ticket_id = ? ORDER BY created_at ASC, id ASC');
$c->execute([$id]);
$comments = $c->fetchAll();
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
<header><h1>UTA IT Support</h1></header>

<div class="ticket-detail">
    <a href="staff_Dashboard.php" class="back-link">&larr; Back to tickets</a>

    <div class="card ticket-info">
        <h2><?= e($ticket['title']) ?></h2>
        <p class="meta">
            Created by <strong><?= e($ticket['created_by']) ?></strong>
            &middot; Priority: <strong><?= e($ticket['priority']) ?></strong>
            &middot; Status: <strong><?= e(ucfirst($ticket['status'])) ?></strong>
            &middot; Opened: <?= e($ticket['created_at']) ?>
        </p>
        <h3>Description</h3>
        <p><?= nl2br(e($ticket['description'])) ?></p>

        <?php if (isStaff()): ?>
            <form method="post" class="status-form">
                <?php if (!$isClosed): ?>
                    <input type="hidden" name="action" value="close">
                    <button type="submit">Close Ticket</button>
                <?php else: ?>
                    <input type="hidden" name="action" value="reopen">
                    <button type="submit">Reopen Ticket</button>
                <?php endif; ?>
            </form>
        <?php endif; ?>
    </div>

    <h3>Comments</h3>
    <div class="comments">
        <?php if (!$comments): ?>
            <p>No comments.</p>
        <?php endif; ?>
        <?php foreach ($comments as $cm): ?>
            <div class="comment <?= $cm['author_role'] === 'it' ? 'comment-it' : 'comment-user' ?>">
                <div class="comment-head">
                    <strong><?= e($cm['author_name']) ?></strong>
                    <?php if ($cm['author_role'] === 'it'): ?><span class="badge">IT Staff</span><?php endif; ?>
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
            <div class="form-row">
                <input type="text" name="name" value="<?= e(currentName()) ?>" placeholder="Your name" required>
            </div>
            <textarea name="body" rows="4" placeholder="Write a comment..." required></textarea>
            <button type="submit">Post Comment</button>
        </form>
    <?php endif; ?>
</div>
</body>
</html>