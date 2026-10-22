<?php
session_start();
require_once __DIR__ . '/../config.php';

header('Content-Type: application/json');
header('Cache-Control: no-cache, must-revalidate');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Not logged in']);
    exit();
}

$userId = (int) $_SESSION['user_id'];

$stmt = $conn->prepare(
    'SELECT status, COUNT(*) AS total FROM tickets WHERE user_id = ? GROUP BY status'
);
$stmt->bind_param('i', $userId);
$stmt->execute();
$result = $stmt->get_result();

$counts = ['open' => 0, 'in_progress' => 0, 'resolved' => 0];

while ($row = $result->fetch_assoc()) {
    if ($row['status'] === 'open') {
        $counts['open'] += (int) $row['total'];
    } elseif ($row['status'] === 'in_progress') {
        $counts['in_progress'] += (int) $row['total'];
    } elseif ($row['status'] === 'resolved' || $row['status'] === 'closed') {
        $counts['resolved'] += (int) $row['total'];
    }
}

echo json_encode($counts);