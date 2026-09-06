<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';
requireLogin();

$pageTitle = "Điều Chỉnh Kho: Hủy / Cho Mượn / Mượn";
$db = getDB();

$loaiFilter = $_GET['loai'] ?? '';
$thang = $_GET['thang'] ?? date('Y-m');

$where = ["dc.ngay_dieu_chinh LIKE :thang"];
$params = ['thang' => "$thang%"];

if (in_array($loaiFilter, ['huy', 'cho_muon', 'muon'])) {
    $where[] = "dc.loai = :loai";
    $params['loai'] = $loaiFilter;
}

$whereSql = implode(' AND ', $where);

$stmt = $db->prepare("
    SELECT dc.*, sp.ma_sp, sp.ten_sp, sp.don_vi_tinh, sp.nhom_sp, u.ho_ten AS nguoi_tao
    FROM dieu_chinh_kho dc
    JOIN san_pham sp ON sp.id = dc.san_pham_id
    LEFT JOIN users u ON u.id = dc.nguoi_tao_id
    WHERE $whereSql
    ORDER BY dc.ngay_dieu_chinh DESC, dc.id DESC
");
$stmt->execute($params);
$dieuChinhList = $stmt->fetchAll();

include __DIR__ . '/../includes/header.php';
?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; flex-wrap: wrap; gap: 12px;">
    <!-- Tabs lọc loại điều chỉnh -->
    <div style="display: flex; gap: 8px;">
        <a href="?thang=<?= urlencode($thang) ?>" class="btn <?= empty($loaiFilter) ? 'btn-primary' : 'btn-secondary' ?>">
            Tất Cả
        </a>
        <a href="?loai=huy&thang=<?= urlencode($thang) ?>" class="btn <?= $loaiFilter === 'huy' ? 'btn-danger' : 'btn-secondary' ?>">
            ❌ Số Hủy (Giảm tồn)
        </a>
        <a href="?loai=cho_muon&thang=<?= urlencode($thang) ?>" class="btn <?= $loaiFilter === 'cho_muon' ? 'btn-warning' : 'btn-secondary' ?>">
            🤝 Cho Mượn (Giảm tồn)
        </a>
        <a href="?loai=muon&thang=<?= urlencode($thang) ?>" class="btn <?= $loaiFilter === 'muon' ? 'btn-success' : 'btn-secondary' ?>">
            🤲 Mượn Về (Tăng tồn)
        </a>
    </div>

    <div style="display: flex; gap: 10px;">
        <form method="GET" action="" style="display: flex; align-items: center; gap: 8px;">
            <?php if ($loaiFilter): ?>
                <input type="hidden" name="loai" value="<?= htmlspecialchars($loaiFilter) ?>">
            <?php endif; ?>
            <input type="month" name="thang" class="form-control" value="<?= htmlspecialchars($thang) ?>" onchange="this.form.submit()" style="width: 160px;">
        </form>

        <a href="<?= BASE_URL ?>/dieuchinh/them.php<?= $loaiFilter ? '?loai=' . urlencode($loaiFilter) : '' ?>" class="btn btn-primary">
            <i class="fa-solid fa-plus"></i> Thêm Phiếu Mới
        </a>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h2 class="card-title">
            <i class="fa-solid fa-arrows-split-up-and-left" style="color: var(--primary);"></i>
            Lịch Sử Điều Chỉnh Kho Tháng <?= date('m/Y', strtotime($thang . '-01')) ?>
        </h2>
    </div>
    <div class="card-body" style="padding: 0;">
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Ngày</th>
                        <th>Loại Nghiệp Vụ</th>
                        <th>Mã SP</th>
                        <th>Tên Sản Phẩm</th>
                        <th>Nhóm</th>
                        <th style="text-align: right;">Số Lượng</th>
                        <th style="text-align: center;">Tác Động Tồn Kho</th>
                        <th>Đối Tác / Người Liên Quan</th>
                        <th>Ghi Chú</th>
                        <th>Người Lập</th>
                        <th style="text-align: center; width: 80px;">Xóa</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($dieuChinhList)): ?>
                        <tr>
                            <td colspan="11" style="text-align: center; padding: 30px; color: var(--text-muted);">
                                Chưa có phát sinh điều chỉnh nào phù hợp điều kiện lọc.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($dieuChinhList as $item): ?>
                            <tr>
                                <td><strong><?= formatDate($item['ngay_dieu_chinh']) ?></strong></td>
                                <td>
                                    <?php if ($item['loai'] === 'huy'): ?>
                                        <span class="badge badge-danger"><i class="fa-solid fa-ban"></i> Hủy Hàng</span>
                                    <?php elseif ($item['loai'] === 'cho_muon'): ?>
                                        <span class="badge badge-warning"><i class="fa-solid fa-handshake"></i> Cho Mượn</span>
                                    <?php else: ?>
                                        <span class="badge badge-success"><i class="fa-solid fa-hand-holding-heart"></i> Mượn Về</span>
                                    <?php endif; ?>
                                </td>
                                <td><strong style="color: var(--primary);"><?= htmlspecialchars($item['ma_sp']) ?></strong></td>
                                <td><?= htmlspecialchars($item['ten_sp']) ?></td>
                                <td><span class="badge badge-secondary"><?= htmlspecialchars($item['nhom_sp']) ?></span></td>
                                <td style="text-align: right; font-weight: 700; font-size: 15px;">
                                    <?= formatNumber($item['so_luong']) ?> <?= htmlspecialchars($item['don_vi_tinh']) ?>
                                </td>
                                <td style="text-align: center;">
                                    <?php if ($item['loai'] === 'muon'): ?>
                                        <span style="color: var(--success); font-weight: 700;">+ Tăng Kho</span>
                                    <?php else: ?>
                                        <span style="color: var(--danger); font-weight: 700;">- Giảm Kho</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= htmlspecialchars($item['nguoi_lien_quan'] ?? '-') ?></td>
                                <td style="font-size: 12px; color: var(--text-muted);"><?= htmlspecialchars($item['ghi_chu'] ?? '-') ?></td>
                                <td style="font-size: 12px;"><?= htmlspecialchars($item['nguoi_tao'] ?? 'Admin') ?></td>
                                <td style="text-align: center;">
                                    <a href="<?= BASE_URL ?>/dieuchinh/xoa.php?id=<?= $item['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Bạn có chắc muốn xóa phiếu điều chỉnh này?');" title="Xóa phiếu">
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
