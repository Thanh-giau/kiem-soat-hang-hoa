<?php
$currentScript = $_SERVER['SCRIPT_NAME'] ?? '';
?>
<aside class="sidebar" id="sidebar">
    <div class="sidebar-brand">
        <div class="brand-content">
            <div class="logo-icon">
                <i class="fa-solid fa-boxes-stacked"></i>
            </div>
            <div class="brand-title">KHO HÀNG CỦA THANH GIÀU</div>
        </div>
        <button type="button" class="sidebar-close-btn" id="sidebar-close-btn" aria-label="Đóng menu">
            <i class="fa-solid fa-xmark"></i>
        </button>
    </div>
    
    <ul class="sidebar-menu">
        <li class="menu-category">Tổng Quan</li>
        <li class="menu-item <?= strpos($currentScript, 'dashboard.php') !== false ? 'active' : '' ?>">
            <a href="<?= BASE_URL ?>/dashboard.php">
                <i class="fa-solid fa-chart-pie"></i>
                <span>Bảng Điều Khiển</span>
            </a>
        </li>

        <li class="menu-category">Kiểm Đếm & Số Bán</li>
        <li class="menu-item <?= strpos($currentScript, 'soban/upload.php') !== false ? 'active' : '' ?>">
            <a href="<?= BASE_URL ?>/soban/upload.php">
                <i class="fa-solid fa-cloud-arrow-up"></i>
                <span>Upload Số Bán (Excel)</span>
            </a>
        </li>
        <li class="menu-item <?= strpos($currentScript, 'kiemke/kiem_ke.php') !== false ? 'active' : '' ?>">
            <a href="<?= BASE_URL ?>/kiemke/kiem_ke.php">
                <i class="fa-solid fa-clipboard-check"></i>
                <span>Kiểm Kê Thực Tế</span>
            </a>
        </li>

        <li class="menu-category">Nghiệp Vụ Hàng Ngày</li>
        <li class="menu-item <?= strpos($currentScript, 'nhaphang') !== false ? 'active' : '' ?>">
            <a href="<?= BASE_URL ?>/nhaphang/danh_sach.php">
                <i class="fa-solid fa-truck-ramp-box"></i>
                <span>Nhập Hàng</span>
            </a>
        </li>
        <li class="menu-item <?= strpos($currentScript, 'dieuchinh') !== false ? 'active' : '' ?>">
            <a href="<?= BASE_URL ?>/dieuchinh/danh_sach.php">
                <i class="fa-solid fa-arrows-split-up-and-left"></i>
                <span>Hủy / Mượn / Cho mượn</span>
            </a>
        </li>

        <li class="menu-category">Danh Mục & Tồn Kho</li>
        <li class="menu-item <?= strpos($currentScript, 'sanpham') !== false ? 'active' : '' ?>">
            <a href="<?= BASE_URL ?>/sanpham/danh_sach.php">
                <i class="fa-solid fa-box-open"></i>
                <span>Quản Lý Sản Phẩm</span>
            </a>
        </li>
        <li class="menu-item <?= strpos($currentScript, 'dinhluong') !== false ? 'active' : '' ?>">
            <a href="<?= BASE_URL ?>/dinhluong/danh_sach.php">
                <i class="fa-solid fa-bowl-food"></i>
                <span>Định Lượng Món (BOM)</span>
            </a>
        </li>
        <li class="menu-item <?= strpos($currentScript, 'ton_dau') !== false ? 'active' : '' ?>">
            <a href="<?= BASE_URL ?>/ton_dau/danh_sach.php">
                <i class="fa-solid fa-cubes"></i>
                <span>Tồn Đầu Kỳ</span>
            </a>
        </li>

        <li class="menu-category">Báo Cáo & Thống Kê</li>
        <li class="menu-item <?= strpos($currentScript, 'baocao/chenh_lech.php') !== false ? 'active' : '' ?>">
            <a href="<?= BASE_URL ?>/baocao/chenh_lech.php">
                <i class="fa-solid fa-scale-unbalanced"></i>
                <span>Báo Cáo Chênh Lệch</span>
            </a>
        </li>
        <li class="menu-item <?= strpos($currentScript, 'baocao/bao_cao_ngay.php') !== false ? 'active' : '' ?>">
            <a href="<?= BASE_URL ?>/baocao/bao_cao_ngay.php">
                <i class="fa-solid fa-calendar-day"></i>
                <span>Báo Cáo Theo Ngày</span>
            </a>
        </li>
        <li class="menu-item <?= strpos($currentScript, 'baocao/bao_cao_thang.php') !== false ? 'active' : '' ?>">
            <a href="<?= BASE_URL ?>/baocao/bao_cao_thang.php">
                <i class="fa-solid fa-calendar-days"></i>
                <span>Báo Cáo Tháng / Khoảng</span>
            </a>
        </li>
        <li class="menu-item <?= strpos($currentScript, 'soban/lich_su.php') !== false ? 'active' : '' ?>">
            <a href="<?= BASE_URL ?>/soban/lich_su.php">
                <i class="fa-solid fa-clock-rotate-left"></i>
                <span>Lịch Sử File Upload</span>
            </a>
        </li>

        <?php if (function_exists('isAdmin') && isAdmin()): ?>
        <li class="menu-category">Hệ Thống Máy Chủ</li>
        <li class="menu-item <?= strpos($currentScript, 'hethong/cap_nhat.php') !== false ? 'active' : '' ?>">
            <a href="<?= BASE_URL ?>/hethong/cap_nhat.php">
                <i class="fa-solid fa-cloud-arrow-down" style="color: #6366f1;"></i>
                <span>Cập Nhật Hệ Thống</span>
            </a>
        </li>
        <?php endif; ?>
    </ul>

    <div class="sidebar-footer">
        <div class="user-badge">
            <div class="user-avatar">
                <?= mb_strtoupper(mb_substr($user['ho_ten'] ?? 'AD', 0, 1, 'UTF-8'), 'UTF-8') ?>
            </div>
            <div class="user-info">
                <div class="user-name"><?= htmlspecialchars($user['ho_ten'] ?? 'Quản trị viên') ?></div>
                <div class="user-role"><?= ($user['vai_tro'] ?? '') === 'admin' ? '👑 Quản Trị Viên' : 'Nhân viên' ?></div>
            </div>
        </div>
    </div>
</aside>
