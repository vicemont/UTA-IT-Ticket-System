
<?php
session_start();

if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'staff') {
    header('Location: index.php');
    exit();
}

header('Location: IT_Staff_Dashboard.php');
exit();
