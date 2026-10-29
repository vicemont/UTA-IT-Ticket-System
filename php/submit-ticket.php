<?php
session_start();
require_once __DIR__ . '/../config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../index.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../userdashboard.php');
    exit();
}
os=34n
$userId      = (int) $_SESSION['user_id'];   // from the session, never from the form
$title       = trim($_POST['title'] ?? '');
$category    = trim($_POST['category'] ?? '');
$description = trim($_POST['description'] ?? '');
$priority    = trim($_POST['priority'] ?? '');
$status      = 'open';                        // new tickets always start open
$dueDate     = !empty($_POST['dueDate']) ? $_POST['dueDate'] : null;

if ($title === '' || $description === '' || $priority === '') {
    echo 'Please fill out all required fields.';
    exit();
}

$stmt = $conn->prepare(
    'INSERT INTO tickets (user_id, title, category, description, priority, status, dueDate)
     VALUES (?, ?, ?, ?, ?, ?, ?)'
);
$stmt->bind_param('issssss', $userId, $title, $category, $description, $priority, $status, $dueDate);
$stmt->execute();

header('Location: ../userdashboard.php?status=success');
exit();