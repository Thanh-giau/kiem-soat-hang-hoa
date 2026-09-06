<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';
requireLogin();

$pageTitle = "Báo Cáo Tổng Hợp Theo Thời Gian";
$db = getDB();

$fromDate = $_GET['from'] ?? date('Y-m-01');
$toDate = $_GET['to'] ?? date('Y-m-d');
$sanPhamId = (int)($_GET['sp_id'] ?? 0);

$params = [
    'from' => $fromDate,
    'to' => $toDate
];

$spFilterSql = "";
if ($sanPhamId > 0) {
    $spFilterSql = "AND sp.id = :sp_id";
    $params['sp_id'] = $sanPhamId;
}

$sql = "
    SELECT 
        sp.id AS san_pham_id,
        sp.ma_sp,
        sp.ten_sp,
        sp.nhom_sp,
        sp.don_vi_tinh,
        sp.can_kiem_ke,
        COALESCE(nh.tong_nhap, 0) AS tong_nhap,
        COALESCE(muon.tong_muon, 0) AS tong_muon,
        COALESCE(sb.tong_ban, 0) AS tong_ban,
        COALESCE(huy.tong_huy, 0) AS tong_huy,
        COALESCE(chomuon.tong_cho_muon, 0) AS tong_cho_muon
    FROM san_pham sp
    LEFT JOIN (
        SELECT san_pham_id, SUM(so_luong) AS tong_nhap 
        FROM nhap_hang 
        WHERE ngay_nhap BETWEEN :from AND :to
        GROUP BY san_pham_id
    ) nh ON nh.san_pham_id = sp.id
    LEFT JOIN (
        SELECT san_pham_id, SUM(so_luong) AS tong_muon 
        FROM dieu_chinh_kho 
        WHERE loai = 'muon' AND ngay_dieu_chinh BETWEEN :from AND :to
        GROUP BY san_pham_id
    ) muon ON muon.san_pham_id = sp.id
    LEFT JOIN (
        SELECT san_pham_id, SUM(so_luong) AS tong_ban 
        FROM so_ban 
        WHERE ngay_ban BETWEEN :from AND :to
        GROUP BY san_pham_id
    ) sb ON sb.san_pham_id = sp.id
    LEFT JOIN (
        SELECT san_pham_id, SUM(so_luong) AS tong_huy 
        FROM dieu_chinh_kho 
        WHERE loai = 'huy' AND ngay_dieu_chinh BETWEEN :from AND :to
        GROUP BY san_pham_id
    ) huy ON huy.san_pham_id = sp.id
    LEFT JOIN (
        SELECT san_pham_id, SUM(so_luong) AS tong_cho_muon 
        FROM dieu_chinh_kho 
        WHERE loai = 'cho_muon' AND ngay_dieu_chinh BETWEEN :from AND :to
        GROUP BY san_pham_id
    ) chomuon ON chomuon.san_pham_id = sp.id
    WHERE sp.trang_thai = 1 $spFilterSql
    ORDER BY sp.can_kiem_ke DESC, sp.nhom_sp ASC, sp.ma_sp ASC
";

$stmt = $db->prepare($sql);
$stmt->execute($params);
$reportList = $stmt->fetchAll();

// Danh sách sản phẩm để lọc
$spList = $db->query("SELECT id, ma_sp, ten_sp FROM san_pham WHERE trang_thai = 1 ORDER BY ma_sp ASC")->fetchAll();

include __DIR__ . '/../includes/header.php';
?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; flex-wrap: wrap; gap: 12px;">
    <form method="GET" action="" style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
        <div style="display: flex; align-items: center; gap: 6px;">
            <label class="form-label" style="margin: 0; font-weight: 600;">Từ ngày:</label>
            <input type="date" name="from" class="form-control" value="<?= htmlspecialchars($fromDate) ?>" style="width: 150px;">
        </div>

        <div style="display: flex; align-items: center; gap: 6px;">
            <label class="form-label" style="margin: 0; font-weight: 600;">Đến ngày:</label>
            <input type="date" name="to" class="form-control" value="<?= htmlspecialchars($toDate) ?>" style="width: 150px;">
        </div>

        <div style="display: flex; align-items: center; gap: 6px;">
            <label class="form-label" style="margin: 0; font-weight: 600;">Sản phẩm:</label>
            <select name="sp_id" class="form-control" style="width: 200px;">
                <option value="">-- Tất cả sản phẩm --</option>
                <?php foreach ($spList as $p): ?>
                    <option value="<?= $p['id'] ?>" <?= $sanPhamId == $p['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($p['ma_sp']) ?> - <?= htmlspecialchars($p['ten_sp']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <button type="submit" class="btn btn-primary">
            <i class="fa-solid fa-filter"></i> Xem Báo Cáo
        </button>
    </form>
</div>

<div class="card">
    <div class="card-header">
        <h2 class="card-title">
            <i class="fa-solid fa-chart-column" style="color: var(--primary);"></i>
            Tổng Hợp Biến Động Kho Từ <?= formatDate($fromDate) ?> Đến <?= formatDate($toDate) ?>
        </h2>
    </div>
    <div class="card-body" style="padding: 0;">
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Mã SP</th>
                        <th>Tên Sản Phẩm</th>
                        <th>Nhóm</th>
                        <th style="text-align: center;">ĐVT</th>
                        <th style="text-align: right; background: #ecfdf5; color: #065f46;">Tổng Nhập (+)</th>
                        <th style="text-align: right; background: #ecfdf5; color: #065f46;">Tổng Mượn (+)</th>
                        <th style="text-align: right; background: #fef2f2; color: #991b1b;">Tổng Bán (-)</th>
                        <th style="text-align: right; background: #fef2f2; color: #991b1b;">Tổng Hủy (-)</th>
                        <th style="text-align: right; background: #fef2f2; color: #991b1b;">Tổng Cho Mượn (-)</th>
                        <th style="text-align: right; background: #eef2ff; color: var(--primary); font-weight: 700;">Biến Động Ròng</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($reportList)): ?>
                        <tr><td colspan="10" style="text-align: center; padding: 30px; color: var(--text-muted);">Không có dữ liệu trong khoảng thời gian đã chọn.</td></tr>
                    <?php else: ?>
                        <?php foreach ($reportList as $r): 
                            // Biến động ròng trong khoảng = Nhập + Mượn - Bán - Hủy - Cho mượn
                            $bienDongRong = (float)$r['tong_nhap'] + (float)$r['tong_muon'] - (float)$r['tong_ban'] - (float)$r['tong_huy'] - (float)$r['tong_cho_muon'];
                        ?>
                            <tr>
                                <td><strong><?= htmlspecialchars($r['ma_sp']) ?></strong></td>
                                <td><?= htmlspecialchars($r['ten_sp']) ?></td>
                                <td><span class="badge badge-secondary"><?= htmlspecialchars($r['nhom_sp']) ?></span></td>
                                <td style="text-align: center;"><?= htmlspecialchars($r['don_vi_tinh']) ?></td>
                                <td style="text-align: right; background: #ecfdf5; font-weight: 600; color: #065f46;"><?= formatNumber($r['tong_nhap']) ?></td>
                                <td style="text-align: right; background: #ecfdf5; font-weight: 600; color: #065f46;"><?= formatNumber($r['tong_muon']) ?></td>
                                <td style="text-align: right; background: #fef2f2; font-weight: 600; color: #991b1b;"><?= formatNumber($r['tong_ban']) ?></td>
                                <td style="text-align: right; background: #fef2f2; font-weight: 600; color: #991b1b;"><?= formatNumber($r['tong_huy']) ?></td>
                                <td style="text-align: right; background: #fef2f2; font-weight: 600; color: #991b1b;"><?= formatNumber($r['tong_cho_muon']) ?></td>
                                <td style="text-align: right; background: #eef2ff; font-weight: 700; font-size: 15px; color: <?= $bienDongRong >= 0 ? 'var(--success)' : 'var(--danger)' ?>;">
                                    <?= ($bienDongRong > 0 ? '+' : '') . formatNumber($bienDongRong) ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
