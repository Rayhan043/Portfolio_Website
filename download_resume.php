<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';

$resume = $pdo->query("SELECT * FROM resume ORDER BY id DESC LIMIT 1")->fetch();
if (!$resume) {
    http_response_code(404);
    die('Resume not found.');
}

$path = __DIR__ . '/' . $resume['file_path'];
if (!file_exists($path)) {
    http_response_code(404);
    die('Resume file not found on disk.');
}

$settings = get_settings($pdo);
$count = (int)($settings['resume_downloads'] ?? 0) + 1;
set_setting($pdo, 'resume_downloads', (string)$count);

header('Content-Type: application/pdf');
header('Content-Disposition: attachment; filename="Resume.pdf"');
header('Content-Length: ' . filesize($path));
readfile($path);
exit;
