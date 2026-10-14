<?php
/**
 * SIAM STORIES - API สำหรับข้อมูลประเพณี (Traditions API)
 * รองรับการดึงข้อมูลทั้งหมด หรือกรองตาม region, month, search, id
 */

declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');

require_once __DIR__ . '/../config/db.php';

try {
    $pdo = getDbConnection();

    // กรณีขอรายการเดี่ยวด้วย id
    if (isset($_GET['id'])) {
        $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
        if ($id === false || $id === null || $id <= 0) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => 'รหัสประเพณีไม่ถูกต้อง'
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }

        $stmt = $pdo->prepare('SELECT * FROM traditions WHERE id = ?');
        $stmt->execute([$id]);
        $tradition = $stmt->fetch();

        if (!$tradition) {
            http_response_code(404);
            echo json_encode([
                'success' => false,
                'message' => 'ไม่พบข้อมูลประเพณีที่ระบุ'
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }

        // ดึงเดือน
        $mStmt = $pdo->prepare('SELECT month FROM tradition_months WHERE tradition_id = ? ORDER BY month ASC');
        $mStmt->execute([$id]);
        $months = array_map(fn($row) => (int)$row['month'], $mStmt->fetchAll());
        $tradition['months'] = $months;

        echo json_encode([
            'success' => true,
            'data' => $tradition
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    // กรองตาม parameters
    $region = filter_input(INPUT_GET, 'region', FILTER_DEFAULT);
    $month = filter_input(INPUT_GET, 'month', FILTER_VALIDATE_INT);
    $search = filter_input(INPUT_GET, 'search', FILTER_DEFAULT);

    $validRegions = ['north', 'isan', 'central', 'south'];
    if ($region !== null && $region !== '' && !in_array($region, $validRegions, true)) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'ภูมิภาคไม่ถูกต้อง'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    if ($month !== null && $month !== false && ($month < 1 || $month > 12)) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'เดือนต้องอยู่ระหว่าง 1 ถึง 12'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $sql = 'SELECT DISTINCT t.id, t.name, t.region, t.province, t.detail, t.image, t.sort_order, t.created_at, t.updated_at
            FROM traditions t';
    $params = [];
    $where = [];

    if ($month !== null && $month !== false && $month > 0) {
        $sql .= ' INNER JOIN tradition_months tm ON t.id = tm.tradition_id';
        $where[] = 'tm.month = ?';
        $params[] = $month;
    }

    if ($region !== null && $region !== '') {
        $where[] = 't.region = ?';
        $params[] = $region;
    }

    if ($search !== null && trim($search) !== '') {
        $where[] = '(t.name LIKE ? OR t.province LIKE ? OR t.detail LIKE ?)';
        $searchParam = '%' . trim($search) . '%';
        $params[] = $searchParam;
        $params[] = $searchParam;
        $params[] = $searchParam;
    }

    if (!empty($where)) {
        $sql .= ' WHERE ' . implode(' AND ', $where);
    }

    $sql .= ' ORDER BY t.sort_order ASC, t.id ASC';

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $traditions = $stmt->fetchAll();

    // ดึง months ทั้งหมดของ traditions ที่พบในครั้งเดียวเพื่อประสิทธิภาพ
    if (!empty($traditions)) {
        $traditionIds = array_column($traditions, 'id');
        $placeholders = implode(',', array_fill(0, count($traditionIds), '?'));
        $monthStmt = $pdo->prepare("SELECT tradition_id, month FROM tradition_months WHERE tradition_id IN ($placeholders) ORDER BY month ASC");
        $monthStmt->execute($traditionIds);
        $allMonths = $monthStmt->fetchAll();

        $monthsMap = [];
        foreach ($allMonths as $row) {
            $monthsMap[(int)$row['tradition_id']][] = (int)$row['month'];
        }

        foreach ($traditions as &$t) {
            $t['months'] = $monthsMap[(int)$t['id']] ?? [];
        }
        unset($t);
    }

    echo json_encode([
        'success' => true,
        'count' => count($traditions),
        'data' => $traditions
    ], JSON_UNESCAPED_UNICODE);

} catch (Throwable $e) {
    error_log('API traditions error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'เกิดข้อผิดพลาดในการโหลดข้อมูลประเพณี'
    ], JSON_UNESCAPED_UNICODE);
}

