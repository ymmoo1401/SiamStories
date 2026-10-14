<?php
/**
 * SIAM STORIES - Authentication Check & Role-Based Access Control (RBAC)
 * จำกัดการเข้าถึงระบบหลังบ้านเฉพาะผู้ที่มีสิทธิ์ admin เท่านั้น
 */

declare(strict_types=1);

function startSecureSession(): void {
    if (session_status() === PHP_SESSION_NONE) {
        $isHttps = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on')
            || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https');

        session_set_cookie_params([
            'lifetime' => 0,
            'path'     => '/',
            'domain'   => '',
            'secure'   => $isHttps,
            'httponly' => true,
            'samesite' => 'Lax'
        ]);

        session_start();
    }
}

/**
 * ตรวจสอบว่าเข้าสู่ระบบหรือไม่ (ไม่ว่าจะเป็น admin หรือ member)
 */
function isLoggedIn(): bool {
    startSecureSession();
    return !empty($_SESSION['admin_id']) && !empty($_SESSION['admin_username']);
}

/**
 * ตรวจสอบว่าเป็นผู้ดูแลระบบ (Admin) หรือไม่
 */
function isAdmin(): bool {
    startSecureSession();
    return isLoggedIn() && (($_SESSION['admin_role'] ?? 'member') === 'admin');
}

/**
 * ตรวจสอบสถานะการเข้าสู่ระบบเข้ากับระบบแอดมิน
 */
function isAdminLoggedIn(): bool {
    return isAdmin();
}

/**
 * บังคับสิทธิ์ Admin เท่านั้น หากเป็น Member ทั่วไป หรือยังไม่ล็อกอิน จะถูกปฏิเสธทันที
 */
function requireAdminLogin(): void {
    startSecureSession();

    // 1. ถ้ายังไม่ล็อกอินเลย ให้ไปหน้า Login
    if (!isLoggedIn()) {
        header('Location: login.php');
        exit;
    }

    // 2. ถ้าล็อกอินแล้วแต่เป็นเพียง member ทั่วไป (ไม่มีสิทธิ์ admin)
    if (!isAdmin()) {
        http_response_code(403);
        $displayName = htmlspecialchars($_SESSION['admin_display_name'] ?? 'ผู้ใช้', ENT_QUOTES, 'UTF-8');
        ?>
        <!DOCTYPE html>
        <html lang="th">
        <head>
            <meta charset="utf-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>403 สิทธิ์การเข้าถึงถูกปฏิเสธ | SIAM STORIES</title>
            <link rel="stylesheet" href="css/admin.css">
        </head>
        <body class="auth-page">
            <div class="auth-dialog-card" style="max-width: 460px; text-align: center;">
                <div style="font-size: 3rem; margin-bottom: 12px;">🔒</div>
                <h1 style="font-size: 1.5rem; color: #1F1B18; margin-bottom: 10px;">เฉพาะผู้ดูแลระบบ (Admin) เท่านั้น</h1>
                <p style="font-size: 0.95rem; color: #666; margin-bottom: 16px; line-height: 1.6;">
                    สวัสดีคุณ <strong><?php echo $displayName; ?></strong><br>
                    บัญชีของคุณมีสถานะเป็น <strong>สมาชิกทั่วไป (Member)</strong> จึงไม่สามารถดูหรือเข้าถึงระบบจัดการข้อมูลหลังบ้านได้
                </p>
                <div style="display: flex; flex-direction: column; gap: 10px;">
                    <a href="../index.html" class="btn-auth-submit" style="text-decoration: none; background: #76502F;">
                        กลับสู่หน้าเว็บไซต์
                    </a>
                    <a href="logout.php" style="color: #888; font-size: 0.9rem; text-decoration: underline; margin-top: 6px;">
                        ออกจากระบบ เพื่อเข้าสู่ระบบด้วยบัญชี Admin อื่น
                    </a>
                </div>
            </div>
        </body>
        </html>
        <?php
        exit;
    }
}

/**
 * ดึงข้อมูลผู้ใช้ปัจจุบันที่ล็อกอินอยู่
 */
function getLoggedInAdmin(): ?array {
    if (!isLoggedIn()) {
        return null;
    }
    return [
        'id'           => $_SESSION['admin_id'],
        'username'     => $_SESSION['admin_username'],
        'display_name' => $_SESSION['admin_display_name'] ?? 'ผู้ใช้งาน',
        'role'         => $_SESSION['admin_role'] ?? 'member'
    ];
}
