<?php
/**
 * Shared helper functions
 */

if (session_status() === PHP_SESSION_NONE) {
    // Harden session cookies
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_strict_mode', 1);
    if (!empty($_SERVER['HTTPS'])) {
        ini_set('session.cookie_secure', 1);
    }
    session_start();
}

/** Escape output for safe HTML rendering (XSS protection) */
function e($str) {
    return htmlspecialchars($str ?? '', ENT_QUOTES, 'UTF-8');
}

/** Generate / return CSRF token for this session */
function csrf_token() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/** Output a hidden CSRF field */
function csrf_field() {
    echo '<input type="hidden" name="csrf_token" value="' . e(csrf_token()) . '">';
}

/** Verify posted CSRF token, die on mismatch */
function csrf_verify() {
    $token = $_POST['csrf_token'] ?? '';
    if (empty($token) || empty($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $token)) {
        http_response_code(403);
        die('Invalid CSRF token. Please refresh the page and try again.');
    }
}

/** Fetch all site_settings as an associative array */
function get_settings(PDO $pdo) {
    static $cache = null;
    if ($cache !== null) return $cache;
    $rows = $pdo->query("SELECT setting_key, setting_value FROM site_settings")->fetchAll();
    $cache = [];
    foreach ($rows as $r) {
        $cache[$r['setting_key']] = $r['setting_value'];
    }
    return $cache;
}

/** Update (or insert) a single setting */
function set_setting(PDO $pdo, $key, $value) {
    $stmt = $pdo->prepare("INSERT INTO site_settings (setting_key, setting_value) VALUES (:k, :v)
                            ON DUPLICATE KEY UPDATE setting_value = :v2");
    $stmt->execute([':k' => $key, ':v' => $value, ':v2' => $value]);
}

/**
 * Validate & move an uploaded file.
 * $allowed = array of allowed mime types, $maxBytes = size limit
 * Returns relative path on success, or false on failure/absence.
 */
function handle_upload($fieldName, $destDir, array $allowedExt, $maxBytes, &$error = null) {
    if (empty($_FILES[$fieldName]) || $_FILES[$fieldName]['error'] === UPLOAD_ERR_NO_FILE) {
        return null; // no file submitted, not an error
    }
    $file = $_FILES[$fieldName];

    if ($file['error'] !== UPLOAD_ERR_OK) {
        $error = 'Upload error code: ' . $file['error'];
        return false;
    }
    if ($file['size'] > $maxBytes) {
        $error = 'File too large. Max ' . round($maxBytes / 1048576, 1) . 'MB.';
        return false;
    }

    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, $allowedExt, true)) {
        $error = 'File type .' . $ext . ' not allowed.';
        return false;
    }

    // Verify real MIME type (defense against renamed files)
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);

    $mimeMap = [
        'jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg', 'png' => 'image/png',
        'webp' => 'image/webp', 'gif' => 'image/gif',
        'pdf' => 'application/pdf', 'mp4' => 'video/mp4',
    ];
    if (isset($mimeMap[$ext]) && $mime !== $mimeMap[$ext] && !($ext === 'jpg' && $mime === 'image/jpeg')) {
        // allow a little leniency for video containers reported oddly
        if (!(in_array($ext, ['mp4']) && strpos($mime, 'video/') === 0)) {
            $error = 'File content does not match its extension.';
            return false;
        }
    }

    if (!is_dir($destDir)) {
        if (!mkdir($destDir, 0755, true) && !is_dir($destDir)) {
            $error = 'Failed to create the upload directory.';
            return false;
        }
    }

    $safeName = bin2hex(random_bytes(8)) . '_' . time() . '.' . $ext;
    $canonicalDir = realpath($destDir);
    if ($canonicalDir === false) {
        $error = 'Upload directory is not available.';
        return false;
    }
    $destPath = $canonicalDir . DIRECTORY_SEPARATOR . $safeName;

    if (!move_uploaded_file($file['tmp_name'], $destPath)) {
        $error = 'Failed to move uploaded file.';
        return false;
    }

    // Convert absolute path to relative path for web access
    $baseDir = realpath(__DIR__ . '/..');
    $destPath = str_replace('\\', '/', $destPath);
    $baseDir = str_replace('\\', '/', $baseDir);
    $relPath = str_replace($baseDir . '/', '', $destPath);
    return $relPath;
}

/** Simple redirect helper */
function redirect($url) {
    header("Location: $url");
    exit;
}

/** Format a date range for the experience timeline */
function format_date_range($start, $end) {
    $s = date('M Y', strtotime($start));
    $e = $end ? date('M Y', strtotime($end)) : 'Present';
    return "$s — $e";
}
