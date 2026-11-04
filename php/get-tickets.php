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

// same grouping as get-ticket-counts.php
$groups = [
    'open'        => ['open'],
    'in_progress' => ['in_progress'],
    'resolved'    => ['resolved', 'closed'],
];

$status = $_GET['status'] ?? '';
if (!isset($groups[$status])) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid status']);
    exit();
}

$statuses     = $groups[$status];
$placeholders = implode(',', array_fill(0, count($statuses), '?'));
$types        = 'i' . str_repeat('s', count($statuses));
$params       = array_merge([$userId], $statuses);

$sql = "SELECT id, title, category, description, priority, status
        FROM tickets
        WHERE user_id = ? AND status IN ($placeholders)
        ORDER BY dueDate ASC";

$stmt = $conn->prepare($sql);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();

echo json_encode($result->fetch_all(MYSQLI_ASSOC));