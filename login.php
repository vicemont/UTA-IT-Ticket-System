
<?php
session_start();
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['login'])) {
    header('Location: index.php');
    exit();
}

$sessionToken = $_SESSION['csrf_token'] ?? '';
$submittedToken = $_POST['csrf_token'] ?? '';

if (
    !is_string($sessionToken) ||
    $sessionToken === '' ||
    !is_string($submittedToken) ||
    $submittedToken === '' ||
    !hash_equals($sessionToken, $submittedToken)
) {
    $_SESSION['login_error'] = 'Your session expired. Please try again.';
    header('Location: index.php');
    exit();
}

$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';

if (!filter_var($email, FILTER_VALIDATE_EMAIL) || $password === '') {
    $_SESSION['login_error'] = 'Enter a valid email and password.';
    header('Location: index.php');
    exit();
}

$statement = $conn->prepare(
    'SELECT id, email, password, role
     FROM users_staff
     WHERE email = ?
     LIMIT 1'
);

if (!$statement) {
    $_SESSION['login_error'] = 'Database error. Please try again later.';
    header('Location: index.php');
    exit();
}

$statement->bind_param('s', $email);
$statement->execute();

$result = $statement->get_result();
$user = $result->fetch_assoc();

if ($user && password_verify($password, $user['password'])) {
    session_regenerate_id(true);

    $_SESSION['user_id'] = (int) $user['id'];
    $_SESSION['user_email'] = $user['email'];
    $_SESSION['user_role'] = $user['role'];

    if ($user['role'] === 'staff') {
        header('Location: staff_dashboard.php');
        exit();
    }

    if ($user['role'] === 'user') {
        header('Location: userdashboard.php');
        exit();
    }

    $_SESSION['login_error'] = 'Your account role is not permitted to access this app.';
    header('Location: index.php');
    exit();
}

$_SESSION['login_error'] = 'Invalid email or password.';
header('Location: index.php');
exit();
?>
