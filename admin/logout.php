<?php
/**
 * SIAM STORIES - Admin Logout
 */

declare(strict_types=1);

require_once __DIR__ . '/auth_check.php';

startSecureSession();

// เคลียร์ค่า session
$_SESSION = [];

// ลบคุกกี้ session
if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params['path'],
        $params['domain'],
        $params['secure'],
        $params['httponly']
    );
}

session_destroy();

$ref = $_GET['ref'] ?? '';
if ($ref === 'home') {
    header('Location: ../index.html');
} else {
    header('Location: login.php');
}
exit;

