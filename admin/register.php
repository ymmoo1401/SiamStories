<?php
/**
 * SIAM STORIES - User Registration Page (สมัครสมาชิกใหม่)
 * สร้างบัญชีผู้ใช้งานใหม่ พร้อมตรวจสอบชื่อผู้ใช้/อีเมลซ้ำและเข้ารหัสผ่าน
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

$errors = [];
$usernameVal = '';
$emailVal = '';
$displayNameVal = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['csrf_token'] ?? '';
    if (!validateCsrfToken($token)) {
        $errors['general'] = 'แบบฟอร์มหมดอายุ กรุณาลองใหม่อีกครั้ง';
    } else {
        $username = trim((string)($_POST['username'] ?? ''));
        $email = trim((string)($_POST['email'] ?? ''));
        $displayName = trim((string)($_POST['display_name'] ?? ''));
        $password = (string)($_POST['password'] ?? '');
        $confirmPassword = (string)($_POST['confirm_password'] ?? '');

        $usernameVal = $username;
        $emailVal = $email;
        $displayNameVal = $displayName;

        // Validation
        if ($username === '' || !preg_match('/^[a-zA-Z0-9_]{3,30}$/', $username)) {
            $errors['username'] = 'ชื่อผู้ใช้ต้องเป็นตัวอักษรภาษาอังกฤษ ตัวเลข หรือ _ ความยาว 3-30 ตัวอักษร';
        }

        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'กรุณาระบุรูปแบบอีเมลที่ถูกต้อง เช่น user@example.com';
        }

        if ($displayName === '' || mb_strlen($displayName) > 100) {
            $errors['display_name'] = 'กรุณาระบุชื่อที่ใช้แสดงผล (ไม่เกิน 100 ตัวอักษร)';
        }

        if (mb_strlen($password) < 6) {
            $errors['password'] = 'รหัสผ่านต้องมีความยาวอย่างน้อย 6 ตัวอักษร';
        } elseif ($password !== $confirmPassword) {
            $errors['confirm_password'] = 'รหัสผ่านและการยืนยันรหัสผ่านไม่ตรงกัน';
        }

        // ตรวจสอบว่าชื่อผู้ใช้หรืออีเมลซ้ำหรือไม่
        if (empty($errors)) {
            $pdo = getDbConnection();

            // เช็ค username
            $stmt = $pdo->prepare('SELECT COUNT(*) AS total FROM admins WHERE username = ?');
            $stmt->execute([$username]);
            if ((int)$stmt->fetch()['total'] > 0) {
                $errors['username'] = 'ชื่อผู้ใช้นี้มีในระบบแล้ว กรุณาใช้ชื่ออื่น';
            }

            // เช็ค email
            if (empty($errors['username'])) {
                $stmt = $pdo->prepare('SELECT COUNT(*) AS total FROM admins WHERE email = ?');
                $stmt->execute([$email]);
                if ((int)$stmt->fetch()['total'] > 0) {
                    $errors['email'] = 'อีเมลนี้ถูกใช้งานแล้ว กรุณาใช้อีเมลอื่น';
                }
            }

            // บันทึกสมาชิกใหม่
            if (empty($errors)) {
                try {
                    $hash = password_hash($password, PASSWORD_DEFAULT);
                    $insertStmt = $pdo->prepare("INSERT INTO admins (username, email, password_hash, display_name, role) VALUES (?, ?, ?, ?, 'member')");
                    $insertStmt->execute([$username, $email, $hash, $displayName]);
                    $newUserId = (int)$pdo->lastInsertId();

                    // เข้าสู่ระบบทันทีและค้างเซสชันไว้
                    session_regenerate_id(true);
                    $_SESSION['admin_id'] = $newUserId;
                    $_SESSION['admin_username'] = $username;
                    $_SESSION['admin_display_name'] = $displayName;
                    $_SESSION['admin_role'] = 'member';

                    // บันทึกประวัติกิจกรรมเริ่มต้น
                    try {
                        $histStmt = $pdo->prepare("INSERT INTO user_history (user_id, action_type, query_text, target_title, target_url) VALUES (?, 'page_visit', 'ยินดีต้อนรับสมาชิกใหม่', 'เริ่มต้นใช้งานเว็บไซต์', 'index.html')");
                        $histStmt->execute([$newUserId]);
                    } catch (Throwable $ignore) {}

                    header('Location: ../index.html');
                    exit;

                } catch (Throwable $e) {
                    error_log('Register error: ' . $e->getMessage());
                    $errors['general'] = 'เกิดข้อผิดพลาดในการสมัครสมาชิก กรุณาลองใหม่อีกครั้ง';
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
    <title>สมัครสมาชิกใหม่ | SIAM STORIES</title>
    <link rel="stylesheet" href="css/admin.css">
</head>
<body class="auth-page">

<div class="auth-dialog-card" style="max-width: 480px;">
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

    <h1 class="auth-title-center">สมัครสมาชิกใหม่</h1>

    <?php if (!empty($errors['general'])): ?>
        <div class="alert alert-danger" role="alert" style="margin-bottom: 18px;">
            <?php echo htmlspecialchars($errors['general'], ENT_QUOTES, 'UTF-8'); ?>
        </div>
    <?php endif; ?>

    <form method="post" action="register.php" novalidate>
        <?php echo csrfField(); ?>

        <!-- ช่องชื่อผู้ใช้ -->
        <div class="input-icon-wrap <?php echo isset($errors['username']) ? 'has-error' : ''; ?>">
            <span class="prefix-icon">👤</span>
            <input 
                type="text" 
                id="username" 
                name="username" 
                value="<?php echo htmlspecialchars($usernameVal, ENT_QUOTES, 'UTF-8'); ?>" 
                required 
                autocomplete="username"
                placeholder="ชื่อผู้ใช้ (ภาษาอังกฤษ เช่น somchai99)"
                aria-label="ชื่อผู้ใช้">
        </div>
        <?php if (isset($errors['username'])): ?>
            <span class="field-error" style="margin-top:-8px; margin-bottom:12px;"><?php echo htmlspecialchars($errors['username'], ENT_QUOTES, 'UTF-8'); ?></span>
        <?php endif; ?>

        <!-- ช่องอีเมล -->
        <div class="input-icon-wrap <?php echo isset($errors['email']) ? 'has-error' : ''; ?>">
            <span class="prefix-icon">@</span>
            <input 
                type="email" 
                id="email" 
                name="email" 
                value="<?php echo htmlspecialchars($emailVal, ENT_QUOTES, 'UTF-8'); ?>" 
                required 
                autocomplete="email"
                placeholder="อีเมล (เช่น user@example.com)"
                aria-label="อีเมล">
        </div>
        <?php if (isset($errors['email'])): ?>
            <span class="field-error" style="margin-top:-8px; margin-bottom:12px;"><?php echo htmlspecialchars($errors['email'], ENT_QUOTES, 'UTF-8'); ?></span>
        <?php endif; ?>

        <!-- ช่องชื่อแสดงผล -->
        <div class="input-icon-wrap <?php echo isset($errors['display_name']) ? 'has-error' : ''; ?>">
            <span class="prefix-icon">✦</span>
            <input 
                type="text" 
                id="display_name" 
                name="display_name" 
                value="<?php echo htmlspecialchars($displayNameVal, ENT_QUOTES, 'UTF-8'); ?>" 
                required 
                placeholder="ชื่อที่ใช้แสดงผล (เช่น สมชาย วัฒนธรรม)"
                aria-label="ชื่อที่ใช้แสดงผล">
        </div>
        <?php if (isset($errors['display_name'])): ?>
            <span class="field-error" style="margin-top:-8px; margin-bottom:12px;"><?php echo htmlspecialchars($errors['display_name'], ENT_QUOTES, 'UTF-8'); ?></span>
        <?php endif; ?>

        <!-- ช่องรหัสผ่าน -->
        <div class="input-icon-wrap <?php echo isset($errors['password']) ? 'has-error' : ''; ?>">
            <span class="prefix-icon">🔒</span>
            <input 
                type="password" 
                id="password" 
                name="password" 
                required 
                autocomplete="new-password"
                placeholder="รหัสผ่าน (อย่างน้อย 6 ตัวอักษร)"
                aria-label="รหัสผ่าน">
            <button 
                type="button" 
                class="btn-toggle-pw" 
                aria-label="แสดงรหัสผ่าน" 
                data-target="password">👁</button>
        </div>
        <?php if (isset($errors['password'])): ?>
            <span class="field-error" style="margin-top:-8px; margin-bottom:12px;"><?php echo htmlspecialchars($errors['password'], ENT_QUOTES, 'UTF-8'); ?></span>
        <?php endif; ?>

        <!-- ช่องยืนยันรหัสผ่าน -->
        <div class="input-icon-wrap <?php echo isset($errors['confirm_password']) ? 'has-error' : ''; ?>">
            <span class="prefix-icon">🔑</span>
            <input 
                type="password" 
                id="confirm_password" 
                name="confirm_password" 
                required 
                autocomplete="new-password"
                placeholder="ยืนยันรหัสผ่านอีกครั้ง"
                aria-label="ยืนยันรหัสผ่าน">
            <button 
                type="button" 
                class="btn-toggle-pw" 
                aria-label="แสดงรหัสผ่าน" 
                data-target="confirm_password">👁</button>
        </div>
        <?php if (isset($errors['confirm_password'])): ?>
            <span class="field-error" style="margin-top:-8px; margin-bottom:12px;"><?php echo htmlspecialchars($errors['confirm_password'], ENT_QUOTES, 'UTF-8'); ?></span>
        <?php endif; ?>

        <!-- ปุ่มสมัครสมาชิก -->
        <div style="margin-top: 14px;">
            <button type="submit" class="btn-auth-submit">
                สมัครสมาชิก
            </button>
        </div>

        <!-- ลิงก์กลับไปหน้าเข้าสู่ระบบ -->
        <div class="auth-footer-links" style="margin-top: 18px;">
            เป็นสมาชิกอยู่แล้ว? <a href="login.php">เข้าสู่ระบบ</a>
        </div>

        <div class="auth-bottom-sub">
            <a href="../index.html">← กลับหน้าเว็บไซต์</a>
        </div>
    </form>
</div>

<script src="js/admin.js"></script>
</body>
</html>

