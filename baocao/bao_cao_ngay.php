<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';
requireLogin();

$pageTitle = "Báo Cáo Hoạt Động Theo Ngày";
$db = getDB();

$ngay = $_GET['ngay'] ?? date('Y-m-d');

// 1. Số bán trong ngày
$stmtBan = $db->prepare("
    SELECT sb.*, sp.ma_sp, sp.ten_sp, sp.don_vi_tinh, sp.nhom_sp
    FROM so_ban sb
    JOIN san_pham sp ON sp.id = sb.san_pham_id
    WHERE sb.ngay_ban = :ngay
    ORDER BY sp.nhom_sp, sp.ma_sp
");
$stmtBan->execute(['ngay' => $ngay]);
$listBan = $stmtBan->fetchAll();

// 2. Nhập hàng trong ngày
$stmtNhap = $db->prepare("
    SELECT nh.*, sp.ma_sp, sp.ten_sp, sp.don_vi_tinh, sp.nhom_sp, u.ho_ten AS nguoi_nhap
    FROM nhap_hang nh
    JOIN san_pham sp ON sp.id = nh.san_pham_id
    LEFT JOIN users u ON u.id = nh.nguoi_nhap_id
    WHERE nh.ngay_nhap = :ngay
    ORDER BY nh.id DESC
");
$stmtNhap->execute(['ngay' => $ngay]);
$listNhap = $stmtNhap->fetchAll();

// 3. Điều chỉnh trong ngày
$stmtDC = $db->prepare("
    SELECT dc.*, sp.ma_sp, sp.ten_sp, sp.don_vi_tinh, sp.nhom_sp, u.ho_ten AS nguoi_tao
    FROM dieu_chinh_kho dc
    JOIN san_pham sp ON sp.id = dc.san_pham_id
    LEFT JOIN users u ON u.id = dc.nguoi_tao_id
    WHERE dc.ngay_dieu_chinh = :ngay
    ORDER BY dc.id DESC
");
$stmtDC->execute(['ngay' => $ngay]);
$listDC = $stmtDC->fetchAll();

include __DIR__ . '/../includes/header.php';
?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; flex-wrap: wrap; gap: 12px;">
    <form method="GET" action="" style="display: flex; align-items: center; gap: 10px;">
        <label class="form-label" style="margin: 0; font-weight: 600;">Xem chi tiết ngày:</label>
        <input type="date" name="ngay" class="form-control" value="<?= htmlspecialchars($ngay) ?>" onchange="this.form.submit()" style="width: 170px;">
        <button type="submit" class="btn btn-secondary btn-sm">Xem</button>
    </form>

    <div style="display: flex; gap: 10px;">
        <a href="<?= BASE_URL ?>/baocao/chenh_lech.php?ngay=<?= urlencode($ngay) ?>" class="btn btn-primary">
            <i class="fa-solid fa-scale-unbalanced"></i> Xem Đối Chiếu Chênh Lệch Ngày Này
        </a>
    </div>
</div>

<!-- Thống kê tổng hợp ngày -->
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; margin-bottom: 24px;">
    <div class="card" style="margin-bottom: 0; padding: 16px; text-align: center;">
        <div style="font-size: 12px; color: var(--text-muted); font-weight: 600;">SỐ BÁN TRONG NGÀY</div>
        <div style="font-size: 24px; font-weight: 700; color: var(--primary);">
            <?= formatNumber(array_sum(array_column($listBan, 'so_luong'))) ?>
        </div>
        <div style="font-size: 12px; color: var(--text-secondary);"><?= count($listBan) ?> mặt hàng bán</div>
    </div>

    <div class="card" style="margin-bottom: 0; padding: 16px; text-align: center;">
        <div style="font-size: 12px; color: var(--text-muted); font-weight: 600;">HÀNG NHẬP TRONG NGÀY</div>
        <div style="font-size: 24px; font-weight: 700; color: var(--success);">
            <?= formatNumber(array_sum(array_column($listNhap, 'so_luong'))) ?>
        </div>
        <div style="font-size: 12px; color: var(--text-secondary);"><?= count($listNhap) ?> lượt nhập</div>
    </div>

    <div class="card" style="margin-bottom: 0; padding: 16px; text-align: center;">
        <div style="font-size: 12px; color: var(--text-muted); font-weight: 600;">PHÁT SINH ĐIỀU CHỈNH</div>
        <div style="font-size: 24px; font-weight: 700; color: var(--warning);">
            <?= count($listDC) ?>
        </div>
        <div style="font-size: 12px; color: var(--text-secondary);">Hủy / Mượn / Cho mượn</div>
    </div>
</div>

<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
    <!-- Bảng số bán -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title"><i class="fa-solid fa-cart-shopping" style="color: var(--primary);"></i> Danh Sách Số Bán Ngày <?= formatDate($ngay) ?></h3>
        </div>
        <div class="card-body" style="padding: 0;">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Mã SP</th>
                            <th>Tên Sản Phẩm</th>
                            <th style="text-align: right;">Số Lượng Bán</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($listBan)): ?>
                            <tr><td colspan="3" style="text-align:center; color: var(--text-muted);">Không có dữ liệu số bán cho ngày này.</td></tr>
                        <?php else: ?>
                            <?php foreach ($listBan as $b): ?>
                                <tr>
                                    <td><strong><?= htmlspecialchars($b['ma_sp']) ?></strong></td>
                                    <td><?= htmlspecialchars($b['ten_sp']) ?></td>
                                    <td style="text-align: right; font-weight: 700; color: var(--danger); font-size: 15px;"><?= formatNumber($b['so_luong']) ?> <?= htmlspecialchars($b['don_vi_tinh']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Bảng nhập hàng & điều chỉnh -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title"><i class="fa-solid fa-arrows-rotate" style="color: var(--success);"></i> Phát Sinh Nhập & Điều Chỉnh</h3>
        </div>
        <div class="card-body" style="padding: 0;">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Loại</th>
                            <th>Mã SP</th>
                            <th>Tên Sản Phẩm</th>
                            <th style="text-align: right;">Số Lượng</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $allMoves = [];
                        foreach ($listNhap as $n) {
                            $allMoves[] = [
                                'loai' => 'Nhập hàng',
                                'color' => 'success',
                                'ma_sp' => $n['ma_sp'],
                                'ten_sp' => $n['ten_sp'],
                                'so_luong' => '+' . formatNumber($n['so_luong']) . ' ' . $n['don_vi_tinh']
                            ];
                        }
                        foreach ($listDC as $dc) {
                            $loaiName = $dc['loai'] === 'muon' ? 'Mượn về' : ($dc['loai'] === 'cho_muon' ? 'Cho mượn' : 'Hủy hàng');
                            $color = $dc['loai'] === 'muon' ? 'success' : 'danger';
                            $sign = $dc['loai'] === 'muon' ? '+' : '-';
                            $allMoves[] = [
                                'loai' => $loaiName,
                                'color' => $color,
                                'ma_sp' => $dc['ma_sp'],
                                'ten_sp' => $dc['ten_sp'],
                                'so_luong' => $sign . formatNumber($dc['so_luong']) . ' ' . $dc['don_vi_tinh']
                            ];
                        }
                        ?>
                        <?php if (empty($allMoves)): ?>
                            <tr><td colspan="4" style="text-align:center; color: var(--text-muted);">Không có phát sinh nhập hoặc điều chỉnh.</td></tr>
                        <?php else: ?>
                            <?php foreach ($allMoves as $m): ?>
                                <tr>
                                    <td><span class="badge badge-<?= $m['color'] ?>"><?= $m['loai'] ?></span></td>
                                    <td><strong><?= htmlspecialchars($m['ma_sp']) ?></strong></td>
                                    <td><?= htmlspecialchars($m['ten_sp']) ?></td>
                                    <td style="text-align: right; font-weight: 700;"><?= $m['so_luong'] ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
