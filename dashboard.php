<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth.php';
requireLogin();

$pageTitle = "Bảng Điều Khiển Tổng Quan";
$extraCss = ['dashboard.css'];

$db = getDB();
$today = date('Y-m-d');
$startOfMonth = date('Y-m-01');

// 1. Tổng sản phẩm đang hoạt động & cần kiểm kê
$stmtSP = $db->query("
    SELECT 
        COUNT(*) AS tong_sp,
        SUM(CASE WHEN can_kiem_ke = 1 THEN 1 ELSE 0 END) AS tong_kiem_ke
    FROM san_pham 
    WHERE trang_thai = 1
");
$statsSP = $stmtSP->fetch();

// 2. Tổng số bán hôm nay & trong tháng
$stmtSB = $db->prepare("
    SELECT 
        SUM(CASE WHEN ngay_ban = :today THEN so_luong ELSE 0 END) AS ban_hom_nay,
        SUM(CASE WHEN ngay_ban BETWEEN :start_month AND :today THEN so_luong ELSE 0 END) AS ban_trong_thang
    FROM so_ban
");
$stmtSB->execute(['today' => $today, 'start_month' => $startOfMonth]);
$statsSB = $stmtSB->fetch();

// 3. Tổng hàng nhập trong tháng
$stmtNH = $db->prepare("
    SELECT SUM(so_luong) AS tong_nhap_thang 
    FROM nhap_hang 
    WHERE ngay_nhap BETWEEN :start_month AND :today
");
$stmtNH->execute(['start_month' => $startOfMonth, 'today' => $today]);
$statsNH = $stmtNH->fetch();

// 4. Lấy chi tiết tồn kho và kiểm kê hôm nay
$danhSachTon = layBaoCaoTonKhoChiTiet($today, null, true);

$tongTonLyThuyet = 0;
$soSanPhamChenhLech = 0;
$danhSachChenhLech = [];
$spThieuNhieuNhat = null;
$spDuNhieuNhat = null;

$maxThieu = 0;
$maxDu = 0;

foreach ($danhSachTon as $item) {
    $tongTonLyThuyet += (float)$item['ton_ly_thuyet'];
    
    if ($item['ton_thuc_te'] !== null) {
        $chenh = (float)$item['chenh_lech'];
        if ($chenh != 0) {
            $soSanPhamChenhLech++;
            $danhSachChenhLech[] = $item;

            if ($chenh < $maxThieu) {
                $maxThieu = $chenh;
                $spThieuNhieuNhat = $item;
            }
            if ($chenh > $maxDu) {
                $maxDu = $chenh;
                $spDuNhieuNhat = $item;
            }
        }
    }
}

include __DIR__ . '/includes/header.php';
?>

<!-- Quick Action Shortcuts -->
<div class="quick-actions-bar">
    <a href="<?= BASE_URL ?>/soban/upload.php" class="btn btn-primary">
        <i class="fa-solid fa-cloud-arrow-up"></i> Upload Số Bán Hôm Nay
    </a>
    <a href="<?= BASE_URL ?>/kiemke/kiem_ke.php" class="btn btn-success">
        <i class="fa-solid fa-clipboard-check"></i> Đi Kiểm Kê Thực Tế
    </a>
    <a href="<?= BASE_URL ?>/nhaphang/them.php" class="btn btn-secondary">
        <i class="fa-solid fa-plus"></i> Nhập Hàng Mới
    </a>
    <a href="<?= BASE_URL ?>/dieuchinh/them.php" class="btn btn-secondary">
        <i class="fa-solid fa-right-left"></i> Nhập Hủy / Mượn
    </a>
    <a href="<?= BASE_URL ?>/baocao/chenh_lech.php" class="btn btn-warning">
        <i class="fa-solid fa-scale-unbalanced"></i> Báo Cáo Chênh Lệch
    </a>
</div>

<!-- 7 Main KPI Cards -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon primary">
            <i class="fa-solid fa-boxes-stacked"></i>
        </div>
        <div class="stat-info">
            <div class="stat-value"><?= formatNumber($statsSP['tong_sp'] ?? 0) ?></div>
            <div class="stat-label">Sản phẩm hoạt động</div>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon success">
            <i class="fa-solid fa-list-check"></i>
        </div>
        <div class="stat-info">
            <div class="stat-value"><?= formatNumber($statsSP['tong_kiem_ke'] ?? 0) ?></div>
            <div class="stat-label">Sản phẩm cần kiểm kê</div>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon info">
            <i class="fa-solid fa-chart-line"></i>
        </div>
        <div class="stat-info">
            <div class="stat-value"><?= formatNumber($statsSB['ban_hom_nay'] ?? 0) ?></div>
            <div class="stat-label">Số bán hôm nay</div>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon primary">
            <i class="fa-solid fa-calendar-check"></i>
        </div>
        <div class="stat-info">
            <div class="stat-value"><?= formatNumber($statsSB['ban_trong_thang'] ?? 0) ?></div>
            <div class="stat-label">Số bán trong tháng</div>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon warning">
            <i class="fa-solid fa-truck-ramp-box"></i>
        </div>
        <div class="stat-info">
            <div class="stat-value"><?= formatNumber($statsNH['tong_nhap_thang'] ?? 0) ?></div>
            <div class="stat-label">Tổng hàng nhập (tháng)</div>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon info">
            <i class="fa-solid fa-calculator"></i>
        </div>
        <div class="stat-info">
            <div class="stat-value"><?= formatNumber($tongTonLyThuyet) ?></div>
            <div class="stat-label">Tổng tồn lý thuyết</div>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon danger">
            <i class="fa-solid fa-triangle-exclamation"></i>
        </div>
        <div class="stat-info">
            <div class="stat-value"><?= formatNumber($soSanPhamChenhLech) ?></div>
            <div class="stat-label">SP đang bị chênh lệch</div>
        </div>
    </div>
</div>

<!-- Highlight Section: Top Thiếu & Top Dư -->
<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 24px;">
    <div class="card" style="margin-bottom: 0; border-left: 4px solid var(--danger);">
        <div class="card-body" style="display: flex; align-items: center; justify-content: space-between;">
            <div>
                <div style="font-size: 13px; font-weight: 700; color: var(--danger); text-transform: uppercase; margin-bottom: 4px;">
                    <i class="fa-solid fa-arrow-trend-down"></i> Sản Phẩm Thiếu Nhiều Nhất
                </div>
                <?php if ($spThieuNhieuNhat): ?>
                    <h3 style="font-size: 17px; margin-bottom: 2px;"><?= htmlspecialchars($spThieuNhieuNhat['ten_sp']) ?> (<?= htmlspecialchars($spThieuNhieuNhat['ma_sp']) ?>)</h3>
                    <div style="font-size: 13px; color: var(--text-secondary);">
                        Lý thuyết: <strong><?= formatNumber($spThieuNhieuNhat['ton_ly_thuyet']) ?></strong> | Thực tế: <strong><?= formatNumber($spThieuNhieuNhat['ton_thuc_te']) ?></strong>
                    </div>
                <?php else: ?>
                    <div style="font-size: 14px; color: var(--text-muted);">Chưa phát hiện sản phẩm thiếu hôm nay</div>
                <?php endif; ?>
            </div>
            <?php if ($spThieuNhieuNhat): ?>
                <div class="badge badge-danger" style="font-size: 16px; padding: 8px 14px;">
                    <?= formatNumber($spThieuNhieuNhat['chenh_lech']) ?> <?= htmlspecialchars($spThieuNhieuNhat['don_vi_tinh']) ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="card" style="margin-bottom: 0; border-left: 4px solid var(--warning);">
        <div class="card-body" style="display: flex; align-items: center; justify-content: space-between;">
            <div>
                <div style="font-size: 13px; font-weight: 700; color: var(--warning); text-transform: uppercase; margin-bottom: 4px;">
                    <i class="fa-solid fa-arrow-trend-up"></i> Sản Phẩm Dư Nhiều Nhất
                </div>
                <?php if ($spDuNhieuNhat): ?>
                    <h3 style="font-size: 17px; margin-bottom: 2px;"><?= htmlspecialchars($spDuNhieuNhat['ten_sp']) ?> (<?= htmlspecialchars($spDuNhieuNhat['ma_sp']) ?>)</h3>
                    <div style="font-size: 13px; color: var(--text-secondary);">
                        Lý thuyết: <strong><?= formatNumber($spDuNhieuNhat['ton_ly_thuyet']) ?></strong> | Thực tế: <strong><?= formatNumber($spDuNhieuNhat['ton_thuc_te']) ?></strong>
                    </div>
                <?php else: ?>
                    <div style="font-size: 14px; color: var(--text-muted);">Chưa phát hiện sản phẩm dư hôm nay</div>
                <?php endif; ?>
            </div>
            <?php if ($spDuNhieuNhat): ?>
                <div class="badge badge-warning" style="font-size: 16px; padding: 8px 14px;">
                    +<?= formatNumber($spDuNhieuNhat['chenh_lech']) ?> <?= htmlspecialchars($spDuNhieuNhat['don_vi_tinh']) ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Discrepancy Table -->
<div class="card">
    <div class="card-header">
        <h2 class="card-title">
            <i class="fa-solid fa-triangle-exclamation" style="color: var(--danger);"></i>
            Danh Sách Sản Phẩm Đang Có Chênh Lệch Hôm Nay (<?= formatDate($today) ?>)
        </h2>
        <a href="<?= BASE_URL ?>/baocao/chenh_lech.php" class="btn btn-secondary btn-sm">
            Xem Toàn Bộ Báo Cáo <i class="fa-solid fa-arrow-right"></i>
        </a>
    </div>
    <div class="card-body" style="padding: 0;">
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Mã SP</th>
                        <th>Tên Sản Phẩm</th>
                        <th>Nhóm</th>
                        <th style="text-align: right;">Tồn Lý Thuyết</th>
                        <th style="text-align: right;">Thực Tế</th>
                        <th style="text-align: center;">Chênh Lệch</th>
                        <th style="text-align: center;">Tình Trạng</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($danhSachChenhLech)): ?>
                        <tr>
                            <td colspan="7" style="text-align: center; padding: 30px; color: var(--text-muted);">
                                <i class="fa-regular fa-circle-check" style="font-size: 32px; color: var(--success); margin-bottom: 8px; display: block;"></i>
                                Tuyệt vời! Hiện tại không có sản phẩm nào bị chênh lệch hoặc chưa phát sinh kiểm kê hôm nay.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($danhSachChenhLech as $sp): ?>
                            <tr>
                                <td><strong><?= htmlspecialchars($sp['ma_sp']) ?></strong></td>
                                <td><?= htmlspecialchars($sp['ten_sp']) ?></td>
                                <td><span class="badge badge-secondary"><?= htmlspecialchars($sp['nhom_sp']) ?></span></td>
                                <td style="text-align: right; font-weight: 600;"><?= formatNumber($sp['ton_ly_thuyet']) ?> <?= htmlspecialchars($sp['don_vi_tinh']) ?></td>
                                <td style="text-align: right; font-weight: 700; color: var(--primary);"><?= formatNumber($sp['ton_thuc_te']) ?> <?= htmlspecialchars($sp['don_vi_tinh']) ?></td>
                                <td style="text-align: center;">
                                    <?= renderChenhLechBadge($sp['chenh_lech']) ?>
                                </td>
                                <td style="text-align: center;">
                                    <?php if ($sp['chenh_lech'] < 0): ?>
                                        <span class="badge badge-danger">🔴 Thiếu Hàng</span>
                                    <?php else: ?>
                                        <span class="badge badge-warning">🟠 Dư Hàng</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
