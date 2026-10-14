<?php
/**
 * SIAM STORIES - Admin Traditions Management (List, Filter, Search, Pagination, Delete)
 */

declare(strict_types=1);

$pageTitle = 'จัดการข้อมูลประเพณี';
require_once __DIR__ . '/layout_header.php';
require_once __DIR__ . '/../config/db.php';

$pdo = getDbConnection();

$alertSuccess = '';
$alertError = '';

// ตรวจสอบ Flash Message จาก session
if (!empty($_SESSION['flash_success'])) {
    $alertSuccess = $_SESSION['flash_success'];
    unset($_SESSION['flash_success']);
}
if (!empty($_SESSION['flash_error'])) {
    $alertError = $_SESSION['flash_error'];
    unset($_SESSION['flash_error']);
}

// จัดการการลบข้อมูลผ่าน POST พร้อม CSRF token
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete') {
    $token = $_POST['csrf_token'] ?? '';
    if (!validateCsrfToken($token)) {
        $alertError = 'ความปลอดภัยล้มเหลว (CSRF Token ไม่ถูกต้อง) กรุณาลองใหม่';
    } else {
        $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
        if ($id && $id > 0) {
            // ดึงข้อมูลก่อนลบเพื่อเก็บชื่อ
            $stmt = $pdo->prepare('SELECT name, image FROM traditions WHERE id = ?');
            $stmt->execute([$id]);
            $item = $stmt->fetch();

            if ($item) {
                // ลบประเพณี (ON DELETE CASCADE จะลบใน tradition_months ให้อัตโนมัติ)
                $delStmt = $pdo->prepare('DELETE FROM traditions WHERE id = ?');
                $delStmt->execute([$id]);

                // หากเป็นรูปใน uploads และไม่มีรายการอื่นใช้ สามารถลบหรือคงไว้เพื่อความปลอดภัย
                $alertSuccess = "ลบประเพณี '{$item['name']}' เรียบร้อยแล้ว";
            } else {
                $alertError = 'ไม่พบข้อมูลประเพณีที่ต้องการลบ';
            }
        } else {
            $alertError = 'รหัสประเพณีไม่ถูกต้อง';
        }
    }
}

// ตัวกรองและการค้นหา
$regionFilter = filter_input(INPUT_GET, 'region', FILTER_DEFAULT) ?? '';
$searchKeyword = trim(filter_input(INPUT_GET, 'search', FILTER_DEFAULT) ?? '');

$validRegions = ['north', 'isan', 'central', 'south'];
if (!in_array($regionFilter, $validRegions, true)) {
    $regionFilter = '';
}

// การแบ่งหน้า (Pagination)
$page = filter_input(INPUT_GET, 'page', FILTER_VALIDATE_INT) ?: 1;
if ($page < 1) $page = 1;
$perPage = 15;

$where = [];
$params = [];

if ($regionFilter !== '') {
    $where[] = 'region = ?';
    $params[] = $regionFilter;
}

if ($searchKeyword !== '') {
    $where[] = '(name LIKE ? OR province LIKE ? OR detail LIKE ?)';
    $kw = '%' . $searchKeyword . '%';
    $params[] = $kw;
    $params[] = $kw;
    $params[] = $kw;
}

$whereClause = !empty($where) ? ' WHERE ' . implode(' AND ', $where) : '';

// นับจำนวนทั้งหมดสำหรับ pagination
$countStmt = $pdo->prepare("SELECT COUNT(*) AS total FROM traditions {$whereClause}");
$countStmt->execute($params);
$totalRows = (int)($countStmt->fetch()['total'] ?? 0);
$totalPages = max(1, (int)ceil($totalRows / $perPage));
if ($page > $totalPages) $page = $totalPages;
$offset = ($page - 1) * $perPage;

// ดึงรายการ
$dataStmt = $pdo->prepare("SELECT id, name, region, province, image, sort_order, updated_at FROM traditions {$whereClause} ORDER BY sort_order ASC, id ASC LIMIT {$perPage} OFFSET {$offset}");
$dataStmt->execute($params);
$traditions = $dataStmt->fetchAll();

// ดึงเดือนของรายการเหล่านี้
if (!empty($traditions)) {
    $ids = array_column($traditions, 'id');
    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    $mStmt = $pdo->prepare("SELECT tradition_id, month FROM tradition_months WHERE tradition_id IN ({$placeholders}) ORDER BY month ASC");
    $mStmt->execute($ids);
    $monthsMap = [];
    foreach ($mStmt->fetchAll() as $row) {
        $monthsMap[(int)$row['tradition_id']][] = (int)$row['month'];
    }
    foreach ($traditions as &$t) {
        $t['months'] = $monthsMap[(int)$t['id']] ?? [];
    }
    unset($t);
}

$regionNames = [
    'north'   => 'ภาคเหนือ',
    'isan'    => 'ภาคอีสาน',
    'central' => 'ภาคกลาง',
    'south'   => 'ภาคใต้'
];

$monthNamesShort = [
    1 => 'ม.ค.', 2 => 'ก.พ.', 3 => 'มี.ค.', 4 => 'เม.ย.', 5 => 'พ.ค.', 6 => 'มิ.ย.',
    7 => 'ก.ค.', 8 => 'ส.ค.', 9 => 'ก.ย.', 10 => 'ต.ค.', 11 => 'พ.ย.', 12 => 'ธ.ค.'
];
?>

<?php if (!empty($alertSuccess)): ?>
    <div class="alert alert-success">
        ✓ <?php echo htmlspecialchars($alertSuccess, ENT_QUOTES, 'UTF-8'); ?>
    </div>
<?php endif; ?>

<?php if (!empty($alertError)): ?>
    <div class="alert alert-danger">
        ⚠️ <?php echo htmlspecialchars($alertError, ENT_QUOTES, 'UTF-8'); ?>
    </div>
<?php endif; ?>

<div class="content-box">
    <div class="box-header">
        <div class="box-title">
            <h3>รายการประเพณีทั้งหมด (<?php echo number_format($totalRows); ?> รายการ)</h3>
        </div>
        <div style="display: flex; gap: 12px; align-items: center;">
            <a href="tradition_form.php" class="btn btn-primary">+ เพิ่มประเพณีใหม่</a>
        </div>
    </div>

    <!-- Filter & Search Bar -->
    <form method="get" action="traditions.php" class="filter-bar" style="margin-bottom: 24px;">
        <div class="search-input-wrap">
            <span class="search-icon">🔍</span>
            <input 
                type="text" 
                name="search" 
                value="<?php echo htmlspecialchars($searchKeyword, ENT_QUOTES, 'UTF-8'); ?>" 
                placeholder="ค้นหาชื่อ, จังหวัด..." 
                class="input-control">
        </div>

        <select name="region" class="input-control" style="width: auto; min-width: 150px;" onchange="this.form.submit()">
            <option value="">ทุกภูมิภาค</option>
            <option value="north" <?php echo $regionFilter === 'north' ? 'selected' : ''; ?>>ภาคเหนือ</option>
            <option value="isan" <?php echo $regionFilter === 'isan' ? 'selected' : ''; ?>>ภาคอีสาน</option>
            <option value="central" <?php echo $regionFilter === 'central' ? 'selected' : ''; ?>>ภาคกลาง</option>
            <option value="south" <?php echo $regionFilter === 'south' ? 'selected' : ''; ?>>ภาคใต้</option>
        </select>

        <button type="submit" class="btn btn-secondary btn-sm">กรองข้อมูล</button>
        <?php if ($regionFilter !== '' || $searchKeyword !== ''): ?>
            <a href="traditions.php" class="link-subtle" style="margin-left: 8px;">ล้างตัวกรอง</a>
        <?php endif; ?>
    </form>

    <!-- Table -->
    <?php if (empty($traditions)): ?>
        <div class="empty-state">
            <div class="icon">🏮</div>
            <h4>ไม่พบข้อมูลประเพณี</h4>
            <p>ยังไม่มีรายการประเพณี หรือไม่พบรายการที่ตรงกับคำค้นหาของคุณ</p>
            <div style="margin-top: 16px;">
                <a href="tradition_form.php" class="btn btn-primary btn-sm">+ เพิ่มประเพณีรายการแรก</a>
            </div>
        </div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th style="width: 70px;">รูปภาพ</th>
                        <th>ชื่อประเพณี</th>
                        <th>ภูมิภาค</th>
                        <th>จังหวัด / พื้นที่</th>
                        <th>เดือนที่จัด</th>
                        <th style="width: 80px;">ลำดับ</th>
                        <th style="width: 140px; text-align: center;">จัดการ</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($traditions as $t): ?>
                        <tr>
                            <td>
                                <?php 
                                    $imgSrc = htmlspecialchars($t['image'], ENT_QUOTES, 'UTF-8');
                                    $imgPath = '../' . $t['image'];
                                ?>
                                <img 
                                    src="<?php echo $imgPath; ?>" 
                                    alt="<?php echo htmlspecialchars($t['name'], ENT_QUOTES, 'UTF-8'); ?>" 
                                    class="table-thumbnail" 
                                    loading="lazy"
                                    onerror="this.onerror=null; this.src='../Img/Minimalist Botanical Arch Pattern.png';">
                            </td>
                            <td>
                                <strong><?php echo htmlspecialchars($t['name'], ENT_QUOTES, 'UTF-8'); ?></strong>
                            </td>
                            <td>
                                <span class="badge badge-<?php echo htmlspecialchars($t['region'], ENT_QUOTES, 'UTF-8'); ?>">
                                    <?php echo htmlspecialchars($regionNames[$t['region']] ?? $t['region'], ENT_QUOTES, 'UTF-8'); ?>
                                </span>
                            </td>
                            <td><?php echo htmlspecialchars($t['province'], ENT_QUOTES, 'UTF-8'); ?></td>
                            <td>
                                <?php 
                                    if (!empty($t['months'])) {
                                        $mLabels = array_map(fn($m) => $monthNamesShort[$m] ?? (string)$m, $t['months']);
                                        echo htmlspecialchars(implode(', ', $mLabels), ENT_QUOTES, 'UTF-8');
                                    } else {
                                        echo '<span style="color:var(--text-muted);">-</span>';
                                    }
                                ?>
                            </td>
                            <td><?php echo (int)$t['sort_order']; ?></td>
                            <td style="text-align: center;">
                                <div class="action-buttons" style="justify-content: center;">
                                    <a href="tradition_form.php?id=<?php echo (int)$t['id']; ?>" class="btn btn-secondary btn-sm" title="แก้ไข">แก้ไข</a>
                                    <button 
                                        type="button" 
                                        class="btn btn-danger btn-sm btn-delete-trigger" 
                                        data-id="<?php echo (int)$t['id']; ?>" 
                                        data-name="<?php echo htmlspecialchars($t['name'], ENT_QUOTES, 'UTF-8'); ?>"
                                        title="ลบ">
                                        ลบ
                                    </button>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <?php if ($totalPages > 1): ?>
            <div class="pagination">
                <?php if ($page > 1): ?>
                    <a href="?page=<?php echo $page - 1; ?>&region=<?php echo urlencode($regionFilter); ?>&search=<?php echo urlencode($searchKeyword); ?>" class="page-link">‹ ก่อนหน้า</a>
                <?php endif; ?>

                <?php 
                    $startPage = max(1, $page - 2);
                    $endPage = min($totalPages, $page + 2);
                    for ($p = $startPage; $p <= $endPage; $p++): 
                ?>
                    <a href="?page=<?php echo $p; ?>&region=<?php echo urlencode($regionFilter); ?>&search=<?php echo urlencode($searchKeyword); ?>" class="page-link <?php echo $p === $page ? 'active' : ''; ?>">
                        <?php echo $p; ?>
                    </a>
                <?php endfor; ?>

                <?php if ($page < $totalPages): ?>
                    <a href="?page=<?php echo $page + 1; ?>&region=<?php echo urlencode($regionFilter); ?>&search=<?php echo urlencode($searchKeyword); ?>" class="page-link">ถัดไป ›</a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/layout_footer.php'; ?>

