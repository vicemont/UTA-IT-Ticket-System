<?php
// Database connection. Edit these values for your server.
const DB_HOST = 'localhost';
const DB_NAME = 'it_tickets';
const DB_USER = 'root';
const DB_PASS = '';

// Uses the session started by the existing login page.
if (session_status() === PHP_SESSION_NONE) session_start();

try {
    $pdo = new PDO(
        'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4',
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]
    );
} catch (PDOException $ex) {
    http_response_code(500);
    exit('Database connection failed. Check db.php settings.');
}

function e($s) { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }

/* ------------------------------------------------------------------
   CONNECT TO THE EXISTING LOGIN:
   These two functions are the only place the app reads the login
   session. I don't know what your login page stores, so adjust the
   session keys below to match it.
------------------------------------------------------------------- */
function isStaff()      { return ($_SESSION['role'] ?? '') === 'it'; }
function currentName()  { return (string)($_SESSION['name'] ?? ''); }