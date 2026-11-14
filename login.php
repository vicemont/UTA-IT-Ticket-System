
<?php
session_start();
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
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
    header('Location: index.php?login_error=session');
    exit();
}

$emailInput = $_POST['email'] ?? '';
$password = $_POST['password'] ?? '';

if (!is_string($emailInput) || !is_string($password)) {
    header('Location: index.php?login_error=input');
    exit();
}

$email = trim($emailInput);

if (!filter_var($email, FILTER_VALIDATE_EMAIL) || $password === '') {
    header('Location: index.php?login_error=input');
    exit();
}

$statement = $conn->prepare(
    'SELECT id, email, password, role
     FROM users_staff
     WHERE email = ?
     LIMIT 1'
);

$statement->bind_param('s', $email);
$statement->execute();

$result = $statement->get_result();
$user = $result->fetch_assoc();
$passwordIsValid = false;

if ($user) {
    $storedPassword = $user['password'];
    $passwordIsValid = password_verify($password, $storedPassword);

    if (!$passwordIsValid) {
        $isLegacyHash = preg_match('/\A[a-f0-9]{32}\z/i', $storedPassword) === 1;
        $legacyPasswordIsValid = $isLegacyHash
            ? hash_equals(strtolower($storedPassword), md5($password))
            : hash_equals($storedPassword, $password);

        if ($legacyPasswordIsValid) {
            $passwordIsValid = true;
            $newPasswordHash = password_hash($password, PASSWORD_DEFAULT);
            $upgrade = $conn->prepare('UPDATE users_staff SET password = ? WHERE id = ?');
            $upgrade->bind_param('si', $newPasswordHash, $user['id']);
            $upgrade->execute();
        }
    }
}

if ($user && $passwordIsValid) {
    session_regenerate_id(true);
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    unset($_SESSION['ticket_csrf_token']);

    $_SESSION['user_id'] = (int) $user['id'];
    $_SESSION['user_email'] = $user['email'];
    $_SESSION['user_role'] = $user['role'];

    if ($user['role'] === 'staff') {
        header('Location: IT_Staff_Dashboard.php');
        exit();
    }

    if ($user['role'] === 'user') {
        header('Location: userdashboard.php');
        exit();
    }

    header('Location: index.php?login_error=account');
    exit();
}

header('Location: index.php?login_error=credentials');
exit();
?>
