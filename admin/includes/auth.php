<?php
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../includes/functions.php';

function require_login() {
    if (empty($_SESSION['admin_id'])) {
        redirect('login.php');
    }
    // Session idle timeout (30 min)
    if (!empty($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > 1800) {
        session_unset();
        session_destroy();
        redirect('login.php?timeout=1');
    }
    $_SESSION['last_activity'] = time();
}
