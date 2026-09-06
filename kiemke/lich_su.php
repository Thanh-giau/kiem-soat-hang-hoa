<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';
requireLogin();

$pageTitle = "Lịch Sử Các Đợt Kiểm Kê";
$db = getDB();

$stmt = $db->query("
    SELECT 
        kk.ngay_kiem_ke,
        COUNT(*) AS tong_san_pham,
        SUM(CASE WHEN kk.chenh_lech = 0 THEN 1 ELSE 0 END) AS so_khop,
        SUM(CASE WHEN kk.chenh_lech < 0 THEN 1 ELSE 0 END) AS so_thieu,
        SUM(CASE WHEN kk.chenh_lech > 0 THEN 1 ELSE 0 END) AS so_du,
        MAX(kk.updated_at) AS thoi_gian_cap_nhat,
        u.ho_ten AS nguoi_kiem_ke
    FROM kiem_ke kk
    LEFT JOIN users u ON u.id = kk.nguoi_kiem_ke_id
    GROUP BY kk.ngay_kiem_ke
    ORDER BY kk.ngay_kiem_ke DESC
");
$history = $stmt->fetchAll();

include __DIR__ . '/../includes/header.php';
?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
    <h2 style="font-size: 18px; font-weight: 700;">Lịch Sử Kiểm Kê Kho Theo Từng Ngày</h2>
    <a href="<?= BASE_URL ?>/kiemke/kiem_ke.php" class="btn btn-success">
        <i class="fa-solid fa-plus"></i> Kiểm Kê Hôm Nay
    </a>
</div>

<div class="card">
    <div class="card-body" style="padding: 0;">
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Ngày Kiểm Kê</th>
                        <th style="text-align: right;">Tổng Mặt Hàng</th>
                        <th style="text-align: center;">Khớp 🟢</th>
                        <th style="text-align: center;">Thiếu Hàng 🔴</th>
                        <th style="text-align: center;">Dư Hàng 🟠</th>
                        <th>Người Kiểm Kê</th>
                        <th>Cập Nhật Lần Cuối</th>
                        <th style="text-align: center; width: 140px;">Hành Động</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($history)): ?>
                        <tr>
                            <td colspan="8" style="text-align: center; padding: 30px; color: var(--text-muted);">
                                Chưa có đợt kiểm kê nào được lưu trong hệ thống.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($history as $h): ?>
                            <tr>
                                <td>
                                    <strong style="color: var(--primary); font-size: 15px;">
                                        <i class="fa-regular fa-calendar-check"></i> <?= formatDate($h['ngay_kiem_ke']) ?>
                                    </strong>
                                </td>
                                <td style="text-align: right; font-weight: 700;"><?= formatNumber($h['tong_san_pham']) ?></td>
                                <td style="text-align: center;">
                                    <span class="badge badge-success"><?= $h['so_khop'] ?></span>
                                </td>
                                <td style="text-align: center;">
                                    <?php if ($h['so_thieu'] > 0): ?>
                                        <span class="badge badge-danger"><?= $h['so_thieu'] ?></span>
                                    <?php else: ?>
                                        <span style="color: var(--text-muted);">-</span>
                                    <?php endif; ?>
                                </td>
                                <td style="text-align: center;">
                                    <?php if ($h['so_du'] > 0): ?>
                                        <span class="badge badge-warning"><?= $h['so_du'] ?></span>
                                    <?php else: ?>
                                        <span style="color: var(--text-muted);">-</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= htmlspecialchars($h['nguoi_kiem_ke'] ?? 'Admin') ?></td>
                                <td style="font-size: 13px; color: var(--text-secondary);"><?= $h['thoi_gian_cap_nhat'] ? date('H:i d/m/Y', strtotime($h['thoi_gian_cap_nhat'])) : '-' ?></td>
                                <td style="text-align: center;">
                                    <div style="display: inline-flex; gap: 6px;">
                                        <a href="<?= BASE_URL ?>/baocao/chenh_lech.php?ngay=<?= $h['ngay_kiem_ke'] ?>" class="btn btn-secondary btn-sm" title="Xem báo cáo chênh lệch">
                                            <i class="fa-solid fa-chart-simple"></i> Báo cáo
                                        </a>
                                        <a href="<?= BASE_URL ?>/kiemke/kiem_ke.php?ngay=<?= $h['ngay_kiem_ke'] ?>" class="btn btn-primary btn-sm" title="Chỉnh sửa kiểm kê">
                                            <i class="fa-solid fa-pen"></i> Sửa
                                        </a>
                                    </div>
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
