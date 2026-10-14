<?php
/**
 * SIAM STORIES - Image Upload and Management Helper
 */

declare(strict_types=1);

const MAX_IMAGE_SIZE = 5 * 1024 * 1024; // 5 MB
const ALLOWED_IMAGE_MIMES = [
    'image/jpeg' => 'jpg',
    'image/png'  => 'png',
    'image/webp' => 'webp'
];

/**
 * จัดการอัปโหลดรูปภาพใหม่
 * @param array $file $_FILES['image']
 * @param string|null $currentPath เส้นทางรูปภาพเดิม (ถ้ามี)
 * @return array ['success' => bool, 'path' => string, 'error' => string]
 */
function handleImageUpload(array $file, ?string $currentPath = null): array {
    // ถ้าไม่ได้เลือกไฟล์ใหม่ และมีรูปเดิม ให้ใช้รูปเดิมต่อไป
    if (empty($file['name']) || $file['error'] === UPLOAD_ERR_NO_FILE) {
        if (!empty($currentPath)) {
            return ['success' => true, 'path' => $currentPath, 'error' => ''];
        }
        return ['success' => false, 'path' => '', 'error' => 'กรุณาเลือกไฟล์รูปภาพ'];
    }

    // ตรวจสอบ error จากการอัปโหลด
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return ['success' => false, 'path' => '', 'error' => 'เกิดข้อผิดพลาดในการอัปโหลดไฟล์ (Error code: ' . $file['error'] . ')'];
    }

    // ตรวจสอบขนาดไฟล์
    if ($file['size'] > MAX_IMAGE_SIZE) {
        return ['success' => false, 'path' => '', 'error' => 'ขนาดไฟล์รูปภาพต้องไม่เกิน 5 MB'];
    }

    $tmpPath = $file['tmp_name'];

    // ตรวจสอบ MIME type จริงฝั่งเซิร์ฟเวอร์
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mimeType = $finfo->file($tmpPath);

    if (!array_key_exists($mimeType, ALLOWED_IMAGE_MIMES)) {
        return ['success' => false, 'path' => '', 'error' => 'รูปแบบไฟล์ไม่ถูกต้อง รองรับเฉพาะ JPG, PNG และ WebP เท่านั้น'];
    }

    // ตรวจสอบด้วย getimagesize อีกชั้นเพื่อความมั่นใจว่าเป็นไฟล์รูปภาพจริง
    $imageInfo = @getimagesize($tmpPath);
    if ($imageInfo === false) {
        return ['success' => false, 'path' => '', 'error' => 'ไฟล์ที่อัปโหลดไม่ใช่รูปภาพที่สมบูรณ์'];
    }

    $ext = ALLOWED_IMAGE_MIMES[$mimeType];

    // สร้างชื่อไฟล์ใหม่ที่ไม่ซ้ำ
    $uploadDir = __DIR__ . '/../uploads';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    $newFilename = sprintf('img_%s_%s.%s', date('Ymd_His'), bin2hex(random_bytes(6)), $ext);
    $targetPath = $uploadDir . '/' . $newFilename;

    if (!move_uploaded_file($tmpPath, $targetPath)) {
        return ['success' => false, 'path' => '', 'error' => 'ไม่สามารถบันทึกไฟล์รูปภาพลงเซิร์ฟเวอร์ได้'];
    }

    // คืน path สำหรับเก็บลงฐานข้อมูล
    $relativePath = 'uploads/' . $newFilename;
    return ['success' => true, 'path' => $relativePath, 'error' => ''];
}

