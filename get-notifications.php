<?php
// Returns the current overdue-ticket notifications for the logged-in staff member as JSON.
// The header (staff-header.js) calls this every so often so the bell updates without a page reload.
require __DIR__ . '/helpers.php';

header('Content-Type: application/json');
header('Cache-Control: no-store');

if (!isLoggedIn() || !isStaff()) {
    http_response_code(401);
    echo json_encode(['error' => 'Staff only']);
    exit();
}

$alerts = overdueAlerts($conn, currentUserName($conn));
session_write_close();   // do not hold the session lock while polling

echo json_encode([
    'count'       => count($alerts),
    'server_time' => time(),
    'alerts'      => array_map(fn($a) => [
        'id'           => (int) $a['id'],
        'title'        => $a['title'],
        'priority'     => $a['priority'],
        'assignedTo'   => $a['assignedTo'],
        'overdue_secs' => (int) $a['overdue_secs'],
        'overdue_text' => formatOverdue((int) $a['overdue_secs']),
    ], $alerts),
]);
