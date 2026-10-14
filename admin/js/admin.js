/**
 * SIAM STORIES - Admin Panel Interactions
 */

'use strict';

document.addEventListener('DOMContentLoaded', () => {

    // 1. Password Visibility Toggle
    document.querySelectorAll('.btn-toggle-pw').forEach(btn => {
        btn.addEventListener('click', () => {
            const targetId = btn.dataset.target;
            const input = document.getElementById(targetId);
            if (!input) return;

            const isPassword = input.type === 'password';
            input.type = isPassword ? 'text' : 'password';
            btn.setAttribute('aria-label', isPassword ? 'ซ่อนรหัสผ่าน' : 'แสดงรหัสผ่าน');
            btn.textContent = isPassword ? '🙈' : '👁';
        });
    });

    // 2. Prevent Double Submit & Show Loading State
    document.querySelectorAll('form').forEach(form => {
        form.addEventListener('submit', (e) => {
            const submitBtn = form.querySelector('button[type="submit"]');
            if (submitBtn && !submitBtn.disabled) {
                // ถ้า form ไม่ผ่าน HTML5 validation ก็ไม่ต้อง disable
                if (!form.checkValidity()) return;
                
                // ให้ปุ่มแสดงสถานะกำลังโหลด
                setTimeout(() => {
                    submitBtn.disabled = true;
                    const originalText = submitBtn.textContent;
                    submitBtn.dataset.originalText = originalText;
                    submitBtn.textContent = 'กำลังประมวลผล...';
                }, 10);
            }
        });
    });

    // 3. Image File Preview
    const imageInput = document.getElementById('image');
    const previewContainer = document.getElementById('image-preview');
    if (imageInput && previewContainer) {
        imageInput.addEventListener('change', () => {
            const file = imageInput.files[0];
            if (!file) return;

            // ตรวจสอบขนาดไฟล์เบื้องต้นฝั่ง client
            if (file.size > 5 * 1024 * 1024) {
                alert('ขนาดไฟล์เกิน 5 MB กรุณาเลือกไฟล์ใหม่');
                imageInput.value = '';
                return;
            }

            const reader = new FileReader();
            reader.onload = (e) => {
                previewContainer.innerHTML = `<img src="${e.target.result}" alt="ตัวอย่างรูปภาพ" style="width:100%; height:100%; object-fit:cover;">`;
            };
            reader.readAsDataURL(file);
        });
    }

    // 4. Confirm Delete Modal
    const deleteModal = document.getElementById('delete-modal');
    const deleteForm = document.getElementById('delete-form');
    const deleteItemName = document.getElementById('delete-item-name');
    const deleteIdInput = document.getElementById('delete-id-input');

    if (deleteModal && deleteForm) {
        document.querySelectorAll('.btn-delete-trigger').forEach(btn => {
            btn.addEventListener('click', () => {
                const id = btn.dataset.id;
                const name = btn.dataset.name;
                if (deleteItemName) deleteItemName.textContent = name;
                if (deleteIdInput) deleteIdInput.value = id;
                deleteModal.classList.add('open');
            });
        });

        const cancelBtn = deleteModal.querySelector('.btn-modal-cancel');
        if (cancelBtn) {
            cancelBtn.addEventListener('click', () => {
                deleteModal.classList.remove('open');
            });
        }

        deleteModal.addEventListener('click', (e) => {
            if (e.target === deleteModal) {
                deleteModal.classList.remove('open');
            }
        });

        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && deleteModal.classList.contains('open')) {
                deleteModal.classList.remove('open');
            }
        });
    }

    // 5. Mobile Sidebar Toggle
    const mobileToggle = document.querySelector('.mobile-menu-toggle');
    const sidebar = document.querySelector('.admin-sidebar');
    if (mobileToggle && sidebar) {
        mobileToggle.addEventListener('click', () => {
            sidebar.classList.toggle('open');
        });

        document.addEventListener('click', (e) => {
            if (sidebar.classList.contains('open') && !sidebar.contains(e.target) && !mobileToggle.contains(e.target)) {
                sidebar.classList.remove('open');
            }
        });
    }
});

