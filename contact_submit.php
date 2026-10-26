<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed.']);
    exit;
}

// Basic rate limiting per session (1 message / 20 seconds)
if (!empty($_SESSION['last_msg_time']) && (time() - $_SESSION['last_msg_time']) < 20) {
    echo json_encode(['success' => false, 'message' => 'Please wait a moment before sending another message.']);
    exit;
}

$name    = trim($_POST['name'] ?? '');
$email   = trim($_POST['email'] ?? '');
$company = trim($_POST['company'] ?? '');
$subject = trim($_POST['subject'] ?? '');
$message = trim($_POST['message'] ?? '');

// Honeypot-style basic validation
if ($name === '' || $email === '' || $subject === '' || $message === '') {
    echo json_encode(['success' => false, 'message' => 'Please fill in all required fields.']);
    exit;
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'Please enter a valid email address.']);
    exit;
}
if (mb_strlen($name) > 150 || mb_strlen($subject) > 200 || mb_strlen($message) > 5000) {
    echo json_encode(['success' => false, 'message' => 'One of the fields is too long.']);
    exit;
}

try {
    $stmt = $pdo->prepare("INSERT INTO messages (name, email, company, subject, message) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$name, $email, $company, $subject, $message]);
    $_SESSION['last_msg_time'] = time();
    echo json_encode(['success' => true, 'message' => 'Message transmitted successfully. I\'ll get back to you soon.']);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Something went wrong. Please try again later.']);
}
