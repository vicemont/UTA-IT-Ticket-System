<?php
session_start();

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$loginErrors = [
    'credentials' => 'Login failed. Check your email and password, or contact IT support if you do not have an account.',
    'input' => 'Enter a valid email and password.',
    'session' => 'Your session expired. Reload the page and try again.',
    'account' => 'Your account role is not permitted to access this app.'
];
$loginError = $loginErrors[$_GET['login_error'] ?? ''] ?? '';
unset($_SESSION['login_error']);
$csrfToken = $_SESSION['csrf_token'];

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Campus IT Help Desk Tickets Login Page</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="css/theme.css">
    <script src="js/theme.js" defer></script>
</head>

<body>
    <div class="container">
        <div class="form-box active" id="login-form">
            <form action="login.php" method="POST" novalidate>
                <button class="theme-toggle" type="button" aria-pressed="false">Switch to dark mode</button>
                <h2>Campus User Login</h2>
                <?php if ($loginError): ?>
                    <p class="error" role="alert"><?= htmlspecialchars($loginError, ENT_QUOTES, 'UTF-8') ?></p>
                <?php endif; ?>
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
                <input type="email" name="email" placeholder="Email" required autocomplete="email" aria-label="Email address">
                <input type="password" name="password" id="password" placeholder="Password" required autocomplete="current-password" aria-label="Password">
                <button type="button" id="toggle-password" class="secondary-button" aria-controls="password" aria-pressed="false">Show password</button>
                <button type="submit" name="login">Login</button>
            </form>
        </div>
    </div>

    <script src="js/script.js"></script>
</body>

</html>