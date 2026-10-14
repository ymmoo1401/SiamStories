<?php
/**
 * SIAM STORIES - Modern Login Page (Matching UI Reference)
 * รองรับการเข้าสู่ระบบด้วยชื่อผู้ใช้หรืออีเมล และระบบจำฉันเข้าระบบ (Remember Me)
 */

declare(strict_types=1);

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/auth_check.php';
require_once __DIR__ . '/csrf.php';

startSecureSession();

// ถ้าเข้าสู่ระบบอยู่แล้ว ให้ไปยัง Dashboard
if (isAdminLoggedIn()) {
    header('Location: index.php');
    exit;
}

$errorMessage = '';
$successMessage = '';
$accountVal = '';

// ตรวจสอบ Flash Message จากหน้าสมัครสมาชิก
if (!empty($_SESSION['flash_success'])) {
    $successMessage = $_SESSION['flash_success'];
    unset($_SESSION['flash_success']);
}

// ตรวจสอบ Rate Limiting (จำกัดการลองผิด 5 ครั้ง รอ 60 วินาที)
$lockoutTime = $_SESSION['login_lockout_until'] ?? 0;
$currentTime = time();

if ($lockoutTime > $currentTime) {
    $remainingSeconds = $lockoutTime - $currentTime;
    $errorMessage = "ลองเข้าสู่ระบบผิดเกินกำหนด กรุณารออีก {$remainingSeconds} วินาทีแล้วลองใหม่";
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['csrf_token'] ?? '';
    if (!validateCsrfToken($token)) {
        $errorMessage = 'แบบฟอร์มหมดอายุ กรุณารีเฟรชแล้วลองใหม่อีกครั้ง';
    } else {
        $account = trim((string)($_POST['account'] ?? ''));
        $password = (string)($_POST['password'] ?? '');
        $rememberMe = !empty($_POST['remember_me']);
        $accountVal = $account;

        if ($account === '' || $password === '') {
            $errorMessage = 'กรุณากรอกอีเมล/ชื่อผู้ใช้ และรหัสผ่านให้ครบถ้วน';
        } else {
            $pdo = getDbConnection();
            // ค้นหาได้ทั้งจาก username หรือ email
            $stmt = $pdo->prepare('SELECT id, username, email, password_hash, display_name, role FROM admins WHERE username = ? OR email = ? LIMIT 1');
            $stmt->execute([$account, $account]);
            $admin = $stmt->fetch();

            if ($admin && password_verify($password, $admin['password_hash'])) {
                // เข้าสู่ระบบสำเร็จ
                session_regenerate_id(true);
                $_SESSION['admin_id'] = $admin['id'];
                $_SESSION['admin_username'] = $admin['username'];
                $_SESSION['admin_display_name'] = $admin['display_name'];
                $_SESSION['admin_role'] = $admin['role'] ?? 'member';

                // จัดการ Remember Me (หากเลือก ให้ยืดอายุคุกกี้ 30 วัน)
                if ($rememberMe) {
                    $lifetime = 30 * 24 * 60 * 60; // 30 วัน
                    $isHttps = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on')
                        || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https');
                    
                    setcookie(session_name(), session_id(), [
                        'expires'  => time() + $lifetime,
                        'path'     => '/',
                        'domain'   => '',
                        'secure'   => $isHttps,
                        'httponly' => true,
                        'samesite' => 'Lax'
                    ]);
                }

                // รีเซ็ตตัวนับการเข้าสู่ระบบผิด
                unset($_SESSION['login_failed_attempts'], $_SESSION['login_lockout_until']);

                // ตรวจสอบสิทธิ์: ถ้าเป็น admin ไปที่หลังบ้าน, ถ้าเป็น member ทั่วไป ให้กลับไปหน้าบ้าน
                if (($_SESSION['admin_role'] ?? 'member') === 'admin') {
                    header('Location: index.php');
                } else {
                    header('Location: ../index.html');
                }
                exit;
            } else {
                $attempts = ($_SESSION['login_failed_attempts'] ?? 0) + 1;
                $_SESSION['login_failed_attempts'] = $attempts;

                if ($attempts >= 5) {
                    $_SESSION['login_lockout_until'] = time() + 60;
                    $errorMessage = 'ลองเข้าสู่ระบบผิดเกิน 5 ครั้ง กรุณารอ 60 วินาทีก่อนลองใหม่';
                } else {
                    $errorMessage = 'ชื่อผู้ใช้หรือรหัสผ่านไม่ถูกต้อง';
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>เข้าสู่ระบบ | SIAM STORIES</title>
    <link rel="stylesheet" href="css/admin.css">
</head>
<body class="auth-page">

<div class="auth-dialog-card">
    <!-- ปุ่มกากบาทมุมบนขวาสำหรับกลับหน้าแรก -->
    <a href="../index.html" class="auth-close-btn" title="กลับหน้าแรก" aria-label="ปิด">✕</a>

    <div class="auth-brand-badge">
        <span>❈</span> SIAM STORIES — รากฐานของความเป็นไทย
    </div>

    <!-- ไอคอนบัญชีผู้ใช้ทรงกลม (ตามรูปตัวอย่าง) -->
    <div class="auth-user-icon-avatar">
        <svg viewBox="0 0 24 24" width="28" height="28" fill="none" stroke="currentColor" stroke-width="1.85" stroke-linecap="round" stroke-linejoin="round">
            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
            <circle cx="12" cy="7" r="4"></circle>
        </svg>
    </div>

    <h1 class="auth-title-center">เข้าสู่ระบบ</h1>

    <?php if (!empty($successMessage)): ?>
        <div class="alert alert-success" role="alert" style="margin-bottom: 18px;">
            ✓ <?php echo htmlspecialchars($successMessage, ENT_QUOTES, 'UTF-8'); ?>
        </div>
    <?php endif; ?>

    <?php if (!empty($errorMessage)): ?>
        <div class="alert alert-danger" role="alert" style="margin-bottom: 18px;">
            <?php echo htmlspecialchars($errorMessage, ENT_QUOTES, 'UTF-8'); ?>
        </div>
    <?php endif; ?>

    <form method="post" action="login.php" novalidate>
        <?php echo csrfField(); ?>

        <!-- ช่องอีเมล / ชื่อผู้ใช้ -->
        <div class="input-icon-wrap">
            <span class="prefix-icon">@</span>
            <input 
                type="text" 
                id="account" 
                name="account" 
                value="<?php echo htmlspecialchars($accountVal, ENT_QUOTES, 'UTF-8'); ?>" 
                required 
                autocomplete="username"
                placeholder="อีเมล หรือ ชื่อผู้ใช้"
                aria-label="อีเมล หรือ ชื่อผู้ใช้">
        </div>

        <!-- ช่องรหัสผ่าน -->
        <div class="input-icon-wrap">
            <span class="prefix-icon">🔒</span>
            <input 
                type="password" 
                id="password" 
                name="password" 
                required 
                autocomplete="current-password"
                placeholder="รหัสผ่าน"
                aria-label="รหัสผ่าน">
            <button 
                type="button" 
                class="btn-toggle-pw" 
                aria-label="แสดงรหัสผ่าน" 
                data-target="password">👁</button>
        </div>

        <!-- จำฉันเข้าระบบ -->
        <div class="auth-options-row">
            <label class="checkbox-label">
                <input type="checkbox" name="remember_me" value="1">
                <span>จำฉันเข้าระบบ</span>
            </label>
        </div>

        <!-- ปุ่มเข้าสู่ระบบ สีแดงชาดสดใสสไตล์โมเดิร์น -->
        <button type="submit" class="btn-auth-submit">
            เข้าสู่ระบบ
        </button>

        <!-- Divider -->
        <div class="auth-divider">
            <span>หรือเข้าสู่ระบบด้วย</span>
        </div>

        <!-- ตัวเลือกเข้าสู่ระบบทางเลือก (จำลองเพื่อความสมบูรณ์ของดีไซน์) -->
        <div class="social-login-group">
            <button type="button" class="btn-social btn-social-fb" onclick="alert('ระบบเข้าสู่ระบบด้วย Facebook จำลองสำหรับตัวอย่างการออกแบบ');">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="#FFFFFF" aria-hidden="true">
                    <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                </svg>
                <span>เข้าสู่ระบบด้วย Facebook</span>
            </button>
            <button type="button" class="btn-social btn-social-google" onclick="alert('ระบบเข้าสู่ระบบด้วย Google จำลองสำหรับตัวอย่างการออกแบบ');">
                <svg width="18" height="18" viewBox="0 0 24 24" aria-hidden="true">
                    <path fill="#EA4335" d="M12 5c1.6 0 3 .6 4.1 1.7l3.1-3.1C17.3 1.8 14.8 1 12 1 7.5 1 3.7 3.6 1.9 7.3l3.7 2.9C6.5 7.4 9 5 12 5z"/>
                    <path fill="#4285F4" d="M23.5 12.3c0-.8-.1-1.7-.2-2.3H12v4.6h6.5c-.3 1.5-1.1 2.8-2.4 3.7l3.7 2.9c2.2-2 3.7-5 3.7-8.9z"/>
                    <path fill="#FBBC05" d="M5.6 14.8c-.2-.7-.4-1.5-.4-2.8s.2-2.1.4-2.8L1.9 6.3C.7 8.7 0 10.3 0 12s.7 3.3 1.9 5.7l3.7-2.9z"/>
                    <path fill="#34A853" d="M12 23c3.2 0 6-1.1 8-3l-3.7-2.9c-1.1.7-2.5 1.2-4.3 1.2-3 0-5.5-2-6.4-4.8L1.9 16.4C3.7 20.4 7.5 23 12 23z"/>
                </svg>
                <span style="color:#FFFFFF;">เข้าสู่ระบบด้วย Google</span>
            </button>
        </div>

        <!-- ลิงก์สมัครสมาชิกใหม่ -->
        <div class="auth-footer-links">
            ไม่ใช่สมาชิก? <a href="register.php">สมัครสมาชิก</a>
        </div>

        <!-- ลิงก์ลืมรหัสผ่าน / กลับหน้าหลัก -->
        <div class="auth-bottom-sub">
            <a href="javascript:void(0);" onclick="alert('หากลืมรหัสผ่าน กรุณาติดต่อผู้ดูแลระบบสูงสุด หรือใช้สคริปต์ setup_admin.php เพื่อตั้งค่าบัญชี');">ลืมรหัสผ่าน ?</a>
            <span style="color: #CCC; margin: 0 8px;">|</span>
            <a href="../index.html">กลับหน้าเว็บไซต์</a>
        </div>
    </form>
</div>

<script src="js/admin.js"></script>
</body>
</html>
