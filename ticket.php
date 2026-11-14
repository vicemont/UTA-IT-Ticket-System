<?php
session_start();

if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'staff') {
    header('Location: index.php');
    exit();
}

$ticketId = $_GET['id'] ?? '';
if (!is_string($ticketId) || !ctype_digit($ticketId)) {
    http_response_code(400);
    exit('Invalid ticket.');
}

header('Location: IT_Staff_Dashboard.php?ticket_id=' . (int) $ticketId);
exit();
?>