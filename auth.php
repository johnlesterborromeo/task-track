<?php
require_once __DIR__ . '/config.php';

// Redirect to login if not authenticated
if (empty($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}

// Write an entry to the history / activity log
function log_activity(PDO $pdo, string $action, ?int $taskId = null, string $details = ''): void
{
    $stmt = $pdo->prepare('INSERT INTO activity_log (user_id, action, task_id, details) VALUES (?, ?, ?, ?)');
    $stmt->execute([$_SESSION['user']['id'], $action, $taskId, $details]);
}
