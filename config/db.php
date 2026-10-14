<?php
/**
 * SIAM STORIES - Database Configuration & PDO Connection
 * ใช้ UTF-8 (utf8mb4) และ PDO ปลอดภัย
 */

declare(strict_types=1);

define('DB_HOST', 'localhost');
define('DB_PORT', '3306');
define('DB_NAME', 'siam_stories');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

function getDbConnection(): PDO {
    static $pdo = null;

    if ($pdo === null) {
        $dsn = sprintf(
            'mysql:host=%s;port=%s;dbname=%s;charset=%s',
            DB_HOST,
            DB_PORT,
            DB_NAME,
            DB_CHARSET
        );

        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci"
        ];

        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            // บันทึก log จริง แต่ไม่แสดง credentials หรือ stack trace ออกไปภายนอก
            error_log('Database connection error: ' . $e->getMessage());
            http_response_code(500);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode([
                'success' => false,
                'message' => 'ไม่สามารถเชื่อมต่อฐานข้อมูลได้ กรุณาลองใหม่อีกครั้งในภายหลัง'
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }
    }

    return $pdo;
}

