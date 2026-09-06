<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';
requireLogin();

$pageTitle = "Quản Lý Nhập Hàng";
$db = getDB();

$thang = $_GET['thang'] ?? date('Y-m');

$stmt = $db->prepare("
    SELECT nh.*, sp.ma_sp, sp.ten_sp, sp.don_vi_tinh, sp.nhom_sp, u.ho_ten AS nguoi_nhap
    FROM nhap_hang nh
    JOIN san_pham sp ON sp.id = nh.san_pham_id
    LEFT JOIN users u ON u.id = nh.nguoi_nhap_id
    WHERE nh.ngay_nhap LIKE :thang
    ORDER BY nh.ngay_nhap DESC, nh.id DESC
");
$stmt->execute(['thang' => "$thang%"]);
$nhapList = $stmt->fetchAll();

include __DIR__ . '/../includes/header.php';
?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; flex-wrap: wrap; gap: 12px;">
    <form method="GET" action="" style="display: flex; align-items: center; gap: 10px;">
        <label class="form-label" style="margin: 0; font-weight: 600;">Xem theo tháng:</label>
        <input type="month" name="thang" class="form-control" value="<?= htmlspecialchars($thang) ?>" onchange="this.form.submit()" style="width: 170px;">
        <button type="submit" class="btn btn-secondary btn-sm">Xem</button>
    </form>

    <a href="<?= BASE_URL ?>/nhaphang/them.php" class="btn btn-success">
        <i class="fa-solid fa-plus"></i> Nhập Hàng Mới
    </a>
</div>

<div class="card">
    <div class="card-header">
        <h2 class="card-title">
            <i class="fa-solid fa-truck-ramp-box" style="color: var(--success);"></i>
            Danh Sách Phiếu Nhập Hàng Tháng <?= date('m/Y', strtotime($thang . '-01')) ?>
        </h2>
    </div>
    <div class="card-body" style="padding: 0;">
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Ngày Nhập</th>
                        <th>Mã SP</th>
                        <th>Tên Sản Phẩm</th>
                        <th>Nhóm</th>
                        <th style="text-align: right;">Số Lượng Nhập</th>
                        <th style="text-align: center;">ĐVT</th>
                        <th>Nhà Cung Cấp</th>
                        <th>Ghi Chú</th>
                        <th>Người Nhập</th>
                        <th style="text-align: center; width: 80px;">Xóa</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($nhapList)): ?>
                        <tr>
                            <td colspan="10" style="text-align: center; padding: 30px; color: var(--text-muted);">
                                Chưa có phát sinh nhập hàng nào trong tháng này.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($nhapList as $item): ?>
                            <tr>
                                <td><strong><?= formatDate($item['ngay_nhap']) ?></strong></td>
                                <td><strong style="color: var(--primary);"><?= htmlspecialchars($item['ma_sp']) ?></strong></td>
                                <td><?= htmlspecialchars($item['ten_sp']) ?></td>
                                <td><span class="badge badge-secondary"><?= htmlspecialchars($item['nhom_sp']) ?></span></td>
                                <td style="text-align: right; font-weight: 700; color: var(--success); font-size: 15px;">
                                    +<?= formatNumber($item['so_luong']) ?>
                                </td>
                                <td style="text-align: center;"><?= htmlspecialchars($item['don_vi_tinh']) ?></td>
                                <td><?= htmlspecialchars($item['nha_cung_cap'] ?? '-') ?></td>
                                <td style="font-size: 12px; color: var(--text-muted);"><?= htmlspecialchars($item['ghi_chu'] ?? '-') ?></td>
                                <td style="font-size: 12px;"><?= htmlspecialchars($item['nguoi_nhap'] ?? 'Admin') ?></td>
                                <td style="text-align: center;">
                                    <a href="<?= BASE_URL ?>/nhaphang/xoa.php?id=<?= $item['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Bạn có chắc muốn xóa phiếu nhập hàng này? Tồn kho lý thuyết sẽ được tự động tính toán lại.');" title="Xóa phiếu">
                                        <i class="fa-solid fa-trash"></i>
                                    </a>
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
