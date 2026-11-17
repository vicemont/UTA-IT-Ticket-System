<?php
// Notification test: creates two test tickets that are due right now and shows the
// notifications arriving live. High priority should alert after 3 seconds, low after 10.
require __DIR__ . '/helpers.php';
requireStaff();

if (empty($_SESSION['ticket_csrf_token'])) {
    $_SESSION['ticket_csrf_token'] = bin2hex(random_bytes(32));
}

// the two test tickets are recognized by their titles, so leftovers can always be cleaned up
function removeTestTickets($conn) {
    qRun($conn, "DELETE FROM tickets WHERE title LIKE 'TEST - % notification'");
    unset($_SESSION['notif_test'], $_SESSION['notif_test_until']);
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!hash_equals($_SESSION['ticket_csrf_token'], (string)($_POST['csrf_token'] ?? ''))) {
        $error = 'Your session expired. Please try again.';
    } else {
        $action = $_POST['action'] ?? '';
        if ($action === 'start' || $action === 'stop') removeTestTickets($conn);

        if ($action === 'start') {
            $due = time();   // both tickets are due right now
            $tests = [];
            $defs = [
                ['high', 'TEST - high priority notification', 'Created by the notification test. It is already due, so the high-priority notification should appear after 3 seconds.'],
                ['low',  'TEST - low priority notification',  'Created by the notification test. It is already due, so the low-priority notification should appear after 10 seconds.'],
            ];
            foreach ($defs as [$priority, $title, $desc]) {
                qRun($conn,
                    "INSERT INTO tickets (user_id, title, category, description, priority, status, dueDate)
                     VALUES (?, ?, 'other', ?, ?, 'open', ?)",
                    'issss', [currentUserId(), $title, $desc, $priority, date('Y-m-d H:i:s', $due)]
                );
                $tests[] = ['id' => (int)$conn->insert_id, 'priority' => $priority, 'due' => $due];
            }
            $_SESSION['notif_test'] = $tests;
            $_SESSION['notif_test_until'] = time() + 600;   // test mode switches itself off after 10 minutes
        }
        if ($action === 'start' || $action === 'stop') { header('Location: notification_test.php'); exit; }
    }
}

$active = notificationTestActive();
$tests  = $active ? ($_SESSION['notif_test'] ?? []) : [];
$jsTests = array_map(fn($t) => [
    'id' => $t['id'], 'priority' => $t['priority'], 'due' => $t['due'],
    'grace' => NOTIFICATION_TEST_SECONDS[$t['priority']],
], $tests);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notification Test - UTA IT Support</title>
    <link rel="stylesheet" href="ITstaffstyles.css">
    <link rel="stylesheet" href="staff-additions.css">
</head>
<body>
<?php include __DIR__ . '/staff_header.php'; ?>

<div class="ticket-detail">
    <a href="IT_Staff_Dashboard.php" class="back-link">&larr; Back to tickets</a>

    <div class="card ticket-info">
        <h2>Notification Test</h2>
        <?php if ($error): ?><p class="form-error"><?= e($error) ?></p><?php endif; ?>

        <?php if (!$tests): ?>
            <p>This creates two test tickets that are already due. A notification should pop up for the
            <strong>high priority</strong> ticket after <strong>3 seconds</strong> and for the
            <strong>low priority</strong> ticket after <strong>10 seconds</strong>, and the bell in the header
            will count up. Only you see the shortened delays, and test mode switches off after 10 minutes.</p>
            <form method="post" class="test-actions">
                <input type="hidden" name="csrf_token" value="<?= e($_SESSION['ticket_csrf_token']) ?>">
                <input type="hidden" name="action" value="start">
                <button type="submit">Start test</button>
            </form>
        <?php else: ?>
            <p>Watch the bell in the top right and the pop-ups. Each ticket's notification is counted from its due time.</p>
            <table class="test-table">
                <tr><th>Ticket</th><th>Priority</th><th>Should alert after</th><th>Status</th></tr>
                <?php foreach ($tests as $t): ?>
                    <tr>
                        <td><a href="ticket.php?id=<?= (int)$t['id'] ?>">#<?= (int)$t['id'] ?></a></td>
                        <td><?= e(ucfirst($t['priority'])) ?></td>
                        <td><?= (int)NOTIFICATION_TEST_SECONDS[$t['priority']] ?> seconds</td>
                        <td id="status-<?= (int)$t['id'] ?>">Waiting&hellip;</td>
                    </tr>
                <?php endforeach; ?>
            </table>
            <div class="test-actions">
                <form method="post">
                    <input type="hidden" name="csrf_token" value="<?= e($_SESSION['ticket_csrf_token']) ?>">
                    <input type="hidden" name="action" value="start">
                    <button type="submit">Run again</button>
                </form>
                <form method="post">
                    <input type="hidden" name="csrf_token" value="<?= e($_SESSION['ticket_csrf_token']) ?>">
                    <input type="hidden" name="action" value="stop">
                    <button type="submit" class="secondary">Stop and delete test tickets</button>
                </form>
            </div>
            <p id="testResult" class="test-result"></p>
        <?php endif; ?>
    </div>
</div>

<?php if ($tests): ?>
<script>
    (function () {
        var tests = <?= json_encode($jsTests) ?>;
        var serverNow = <?= time() ?>, loaded = performance.now();
        var received = {};   // ticket id -> seconds after its due time when we first saw the notification

        function now() { return serverNow + (performance.now() - loaded) / 1000; }

        document.addEventListener('notifications:updated', function (e) {
            (e.detail.alerts || []).forEach(function (a) {
                if (received[a.id] === undefined) received[a.id] = a.overdue_secs;
            });
            draw();
        });

        function draw() {
            var done = 0, passed = 0;
            tests.forEach(function (t) {
                var cell = document.getElementById('status-' + t.id);
                if (!cell) return;
                if (received[t.id] !== undefined) {
                    var got = received[t.id], onTime = got >= t.grace && got <= t.grace + 2;
                    done++; if (onTime) passed++;
                    cell.className = onTime ? 'ok' : 'warn';
                    cell.textContent = onTime
                        ? '✓ Notification sent, ' + got + ' s after due'
                        : '⚠ First seen ' + got + ' s after due (page opened late or check was slow)';
                } else {
                    var left = t.due + t.grace - now();
                    cell.textContent = left > 0 ? 'Waiting… about ' + Math.ceil(left) + ' s left' : 'Time reached, waiting for the next check…';
                }
            });
            var result = document.getElementById('testResult');
            if (result && done === tests.length) {
                result.textContent = passed === tests.length ? '✓ Test passed: both notifications arrived on time.' : 'Test finished, but check the warnings above.';
                result.className = 'test-result ' + (passed === tests.length ? 'ok' : 'warn');
            }
        }
        setInterval(draw, 250);
        draw();
    })();
</script>
<?php endif; ?>
</body>
</html>
