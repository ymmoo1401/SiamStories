<?php
/**
 * SIAM STORIES - API สำหรับตรวจสอบสถานะการเข้าสู่ระบบ (Authentication Status)
 * ใช้งานโดย JavaScript ฝั่งหน้าบ้าน (index.html, isan.html, ฯลฯ)
 * เพื่อตรวจว่าผู้ใช้ล็อกอินอยู่หรือไม่ ค้างสถานะผู้ใช้ไว้ และแสดงโปรไฟล์/เมนูที่เหมาะสม
 */

declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');

require_once __DIR__ . '/../admin/auth_check.php';
require_once __DIR__ . '/../config/db.php';

startSecureSession();

if (isLoggedIn()) {
    $userId = (int)$_SESSION['admin_id'];
    $role = $_SESSION['admin_role'] ?? 'member';
    $username = (string)$_SESSION['admin_username'];
    $displayName = (string)($_SESSION['admin_display_name'] ?? $username);
    $email = '';

    // ดึงข้อมูลอีเมลและสถิติการใช้งานคร่าวๆ จากฐานข้อมูล
    try {
        $pdo = getDbConnection();
        $stmt = $pdo->prepare('SELECT email, role, display_name, created_at FROM admins WHERE id = ? LIMIT 1');
        $stmt->execute([$userId]);
        $userData = $stmt->fetch();
        if ($userData) {
            $email = (string)($userData['email'] ?? '');
            $role = (string)($userData['role'] ?? $role);
            $displayName = (string)($userData['display_name'] ?? $displayName);
            $createdAt = (string)($userData['created_at'] ?? '');
        }

        // นับประวัติกิจกรรมที่เคยทำ
        $stmtCount = $pdo->prepare('SELECT COUNT(*) FROM user_history WHERE user_id = ?');
        $stmtCount->execute([$userId]);
        $historyCount = (int)$stmtCount->fetchColumn();
    } catch (Throwable $e) {
        $historyCount = 0;
        $createdAt = '';
    }

    echo json_encode([
        'success'   => true,
        'logged_in' => true,
        'user'      => [
            'id'            => $userId,
            'username'      => $username,
            'display_name'  => $displayName,
            'email'         => $email,
            'role'          => $role,
            'is_admin'      => ($role === 'admin'),
            'created_at'    => $createdAt ?? '',
            'history_count' => $historyCount
        ]
    ], JSON_UNESCAPED_UNICODE);
} else {
    echo json_encode([
        'success'   => true,
        'logged_in' => false,
        'user'      => null
    ], JSON_UNESCAPED_UNICODE);
}

