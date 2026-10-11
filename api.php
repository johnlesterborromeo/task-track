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

function normalizeChecklist($items): array
{
    if (!is_array($items)) {
        out(['error' => 'Checklist must be a list'], 422);
    }

    $checklist = [];
    foreach ($items as $item) {
        if (!is_array($item) || !isset($item['title']) || !is_string($item['title'])) {
            out(['error' => 'Invalid checklist item'], 422);
        }
        $title = trim($item['title'] ?? '');
        if ($title === '') {
            continue;
        }
        $length = preg_match_all('/./us', $title, $matches);
        if ($length === false) {
            out(['error' => 'Checklist item titles must be valid UTF-8'], 422);
        }
        if ($length > 150) {
            out(['error' => 'Checklist item titles must be 150 characters or fewer'], 422);
        }
        $checklist[] = ['title' => $title, 'completed' => !empty($item['completed']) ? 1 : 0];
    }
    return $checklist;
}

function saveChecklist(PDO $pdo, int $taskId, array $checklist): void
{
    $pdo->prepare('DELETE FROM task_checklist_items WHERE task_id=?')->execute([$taskId]);
    $stmt = $pdo->prepare('INSERT INTO task_checklist_items (task_id, title, completed) VALUES (?, ?, ?)');
    foreach ($checklist as $item) {
        $stmt->execute([$taskId, $item['title'], $item['completed']]);
    }
}

try {
    $pdo->exec("CREATE TABLE IF NOT EXISTS task_checklist_items (
        id INT AUTO_INCREMENT PRIMARY KEY,
        task_id INT NOT NULL,
        title VARCHAR(150) NOT NULL,
        completed TINYINT(1) NOT NULL DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (task_id) REFERENCES tasks(id) ON DELETE CASCADE
    ) ENGINE=InnoDB");

    if (in_array($action, ['profile', 'update_profile', 'upload_profile_picture', 'remove_profile_picture'], true)) {
        $columns = $pdo->query('SHOW COLUMNS FROM users');
        $userColumns = array_column($columns->fetchAll(), 'Field');
        if (!in_array('display_name', $userColumns, true)) {
            $pdo->exec("ALTER TABLE users ADD display_name VARCHAR(80) NOT NULL DEFAULT ''");
        }
        if (!in_array('bio', $userColumns, true)) {
            $pdo->exec('ALTER TABLE users ADD bio TEXT NULL');
        }
        if (!in_array('profile_picture', $userColumns, true)) {
            $pdo->exec('ALTER TABLE users ADD profile_picture MEDIUMBLOB NULL');
        }
        if (!in_array('profile_picture_mime', $userColumns, true)) {
            $pdo->exec('ALTER TABLE users ADD profile_picture_mime VARCHAR(40) NULL');
        }
    }

    switch ($action) {

        case 'profile':
            $stmt = $pdo->prepare("SELECT username, COALESCE(NULLIF(display_name, ''), username) AS display_name, bio, created_at, profile_picture IS NOT NULL AS has_profile_picture FROM users WHERE id=?");
            $stmt->execute([$uid]);
            $profile = $stmt->fetch();
            if (!$profile) {
                out(['error' => 'User profile not found'], 404);
            }
            $profile['has_profile_picture'] = (bool)$profile['has_profile_picture'];
            out($profile);

        case 'upload_profile_picture':
            $file = $_FILES['profile_picture'] ?? null;
            if (!is_array($file) || !isset($file['error'], $file['tmp_name'], $file['size'])) {
                out(['error' => 'Choose an image to upload.'], 422);
            }
            if ($file['error'] !== UPLOAD_ERR_OK) {
                $message = $file['error'] === UPLOAD_ERR_INI_SIZE || $file['error'] === UPLOAD_ERR_FORM_SIZE
                    ? 'Profile picture must be 5 MB or smaller.'
                    : 'The image could not be uploaded. Please try again.';
                out(['error' => $message], 422);
            }
            if (!is_uploaded_file($file['tmp_name']) || $file['size'] < 1 || $file['size'] > 5 * 1024 * 1024) {
                out(['error' => 'Profile picture must be a valid image no larger than 5 MB.'], 422);
            }
            $imageData = file_get_contents($file['tmp_name']);
            if ($imageData === false) {
                out(['error' => 'Could not read the uploaded image.'], 422);
            }
            $finfo = new finfo(FILEINFO_MIME_TYPE);
            $mime = $finfo->buffer($imageData);
            $image = getimagesizefromstring($imageData);
            $allowedMimes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
            if (!is_array($image) || !in_array($mime, $allowedMimes, true) || $image['mime'] !== $mime) {
                out(['error' => 'Choose a JPEG, PNG, GIF, or WebP image.'], 422);
            }
            if ($image[0] > 6000 || $image[1] > 6000 || $image[0] * $image[1] > 30000000) {
                out(['error' => 'Image dimensions must be 6000 pixels or smaller per side.'], 422);
            }
            $stmt = $pdo->prepare('UPDATE users SET profile_picture=?, profile_picture_mime=? WHERE id=?');
            $stmt->bindValue(1, $imageData, PDO::PARAM_LOB);
            $stmt->bindValue(2, $mime, PDO::PARAM_STR);
            $stmt->bindValue(3, $uid, PDO::PARAM_INT);
            $stmt->execute();
            out(['ok' => true, 'has_profile_picture' => true]);

        case 'remove_profile_picture':
            $pdo->prepare('UPDATE users SET profile_picture=NULL, profile_picture_mime=NULL WHERE id=?')->execute([$uid]);
            out(['ok' => true, 'has_profile_picture' => false]);

        case 'update_profile':
            $username = $in['username'] ?? null;
            $displayName = $in['display_name'] ?? null;
            $bio = $in['bio'] ?? '';
            if (!is_string($username) || !is_string($displayName) || !is_string($bio)) {
                out(['error' => 'Please enter valid profile information.'], 422);
            }
            $username = trim($username);
            $displayName = trim($displayName);
            $bio = trim($bio);
            $usernameLength = preg_match_all('/./us', $username, $matches);
            $displayNameLength = preg_match_all('/./us', $displayName, $matches);
            $bioLength = preg_match_all('/./us', $bio, $matches);
            if ($usernameLength === false || $displayNameLength === false || $bioLength === false) {
                out(['error' => 'Profile information must be valid UTF-8.'], 422);
            }
            if ($usernameLength < 1 || $usernameLength > 50) {
                out(['error' => 'Username must be between 1 and 50 characters.'], 422);
            }
            if ($displayNameLength < 1 || $displayNameLength > 80) {
                out(['error' => 'Display name must be between 1 and 80 characters.'], 422);
            }
            if ($bioLength > 500) {
                out(['error' => 'Bio must be 500 characters or fewer.'], 422);
            }
            $stmt = $pdo->prepare('SELECT id FROM users WHERE username=? AND id<>?');
            $stmt->execute([$username, $uid]);
            if ($stmt->fetch()) {
                out(['error' => 'That username is already taken.'], 409);
            }
            try {
                $pdo->prepare('UPDATE users SET username=?, display_name=?, bio=? WHERE id=?')
                    ->execute([$username, $displayName, $bio, $uid]);
            } catch (PDOException $e) {
                if ($e->getCode() === '23000') {
                    out(['error' => 'That username is already taken.'], 409);
                }
                throw $e;
            }
            $_SESSION['user']['username'] = $username;
            $_SESSION['user']['display_name'] = $displayName;
            out([
                'ok' => true,
                'username' => $username,
                'display_name' => $displayName,
                'bio' => $bio,
            ]);

        case 'update_username':
            $username = $in['username'] ?? null;
            if (!is_string($username)) {
                out(['error' => 'Username is required'], 422);
            }
            $username = trim($username);
            $length = preg_match_all('/./us', $username, $matches);
            if ($length === false) {
                out(['error' => 'Username must be valid UTF-8'], 422);
            }
            if ($length < 1 || $length > 50) {
                out(['error' => 'Username must be between 1 and 50 characters'], 422);
            }
            $stmt = $pdo->prepare('SELECT id FROM users WHERE username=? AND id<>?');
            $stmt->execute([$username, $uid]);
            if ($stmt->fetch()) {
                out(['error' => 'That username is already taken.'], 409);
            }
            try {
                $pdo->prepare('UPDATE users SET username=? WHERE id=?')->execute([$username, $uid]);
            } catch (PDOException $e) {
                if ($e->getCode() === '23000') {
                    out(['error' => 'That username is already taken.'], 409);
                }
                throw $e;
            }
            $_SESSION['user']['username'] = $username;
            out(['ok' => true, 'username' => $username]);

        case 'update_password':
            $currentPassword = $in['current_password'] ?? null;
            $newPassword = $in['new_password'] ?? null;
            $confirmPassword = $in['confirm_password'] ?? null;
            if (!is_string($currentPassword) || !is_string($newPassword) || !is_string($confirmPassword)
                || $currentPassword === '' || $newPassword === '' || $confirmPassword === '') {
                out(['error' => 'Please fill in every password field.'], 422);
            }
            $length = preg_match_all('/./us', $newPassword, $matches);
            if ($length === false) {
                out(['error' => 'Password must be valid UTF-8.'], 422);
            }
            if ($length < 8) {
                out(['error' => 'New password must be at least 8 characters.'], 422);
            }
            if ($newPassword !== $confirmPassword) {
                out(['error' => 'New passwords do not match.'], 422);
            }
            $stmt = $pdo->prepare('SELECT password_hash FROM users WHERE id=?');
            $stmt->execute([$uid]);
            $user = $stmt->fetch();
            if (!$user || !password_verify($currentPassword, $user['password_hash'])) {
                out(['error' => 'Current password is incorrect.'], 422);
            }
            $passwordHash = password_hash($newPassword, PASSWORD_DEFAULT);
            $pdo->prepare('UPDATE users SET password_hash=? WHERE id=?')->execute([$passwordHash, $uid]);
            out(['ok' => true]);

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
            $tasks = $stmt->fetchAll();
            if ($tasks) {
                $taskIds = array_column($tasks, 'id');
                $placeholders = implode(',', array_fill(0, count($taskIds), '?'));
                $items = $pdo->prepare("SELECT id, task_id, title, completed FROM task_checklist_items WHERE task_id IN ($placeholders) ORDER BY id");
                $items->execute($taskIds);
                $byTask = [];
                foreach ($items->fetchAll() as $item) {
                    $item['completed'] = (bool)$item['completed'];
                    $byTask[$item['task_id']][] = $item;
                }
                foreach ($tasks as &$task) {
                    $task['checklist'] = $byTask[$task['id']] ?? [];
                }
                unset($task);
            }
            out($tasks);

        case 'add_task':
            $title = trim($in['title'] ?? '');
            if ($title === '') {
                out(['error' => 'Title is required'], 422);
            }
            $checklist = normalizeChecklist($in['checklist'] ?? []);
            $pdo->beginTransaction();
            try {
                $stmt = $pdo->prepare('INSERT INTO tasks (user_id, title, description, priority, due_date) VALUES (?, ?, ?, ?, ?)');
                $stmt->execute([$uid, $title, $in['description'] ?? '', $in['priority'] ?? 'medium', ($in['due_date'] ?? null) ?: null]);
                $id = (int)$pdo->lastInsertId();
                saveChecklist($pdo, $id, $checklist);
                $pdo->commit();
            } catch (Throwable $e) {
                $pdo->rollBack();
                throw $e;
            }
            log_activity($pdo, 'task_created', $id, 'Created task "' . $title . '"');
            out(['ok' => true, 'id' => $id]);

        case 'update_task':
            $id = (int)($in['id'] ?? 0);
            $stmt = $pdo->prepare('SELECT * FROM tasks WHERE id=? AND user_id=?');
            $stmt->execute([$id, $uid]);
            $task = $stmt->fetch();
            if (!$task) {
                out(['error' => 'Task not found'], 404);
            }
            if ($task['status'] === 'done') {
                out(['error' => 'Completed tasks cannot be edited'], 409);
            }
            $checklist = array_key_exists('checklist', $in) ? normalizeChecklist($in['checklist']) : null;
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
            $pdo->beginTransaction();
            try {
                $pdo->prepare('UPDATE tasks SET title=?, description=?, priority=?, due_date=?, status=?, completed_at=? WHERE id=?')
                    ->execute([$title, $desc, $priority, $due, $status, $completed, $id]);
                if ($checklist !== null) {
                    saveChecklist($pdo, $id, $checklist);
                }
                $pdo->commit();
            } catch (Throwable $e) {
                $pdo->rollBack();
                throw $e;
            }
            if (isset($in['status'])) {
                log_activity($pdo, 'task_status', $id, 'Moved "' . $title . '" to ' . $status);
            } else {
                log_activity($pdo, 'task_updated', $id, 'Updated task "' . $title . '"');
            }
            out(['ok' => true]);

        case 'toggle_checklist_item':
            $itemId = (int)($in['id'] ?? 0);
            $stmt = $pdo->prepare('SELECT i.completed, t.status FROM task_checklist_items i JOIN tasks t ON t.id=i.task_id WHERE i.id=? AND t.user_id=?');
            $stmt->execute([$itemId, $uid]);
            $item = $stmt->fetch();
            if (!$item) {
                out(['error' => 'Checklist item not found'], 404);
            }
            if ($item['status'] === 'done') {
                out(['error' => 'Completed task checklists cannot be changed'], 409);
            }
            $pdo->prepare('UPDATE task_checklist_items SET completed=? WHERE id=?')
                ->execute([(int)!$item['completed'], $itemId]);
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
