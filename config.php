<?php

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

$host = '127.0.0.1';
$user = 'root';
$password = '';
$database = 'users_staff_db';

$conn = new mysqli($host, $user, $password, $database);

if ($conn->connect_error) {
    die('Connection failed: ' . $conn->connect_error);
}

$conn->set_charset('utf8mb4');
