<?php
session_start();
header('Content-Type: application/json');
header('Cache-Control: no-store');

if (
    ($_SESSION['user_role'] ?? null) !== 'user' ||
    !isset($_SESSION['user_id']) ||
    !is_int($_SESSION['user_id'])
) {
    http_response_code(401);
    echo json_encode(['error' => 'Authentication required.']);
    exit();
}

try {
    require_once __DIR__ . '/../config.php';

    $statement = $conn->prepare(
        'SELECT LOWER(status) AS status, COUNT(*) AS total
         FROM tickets
         WHERE user_id = ?
         GROUP BY LOWER(status)'
    );
    $userId = $_SESSION['user_id'];
    $statement->bind_param('i', $userId);
    $statement->execute();
    $result = $statement->get_result();

    $counts = ['open' => 0, 'in_progress' => 0, 'resolved' => 0];
    while ($row = $result->fetch_assoc()) {
        if ($row['status'] === 'open') {
            $counts['open'] += (int) $row['total'];
        } elseif ($row['status'] === 'in_progress') {
            $counts['in_progress'] += (int) $row['total'];
        } elseif (in_array($row['status'], ['resolved', 'closed', 'cancelled'], true)) {
            $counts['resolved'] += (int) $row['total'];
        }
    }

    $ticketStatement = $conn->prepare(
        'SELECT title, status, created_at
         FROM tickets
         WHERE user_id = ?
         ORDER BY created_at DESC, id DESC'
    );
    $ticketStatement->bind_param('i', $userId);
    $ticketStatement->execute();
    $ticketResult = $ticketStatement->get_result();
    $tickets = [];

    while ($ticket = $ticketResult->fetch_assoc()) {
        $tickets[] = $ticket;
    }

    echo json_encode(['counts' => $counts, 'tickets' => $tickets]);
} catch (mysqli_sql_exception $exception) {
    error_log('Ticket count lookup failed: ' . $exception->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Unable to load ticket counts.']);
}
