<?php
/**
 * SIAM STORIES - Admin Dashboard
 */

declare(strict_types=1);

$pageTitle = 'ภาพรวมระบบ (Dashboard)';
require_once __DIR__ . '/layout_header.php';
require_once __DIR__ . '/../config/db.php';

$pdo = getDbConnection();

// นับจำนวนประเพณีทั้งหมด และแยกตามภาค
$stmt = $pdo->query('SELECT COUNT(*) AS total FROM traditions');
$totalTraditions = (int)($stmt->fetch()['total'] ?? 0);

$stmt = $pdo->query('SELECT region, COUNT(*) AS count FROM traditions GROUP BY region');
$traditionsByRegion = ['north' => 0, 'isan' => 0, 'central' => 0, 'south' => 0];
foreach ($stmt->fetchAll() as $row) {
    $traditionsByRegion[$row['region']] = (int)$row['count'];
}

// นับจำนวนอาหารทั้งหมด และแยกตามภาค
$stmt = $pdo->query('SELECT COUNT(*) AS total FROM foods');
$totalFoods = (int)($stmt->fetch()['total'] ?? 0);

$stmt = $pdo->query('SELECT region, COUNT(*) AS count FROM foods GROUP BY region');
$foodsByRegion = ['north' => 0, 'isan' => 0, 'central' => 0, 'south' => 0];
foreach ($stmt->fetchAll() as $row) {
    $foodsByRegion[$row['region']] = (int)$row['count'];
}

// รายการประเพณีล่าสุด 5 รายการ
$stmt = $pdo->query('SELECT id, name, region, province, updated_at FROM traditions ORDER BY updated_at DESC, id DESC LIMIT 5');
$recentTraditions = $stmt->fetchAll();

// รายการอาหารล่าสุด 5 รายการ
$stmt = $pdo->query('SELECT id, name, region, updated_at FROM foods ORDER BY updated_at DESC, id DESC LIMIT 5');
$recentFoods = $stmt->fetchAll();

// นับจำนวนผู้ใช้ทั้งหมด และประวัติการค้นหา/กิจกรรม
$stmt = $pdo->query('SELECT COUNT(*) AS total FROM admins');
$totalUsers = (int)($stmt->fetch()['total'] ?? 0);

$stmt = $pdo->query('SELECT COUNT(*) AS total FROM user_history');
$totalHistory = (int)($stmt->fetch()['total'] ?? 0);

// ดึงกิจกรรมและการค้นหาของผู้ใช้ล่าสุด 8 รายการ
$stmt = $pdo->query('
    SELECT h.id, h.action_type, h.query_text, h.target_title, h.target_url, h.created_at,
           a.username, a.display_name, a.role
    FROM user_history h
    JOIN admins a ON h.user_id = a.id
    ORDER BY h.id DESC
    LIMIT 8
');
$recentUserActivities = $stmt->fetchAll();

$regionNames = [
    'north'   => 'ภาคเหนือ',
    'isan'    => 'ภาคอีสาน',
    'central' => 'ภาคกลาง',
    'south'   => 'ภาคใต้'
];
?>

<!-- ภาพรวมสถิติ -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon">🏮</div>
        <div class="stat-data">
            <h3><?php echo number_format($totalTraditions); ?></h3>
            <p>ประเพณีทั้งหมด</p>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon">🍲</div>
        <div class="stat-data">
            <h3><?php echo number_format($totalFoods); ?></h3>
            <p>อาหารท้องถิ่นทั้งหมด</p>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon north">⛰️</div>
        <div class="stat-data">
            <h3><?php echo number_format($traditionsByRegion['north']); ?> <small style="font-size:0.5em; font-weight:normal; color:var(--text-muted);">(อาหาร <?php echo $foodsByRegion['north']; ?>)</small></h3>
            <p>ภาคเหนือ</p>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon isan">🌾</div>
        <div class="stat-data">
            <h3><?php echo number_format($traditionsByRegion['isan']); ?> <small style="font-size:0.5em; font-weight:normal; color:var(--text-muted);">(อาหาร <?php echo $foodsByRegion['isan']; ?>)</small></h3>
            <p>ภาคอีสาน</p>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon central">🏛️</div>
        <div class="stat-data">
            <h3><?php echo number_format($traditionsByRegion['central']); ?> <small style="font-size:0.5em; font-weight:normal; color:var(--text-muted);">(อาหาร <?php echo $foodsByRegion['central']; ?>)</small></h3>
            <p>ภาคกลาง</p>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon south">🌊</div>
        <div class="stat-data">
            <h3><?php echo number_format($traditionsByRegion['south']); ?> <small style="font-size:0.5em; font-weight:normal; color:var(--text-muted);">(อาหาร <?php echo $foodsByRegion['south']; ?>)</small></h3>
            <p>ภาคใต้</p>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon" style="background:#eef2ff; color:#4338ca;">👥</div>
        <div class="stat-data">
            <h3><?php echo number_format($totalUsers); ?></h3>
            <p>ผู้ใช้ในระบบทั้งหมด</p>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon" style="background:#fef3c7; color:#b45309;">⚡</div>
        <div class="stat-data">
            <h3><?php echo number_format($totalHistory); ?></h3>
            <p>การค้นหา & กิจกรรมสะสม</p>
        </div>
    </div>
</div>

<!-- ทางลัดการจัดการข้อมูล (Quick Actions) -->
<div class="content-box">
    <div class="box-header">
        <div class="box-title">
            <h3>เมนูด่วนสำหรับจัดการข้อมูล</h3>
        </div>
        <div style="display: flex; gap: 10px; flex-wrap: wrap;">
            <a href="tradition_form.php" class="btn btn-primary btn-sm">+ เพิ่มประเพณีใหม่</a>
            <a href="food_form.php" class="btn btn-primary btn-sm">+ เพิ่มอาหารใหม่</a>
        </div>
    </div>
    <p style="font-size: 0.95rem; color: var(--text-muted);">
        คุณสามารถเพิ่ม แก้ไข หรือลบข้อมูลประเพณีและอาหารท้องถิ่นได้ตลอดเวลา โดยข้อมูลที่แก้ไขในระบบนี้จะมีผลไปยังหน้าเว็บไซต์เดิมโดยทันทีเมื่อผู้ใช้เปิดหรือรีเฟรชหน้าเว็บ
    </p>
</div>

<!-- ตารางประเพณีและอาหารล่าสุด 2 คอลัมน์ -->
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(360px, 1fr)); gap: 24px;">
    <!-- ประเพณีอัปเดตล่าสุด -->
    <div class="content-box">
        <div class="box-header">
            <div class="box-title">
                <h3>ประเพณีอัปเดตล่าสุด</h3>
            </div>
            <a href="traditions.php" class="link-subtle">ดูทั้งหมด →</a>
        </div>
        <div class="table-responsive">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>ชื่อประเพณี</th>
                        <th>ภูมิภาค</th>
                        <th>จังหวัด</th>
                        <th>จัดการ</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($recentTraditions)): ?>
                        <tr><td colspan="4" style="text-align:center; color:var(--text-muted);">ยังไม่มีข้อมูลประเพณี</td></tr>
                    <?php else: ?>
                        <?php foreach ($recentTraditions as $rt): ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($rt['name'], ENT_QUOTES, 'UTF-8'); ?></strong></td>
                                <td>
                                    <span class="badge badge-<?php echo htmlspecialchars($rt['region'], ENT_QUOTES, 'UTF-8'); ?>">
                                        <?php echo htmlspecialchars($regionNames[$rt['region']] ?? $rt['region'], ENT_QUOTES, 'UTF-8'); ?>
                                    </span>
                                </td>
                                <td><?php echo htmlspecialchars($rt['province'], ENT_QUOTES, 'UTF-8'); ?></td>
                                <td>
                                    <a href="tradition_form.php?id=<?php echo (int)$rt['id']; ?>" class="btn btn-secondary btn-sm">แก้ไข</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- อาหารอัปเดตล่าสุด -->
    <div class="content-box">
        <div class="box-header">
            <div class="box-title">
                <h3>อาหารท้องถิ่นอัปเดตล่าสุด</h3>
            </div>
            <a href="foods.php" class="link-subtle">ดูทั้งหมด →</a>
        </div>
        <div class="table-responsive">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>ชื่ออาหาร</th>
                        <th>ภูมิภาค</th>
                        <th>จัดการ</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($recentFoods)): ?>
                        <tr><td colspan="3" style="text-align:center; color:var(--text-muted);">ยังไม่มีข้อมูลอาหาร</td></tr>
                    <?php else: ?>
                        <?php foreach ($recentFoods as $rf): ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($rf['name'], ENT_QUOTES, 'UTF-8'); ?></strong></td>
                                <td>
                                    <span class="badge badge-<?php echo htmlspecialchars($rf['region'], ENT_QUOTES, 'UTF-8'); ?>">
                                        <?php echo htmlspecialchars($regionNames[$rf['region']] ?? $rf['region'], ENT_QUOTES, 'UTF-8'); ?>
                                    </span>
                                </td>
                                <td>
                                    <a href="food_form.php?id=<?php echo (int)$rf['id']; ?>" class="btn btn-secondary btn-sm">แก้ไข</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- กิจกรรมและการค้นหาล่าสุดของผู้ใช้ในระบบ -->
<div class="content-box" style="margin-top: 24px;">
    <div class="box-header">
        <div class="box-title">
            <h3>⚡ ประวัติการค้นหาและกิจกรรมของผู้ใช้ล่าสุด (User Activity Stream)</h3>
            <p style="font-size: 0.85rem; color: var(--text-muted); margin: 3px 0 0 0;">
                บันทึกคำค้นหา การเปิดดูประเพณี/อาหาร และการเข้าชมหน้าเว็บของผู้ใช้แต่ละคนโดยอัตโนมัติ
            </p>
        </div>
    </div>
    <div class="table-responsive">
        <table class="admin-table">
            <thead>
                <tr>
                    <th style="width: 70px;">ประเภท</th>
                    <th>ผู้ใช้งาน</th>
                    <th>สิ่งที่ค้นหา / รายการที่ดู</th>
                    <th>หน้าที่เกี่ยวข้อง</th>
                    <th style="width: 170px;">เวลา</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($recentUserActivities)): ?>
                    <tr>
                        <td colspan="5" style="text-align: center; color: var(--text-muted); padding: 25px;">
                            ยังไม่มีข้อมูลประวัติกิจกรรมของผู้ใช้
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($recentUserActivities as $act): ?>
                        <tr>
                            <td>
                                <?php if ($act['action_type'] === 'search'): ?>
                                    <span class="badge" style="background:#fef3c7; color:#b45309;">🔍 ค้นหา</span>
                                <?php elseif ($act['action_type'] === 'view'): ?>
                                    <span class="badge" style="background:#dbeafe; color:#1e40af;">🏮 ดูเนื้อหา</span>
                                <?php elseif ($act['action_type'] === 'page_visit'): ?>
                                    <span class="badge" style="background:#e0e7ff; color:#4338ca;">🧭 เข้าชม</span>
                                <?php else: ?>
                                    <span class="badge" style="background:#f3f4f6; color:#4b5563;">⚙️ กรอง</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <strong><?php echo htmlspecialchars($act['display_name'] ?: $act['username'], ENT_QUOTES, 'UTF-8'); ?></strong>
                                <br>
                                <small style="color:var(--text-muted);">
                                    @<?php echo htmlspecialchars($act['username'], ENT_QUOTES, 'UTF-8'); ?> 
                                    (<?php echo $act['role'] === 'admin' ? '🛡️ ผู้ดูแล' : '👤 สมาชิก'; ?>)
                                </small>
                            </td>
                            <td>
                                <strong style="color: var(--text-dark);">
                                    <?php echo htmlspecialchars($act['query_text'] ?: $act['target_title'] ?: '-', ENT_QUOTES, 'UTF-8'); ?>
                                </strong>
                                <?php if (!empty($act['target_title']) && $act['target_title'] !== $act['query_text']): ?>
                                    <br><small style="color: var(--text-muted);"><?php echo htmlspecialchars($act['target_title'], ENT_QUOTES, 'UTF-8'); ?></small>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if (!empty($act['target_url'])): ?>
                                    <a href="../<?php echo htmlspecialchars(ltrim($act['target_url'], '/'), ENT_QUOTES, 'UTF-8'); ?>" target="_blank" style="color: #c5a059; text-decoration: underline; font-size: 0.88rem;">
                                        <?php echo htmlspecialchars($act['target_url'], ENT_QUOTES, 'UTF-8'); ?> ↗
                                    </a>
                                <?php else: ?>
                                    <span style="color: var(--text-muted);">-</span>
                                <?php endif; ?>
                            </td>
                            <td style="font-size: 0.85rem; color: var(--text-muted);">
                                <?php echo htmlspecialchars(date('d/m/Y H:i', strtotime($act['created_at'])), ENT_QUOTES, 'UTF-8'); ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/layout_footer.php'; ?>

