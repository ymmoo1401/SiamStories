<?php
/**
 * SIAM STORIES - Admin Tradition Create/Edit Form
 * บันทึกด้วย Transaction ครอบคลุมทั้ง traditions และ tradition_months
 */

declare(strict_types=1);

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/auth_check.php';
require_once __DIR__ . '/csrf.php';
require_once __DIR__ . '/image_helper.php';

$pdo = getDbConnection();

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT) ?: null;
$isEdit = !empty($id);

$pageTitle = $isEdit ? 'แก้ไขข้อมูลประเพณี' : 'เพิ่มประเพณีใหม่';

$errors = [];
$nameVal = '';
$regionVal = 'north';
$provinceVal = '';
$detailVal = '';
$sortOrderVal = 0;
$imageVal = '';
$selectedMonths = [];

// ถ้าเป็นโหมดแก้ไข ให้โหลดข้อมูลเดิม
if ($isEdit) {
    $stmt = $pdo->prepare('SELECT * FROM traditions WHERE id = ?');
    $stmt->execute([$id]);
    $tradition = $stmt->fetch();

    if (!$tradition) {
        $_SESSION['flash_error'] = 'ไม่พบข้อมูลประเพณีที่ต้องการแก้ไข';
        header('Location: traditions.php');
        exit;
    }

    $nameVal = $tradition['name'];
    $regionVal = $tradition['region'];
    $provinceVal = $tradition['province'];
    $detailVal = $tradition['detail'];
    $sortOrderVal = (int)$tradition['sort_order'];
    $imageVal = $tradition['image'];

    // โหลดเดือนเดิม
    $mStmt = $pdo->prepare('SELECT month FROM tradition_months WHERE tradition_id = ?');
    $mStmt->execute([$id]);
    $selectedMonths = array_map(fn($r) => (int)$r['month'], $mStmt->fetchAll());
}

// เมื่อมีการ Submit Form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['csrf_token'] ?? '';
    if (!validateCsrfToken($token)) {
        $errors['general'] = 'แบบฟอร์มหมดอายุ กรุณาลองใหม่อีกครั้ง';
    } else {
        $name = trim((string)($_POST['name'] ?? ''));
        $region = trim((string)($_POST['region'] ?? ''));
        $province = trim((string)($_POST['province'] ?? ''));
        $detail = trim((string)($_POST['detail'] ?? ''));
        $sortOrder = filter_input(INPUT_POST, 'sort_order', FILTER_VALIDATE_INT);
        if ($sortOrder === false || $sortOrder === null) $sortOrder = 0;
        
        $postMonths = $_POST['months'] ?? [];
        if (!is_array($postMonths)) $postMonths = [];
        $selectedMonths = array_map('intval', array_filter($postMonths, fn($m) => is_numeric($m) && $m >= 1 && $m <= 12));

        // เก็บค่าไว้ในตัวแปรแสดงผลในฟอร์มเมื่อเกิดข้อผิดพลาด
        $nameVal = $name;
        $regionVal = $region;
        $provinceVal = $province;
        $detailVal = $detail;
        $sortOrderVal = $sortOrder;

        // การตรวจสอบความถูกต้อง (Validation)
        if ($name === '' || mb_strlen($name) > 255) {
            $errors['name'] = 'กรุณาระบุชื่อประเพณี (ความยาวไม่เกิน 255 ตัวอักษร)';
        }

        $validRegions = ['north', 'isan', 'central', 'south'];
        if (!in_array($region, $validRegions, true)) {
            $errors['region'] = 'กรุณาเลือกภูมิภาคที่ถูกต้อง';
        }

        if ($province === '' || mb_strlen($province) > 255) {
            $errors['province'] = 'กรุณาระบุจังหวัดหรือพื้นที่จัดงาน';
        }

        if ($detail === '') {
            $errors['detail'] = 'กรุณาระบุรายละเอียดประเพณี';
        }

        if (empty($selectedMonths)) {
            $errors['months'] = 'กรุณาเลือกเดือนที่จัดงานอย่างน้อย 1 เดือน';
        }

        // จัดการไฟล์รูปภาพ
        $imagePath = $imageVal;
        if (!empty($_FILES['image']['name'])) {
            $uploadResult = handleImageUpload($_FILES['image'], $imageVal);
            if ($uploadResult['success']) {
                $imagePath = $uploadResult['path'];
            } else {
                $errors['image'] = $uploadResult['error'];
            }
        } elseif (!$isEdit && empty($imageVal)) {
            $errors['image'] = 'กรุณาเลือกไฟล์รูปภาพสำหรับประเพณี';
        }

        // หากไม่มีข้อผิดพลาด ให้บันทึกลงฐานข้อมูลด้วย Transaction
        if (empty($errors)) {
            try {
                $pdo->beginTransaction();

                if ($isEdit) {
                    $updateStmt = $pdo->prepare('UPDATE traditions SET name = ?, region = ?, province = ?, detail = ?, image = ?, sort_order = ? WHERE id = ?');
                    $updateStmt->execute([$name, $region, $province, $detail, $imagePath, $sortOrder, $id]);
                    $traditionId = (int)$id;

                    // ลบเดือนเดิมทิ้งเพื่อบันทึกชุดใหม่
                    $delMonthStmt = $pdo->prepare('DELETE FROM tradition_months WHERE tradition_id = ?');
                    $delMonthStmt->execute([$traditionId]);
                } else {
                    $insertStmt = $pdo->prepare('INSERT INTO traditions (name, region, province, detail, image, sort_order) VALUES (?, ?, ?, ?, ?, ?)');
                    $insertStmt->execute([$name, $region, $province, $detail, $imagePath, $sortOrder]);
                    $traditionId = (int)$pdo->lastInsertId();
                }

                // บันทึกเดือนใน tradition_months
                $monthStmt = $pdo->prepare('INSERT INTO tradition_months (tradition_id, month) VALUES (?, ?)');
                foreach (array_unique($selectedMonths) as $m) {
                    $monthStmt->execute([$traditionId, $m]);
                }

                $pdo->commit();

                $_SESSION['flash_success'] = $isEdit ? 'แก้ไขข้อมูลประเพณีเรียบร้อยแล้ว' : 'เพิ่มประเพณีใหม่เรียบร้อยแล้ว';
                header('Location: traditions.php');
                exit;

            } catch (Throwable $e) {
                if ($pdo->inTransaction()) {
                    $pdo->rollBack();
                }
                error_log('Save tradition error: ' . $e->getMessage());
                $errors['general'] = 'เกิดข้อผิดพลาดในการบันทึกข้อมูล กรุณาลองใหม่อีกครั้ง';
            }
        }
    }
}

$monthLabels = [
    1 => '1. มกราคม', 2 => '2. กุมภาพันธ์', 3 => '3. มีนาคม', 4 => '4. เมษายน',
    5 => '5. พฤษภาคม', 6 => '6. มิถุนายน', 7 => '7. กรกฎาคม', 8 => '8. สิงหาคม',
    9 => '9. กันยายน', 10 => '10. ตุลาคม', 11 => '11. พฤศจิกายน', 12 => '12. ธันวาคม'
];

require_once __DIR__ . '/layout_header.php';
?>

<div class="content-box" style="max-width: 860px; margin: 0 auto;">
    <div class="box-header">
        <div class="box-title">
            <h3><?php echo htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8'); ?></h3>
        </div>
        <a href="traditions.php" class="btn btn-secondary btn-sm">← กลับหน้ารายการ</a>
    </div>

    <?php if (!empty($errors['general'])): ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($errors['general'], ENT_QUOTES, 'UTF-8'); ?></div>
    <?php endif; ?>

    <form method="post" action="<?php echo $isEdit ? 'tradition_form.php?id=' . (int)$id : 'tradition_form.php'; ?>" enctype="multipart/form-data" novalidate>
        <?php echo csrfField(); ?>

        <div class="form-grid">
            <!-- ชื่อประเพณี -->
            <div class="form-group form-full <?php echo isset($errors['name']) ? 'has-error' : ''; ?>">
                <label for="name">ชื่อประเพณี <span class="req">*</span></label>
                <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($nameVal, ENT_QUOTES, 'UTF-8'); ?>" required placeholder="เช่น ประเพณียี่เป็ง" class="input-control">
                <?php if (isset($errors['name'])): ?>
                    <span class="field-error"><?php echo htmlspecialchars($errors['name'], ENT_QUOTES, 'UTF-8'); ?></span>
                <?php endif; ?>
            </div>

            <!-- ภูมิภาค -->
            <div class="form-group <?php echo isset($errors['region']) ? 'has-error' : ''; ?>">
                <label for="region">ภูมิภาค <span class="req">*</span></label>
                <select id="region" name="region" required class="input-control">
                    <option value="north" <?php echo $regionVal === 'north' ? 'selected' : ''; ?>>ภาคเหนือ (North)</option>
                    <option value="isan" <?php echo $regionVal === 'isan' ? 'selected' : ''; ?>>ภาคอีสาน (Isan)</option>
                    <option value="central" <?php echo $regionVal === 'central' ? 'selected' : ''; ?>>ภาคกลาง (Central)</option>
                    <option value="south" <?php echo $regionVal === 'south' ? 'selected' : ''; ?>>ภาคใต้ (South)</option>
                </select>
                <?php if (isset($errors['region'])): ?>
                    <span class="field-error"><?php echo htmlspecialchars($errors['region'], ENT_QUOTES, 'UTF-8'); ?></span>
                <?php endif; ?>
            </div>

            <!-- จังหวัด / พื้นที่ -->
            <div class="form-group <?php echo isset($errors['province']) ? 'has-error' : ''; ?>">
                <label for="province">จังหวัดหรือพื้นที่จัดงาน <span class="req">*</span></label>
                <input type="text" id="province" name="province" value="<?php echo htmlspecialchars($provinceVal, ENT_QUOTES, 'UTF-8'); ?>" required placeholder="เช่น เชียงใหม่ หรือ ภาคเหนือ" class="input-control">
                <?php if (isset($errors['province'])): ?>
                    <span class="field-error"><?php echo htmlspecialchars($errors['province'], ENT_QUOTES, 'UTF-8'); ?></span>
                <?php endif; ?>
            </div>

            <!-- เดือนที่จัดงาน (เลือกได้หลายเดือน) -->
            <div class="form-group form-full <?php echo isset($errors['months']) ? 'has-error' : ''; ?>">
                <label>เดือนที่จัดงาน (เลือกได้มากกว่า 1 เดือน) <span class="req">*</span></label>
                <div class="month-checkboxes">
                    <?php foreach ($monthLabels as $mNum => $mText): ?>
                        <label class="month-label">
                            <input type="checkbox" name="months[]" value="<?php echo $mNum; ?>" <?php echo in_array($mNum, $selectedMonths, true) ? 'checked' : ''; ?>>
                            <span><?php echo $mText; ?></span>
                        </label>
                    <?php endforeach; ?>
                </div>
                <?php if (isset($errors['months'])): ?>
                    <span class="field-error"><?php echo htmlspecialchars($errors['months'], ENT_QUOTES, 'UTF-8'); ?></span>
                <?php endif; ?>
            </div>

            <!-- รายละเอียด -->
            <div class="form-group form-full <?php echo isset($errors['detail']) ? 'has-error' : ''; ?>">
                <label for="detail">รายละเอียดประเพณี <span class="req">*</span></label>
                <textarea id="detail" name="detail" rows="4" required class="input-control" placeholder="อธิบายกิจกรรม ความเป็นมา หรือจุดเด่นของประเพณี"><?php echo htmlspecialchars($detailVal, ENT_QUOTES, 'UTF-8'); ?></textarea>
                <?php if (isset($errors['detail'])): ?>
                    <span class="field-error"><?php echo htmlspecialchars($errors['detail'], ENT_QUOTES, 'UTF-8'); ?></span>
                <?php endif; ?>
            </div>

            <!-- ลำดับการแสดงผล -->
            <div class="form-group">
                <label for="sort_order">ลำดับการแสดงผล (เรียงจากน้อยไปมาก)</label>
                <input type="number" id="sort_order" name="sort_order" value="<?php echo $sortOrderVal; ?>" class="input-control" min="0">
                <small style="color: var(--text-muted);">ตัวเลขน้อยจะแสดงก่อน เช่น 1, 2, 3</small>
            </div>

            <!-- รูปภาพ -->
            <div class="form-group form-full <?php echo isset($errors['image']) ? 'has-error' : ''; ?>">
                <label for="image">รูปภาพประกอบ <?php echo $isEdit ? '(เลือกใหม่หากต้องการเปลี่ยน)' : '<span class="req">*</span>'; ?></label>
                <input type="file" id="image" name="image" accept="image/jpeg,image/png,image/webp" class="input-control">
                <small style="color: var(--text-muted); display: block; margin-top: 4px;">รองรับไฟล์ JPG, PNG, WebP ขนาดไม่เกิน 5 MB</small>
                <?php if (isset($errors['image'])): ?>
                    <span class="field-error"><?php echo htmlspecialchars($errors['image'], ENT_QUOTES, 'UTF-8'); ?></span>
                <?php endif; ?>

                <div class="image-preview-box" id="image-preview">
                    <?php if (!empty($imageVal)): ?>
                        <img src="../<?php echo htmlspecialchars($imageVal, ENT_QUOTES, 'UTF-8'); ?>" alt="ตัวอย่างรูปภาพเดิม" onerror="this.onerror=null; this.src='../Img/Minimalist Botanical Arch Pattern.png';">
                    <?php else: ?>
                        <span style="color: var(--text-muted); font-size: 0.85rem;">ยังไม่มีรูปภาพ</span>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div style="margin-top: 32px; display: flex; gap: 12px; justify-content: flex-end;">
            <a href="traditions.php" class="btn btn-secondary">ยกเลิก</a>
            <button type="submit" class="btn btn-primary">บันทึกข้อมูลประเพณี</button>
        </div>
    </form>
</div>

<?php require_once __DIR__ . '/layout_footer.php'; ?>

