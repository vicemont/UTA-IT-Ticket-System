<?php
/* Shared helpers for the staff dashboard, ticket page and edit page.
   Uses the same session and the same $conn (mysqli) from config.php
   as the rest of the site, so there is only one database connection to configure. */
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/config.php';   // provides $conn

function e($s) { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }

/* ---- login session (set by the login page) ---- */
function isLoggedIn()    { return !empty($_SESSION['user_id']); }
function isStaff()       { return ($_SESSION['user_role'] ?? '') === 'staff'; }
function currentUserId() { return (int)($_SESSION['user_id'] ?? 0); }

function requireLogin() {
    if (!isLoggedIn()) { header('Location: index.php'); exit; }
}
function requireStaff() {
    requireLogin();
    if (!isStaff()) { header('Location: userdashboard.php'); exit; }
}

/* ---- tiny mysqli helpers ---- */
// Run a SELECT and return all rows
function q(mysqli $conn, string $sql, string $types = '', array $params = []): array {
    $st = $conn->prepare($sql);
    if ($types !== '') $st->bind_param($types, ...$params);
    $st->execute();
    $res = $st->get_result();
    return $res ? $res->fetch_all(MYSQLI_ASSOC) : [];
}
// Run a SELECT and return the first row (or null)
function qOne(mysqli $conn, string $sql, string $types = '', array $params = []): ?array {
    return q($conn, $sql, $types, $params)[0] ?? null;
}
// Run an INSERT/UPDATE and return affected rows
function qRun(mysqli $conn, string $sql, string $types = '', array $params = []): int {
    $st = $conn->prepare($sql);
    if ($types !== '') $st->bind_param($types, ...$params);
    $st->execute();
    return $st->affected_rows;
}

/* ---- ticket statuses (same values the rest of the site uses) ----
   open, in_progress  = still active
   resolved, closed   = finished (users can cancel -> 'closed', staff close -> 'resolved') */
function isClosedStatus(string $status): bool {
    return in_array(strtolower($status), ['closed', 'resolved'], true);
}
function statusLabel(string $status): string {
    return ucfirst(str_replace('_', ' ', $status));
}

/* ---- automatic notes on a ticket ----
   Posts an automatic comment shown as "IT", e.g. when a ticket is reassigned
   or reopened. It is a normal 'staff' comment with no user_id; the ticket page
   shows staff comments with no user as "IT" with their own look. */
function addTicketNote(mysqli $conn, int $ticketId, string $text): void {
    qRun($conn,
        "INSERT INTO ticket_comments (ticket_id, user_id, author_role, body) VALUES (?, NULL, 'staff', ?)",
        'is', [$ticketId, $text]
    );
}

/* ---- staff ---- */
function currentUserName(mysqli $conn): string {
    $r = qOne($conn, 'SELECT name FROM users_staff WHERE id = ?', 'i', [currentUserId()]);
    return (string)($r['name'] ?? '');
}
// Names of all staff accounts, for the "assigned to" dropdown
function staffNames(mysqli $conn): array {
    return array_column(q($conn, "SELECT name FROM users_staff WHERE role = 'staff' ORDER BY name"), 'name');
}

/* ------------------------------------------------------------------
   Overdue notifications
   A ticket is "past due" once its due date has passed. The notification
   appears after a grace period that depends on priority: urgent tickets
   alert sooner, low-priority ones wait a couple of days.
   Change the hours here to adjust.
------------------------------------------------------------------- */
const OVERDUE_GRACE_HOURS = ['high' => 24, 'medium' => 36, 'low' => 48];

// Active tickets whose grace period has run out. Shows tickets assigned to
// $myName plus unassigned ones.
function overdueAlerts(mysqli $conn, string $myName): array {
    $rows = q($conn,
        "SELECT id, title, priority, dueDate, assignedTo FROM tickets
         WHERE status IN ('open', 'in_progress') AND dueDate IS NOT NULL"
    );
    $now = time();
    $rank = ['high' => 0, 'medium' => 1, 'low' => 2];
    $alerts = [];
    foreach ($rows as $r) {
        $due = strtotime($r['dueDate']);
        if ($due === false) continue;
        $notifyAt = $due + (OVERDUE_GRACE_HOURS[$r['priority']] ?? 48) * 3600;
        if ($now < $notifyAt) continue;
        if ($r['assignedTo'] !== null && $r['assignedTo'] !== '' && $r['assignedTo'] !== $myName) continue;
        $r['overdue_secs'] = $now - $due;
        $alerts[] = $r;
    }
    usort($alerts, fn($a, $b) =>
        [$rank[$a['priority']] ?? 3, -$a['overdue_secs']] <=> [$rank[$b['priority']] ?? 3, -$b['overdue_secs']]);
    return $alerts;
}

function formatOverdue(int $secs): string {
    $d = intdiv($secs, 86400);
    $h = intdiv($secs % 86400, 3600);
    if ($d > 0) return $d . ' day' . ($d === 1 ? '' : 's') . ($h ? " $h hr" : '');
    return max($h, 1) . ' hr';
}