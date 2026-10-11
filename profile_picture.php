<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/config.php';

$stmt = $pdo->prepare('SELECT profile_picture, profile_picture_mime FROM users WHERE id=?');
$stmt->execute([(int)$_SESSION['user']['id']]);
$profile = $stmt->fetch();
$allowedMimes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];

if (!$profile || !$profile['profile_picture'] || !in_array($profile['profile_picture_mime'], $allowedMimes, true)) {
    http_response_code(404);
    exit;
}

header('Content-Type: ' . $profile['profile_picture_mime']);
header('Content-Length: ' . strlen($profile['profile_picture']));
header('Cache-Control: private, no-store, max-age=0');
header('X-Content-Type-Options: nosniff');
echo $profile['profile_picture'];
