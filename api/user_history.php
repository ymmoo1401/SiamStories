<?php
/**
 * SIAM STORIES - API สำหรับดึงและจัดการประวัติการใช้งานของผู้ใช้
 * รองรับ:
 * - GET: ดึงรายการประวัติ (พร้อมตัวกรอง search, view, visit, filter และค้นหาในประวัติ)
 * - DELETE: ลบรายการใดรายการหนึ่ง หรือล้างประวัติทั้งหมด
 */

declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');
header('Cache-Control: no-store, no-cache, must-revalidate');

require_once __DIR__ . '/../admin/auth_check.php';
require_once __DIR__ . '/../config/db.php';

startSecureSession();

if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode([
        'success'   => false,
        'message'   => 'กรุณาเข้าสู่ระบบก่อนดูประวัติการใช้งาน',
        'logged_in' => false
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

$userId = (int)$_SESSION['admin_id'];
$pdo = getDbConnection();

/**
 * ฟังก์ชันแปลงเวลาเป็นภาษาไทยแบบเข้าใจง่าย (Relative Time)
 */
function formatThaiTime(string $datetime): string {
    $timestamp = strtotime($datetime);
    if (!$timestamp) return $datetime;

    $now = time();
    $diff = $now - $timestamp;

    if ($diff < 45) {
        return 'เมื่อสักครู่';
    }
    if ($diff < 3600) {
        $minutes = max(1, (int)round($diff / 60));
        return "{$minutes} นาทีที่แล้ว";
    }
    if ($diff < 86400) {
        $hours = (int)floor($diff / 3600);
        return "{$hours} ชั่วโมงที่แล้ว";
    }
    if ($diff < 172800) {
        return 'เมื่อวานนี้ ' . date('H:i', $timestamp);
    }

    $thaiMonths = [
        1 => 'ม.ค.', 2 => 'ก.พ.', 3 => 'มี.ค.', 4 => 'เม.ย.', 5 => 'พ.ค.', 6 => 'มิ.ย.',
        7 => 'ก.ค.', 8 => 'ส.ค.', 9 => 'ก.ย.', 10 => 'ต.ค.', 11 => 'พ.ย.', 12 => 'ธ.ค.'
    ];
    $day = date('j', $timestamp);
    $month = $thaiMonths[(int)date('n', $timestamp)] ?? '';
    $year = (int)date('Y', $timestamp) + 543;
    $time = date('H:i', $timestamp);

    return "{$day} {$month} {$year} {$time}";
}

$method = $_SERVER['REQUEST_METHOD'];

// ==========================================
// METHOD: DELETE (ล้างประวัติ หรือลบรายการเฉพาะ)
// ==========================================
if ($method === 'DELETE' || ($method === 'POST' && isset($_POST['_method']) && $_POST['_method'] === 'DELETE')) {
    $rawInput = file_get_contents('php://input');
    $deleteParams = [];
    if (!empty($rawInput)) {
        $decoded = json_decode($rawInput, true);
        if (is_array($decoded)) {
            $deleteParams = $decoded;
        }
    }
    if (empty($deleteParams)) {
        $deleteParams = $_GET;
    }

    $clearAll = !empty($deleteParams['clear_all']) || !empty($_GET['clear_all']);
    $itemId = isset($deleteParams['id']) ? (int)$deleteParams['id'] : (isset($_GET['id']) ? (int)$_GET['id'] : 0);

    try {
        if ($clearAll) {
            $stmt = $pdo->prepare('DELETE FROM user_history WHERE user_id = ?');
            $stmt->execute([$userId]);
            $deletedCount = $stmt->rowCount();

            echo json_encode([
                'success' => true,
                'message' => "ล้างประวัติการใช้งานทั้งหมดเรียบร้อยแล้ว ({$deletedCount} รายการ)",
                'deleted' => $deletedCount
            ], JSON_UNESCAPED_UNICODE);
            exit;
        } elseif ($itemId > 0) {
            $stmt = $pdo->prepare('DELETE FROM user_history WHERE id = ? AND user_id = ?');
            $stmt->execute([$itemId, $userId]);
            $deletedCount = $stmt->rowCount();

            if ($deletedCount > 0) {
                echo json_encode([
                    'success' => true,
                    'message' => 'ลบรายการประวัติเรียบร้อยแล้ว',
                    'id'      => $itemId
                ], JSON_UNESCAPED_UNICODE);
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => 'ไม่พบรายการที่ต้องการลบ หรือไม่มีสิทธิ์เข้าถึง'
                ], JSON_UNESCAPED_UNICODE);
            }
            exit;
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'ระบุ id หรือ clear_all เพื่อลบประวัติ'
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }
    } catch (Throwable $e) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'เกิดข้อผิดพลาดในการลบข้อมูล: ' . $e->getMessage()
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
}

// ==========================================
// METHOD: GET (ดึงประวัติการใช้งาน)
// ==========================================
$type = trim((string)($_GET['type'] ?? 'all'));
$keyword = trim((string)($_GET['q'] ?? ''));
$limit = filter_input(INPUT_GET, 'limit', FILTER_VALIDATE_INT) ?: 60;
$limit = min(max($limit, 5), 150);

$whereParts = ['user_id = ?'];
$bindParams = [$userId];

if ($type !== '' && $type !== 'all') {
    $whereParts[] = 'action_type = ?';
    $bindParams[] = $type;
}

if ($keyword !== '') {
    $whereParts[] = '(query_text LIKE ? OR target_title LIKE ?)';
    $likeKw = '%' . $keyword . '%';
    $bindParams[] = $likeKw;
    $bindParams[] = $likeKw;
}

$whereSql = implode(' AND ', $whereParts);

try {
    // 1. ดึงสถิติรวมของประวัติผู้ใช้
    $statsStmt = $pdo->prepare('
        SELECT 
            COUNT(*) AS total,
            SUM(CASE WHEN action_type = "search" THEN 1 ELSE 0 END) AS searches,
            SUM(CASE WHEN action_type = "view" THEN 1 ELSE 0 END) AS views,
            SUM(CASE WHEN action_type = "page_visit" THEN 1 ELSE 0 END) AS visits,
            SUM(CASE WHEN action_type = "filter" THEN 1 ELSE 0 END) AS filters
        FROM user_history 
        WHERE user_id = ?
    ');
    $statsStmt->execute([$userId]);
    $stats = $statsStmt->fetch() ?: [
        'total' => 0, 'searches' => 0, 'views' => 0, 'visits' => 0, 'filters' => 0
    ];

    // 2. ดึงรายการประวัติ
    $sql = "SELECT id, action_type, query_text, target_title, target_url, created_at 
            FROM user_history 
            WHERE {$whereSql} 
            ORDER BY id DESC 
            LIMIT {$limit}";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($bindParams);
    $rows = $stmt->fetchAll();

    $items = [];
    foreach ($rows as $row) {
        $items[] = [
            'id'           => (int)$row['id'],
            'action_type'  => $row['action_type'],
            'query_text'   => $row['query_text'] ?? '',
            'target_title' => $row['target_title'] ?? '',
            'target_url'   => $row['target_url'] ?? '',
            'created_at'   => $row['created_at'],
            'thai_time'    => formatThaiTime($row['created_at'])
        ];
    }

    echo json_encode([
        'success' => true,
        'stats'   => [
            'total'    => (int)($stats['total'] ?? 0),
            'searches' => (int)($stats['searches'] ?? 0),
            'views'    => (int)($stats['views'] ?? 0),
            'visits'   => (int)($stats['visits'] ?? 0),
            'filters'  => (int)($stats['filters'] ?? 0)
        ],
        'count'   => count($items),
        'items'   => $items
    ], JSON_UNESCAPED_UNICODE);

} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'เกิดข้อผิดพลาดในการดึงข้อมูลประวัติ: ' . $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
