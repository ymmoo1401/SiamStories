<?php
/**
 * SIAM STORIES - Admin Header & Sidebar Layout Template
 */

declare(strict_types=1);

require_once __DIR__ . '/auth_check.php';
require_once __DIR__ . '/csrf.php';

requireAdminLogin();
$adminUser = getLoggedInAdmin();

$currentScript = basename($_SERVER['PHP_SELF']);
$pageTitle = $pageTitle ?? 'ระบบผู้ดูแล';
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8'); ?> | SIAM STORIES</title>
    <link rel="stylesheet" href="css/admin.css">
</head>
<body>
<div class="admin-layout">
    <!-- Sidebar -->
    <aside class="admin-sidebar">
        <a href="index.php" class="sidebar-brand">
            <span class="mark">❈</span>
            <div class="sidebar-brand-text">
                <span class="sidebar-brand-title">SIAM STORIES</span>
                <span class="sidebar-brand-sub">ระบบผู้ดูแลเว็บไซต์</span>
            </div>
        </a>

        <nav class="sidebar-nav">
            <a href="index.php" class="nav-item <?php echo $currentScript === 'index.php' ? 'active' : ''; ?>">
                <span class="icon">📊</span>
                <span>ภาพรวม</span>
            </a>
            <a href="traditions.php" class="nav-item <?php echo in_array($currentScript, ['traditions.php', 'tradition_form.php'], true) ? 'active' : ''; ?>">
                <span class="icon">🏮</span>
                <span>จัดการประเพณี</span>
            </a>
            <a href="foods.php" class="nav-item <?php echo in_array($currentScript, ['foods.php', 'food_form.php'], true) ? 'active' : ''; ?>">
                <span class="icon">🍲</span>
                <span>จัดการอาหาร</span>
            </a>
            <a href="../index.html" class="nav-item" target="_blank" rel="noopener">
                <span class="icon">🌐</span>
                <span>ดูหน้าเว็บไซต์ ↗</span>
            </a>
            <a href="logout.php" class="nav-item">
                <span class="icon">🚪</span>
                <span>ออกจากระบบ</span>
            </a>
        </nav>

        <div class="sidebar-footer">
            <div class="admin-user-info">
                <div class="user-avatar">
                    <?php echo mb_substr($adminUser['display_name'], 0, 1, 'UTF-8'); ?>
                </div>
                <div>
                    <div class="user-name"><?php echo htmlspecialchars($adminUser['display_name'], ENT_QUOTES, 'UTF-8'); ?></div>
                    <div class="user-role">@<?php echo htmlspecialchars($adminUser['username'], ENT_QUOTES, 'UTF-8'); ?></div>
                </div>
            </div>
        </div>
    </aside>

    <!-- Main Content Area -->
    <main class="admin-main">
        <header class="admin-header">
            <div class="header-title" style="display: flex; align-items: center; gap: 12px;">
                <button type="button" class="mobile-menu-toggle" aria-label="เปิดเมนู">☰</button>
                <h2><?php echo htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8'); ?></h2>
            </div>
            <div class="header-actions">
                <a href="../index.html" class="btn btn-secondary btn-sm" target="_blank" rel="noopener">🌐 ดูหน้าบ้าน</a>
                <a href="logout.php" class="btn btn-secondary btn-sm">ออกจากระบบ</a>
            </div>
        </header>
        <div class="admin-content">

