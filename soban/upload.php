<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';
requireLogin();

$pageTitle = "Upload Số Bán Hàng Ngày";
$extraCss = ['soban.css'];
$extraJs = ['upload.js'];

include __DIR__ . '/../includes/header.php';
?>

<div style="max-width: 900px; margin: 0 auto;">
    <!-- KHU VỰC 1: FORM CHỌN FILE VÀ NGÀY BÁN -->
    <div id="upload-area">
        <div class="card">
            <div class="card-header">
                <h2 class="card-title">
                    <i class="fa-solid fa-cloud-arrow-up" style="color: var(--primary);"></i>
                    Tải Lên File Excel Số Bán Mỗi Ngày
                </h2>
                <a href="<?= BASE_URL ?>/soban/lich_su.php" class="btn btn-secondary btn-sm">
                    <i class="fa-solid fa-clock-rotate-left"></i> Xem lịch sử file
                </a>
            </div>
            <div class="card-body">
                <div style="margin-bottom: 20px;">
                    <label class="form-label" for="ngay_ban" style="font-weight: 700; font-size: 14px;">
                        <i class="fa-regular fa-calendar-days"></i> Chọn Ngày Áp Dụng Số Bán:
                    </label>
                    <input type="date" id="ngay_ban" name="ngay_ban" class="form-control" value="<?= date('Y-m-d') ?>" style="max-width: 250px; font-weight: 600;">
                </div>

                <div class="upload-dropzone" id="dropzone">
                    <div class="upload-icon">
                        <i class="fa-solid fa-file-excel"></i>
                    </div>
                    <div class="upload-text">Kéo thả file Excel (.xlsx, .xls hoặc .csv) vào đây</div>
                    <div class="upload-subtext">Hoặc bấm trực tiếp để chọn file từ máy tính / điện thoại</div>
                    <input type="file" id="excel_file" accept=".xlsx, .xls, .csv" style="display: none;">
                    
                    <div id="selected-file-name" style="margin-top: 16px; font-weight: 700; color: var(--primary); display: none;"></div>
                </div>

                <div style="text-align: center; display: flex; justify-content: center; gap: 12px; flex-wrap: wrap;">
                    <button type="button" id="btn-analyze" class="btn btn-primary btn-lg" onclick="analyzeFile()" style="display: none;">
                        <i class="fa-solid fa-wand-magic-sparkles"></i> Đọc & Tự Động Lọc Sản Phẩm
                    </button>
                    <a href="<?= BASE_URL ?>/uploads/so_ban_mau.csv" class="btn btn-secondary" download>
                        <i class="fa-solid fa-download" style="color: var(--primary);"></i> Tải File Mẫu CSV Chuẩn
                    </a>
                </div>

                <div style="margin-top: 24px; padding: 16px; background: #f8fafc; border-radius: var(--border-radius-sm); border: 1px dashed var(--border-color); font-size: 13px; color: var(--text-secondary);">
                    <strong style="color: var(--text-primary);"><i class="fa-solid fa-circle-info" style="color: var(--info);"></i> Cơ chế hoạt động:</strong>
                    <ul style="margin-left: 20px; margin-top: 6px; line-height: 1.6;">
                        <li>Hệ thống tự động đọc và nhận diện cột <strong>Mã SP</strong> và <strong>Số lượng bán</strong>.</li>
                        <li><strong>Tự động lọc</strong>: Chỉ lưu các món bánh, đồ ăn, nước suối (có đánh dấu cần kiểm kê).</li>
                        <li><strong>Tự động bỏ qua</strong>: Các món nước pha chế (cà phê, trà...) sẽ không đưa vào kiểm kê.</li>
                        <li><strong>Cảnh báo thông minh</strong>: Tự phát hiện mã mới chưa có trong hệ thống và chống upload trùng lặp.</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <!-- KHU VỰC 2: KẾT QUẢ PHÂN TÍCH TỰ ĐỘNG & ĐỐI CHIẾU CƠ SỞ DỮ LIỆU -->
    <div id="analysis-result" style="display: none;">
        <!-- Cảnh báo trùng lặp (Mục 6) -->
        <div id="duplicate-warning" class="alert alert-warning" style="display: none; align-items: flex-start;">
            <i class="fa-solid fa-triangle-exclamation" style="font-size: 24px; margin-top: 2px;"></i>
            <div style="flex: 1;">
                <div style="font-weight: 700; font-size: 15px; margin-bottom: 4px;">DỮ LIỆU ĐÃ TỒN TẠI</div>
                <div id="duplicate-message" style="margin-bottom: 12px;"></div>
                <div style="display: flex; gap: 10px;">
                    <button type="button" class="btn btn-sm btn-danger" onclick="commitSave(true)">
                        <i class="fa-solid fa-rotate"></i> Thay Thế & Ghi Đè Dữ Liệu
                    </button>
                    <button type="button" class="btn btn-sm btn-secondary" onclick="resetUpload()">
                        <i class="fa-solid fa-xmark"></i> Hủy Bỏ
                    </button>
                </div>
            </div>
        </div>

        <!-- 3 Thẻ thống kê phân loại sản phẩm (Linh hồn hệ thống) -->
        <div class="filter-summary-card">
            <div class="filter-box checked">
                <div class="filter-count" id="count-kiem-ke">0</div>
                <div class="filter-title">🍰 SẢN PHẨM CẦN KIỂM KÊ (SẼ LƯU)</div>
            </div>
            <div class="filter-box skipped">
                <div class="filter-count" id="count-bo-qua">0</div>
                <div class="filter-title">☕ ĐỒ PHA CHẾ (TỰ BỎ QUA)</div>
            </div>
            <div class="filter-box alert">
                <div class="filter-count" id="count-moi">0</div>
                <div class="filter-title">⚠️ MÃ MỚI PHÁT HIỆN</div>
            </div>
        </div>

        <!-- Cảnh báo sản phẩm mới (Mục 5) -->
        <div id="new-items-alert" style="display: none; margin-bottom: 24px;">
            <div class="alert alert-danger" style="margin-bottom: 12px;">
                <i class="fa-solid fa-bell"></i>
                <span><strong>PHÁT HIỆN SẢN PHẨM CHƯA CÓ TRONG HỆ THỐNG:</strong> Vui lòng chọn cách xử lý cho các mã bên dưới:</span>
            </div>
            <div id="new-items-container"></div>
        </div>

        <!-- Bảng sản phẩm cần kiểm kê -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fa-solid fa-clipboard-check" style="color: var(--success);"></i>
                    Danh Sách Sản Phẩm Được Lọc Để Kiểm Kê
                </h3>
            </div>
            <div class="card-body" style="padding: 0;">
                <div class="table-responsive" style="max-height: 350px; overflow-y: auto;">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Mã SP</th>
                                <th>Tên Sản Phẩm</th>
                                <th>Nhóm</th>
                                <th style="text-align: right;">Số Bán</th>
                                <th style="text-align: center;">Trạng Thái Lọc</th>
                            </tr>
                        </thead>
                        <tbody id="tbody-kiem-ke"></tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Bảng sản phẩm tự động bỏ qua (Collapsible) -->
        <div class="card">
            <div class="card-header" style="cursor: pointer;" onclick="document.getElementById('wrapper-bo-qua').classList.toggle('hidden');">
                <h3 class="card-title" style="font-size: 14px; color: var(--text-secondary);">
                    <i class="fa-solid fa-eye-slash"></i>
                    Danh Sách Sản Phẩm Tự Động Bỏ Qua (Bấm để xem/ẩn)
                </h3>
                <i class="fa-solid fa-chevron-down"></i>
            </div>
            <div id="wrapper-bo-qua" class="card-body hidden" style="padding: 0; display: none;">
                <div class="table-responsive" style="max-height: 250px; overflow-y: auto;">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Mã SP</th>
                                <th>Tên Sản Phẩm</th>
                                <th>Nhóm</th>
                                <th style="text-align: right;">Số Bán</th>
                                <th style="text-align: center;">Hành Động</th>
                            </tr>
                        </thead>
                        <tbody id="tbody-bo-qua"></tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Thanh điều khiển lưu dữ liệu -->
        <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 24px; padding: 16px; background: #fff; border-radius: var(--border-radius); border: 1px solid var(--border-color);">
            <button type="button" class="btn btn-secondary" onclick="resetUpload()">
                <i class="fa-solid fa-arrow-rotate-left"></i> Chọn File Khác
            </button>
            <button type="button" class="btn btn-success btn-lg" onclick="commitSave(false)">
                <i class="fa-solid fa-floppy-disk"></i> Xác Nhận Lưu Số Bán Vào Cơ Sở Dữ Liệu
            </button>
        </div>
    </div>
</div>

<script>
document.querySelector('#wrapper-bo-qua').parentElement.querySelector('.card-header').addEventListener('click', function() {
    const el = document.getElementById('wrapper-bo-qua');
    el.style.display = (el.style.display === 'none' || el.style.display === '') ? 'block' : 'none';
});
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
