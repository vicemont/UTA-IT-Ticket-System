<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../userdashboard.php');
    exit();
}

if (
    ($_SESSION['user_role'] ?? null) !== 'user' ||
    !isset($_SESSION['user_id']) ||
    !is_int($_SESSION['user_id'])
) {
    header('Location: ../index.php');
    exit();
}

$sessionToken = $_SESSION['ticket_csrf_token'] ?? '';
$submittedToken = $_POST['csrf_token'] ?? '';

if (
    !is_string($sessionToken) ||
    $sessionToken === '' ||
    !is_string($submittedToken) ||
    $submittedToken === '' ||
    !hash_equals($sessionToken, $submittedToken)
) {
    $_SESSION['ticket_error'] = 'Your session expired. Please try again.';
    header('Location: ../userdashboard.php');
    exit();
}

$title = $_POST['title'] ?? '';
$category = $_POST['category'] ?? '';
$description = $_POST['description'] ?? '';
$priority = $_POST['priority'] ?? '';
$dueDateInput = $_POST['dueDate'] ?? '';
$dueDate = null;
$validCategories = ['hardware', 'software', 'network', 'account', 'other'];
$validPriorities = ['low', 'medium', 'high'];

$validInput = is_string($title)
    && is_string($category)
    && is_string($description)
    && is_string($priority)
    && is_string($dueDateInput);

if ($validInput) {
    $title = trim($title);
    $description = trim($description);
    $dueDateInput = trim($dueDateInput);

    $validInput = $title !== ''
        && strlen($title) <= 255
        && $description !== ''
        && strlen($description) <= 10000
        && in_array($category, $validCategories, true)
        && in_array($priority, $validPriorities, true);

    if ($validInput && $dueDateInput !== '') {
        $date = DateTimeImmutable::createFromFormat('!Y-m-d', $dueDateInput);
        $dateErrors = DateTimeImmutable::getLastErrors();
        $validInput = $date !== false
            && ($dateErrors === false || ($dateErrors['warning_count'] === 0 && $dateErrors['error_count'] === 0))
            && $date->format('Y-m-d') === $dueDateInput;

        if ($validInput) {
            $dueDate = $dueDateInput;
        }
    }
}

if (!$validInput) {
    $_SESSION['ticket_error'] = 'Check the ticket details and try again.';
    header('Location: ../userdashboard.php');
    exit();
}

try {
    require_once __DIR__ . '/../config.php';

    $statement = $conn->prepare(
        'INSERT INTO tickets (user_id, title, category, description, priority, status, dueDate)
         VALUES (?, ?, ?, ?, ?, \'open\', ?)'
    );
    $userId = $_SESSION['user_id'];
    $statement->bind_param('isssss', $userId, $title, $category, $description, $priority, $dueDate);
    $statement->execute();

    header('Location: ../userdashboard.php?status=success');
    exit();
} catch (mysqli_sql_exception $exception) {
    error_log('Ticket submission failed: ' . $exception->getMessage());
    $_SESSION['ticket_error'] = 'We could not save your ticket. Please try again later.';
    header('Location: ../userdashboard.php');
    exit();
}
