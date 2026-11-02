
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>IT Staff Dashboard</title>
    <link rel="stylesheet" href="css/theme.css">
    <script src="js/theme.js" defer></script>
</head>
<body>
    <button class="theme-toggle" type="button" aria-pressed="false">Switch to dark mode</button>
    <h1>IT Staff Dashboard</h1>
    <p>Welcome, <?= $email ?>!</p>
</body>
</html>
