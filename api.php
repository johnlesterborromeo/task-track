<?php
require_once __DIR__ . '/auth.php';
header('Content-Type: application/json; charset=utf-8');

$uid    = (int)$_SESSION['user']['id'];
$action = $_GET['action'] ?? '';
$in     = json_decode(file_get_contents('php://input'), true);
if (!is_array($in)) {
    $in = $_POST;
}

function out($data, int $code = 200): void
{
    http_response_code($code);
    echo json_encode($data);
    exit;
}

function cnt(PDO $pdo, string $sql, array $params): int
{
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return (int)$stmt->fetch()['c'];
}

try {
    switch ($action) {

        case 'stats':
            out([
                'total'       => cnt($pdo, 'SELECT COUNT(*) c FROM tasks WHERE user_id=?', [$uid]),
                'todo'        => cnt($pdo, "SELECT COUNT(*) c FROM tasks WHERE user_id=? AND status='todo'", [$uid]),
                'in_progress' => cnt($pdo, "SELECT COUNT(*) c FROM tasks WHERE user_id=? AND status='in_progress'", [$uid]),
                'done'        => cnt($pdo, "SELECT COUNT(*) c FROM tasks WHERE user_id=? AND status='done'", [$uid]),
                'overdue'     => cnt($pdo, "SELECT COUNT(*) c FROM tasks WHERE user_id=? AND status!='done' AND due_date IS NOT NULL AND due_date < CURDATE()", [$uid]),
                'notes'       => cnt($pdo, 'SELECT COUNT(*) c FROM notes WHERE user_id=?', [$uid]),
            ]);

        case 'tasks':
            if (isset($_GET['month']) && preg_match('/^\d{4}-\d{2}$/', $_GET['month'])) {
                $stmt = $pdo->prepare("SELECT * FROM tasks WHERE user_id=? AND DATE_FORMAT(due_date, '%Y-%m')=? ORDER BY due_date");
                $stmt->execute([$uid, $_GET['month']]);
            } else {
                $stmt = $pdo->prepare("SELECT * FROM tasks WHERE user_id=? ORDER BY (status='done'), (due_date IS NULL), due_date ASC, id DESC");
                $stmt->execute([$uid]);
            }
            out($stmt->fetchAll());

        case 'add_task':
            $title = trim($in['title'] ?? '');
            if ($title === '') {
                out(['error' => 'Title is required'], 422);
            }
            $stmt = $pdo->prepare('INSERT INTO tasks (user_id, title, description, priority, due_date) VALUES (?, ?, ?, ?, ?)');
            $stmt->execute([$uid, $title, $in['description'] ?? '', $in['priority'] ?? 'medium', ($in['due_date'] ?? null) ?: null]);
            log_activity($pdo, 'task_created', (int)$pdo->lastInsertId(), 'Created task "' . $title . '"');
            out(['ok' => true, 'id' => (int)$pdo->lastInsertId()]);

        case 'update_task':
            $id = (int)($in['id'] ?? 0);
            $stmt = $pdo->prepare('SELECT * FROM tasks WHERE id=? AND user_id=?');
            $stmt->execute([$id, $uid]);
            $task = $stmt->fetch();
            if (!$task) {
                out(['error' => 'Task not found'], 404);
            }
            $title    = $in['title']       ?? $task['title'];
            $desc     = $in['description'] ?? $task['description'];
            $priority = $in['priority']    ?? $task['priority'];
            $due      = array_key_exists('due_date', $in) ? (($in['due_date'] ?? '') ?: null) : $task['due_date'];
            $status   = $in['status']      ?? $task['status'];
            $completed = $task['completed_at'];
            if ($status === 'done' && !$completed) {
                $completed = date('Y-m-d H:i:s');
            }
            if ($status !== 'done') {
                $completed = null;
            }
            $pdo->prepare('UPDATE tasks SET title=?, description=?, priority=?, due_date=?, status=?, completed_at=? WHERE id=?')
                ->execute([$title, $desc, $priority, $due, $status, $completed, $id]);
            if (isset($in['status'])) {
                log_activity($pdo, 'task_status', $id, 'Moved "' . $title . '" to ' . $status);
            } else {
                log_activity($pdo, 'task_updated', $id, 'Updated task "' . $title . '"');
            }
            out(['ok' => true]);

        case 'delete_task':
            $id = (int)($in['id'] ?? 0);
            $stmt = $pdo->prepare('SELECT title FROM tasks WHERE id=? AND user_id=?');
            $stmt->execute([$id, $uid]);
            $task = $stmt->fetch();
            if (!$task) {
                out(['error' => 'Task not found'], 404);
            }
            $pdo->prepare('DELETE FROM tasks WHERE id=?')->execute([$id]);
            log_activity($pdo, 'task_deleted', null, 'Deleted task "' . $task['title'] . '"');
            out(['ok' => true]);

        case 'notes':
            $stmt = $pdo->prepare('SELECT * FROM notes WHERE user_id=? ORDER BY updated_at DESC');
            $stmt->execute([$uid]);
            out($stmt->fetchAll());

        case 'save_note':
            $id    = (int)($in['id'] ?? 0);
            $title = trim($in['title'] ?? '');
            if ($title === '') {
                out(['error' => 'Title is required'], 422);
            }
            if ($id) {
                $pdo->prepare('UPDATE notes SET title=?, content=? WHERE id=? AND user_id=?')
                    ->execute([$title, $in['content'] ?? '', $id, $uid]);
                log_activity($pdo, 'note_updated', null, 'Updated note "' . $title . '"');
            } else {
                $pdo->prepare('INSERT INTO notes (user_id, title, content) VALUES (?, ?, ?)')
                    ->execute([$uid, $title, $in['content'] ?? '']);
                log_activity($pdo, 'note_created', null, 'Created note "' . $title . '"');
            }
            out(['ok' => true]);

        case 'delete_note':
            $id = (int)($in['id'] ?? 0);
            $stmt = $pdo->prepare('SELECT title FROM notes WHERE id=? AND user_id=?');
            $stmt->execute([$id, $uid]);
            $note = $stmt->fetch();
            if (!$note) {
                out(['error' => 'Note not found'], 404);
            }
            $pdo->prepare('DELETE FROM notes WHERE id=?')->execute([$id]);
            log_activity($pdo, 'note_deleted', null, 'Deleted note "' . $note['title'] . '"');
            out(['ok' => true]);

        case 'history':
            $stmt = $pdo->prepare('SELECT * FROM activity_log WHERE user_id=? ORDER BY created_at DESC, id DESC LIMIT 200');
            $stmt->execute([$uid]);
            out($stmt->fetchAll());

            case 'clear_history':
    // Wipe the user's activity log, then leave one entry so the
    // user gets feedback that the action succeeded.
    $pdo->prepare('DELETE FROM activity_log WHERE user_id=?')->execute([$uid]);
    log_activity($pdo, 'history_cleared', null, 'Cleared all history');
    out(['ok' => true]);

        default:
            out(['error' => 'Unknown action'], 400);
    }
} catch (Throwable $e) {
    out(['error' => 'Server error: ' . $e->getMessage()], 500);
}
