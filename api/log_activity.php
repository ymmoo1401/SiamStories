<?php
/**
 * SIAM STORIES - API สำหรับบันทึกประวัติการใช้งานและการค้นหาของผู้ใช้
 * บันทึก:
 * 1. คำค้นหา (Search queries)
 * 2. การเปิดดูเนื้อหาประเพณี/อาหาร (View tradition/food items)
 * 3. การเข้าชมหน้าเว็บภูมิภาค/ปฏิทิน (Page visits)
 * 4. การกรองข้อมูลปฏิทิน/หมวดหมู่ (Filter actions)
 */

declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');

require_once __DIR__ . '/../admin/auth_check.php';
require_once __DIR__ . '/../config/db.php';

startSecureSession();

// ถ้ายังไม่ได้เข้าสู่ระบบ จะไม่บันทึก (หรือตอบกลับว่า anonymous)
if (!isLoggedIn()) {
    echo json_encode([
        'success'   => false,
        'message'   => 'ผู้ใช้ยังไม่ได้เข้าสู่ระบบ ไม่มีการบันทึกประวัติ',
        'logged_in' => false
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'message' => 'รองรับเฉพาะคำขอแบบ POST เท่านั้น'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// รับข้อมูลจาก JSON payload หรือ POST body
$inputRaw = file_get_contents('php://input');
$data = [];
if (!empty($inputRaw)) {
    $decoded = json_decode($inputRaw, true);
    if (is_array($decoded)) {
        $data = $decoded;
    }
}
if (empty($data)) {
    $data = $_POST;
}

$userId = (int)$_SESSION['admin_id'];
$actionType = trim((string)($data['action_type'] ?? 'search'));
$allowedActions = ['search', 'view', 'page_visit', 'filter'];
if (!in_array($actionType, $allowedActions, true)) {
    $actionType = 'search';
}

$queryText = trim((string)($data['query_text'] ?? ''));
$targetTitle = trim((string)($data['target_title'] ?? ''));
$targetUrl = trim((string)($data['target_url'] ?? ''));

// ป้องกันข้อมูลว่างเปล่า
if ($queryText === '' && $targetTitle === '') {
    echo json_encode([
        'success' => false,
        'message' => 'ไม่มีข้อมูลที่ต้องบันทึก'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// จำกัดความยาวเพื่อความปลอดภัย
$queryText = mb_substr($queryText, 0, 255);
$targetTitle = mb_substr($targetTitle, 0, 255);
$targetUrl = mb_substr($targetUrl, 0, 255);
$ipAddress = $_SERVER['REMOTE_ADDR'] ?? '';

try {
    $pdo = getDbConnection();

    // ตรวจสอบข้อมูลซ้ำซ้อนล่าสุดใน 15 วินาที เพื่อไม่ให้บันทึกซ้ำซากจากการพิมพ์ต่อเนื่อง
    $checkStmt = $pdo->prepare('
        SELECT id, created_at 
        FROM user_history 
        WHERE user_id = ? AND action_type = ? AND query_text = ? 
        ORDER BY id DESC LIMIT 1
    ');
    $checkStmt->execute([$userId, $actionType, $queryText]);
    $lastItem = $checkStmt->fetch();

    if ($lastItem) {
        $diffSeconds = time() - strtotime((string)$lastItem['created_at']);
        if ($diffSeconds < 20) {
            // อัปเดตเวลาล่าสุดแทนการแทรกแถวใหม่
            $updateStmt = $pdo->prepare('UPDATE user_history SET created_at = CURRENT_TIMESTAMP, target_title = ?, target_url = ? WHERE id = ?');
            $updateStmt->execute([$targetTitle ?: null, $targetUrl ?: null, $lastItem['id']]);

            echo json_encode([
                'success' => true,
                'action'  => 'updated',
                'id'      => (int)$lastItem['id']
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }
    }

    // แทรกประวัติใหม่
    $insertStmt = $pdo->prepare('
        INSERT INTO user_history (user_id, action_type, query_text, target_title, target_url, ip_address)
        VALUES (?, ?, ?, ?, ?, ?)
    ');
    $insertStmt->execute([
        $userId,
        $actionType,
        $queryText ?: null,
        $targetTitle ?: null,
        $targetUrl ?: null,
        $ipAddress ?: null
    ]);

    $newId = (int)$pdo->lastInsertId();

    echo json_encode([
        'success' => true,
        'action'  => 'inserted',
        'id'      => $newId
    ], JSON_UNESCAPED_UNICODE);

} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'เกิดข้อผิดพลาดในการบันทึกประวัติ: ' . $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}

