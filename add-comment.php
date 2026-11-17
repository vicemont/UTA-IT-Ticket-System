<?php
// Adds a comment to a ticket (used by the user dashboard).
session_start();
require_once __DIR__ . '/config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Not logged in']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'POST only']);
    exit();
}

// same token that get-comments.php handed to the page
$sessionToken   = $_SESSION['ticket_csrf_token'] ?? '';
$submittedToken = $_POST['csrf_token'] ?? '';
if (
    !is_string($sessionToken) || $sessionToken === '' ||
    !is_string($submittedToken) || !hash_equals($sessionToken, $submittedToken)
) {
    http_response_code(403);
    echo json_encode(['error' => 'Your session expired. Please try again.']);
    exit();
}

$userId   = (int) $_SESSION['user_id'];                 // from the session, never from the form
$role     = ($_SESSION['user_role'] ?? '') === 'staff' ? 'staff' : 'user';
$ticketId = (int) ($_POST['ticket_id'] ?? 0);
$body     = trim((string) ($_POST['body'] ?? ''));

if ($ticketId <= 0 || $body === '') {
    http_response_code(400);
    echo json_encode(['error' => 'Please write a comment.']);
    exit();
}
$body = mb_substr($body, 0, 3000);

// users can only comment on their own tickets; staff on any
$stmt = $conn->prepare('SELECT user_id, status FROM tickets WHERE id = ?');
$stmt->bind_param('i', $ticketId);
$stmt->execute();
$ticket = $stmt->get_result()->fetch_assoc();

if (!$ticket || ($role !== 'staff' && (int) $ticket['user_id'] !== $userId)) {
    http_response_code(404);
    echo json_encode(['error' => 'Ticket not found']);
    exit();
}

// closed / resolved tickets are read-only
if (in_array(strtolower($ticket['status']), ['closed', 'resolved'], true)) {
    http_response_code(409);
    echo json_encode(['error' => 'This ticket is closed, so new comments cannot be added.']);
    exit();
}

$stmt = $conn->prepare(
    'INSERT INTO ticket_comments (ticket_id, user_id, author_role, body) VALUES (?, ?, ?, ?)'
);
$stmt->bind_param('iiss', $ticketId, $userId, $role, $body);
$stmt->execute();

echo json_encode(['success' => true]);
