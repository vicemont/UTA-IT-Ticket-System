<?php
// Returns the comments on one ticket as JSON (used by the user dashboard).
session_start();
require_once __DIR__ . '/config.php';

header('Content-Type: application/json');
header('Cache-Control: no-cache, must-revalidate');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Not logged in']);
    exit();
}

$userId   = (int) $_SESSION['user_id'];
$role     = $_SESSION['user_role'] ?? '';
$ticketId = (int) ($_GET['ticket_id'] ?? 0);

if ($ticketId <= 0) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid ticket']);
    exit();
}

// users can only read comments on their own tickets; staff can read any
$stmt = $conn->prepare('SELECT user_id, status FROM tickets WHERE id = ?');
$stmt->bind_param('i', $ticketId);
$stmt->execute();
$ticket = $stmt->get_result()->fetch_assoc();

if (!$ticket || ($role !== 'staff' && (int) $ticket['user_id'] !== $userId)) {
    http_response_code(404);
    echo json_encode(['error' => 'Ticket not found']);
    exit();
}

$stmt = $conn->prepare(
    "SELECT c.id, c.user_id, c.author_role, c.body, c.created_at,
            COALESCE(NULLIF(u.name, ''), u.email, 'Deleted user') AS author_name
     FROM ticket_comments c
     LEFT JOIN users_staff u ON u.id = c.user_id
     WHERE c.ticket_id = ?
     ORDER BY c.created_at ASC, c.id ASC"
);
$stmt->bind_param('i', $ticketId);
$stmt->execute();
$result = $stmt->get_result();

$comments = [];
while ($row = $result->fetch_assoc()) {
    // automatic notes (reassigned / reopened) are staff comments with no user: shown as "IT"
    $isNote = $row['author_role'] === 'staff' && $row['user_id'] === null;
    $comments[] = [
        'id'         => (int) $row['id'],
        'author'     => $isNote ? 'IT' : $row['author_name'],
        'type'       => $isNote ? 'it' : $row['author_role'],   // user | staff | it
        'body'       => $row['body'],
        'created_at' => $row['created_at'],
    ];
}

// token the page sends back when posting a comment
if (empty($_SESSION['ticket_csrf_token'])) {
    $_SESSION['ticket_csrf_token'] = bin2hex(random_bytes(32));
}

echo json_encode([
    'comments' => $comments,
    'closed'   => in_array(strtolower($ticket['status']), ['closed', 'resolved'], true),
    'csrf'     => $_SESSION['ticket_csrf_token'],
]);
