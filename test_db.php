<?php
declare(strict_types=1);

require_once __DIR__ . '/config/db.php';

header('Content-Type: text/html; charset=utf-8');

try {
    $pdo = getDbConnection();

    echo '<h2>เชื่อมต่อ MySQL สำเร็จ!</h2>';

    $dbName = $pdo->query('SELECT DATABASE()')->fetchColumn();

    echo '<p>ฐานข้อมูล: ' .
         htmlspecialchars((string)$dbName, ENT_QUOTES, 'UTF-8') .
         '</p>';

    $tables = [
        'admins',
        'traditions',
        'tradition_months',
        'foods',
        'user_history'
    ];

    foreach ($tables as $table) {
        $stmt = $pdo->query(
            'SELECT COUNT(*) FROM `' . $table . '`'
        );

        $count = (int)$stmt->fetchColumn();

        echo '<p>ตาราง ' .
             htmlspecialchars($table, ENT_QUOTES, 'UTF-8') .
             ': ' . $count . ' รายการ</p>';
    }

    echo '<h3>ทดสอบการอ่านข้อมูลสำเร็จ</h3>';

} catch (Throwable $e) {
    http_response_code(500);

    error_log(
        'Database test failed: ' . $e->getMessage()
    );

    echo '<h2>ทดสอบฐานข้อมูลไม่สำเร็จ</h2>';
    echo '<p>ตรวจสอบการตั้งค่าการเชื่อมต่อและตารางใน MySQL</p>';
}
