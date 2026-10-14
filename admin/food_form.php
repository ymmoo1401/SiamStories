<?php
/**
 * SIAM STORIES - Admin Food Create/Edit Form
 */

declare(strict_types=1);

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/auth_check.php';
require_once __DIR__ . '/csrf.php';
require_once __DIR__ . '/image_helper.php';

$pdo = getDbConnection();

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT) ?: null;
$isEdit = !empty($id);

$pageTitle = $isEdit ? 'แก้ไขข้อมูลอาหารท้องถิ่น' : 'เพิ่มอาหารท้องถิ่นใหม่';

$errors = [];
$nameVal = '';
$regionVal = 'north';
$detailVal = '';
$tagsVal = '';
$sortOrderVal = 0;
$imageVal = '';

if ($isEdit) {
    $stmt = $pdo->prepare('SELECT * FROM foods WHERE id = ?');
    $stmt->execute([$id]);
    $food = $stmt->fetch();

    if (!$food) {
        $_SESSION['flash_error'] = 'ไม่พบข้อมูลอาหารที่ต้องการแก้ไข';
        header('Location: foods.php');
        exit;
    }

    $nameVal = $food['name'];
    $regionVal = $food['region'];
    $detailVal = $food['detail'];
    $tagsVal = $food['tags'];
    $sortOrderVal = (int)$food['sort_order'];
    $imageVal = $food['image'];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['csrf_token'] ?? '';
    if (!validateCsrfToken($token)) {
        $errors['general'] = 'แบบฟอร์มหมดอายุ กรุณาลองใหม่อีกครั้ง';
    } else {
        $name = trim((string)($_POST['name'] ?? ''));
        $region = trim((string)($_POST['region'] ?? ''));
        $detail = trim((string)($_POST['detail'] ?? ''));
        $tags = trim((string)($_POST['tags'] ?? ''));
        $sortOrder = filter_input(INPUT_POST, 'sort_order', FILTER_VALIDATE_INT);
        if ($sortOrder === false || $sortOrder === null) $sortOrder = 0;

        $nameVal = $name;
        $regionVal = $region;
        $detailVal = $detail;
        $tagsVal = $tags;
        $sortOrderVal = $sortOrder;

        if ($name === '' || mb_strlen($name) > 255) {
            $errors['name'] = 'กรุณาระบุชื่ออาหาร (ความยาวไม่เกิน 255 ตัวอักษร)';
        }

        $validRegions = ['north', 'isan', 'central', 'south'];
        if (!in_array($region, $validRegions, true)) {
            $errors['region'] = 'กรุณาเลือกภูมิภาคที่ถูกต้อง';
        }

        if ($detail === '') {
            $errors['detail'] = 'กรุณาระบุรายละเอียดอาหาร';
        }

        if ($tags === '' || mb_strlen($tags) > 255) {
            $errors['tags'] = 'กรุณาระบุแท็กคำบรรยายสั้น คั่นด้วยเครื่องหมายจุลภาค (ไม่เกิน 255 ตัวอักษร)';
        }

        $imagePath = $imageVal;
        if (!empty($_FILES['image']['name'])) {
            $uploadResult = handleImageUpload($_FILES['image'], $imageVal);
            if ($uploadResult['success']) {
                $imagePath = $uploadResult['path'];
            } else {
                $errors['image'] = $uploadResult['error'];
            }
        } elseif (!$isEdit && empty($imageVal)) {
            $errors['image'] = 'กรุณาเลือกไฟล์รูปภาพสำหรับอาหาร';
        }

        if (empty($errors)) {
            try {
                if ($isEdit) {
                    $stmt = $pdo->prepare('UPDATE foods SET name = ?, region = ?, detail = ?, tags = ?, image = ?, sort_order = ? WHERE id = ?');
                    $stmt->execute([$name, $region, $detail, $tags, $imagePath, $sortOrder, $id]);
                    $_SESSION['flash_success'] = 'แก้ไขข้อมูลอาหารเรียบร้อยแล้ว';
                } else {
                    $stmt = $pdo->prepare('INSERT INTO foods (name, region, detail, tags, image, sort_order) VALUES (?, ?, ?, ?, ?, ?)');
                    $stmt->execute([$name, $region, $detail, $tags, $imagePath, $sortOrder]);
                    $_SESSION['flash_success'] = 'เพิ่มอาหารใหม่เรียบร้อยแล้ว';
                }

                header('Location: foods.php');
                exit;

            } catch (Throwable $e) {
                error_log('Save food error: ' . $e->getMessage());
                $errors['general'] = 'เกิดข้อผิดพลาดในการบันทึกข้อมูล กรุณาลองใหม่อีกครั้ง';
            }
        }
    }
}

require_once __DIR__ . '/layout_header.php';
?>

<div class="content-box" style="max-width: 860px; margin: 0 auto;">
    <div class="box-header">
        <div class="box-title">
            <h3><?php echo htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8'); ?></h3>
        </div>
        <a href="foods.php" class="btn btn-secondary btn-sm">← กลับหน้ารายการ</a>
    </div>

    <?php if (!empty($errors['general'])): ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($errors['general'], ENT_QUOTES, 'UTF-8'); ?></div>
    <?php endif; ?>

    <form method="post" action="<?php echo $isEdit ? 'food_form.php?id=' . (int)$id : 'food_form.php'; ?>" enctype="multipart/form-data" novalidate>
        <?php echo csrfField(); ?>

        <div class="form-grid">
            <!-- ชื่ออาหาร -->
            <div class="form-group form-full <?php echo isset($errors['name']) ? 'has-error' : ''; ?>">
                <label for="name">ชื่ออาหาร <span class="req">*</span></label>
                <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($nameVal, ENT_QUOTES, 'UTF-8'); ?>" required placeholder="เช่น ส้มตำ หรือ ข้าวซอยไก่" class="input-control">
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

            <!-- ลำดับการแสดงผล -->
            <div class="form-group">
                <label for="sort_order">ลำดับการแสดงผล</label>
                <input type="number" id="sort_order" name="sort_order" value="<?php echo $sortOrderVal; ?>" class="input-control" min="0">
                <small style="color: var(--text-muted);">เรียงจากน้อยไปมาก</small>
            </div>

            <!-- แท็กคำบรรยายสั้น -->
            <div class="form-group form-full <?php echo isset($errors['tags']) ? 'has-error' : ''; ?>">
                <label for="tags">แท็กคำบรรยายสั้น (คั่นด้วยเครื่องหมายจุลภาค <code>,</code>) <span class="req">*</span></label>
                <input type="text" id="tags" name="tags" value="<?php echo htmlspecialchars($tagsVal, ENT_QUOTES, 'UTF-8'); ?>" required placeholder="เช่น รสจัดจ้าน, มะละกอกรอบ, กินคู่ข้าวเหนียว" class="input-control">
                <small style="color: var(--text-muted);">สำหรับแสดงเป็นป้ายกำกับในการ์ดอาหาร เช่น <code>หอมข้าวคั่ว, สมุนไพรสด, เมนูขึ้นชื่อ</code></small>
                <?php if (isset($errors['tags'])): ?>
                    <span class="field-error"><?php echo htmlspecialchars($errors['tags'], ENT_QUOTES, 'UTF-8'); ?></span>
                <?php endif; ?>
            </div>

            <!-- รายละเอียด -->
            <div class="form-group form-full <?php echo isset($errors['detail']) ? 'has-error' : ''; ?>">
                <label for="detail">รายละเอียดอาหาร <span class="req">*</span></label>
                <textarea id="detail" name="detail" rows="4" required class="input-control" placeholder="อธิบายรสชาติ วัตถุดิบเด่น และเอกลักษณ์ของอาหารจานนี้"><?php echo htmlspecialchars($detailVal, ENT_QUOTES, 'UTF-8'); ?></textarea>
                <?php if (isset($errors['detail'])): ?>
                    <span class="field-error"><?php echo htmlspecialchars($errors['detail'], ENT_QUOTES, 'UTF-8'); ?></span>
                <?php endif; ?>
            </div>

            <!-- รูปภาพ -->
            <div class="form-group form-full <?php echo isset($errors['image']) ? 'has-error' : ''; ?>">
                <label for="image">รูปภาพอาหาร <?php echo $isEdit ? '(เลือกใหม่หากต้องการเปลี่ยน)' : '<span class="req">*</span>'; ?></label>
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
            <a href="foods.php" class="btn btn-secondary">ยกเลิก</a>
            <button type="submit" class="btn btn-primary">บันทึกข้อมูลอาหาร</button>
        </div>
    </form>
</div>

<?php require_once __DIR__ . '/layout_footer.php'; ?>

