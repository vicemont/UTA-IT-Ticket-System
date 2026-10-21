
<?php
session_start();

// Only staff members can access this page
if (!isset($_SESSION['user_role']) ||
    $_SESSION['user_role'] !== 'staff') {

    header('Location: index.php');
    exit();
}

$email = htmlspecialchars(
    $_SESSION['user_email'],
    ENT_QUOTES,
    'UTF-8'
);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>IT Staff Dashboard</title>
</head>
<body>
    <h1>IT Staff Dashboard</h1>
    <p>Welcome, <?= $email ?>!</p>
</body>
</html>
