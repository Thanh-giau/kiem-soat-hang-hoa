        </main>
    </div>
</div>
<!-- Modal Hướng Dẫn Kết Nối Điện Thoại -->
<div class="modal-overlay" id="modal-mobile-qr">
    <div class="modal-card" style="max-width: 440px; text-align: center;">
        <div class="modal-header">
            <div class="modal-title" style="display: flex; align-items: center; gap: 8px;">
                <i class="fa-solid fa-mobile-screen-button" style="color: var(--primary);"></i>
                <span>Sử Dụng Trên Điện Thoại</span>
            </div>
            <button type="button" onclick="closeModal('modal-mobile-qr')" style="background: none; border: none; font-size: 22px; cursor: pointer; color: var(--text-muted);">&times;</button>
        </div>
        <div class="modal-body" style="padding: 24px 20px;">
            <p style="font-size: 13.5px; color: var(--text-secondary); margin-bottom: 16px;">
                Dùng được trên <strong>4G/5G hoặc bất kỳ mạng Wi-Fi nào</strong>! Mở <strong>Camera</strong> hoặc <strong>Zalo</strong> quét mã bên dưới để vào ngay:
            </p>

            <div style="background: #ffffff; padding: 12px; border-radius: 12px; display: inline-block; box-shadow: 0 4px 15px rgba(0,0,0,0.08); border: 1px solid var(--border-color); margin-bottom: 16px;">
                <img src="<?= BASE_URL ?>/assets/images/qr_mobile.png" alt="Mã QR Truy Cập Điện Thoại" style="width: 200px; height: 200px; display: block;">
            </div>

            <div style="background: #f8fafc; border: 1px solid var(--border-color); border-radius: 8px; padding: 10px 14px; font-size: 13.5px; margin-bottom: 14px;">
                <div style="color: var(--text-secondary); font-size: 12px;">Hoặc gửi đường link này cho nhân viên mở trên trình duyệt:</div>
                <a href="https://gospel-elder-entry-lobby.trycloudflare.com" target="_blank" style="color: var(--primary); font-size: 15px; font-weight: 700; word-break: break-all;">
                    https://gospel-elder-entry-lobby.trycloudflare.com
                </a>
            </div>

            <div style="font-size: 12.5px; color: var(--text-secondary); line-height: 1.5; text-align: left; background: #fffbeb; padding: 10px 14px; border-radius: 8px; border: 1px solid #fde68a;">
                💡 <strong>Mẹo dùng như App:</strong> Khi đã vào web trên điện thoại, bấm nút <strong>Chia sẻ</strong> (Safari) hoặc <strong>dấu 3 chấm</strong> (Chrome) &rarr; chọn <strong>"Thêm vào màn hình chính" (Add to Home Screen)</strong> để dùng mượt mà như app cài đặt!
            </div>
        </div>
        <div class="modal-footer" style="justify-content: center;">
            <button type="button" class="btn btn-primary" onclick="closeModal('modal-mobile-qr')">
                <i class="fa-solid fa-check"></i> Đã Hiểu
            </button>
        </div>
    </div>
</div>

<!-- Core JavaScript -->
<script src="<?= BASE_URL ?>/assets/js/main.js?v=<?= time() ?>"></script>
<script src="<?= BASE_URL ?>/assets/js/searchable-select.js?v=<?= time() ?>"></script>
<?php if (isset($extraJs)): ?>
    <?php foreach ($extraJs as $js): ?>
        <script src="<?= BASE_URL ?>/assets/js/<?= $js ?>?v=<?= time() ?>"></script>
    <?php endforeach; ?>
<?php endif; ?>
</body>
</html>
