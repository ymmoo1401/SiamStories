<?php
/**
 * SIAM STORIES - Admin Foods Management (List, Filter, Search, Pagination, Delete)
 */

declare(strict_types=1);

$pageTitle = 'จัดการข้อมูลอาหารท้องถิ่น';
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
            $stmt = $pdo->prepare('SELECT name, image FROM foods WHERE id = ?');
            $stmt->execute([$id]);
            $item = $stmt->fetch();

            if ($item) {
                $delStmt = $pdo->prepare('DELETE FROM foods WHERE id = ?');
                $delStmt->execute([$id]);
                $alertSuccess = "ลบอาหาร '{$item['name']}' เรียบร้อยแล้ว";
            } else {
                $alertError = 'ไม่พบข้อมูลอาหารที่ต้องการลบ';
            }
        } else {
            $alertError = 'รหัสอาหารไม่ถูกต้อง';
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

// การแบ่งหน้า
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
    $where[] = '(name LIKE ? OR detail LIKE ? OR tags LIKE ?)';
    $kw = '%' . $searchKeyword . '%';
    $params[] = $kw;
    $params[] = $kw;
    $params[] = $kw;
}

$whereClause = !empty($where) ? ' WHERE ' . implode(' AND ', $where) : '';

$countStmt = $pdo->prepare("SELECT COUNT(*) AS total FROM foods {$whereClause}");
$countStmt->execute($params);
$totalRows = (int)($countStmt->fetch()['total'] ?? 0);
$totalPages = max(1, (int)ceil($totalRows / $perPage));
if ($page > $totalPages) $page = $totalPages;
$offset = ($page - 1) * $perPage;

$dataStmt = $pdo->prepare("SELECT id, name, region, detail, image, tags, sort_order, updated_at FROM foods {$whereClause} ORDER BY sort_order ASC, id ASC LIMIT {$perPage} OFFSET {$offset}");
$dataStmt->execute($params);
$foods = $dataStmt->fetchAll();

$regionNames = [
    'north'   => 'ภาคเหนือ',
    'isan'    => 'ภาคอีสาน',
    'central' => 'ภาคกลาง',
    'south'   => 'ภาคใต้'
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
            <h3>รายการอาหารท้องถิ่นทั้งหมด (<?php echo number_format($totalRows); ?> รายการ)</h3>
        </div>
        <div style="display: flex; gap: 12px; align-items: center;">
            <a href="food_form.php" class="btn btn-primary">+ เพิ่มอาหารใหม่</a>
        </div>
    </div>

    <!-- Filter & Search Bar -->
    <form method="get" action="foods.php" class="filter-bar" style="margin-bottom: 24px;">
        <div class="search-input-wrap">
            <span class="search-icon">🔍</span>
            <input 
                type="text" 
                name="search" 
                value="<?php echo htmlspecialchars($searchKeyword, ENT_QUOTES, 'UTF-8'); ?>" 
                placeholder="ค้นหาชื่ออาหาร, แท็ก..." 
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
            <a href="foods.php" class="link-subtle" style="margin-left: 8px;">ล้างตัวกรอง</a>
        <?php endif; ?>
    </form>

    <!-- Table -->
    <?php if (empty($foods)): ?>
        <div class="empty-state">
            <div class="icon">🍲</div>
            <h4>ไม่พบข้อมูลอาหาร</h4>
            <p>ยังไม่มีรายการอาหาร หรือไม่พบรายการที่ตรงกับคำค้นหาของคุณ</p>
            <div style="margin-top: 16px;">
                <a href="food_form.php" class="btn btn-primary btn-sm">+ เพิ่มอาหารรายการแรก</a>
            </div>
        </div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th style="width: 70px;">รูปภาพ</th>
                        <th>ชื่ออาหาร</th>
                        <th>ภูมิภาค</th>
                        <th>คำบรรยายย่อ / แท็ก</th>
                        <th style="width: 80px;">ลำดับ</th>
                        <th style="width: 140px; text-align: center;">จัดการ</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($foods as $f): ?>
                        <tr>
                            <td>
                                <?php 
                                    $imgSrc = htmlspecialchars($f['image'], ENT_QUOTES, 'UTF-8');
                                    $imgPath = '../' . $f['image'];
                                ?>
                                <img 
                                    src="<?php echo $imgPath; ?>" 
                                    alt="<?php echo htmlspecialchars($f['name'], ENT_QUOTES, 'UTF-8'); ?>" 
                                    class="table-thumbnail" 
                                    loading="lazy"
                                    onerror="this.onerror=null; this.src='../Img/Minimalist Botanical Arch Pattern.png';">
                            </td>
                            <td>
                                <strong><?php echo htmlspecialchars($f['name'], ENT_QUOTES, 'UTF-8'); ?></strong>
                            </td>
                            <td>
                                <span class="badge badge-<?php echo htmlspecialchars($f['region'], ENT_QUOTES, 'UTF-8'); ?>">
                                    <?php echo htmlspecialchars($regionNames[$f['region']] ?? $f['region'], ENT_QUOTES, 'UTF-8'); ?>
                                </span>
                            </td>
                            <td>
                                <?php 
                                    $tagList = array_filter(array_map('trim', explode(',', $f['tags'] ?? '')));
                                    foreach ($tagList as $tag):
                                ?>
                                    <span style="display:inline-block; background:#FAF5ED; border:1px solid var(--border-color); font-size:0.75rem; padding:2px 8px; border-radius:12px; margin-right:4px;">
                                        <?php echo htmlspecialchars($tag, ENT_QUOTES, 'UTF-8'); ?>
                                    </span>
                                <?php endforeach; ?>
                            </td>
                            <td><?php echo (int)$f['sort_order']; ?></td>
                            <td style="text-align: center;">
                                <div class="action-buttons" style="justify-content: center;">
                                    <a href="food_form.php?id=<?php echo (int)$f['id']; ?>" class="btn btn-secondary btn-sm" title="แก้ไข">แก้ไข</a>
                                    <button 
                                        type="button" 
                                        class="btn btn-danger btn-sm btn-delete-trigger" 
                                        data-id="<?php echo (int)$f['id']; ?>" 
                                        data-name="<?php echo htmlspecialchars($f['name'], ENT_QUOTES, 'UTF-8'); ?>"
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

