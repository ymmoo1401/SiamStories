        </div><!-- /.admin-content -->
    </main><!-- /.admin-main -->
</div><!-- /.admin-layout -->

<!-- Confirm Delete Modal (Reusable) -->
<div class="modal-backdrop" id="delete-modal" role="dialog" aria-modal="true" aria-labelledby="modal-delete-title">
    <div class="modal-dialog">
        <div class="modal-header">
            <h3 id="modal-delete-title">ยืนยันการลบข้อมูล</h3>
        </div>
        <div class="modal-body">
            คุณแน่ใจหรือไม่ว่าต้องการลบรายการ <strong id="delete-item-name" style="color: var(--brown-dark);"></strong> ?
            <p style="margin-top: 8px; font-size: 0.85em; color: var(--danger-color);">การกระทำนี้ไม่สามารถย้อนกลับได้</p>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary btn-modal-cancel">ยกเลิก</button>
            <form id="delete-form" method="post" action="" style="display: inline;">
                <?php echo csrfField(); ?>
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="id" id="delete-id-input" value="">
                <button type="submit" class="btn btn-danger">ยืนยันลบ</button>
            </form>
        </div>
    </div>
</div>

<script src="js/admin.js"></script>
</body>
</html>

