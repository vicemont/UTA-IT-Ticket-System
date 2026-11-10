<?php
require 'db.php';

$id = (int)($_GET['id'] ?? 0);

$stmt = $pdo->prepare('SELECT * FROM tickets WHERE id = ?');
$stmt->execute([$id]);
$ticket = $stmt->fetch();
if (!$ticket) { http_response_code(404); exit('Ticket not found.'); }

// Handle new comment
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $role = ($_POST['role'] ?? 'user') === 'it' ? 'it' : 'user';
    $body = trim($_POST['body'] ?? '');

    if ($name !== '' && $body !== '') {
        $ins = $pdo->prepare(
            'INSERT INTO ticket_comments (ticket_id, author_name, author_role, body)
             VALUES (?, ?, ?, ?)'
        );
        $ins->execute([$id, mb_substr($name, 0, 100), $role, mb_substr($body, 0, 3000)]);
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
    <a href="index.php" class="back-link">&larr; Back to tickets</a>

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
    </div>

    <h3>Comments</h3>
    <div class="comments">
        <?php if (!$comments): ?>
            <p>No comments yet.</p>
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

    <form method="post" class="comment-form">
        <div class="form-row">
            <input type="text" name="name" placeholder="Your name" required>
            <select name="role">
                <option value="user">User</option>
                <option value="it">IT Staff</option>
            </select>
        </div>
        <textarea name="body" rows="4" placeholder="Write a comment..." required></textarea>
        <button type="submit">Post Comment</button>
    </form>
</div>
</body>
</html>
