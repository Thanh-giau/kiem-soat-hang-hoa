<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';
requireLogin();

$pageTitle = "Quản Lý Tồn Đầu Kỳ";
$db = getDB();

$ky = $_GET['ky'] ?? date('Y-m');

$stmt = $db->prepare("
    SELECT 
        sp.id AS san_pham_id,
        sp.ma_sp,
        sp.ten_sp,
        sp.nhom_sp,
        sp.don_vi_tinh,
        sp.can_kiem_ke,
        COALESCE(td.so_luong, 0) AS ton_dau,
        td.ghi_chu,
        td.updated_at
    FROM san_pham sp
    LEFT JOIN ton_dau td ON td.san_pham_id = sp.id AND td.ky_kiem_ke = :ky
    WHERE sp.trang_thai = 1
    ORDER BY sp.can_kiem_ke DESC, sp.nhom_sp ASC, sp.ma_sp ASC
");
$stmt->execute(['ky' => $ky]);
$danhSachTonDau = $stmt->fetchAll();

include __DIR__ . '/../includes/header.php';
?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; flex-wrap: wrap; gap: 12px;">
    <form method="GET" action="" style="display: flex; align-items: center; gap: 10px;">
        <label class="form-label" style="margin: 0; font-weight: 600;">Kỳ kiểm kê (Tháng):</label>
        <input type="month" name="ky" class="form-control" value="<?= htmlspecialchars($ky) ?>" onchange="this.form.submit()" style="width: 170px;">
        <button type="submit" class="btn btn-secondary btn-sm">Xem</button>
    </form>

    <div style="display: flex; gap: 10px;">
        <a href="<?= BASE_URL ?>/ton_dau/nhap_ton.php?ky=<?= htmlspecialchars($ky) ?>" class="btn btn-primary">
            <i class="fa-solid fa-pen-to-square"></i> Cập Nhật Tồn Đầu Kỳ Này
        </a>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h2 class="card-title">
            <i class="fa-solid fa-cubes" style="color: var(--primary);"></i>
            Bảng Tồn Đầu Kỳ Tháng <?= date('m/Y', strtotime($ky . '-01')) ?>
        </h2>
    </div>
    <div class="card-body" style="padding: 0;">
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Mã SP</th>
                        <th>Tên Sản Phẩm</th>
                        <th>Nhóm Sản Phẩm</th>
                        <th style="text-align: center;">ĐVT</th>
                        <th style="text-align: right;">Số Lượng Tồn Đầu</th>
                        <th style="text-align: center;">Cần Kiểm Kê?</th>
                        <th>Ghi Chú</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($danhSachTonDau)): ?>
                        <tr>
                            <td colspan="7" style="text-align: center; padding: 30px; color: var(--text-muted);">
                                Chưa có sản phẩm nào trong hệ thống.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($danhSachTonDau as $item): ?>
                            <tr>
                                <td><strong><?= htmlspecialchars($item['ma_sp']) ?></strong></td>
                                <td><?= htmlspecialchars($item['ten_sp']) ?></td>
                                <td><span class="badge badge-secondary"><?= htmlspecialchars($item['nhom_sp']) ?></span></td>
                                <td style="text-align: center;"><?= htmlspecialchars($item['don_vi_tinh']) ?></td>
                                <td style="text-align: right; font-weight: 700; font-size: 15px; color: var(--primary);">
                                    <?= formatNumber($item['ton_dau']) ?>
                                </td>
                                <td style="text-align: center;">
                                    <?php if ($item['can_kiem_ke'] == 1): ?>
                                        <span class="badge badge-success"><i class="fa-solid fa-check"></i> Cần kiểm kê</span>
                                    <?php else: ?>
                                        <span class="badge badge-secondary"><i class="fa-solid fa-xmark"></i> Bỏ qua</span>
                                    <?php endif; ?>
                                </td>
                                <td style="font-size: 12px; color: var(--text-muted);">
                                    <?= htmlspecialchars($item['ghi_chu'] ?? '-') ?>
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
