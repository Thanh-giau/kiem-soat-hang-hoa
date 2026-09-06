<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';
requireLogin();

$pageTitle = "Định Lượng Công Thức Món (BOM)";
$db = getDB();

$search = trim($_GET['search'] ?? '');

$where = [];
$params = [];
if ($search !== '') {
    $where[] = "(dlm.ma_mon_pos LIKE :search OR dlm.ten_mon_pos LIKE :search OR sp.ten_sp LIKE :search)";
    $params['search'] = "%$search%";
}

$whereSql = !empty($where) ? "WHERE " . implode(' AND ', $where) : "";

$sql = "
    SELECT 
        dlm.*,
        sp.ma_sp AS nvl_ma,
        sp.ten_sp AS nvl_ten,
        sp.don_vi_tinh AS nvl_dvt,
        sp.nhom_sp AS nvl_nhom
    FROM dinh_luong_mon dlm
    JOIN san_pham sp ON sp.id = dlm.san_pham_id
    $whereSql
    ORDER BY dlm.ten_mon_pos ASC, dlm.id ASC
";
$stmt = $db->prepare($sql);
$stmt->execute($params);
$rows = $stmt->fetchAll();

// Gom nhóm theo món bán trên POS (ma_mon_pos hoặc ten_mon_pos)
$groupedDishes = [];
$totalNVLCount = 0;
foreach ($rows as $r) {
    $key = !empty($r['ma_mon_pos']) ? $r['ma_mon_pos'] : $r['ten_mon_pos'];
    if (!isset($groupedDishes[$key])) {
        $groupedDishes[$key] = [
            'ma_mon_pos' => $r['ma_mon_pos'],
            'ten_mon_pos' => $r['ten_mon_pos'],
            'nguyen_lieu' => []
        ];
    }
    $groupedDishes[$key]['nguyen_lieu'][] = $r;
    $totalNVLCount++;
}

include __DIR__ . '/../includes/header.php';
?>

<!-- Header tiêu đề trang -->
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; flex-wrap: wrap; gap: 16px;">
    <div>
        <h2 style="font-size: 22px; font-weight: 800; color: var(--text-primary); letter-spacing: -0.5px; display: flex; align-items: center; gap: 10px;">
            <span style="display: inline-flex; align-items: center; justify-content: center; width: 36px; height: 36px; background: linear-gradient(135deg, #6366f1, #4f46e5); color: #fff; border-radius: 10px; font-size: 18px; box-shadow: 0 4px 10px rgba(99, 102, 241, 0.3);">
                <i class="fa-solid fa-bowl-food"></i>
            </span>
            Định Lượng & Công Thức Món Bán (BOM)
        </h2>
        <div style="font-size: 13px; color: var(--text-secondary); margin-top: 4px;">
            Tự động bóc tách từ 1 món bán trên POS thành các nguyên vật liệu kiểm kê trong kho (vd: 1 ly Matcha Mochi &rarr; 1 Viên Mochi; 1 Sandwich &rarr; 3 NVL).
        </div>
    </div>
    <div style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
        <a href="<?= BASE_URL ?>/dinhluong/dong_bo.php" class="btn btn-warning" onclick="return confirm('Hệ thống sẽ quét lại toàn bộ file Excel số bán đã tải lên để áp dụng công thức BOM mới nhất vào tồn kho. Bạn có muốn tiếp tục?')" title="Áp dụng công thức BOM vào toàn bộ số bán của các ngày đã tải file" style="box-shadow: 0 4px 12px rgba(245, 158, 11, 0.25);">
            <i class="fa-solid fa-arrows-rotate"></i> Đồng Bộ Lại Số Bán Đã Tải Lên
        </a>
        <a href="<?= BASE_URL ?>/dinhluong/them.php" class="btn btn-primary" style="padding: 10px 18px; font-weight: 600; box-shadow: 0 4px 12px rgba(79, 70, 229, 0.3);">
            <i class="fa-solid fa-plus"></i> Thêm Công Thức Mới
        </a>
    </div>
</div>

<!-- Banner thông tin hướng dẫn nguyên lý -->
<div class="card" style="margin-bottom: 24px; border: 1px solid #c7d2fe; background: linear-gradient(135deg, #f5f7ff 0%, #eef2ff 100%);">
    <div class="card-body" style="padding: 18px 20px;">
        <div style="display: flex; gap: 16px; align-items: flex-start;">
            <div style="width: 44px; height: 44px; border-radius: 12px; background: #4f46e5; color: #fff; display: flex; align-items: center; justify-content: center; font-size: 20px; flex-shrink: 0; box-shadow: 0 4px 12px rgba(79, 70, 229, 0.25);">
                <i class="fa-solid fa-wand-magic-sparkles"></i>
            </div>
            <div style="flex: 1;">
                <h4 style="font-size: 15px; font-weight: 700; color: #312e81; margin-bottom: 4px;">
                    Cơ Chế Tự Động Bóc Tách Khi Upload File Excel Số Bán
                </h4>
                <p style="font-size: 13px; color: #4338ca; line-height: 1.6; margin: 0 0 6px 0;">
                    Khi bạn tải file Excel số bán mỗi ngày lên (kể cả iPOS, KiotViet...), hệ thống sẽ tự động quét danh sách món có cài công thức bên dưới. 
                    Số bán của món trên POS sẽ tự nhân với định lượng và <strong>tự động cộng dồn vào số bán của từng mặt hàng kiểm kê</strong> trong kho. Bạn hoàn toàn không cần tính tay!
                </p>
                <div style="font-size: 12px; color: #4f46e5; background: #e0e7ff; padding: 4px 10px; border-radius: 6px; display: inline-block;">
                    <i class="fa-solid fa-circle-check"></i> <strong>Khớp thông minh đa biến thể:</strong> Cài món <em>"Matcha Tây Bắc Mochi"</em> sẽ tự động bóc tách cho cả <em>"Matcha Latte Tây Bắc Mochi (Vừa)"</em>, <em>"Matcha Latte Tây Bắc Mochi (Lớn)"</em> và các size khác!
                </div>
            </div>
            <div style="display: flex; gap: 12px; align-items: center;">
                <div style="background: #fff; padding: 8px 16px; border-radius: 8px; border: 1px solid #e0e7ff; text-align: center;">
                    <div style="font-size: 11px; font-weight: 600; color: var(--text-muted); text-transform: uppercase;">Món Đã Cài</div>
                    <div style="font-size: 20px; font-weight: 800; color: var(--primary);"><?= count($groupedDishes) ?></div>
                </div>
                <div style="background: #fff; padding: 8px 16px; border-radius: 8px; border: 1px solid #e0e7ff; text-align: center;">
                    <div style="font-size: 11px; font-weight: 600; color: var(--text-muted); text-transform: uppercase;">Liên Kết NVL</div>
                    <div style="font-size: 20px; font-weight: 800; color: var(--success);"><?= $totalNVLCount ?></div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Bảng danh sách công thức -->
<div class="card">
    <div class="card-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px;">
        <h3 class="card-title" style="font-size: 16px; font-weight: 700;">
            <i class="fa-solid fa-list-check" style="color: var(--primary);"></i>
            Danh Sách Món Đã Cài Công Thức (<?= count($groupedDishes) ?> Món)
        </h3>
        <form method="GET" action="" style="display: flex; gap: 8px; align-items: center;">
            <div style="position: relative;">
                <input type="text" name="search" class="form-control form-control-sm" placeholder="Tìm tên món, mã món, NVL..." value="<?= htmlspecialchars($search) ?>" style="width: 260px; padding-left: 32px;">
                <i class="fa-solid fa-search" style="position: absolute; left: 10px; top: 50%; transform: translateY(-50%); font-size: 12px; color: var(--text-muted);"></i>
            </div>
            <button type="submit" class="btn btn-secondary btn-sm">Tìm kiếm</button>
            <?php if ($search !== ''): ?>
                <a href="<?= BASE_URL ?>/dinhluong/danh_sach.php" class="btn btn-outline-secondary btn-sm" title="Xóa bộ lọc">
                    <i class="fa-solid fa-xmark"></i>
                </a>
            <?php endif; ?>
        </form>
    </div>
    <div class="card-body" style="padding: 0;">
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th style="width: 140px;">Mã Món (POS)</th>
                        <th style="width: 260px;">Tên Món Bán Trên POS</th>
                        <th>Nguyên Vật Liệu Tiêu Hao Trong Kho (Cho 1 Phần Bán)</th>
                        <th style="text-align: center; width: 140px;">Thao Tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($groupedDishes)): ?>
                        <tr>
                            <td colspan="4" style="text-align: center; padding: 48px 20px; color: var(--text-muted);">
                                <div style="font-size: 42px; color: #cbd5e1; margin-bottom: 12px;">
                                    <i class="fa-solid fa-utensils"></i>
                                </div>
                                <div style="font-weight: 600; font-size: 15px; color: var(--text-primary); margin-bottom: 4px;">
                                    <?= $search !== '' ? 'Không tìm thấy món nào phù hợp với từ khóa "' . htmlspecialchars($search) . '"' : 'Chưa có công thức định lượng nào trong hệ thống' ?>
                                </div>
                                <div style="font-size: 13px; margin-bottom: 16px;">
                                    Cài đặt công thức định lượng để hệ thống tự động bóc tách số bán từ các món phức hợp.
                                </div>
                                <a href="<?= BASE_URL ?>/dinhluong/them.php" class="btn btn-primary btn-sm">
                                    <i class="fa-solid fa-plus"></i> Thêm Công Thức Mới
                                </a>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($groupedDishes as $dish): ?>
                            <tr>
                                <td>
                                    <?php if (!empty($dish['ma_mon_pos'])): ?>
                                        <span class="badge badge-info" style="font-family: monospace; font-size: 12px; padding: 4px 8px;">
                                            <?= htmlspecialchars($dish['ma_mon_pos']) ?>
                                        </span>
                                    <?php else: ?>
                                        <span style="color: var(--text-muted); font-size: 12px;">(Theo tên)</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div style="font-weight: 700; font-size: 15px; color: var(--text-primary);">
                                        <?= htmlspecialchars($dish['ten_mon_pos']) ?>
                                    </div>
                                    <div style="font-size: 12px; color: var(--text-muted); margin-top: 2px;">
                                        <i class="fa-solid fa-cubes"></i> Bóc tách thành <?= count($dish['nguyen_lieu']) ?> nguyên vật liệu kiểm kê
                                    </div>
                                </td>
                                <td>
                                    <div style="display: flex; flex-wrap: wrap; gap: 8px;">
                                        <?php foreach ($dish['nguyen_lieu'] as $nl): ?>
                                            <div style="display: inline-flex; align-items: center; gap: 6px; background: #f8fafc; padding: 6px 12px; border-radius: 8px; border: 1px solid var(--border-color); box-shadow: 0 1px 2px rgba(0,0,0,0.03);">
                                                <span style="font-family: monospace; font-size: 11px; background: #e2e8f0; color: #334155; padding: 2px 6px; border-radius: 4px; font-weight: 600;">
                                                    <?= htmlspecialchars($nl['nvl_ma']) ?>
                                                </span>
                                                <span style="font-weight: 600; color: var(--text-primary);">
                                                    <?= htmlspecialchars($nl['nvl_ten']) ?>
                                                </span>
                                                <span style="color: var(--text-muted);">&rarr;</span>
                                                <span style="font-weight: 800; color: var(--primary); font-size: 14px;">
                                                    <?= formatNumber($nl['so_luong_tieu_hao']) ?> <?= htmlspecialchars($nl['nvl_dvt']) ?>
                                                </span>
                                                <?php if (!empty($nl['ghi_chu'])): ?>
                                                    <span style="font-size: 11px; color: var(--text-muted); font-style: italic;">
                                                        (<?= htmlspecialchars($nl['ghi_chu']) ?>)
                                                    </span>
                                                <?php endif; ?>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </td>
                                <td style="text-align: center;">
                                    <div style="display: inline-flex; gap: 6px;">
                                        <a href="<?= BASE_URL ?>/dinhluong/sua.php?ma=<?= urlencode($dish['ma_mon_pos']) ?>&ten=<?= urlencode($dish['ten_mon_pos']) ?>" class="btn btn-secondary btn-sm" title="Chỉnh sửa công thức">
                                            <i class="fa-solid fa-pen-to-square"></i> Sửa
                                        </a>
                                        <a href="<?= BASE_URL ?>/dinhluong/xoa.php?ma=<?= urlencode($dish['ma_mon_pos']) ?>&ten=<?= urlencode($dish['ten_mon_pos']) ?>" class="btn btn-danger btn-sm" onclick="return confirm('Bạn có chắc muốn xóa công thức định lượng của món [<?= htmlspecialchars(addslashes($dish['ten_mon_pos'])) ?>]?');" title="Xóa công thức">
                                            <i class="fa-solid fa-trash-can"></i>
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
