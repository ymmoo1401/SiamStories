<?php
/**
 * SIAM STORIES - API สำหรับข้อมูลอาหารท้องถิ่น (Foods API)
 * รองรับการดึงข้อมูลทั้งหมด หรือกรองตาม region, search, id
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
                'message' => 'รหัสอาหารไม่ถูกต้อง'
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }

        $stmt = $pdo->prepare('SELECT * FROM foods WHERE id = ?');
        $stmt->execute([$id]);
        $food = $stmt->fetch();

        if (!$food) {
            http_response_code(404);
            echo json_encode([
                'success' => false,
                'message' => 'ไม่พบข้อมูลอาหารที่ระบุ'
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }

        $tagsArray = array_values(array_filter(array_map('trim', explode(',', $food['tags'] ?? ''))));
        $food['tags_list'] = $tagsArray;

        echo json_encode([
            'success' => true,
            'data' => $food
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    // กรองตาม parameters
    $region = filter_input(INPUT_GET, 'region', FILTER_DEFAULT);
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

    $sql = 'SELECT id, name, region, detail, image, tags, sort_order, created_at, updated_at FROM foods';
    $params = [];
    $where = [];

    if ($region !== null && $region !== '') {
        $where[] = 'region = ?';
        $params[] = $region;
    }

    if ($search !== null && trim($search) !== '') {
        $where[] = '(name LIKE ? OR detail LIKE ? OR tags LIKE ?)';
        $searchParam = '%' . trim($search) . '%';
        $params[] = $searchParam;
        $params[] = $searchParam;
        $params[] = $searchParam;
    }

    if (!empty($where)) {
        $sql .= ' WHERE ' . implode(' AND ', $where);
    }

    $sql .= ' ORDER BY sort_order ASC, id ASC';

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $foods = $stmt->fetchAll();

    foreach ($foods as &$f) {
        $tagsArray = array_values(array_filter(array_map('trim', explode(',', $f['tags'] ?? ''))));
        $f['tags_list'] = $tagsArray;
    }
    unset($f);

    echo json_encode([
        'success' => true,
        'count' => count($foods),
        'data' => $foods
    ], JSON_UNESCAPED_UNICODE);

} catch (Throwable $e) {
    error_log('API foods error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'เกิดข้อผิดพลาดในการโหลดข้อมูลอาหาร'
    ], JSON_UNESCAPED_UNICODE);
}

