<?php
session_start();
require_once __DIR__ . '/../config.php';

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

$userId   = (int) $_SESSION['user_id'];
$ticketId = (int) ($_POST['id'] ?? 0);

if ($ticketId <= 0) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid ticket']);
    exit();
}

// only the owner can cancel, and only if it's still open or in progress
$stmt = $conn->prepare(
    "UPDATE tickets SET status = 'closed'
     WHERE id = ? AND user_id = ? AND status IN ('open', 'in_progress')"
);
$stmt->bind_param('ii', $ticketId, $userId);
$stmt->execute();

if ($stmt->affected_rows === 0) {
    http_response_code(404);
    echo json_encode(['error' => 'Ticket not found or already closed']);
    exit();
}

echo json_encode(['success' => true]);