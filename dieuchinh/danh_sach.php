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

// Gom nhóm dữ liệu theo từng ngày điều chỉnh (Dạng Accordion)
$groupedByDate = [];
$totalRows = count($dieuChinhList);
$totalHuy = 0;
$totalChoMuon = 0;
$totalMuon = 0;

foreach ($dieuChinhList as $item) {
    $d = $item['ngay_dieu_chinh'];
    if (!isset($groupedByDate[$d])) {
        $groupedByDate[$d] = [
            'ngay' => $d,
            'items' => [],
            'total_huy' => 0,
            'total_cho_muon' => 0,
            'total_muon' => 0,
            'loai_list' => [],
            'nguoi_lien_quan' => [],
            'nguoi_tao' => []
        ];
    }
    $groupedByDate[$d]['items'][] = $item;
    $qty = (float)$item['so_luong'];

    if ($item['loai'] === 'huy') {
        $groupedByDate[$d]['total_huy'] += $qty;
        $totalHuy += $qty;
    } elseif ($item['loai'] === 'cho_muon') {
        $groupedByDate[$d]['total_cho_muon'] += $qty;
        $totalChoMuon += $qty;
    } elseif ($item['loai'] === 'muon') {
        $groupedByDate[$d]['total_muon'] += $qty;
        $totalMuon += $qty;
    }

    if (!in_array($item['loai'], $groupedByDate[$d]['loai_list'])) {
        $groupedByDate[$d]['loai_list'][] = $item['loai'];
    }
    if (!empty($item['nguoi_lien_quan']) && !in_array($item['nguoi_lien_quan'], $groupedByDate[$d]['nguoi_lien_quan'])) {
        $groupedByDate[$d]['nguoi_lien_quan'][] = $item['nguoi_lien_quan'];
    }
    if (!empty($item['nguoi_tao']) && !in_array($item['nguoi_tao'], $groupedByDate[$d]['nguoi_tao'])) {
        $groupedByDate[$d]['nguoi_tao'][] = $item['nguoi_tao'];
    }
}

include __DIR__ . '/../includes/header.php';
?>

<!-- Thanh Điều Hướng & Bộ Lọc Nâng Cao -->
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; flex-wrap: wrap; gap: 14px;">
    <!-- Tabs lọc loại điều chỉnh -->
    <div style="display: flex; gap: 8px; flex-wrap: wrap;">
        <a href="?thang=<?= urlencode($thang) ?>" class="btn <?= empty($loaiFilter) ? 'btn-primary' : 'btn-secondary' ?>">
            <i class="fa-solid fa-list-check"></i> Tất Cả
        </a>
        <a href="?loai=huy&thang=<?= urlencode($thang) ?>" class="btn <?= $loaiFilter === 'huy' ? 'btn-danger' : 'btn-secondary' ?>">
            <i class="fa-solid fa-ban"></i> Số Hủy (Giảm tồn)
        </a>
        <a href="?loai=cho_muon&thang=<?= urlencode($thang) ?>" class="btn <?= $loaiFilter === 'cho_muon' ? 'btn-warning' : 'btn-secondary' ?>">
            <i class="fa-solid fa-handshake"></i> Cho Mượn (Giảm tồn)
        </a>
        <a href="?loai=muon&thang=<?= urlencode($thang) ?>" class="btn <?= $loaiFilter === 'muon' ? 'btn-success' : 'btn-secondary' ?>">
            <i class="fa-solid fa-hand-holding-heart"></i> Mượn Về (Tăng tồn)
        </a>
    </div>

    <!-- Thanh công cụ Tháng, Mở rộng/Thu gọn, Nút Thêm mới -->
    <div style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
        <form method="GET" action="" style="display: flex; align-items: center; gap: 8px;">
            <?php if ($loaiFilter): ?>
                <input type="hidden" name="loai" value="<?= htmlspecialchars($loaiFilter) ?>">
            <?php endif; ?>
            <div style="display: flex; align-items: center; gap: 6px; background: #fff; padding: 4px 10px; border-radius: 8px; border: 1px solid var(--border-color);">
                <i class="fa-solid fa-calendar-days" style="color: var(--primary);"></i>
                <label style="font-size: 13px; font-weight: 700; color: var(--text-secondary); margin: 0;">Tháng:</label>
                <input type="month" name="thang" value="<?= htmlspecialchars($thang) ?>" onchange="this.form.submit()" style="border: none; outline: none; font-weight: 600; font-size: 13.5px; background: transparent; cursor: pointer;">
            </div>
            <button type="submit" class="btn btn-sm btn-secondary">Xem</button>
        </form>

        <?php if (!empty($groupedByDate)): ?>
            <div style="display: flex; gap: 6px;">
                <button type="button" class="btn btn-sm btn-outline-secondary" onclick="expandAllDates()" title="Mở rộng tất cả các ngày">
                    <i class="fa-solid fa-angles-down"></i> Mở rộng tất cả
                </button>
                <button type="button" class="btn btn-sm btn-outline-secondary" onclick="collapseAllDates()" title="Thu gọn tất cả các ngày">
                    <i class="fa-solid fa-angles-up"></i> Thu gọn tất cả
                </button>
            </div>
        <?php endif; ?>

        <a href="<?= BASE_URL ?>/dieuchinh/them.php<?= $loaiFilter ? '?loai=' . urlencode($loaiFilter) : '' ?>" class="btn btn-primary" style="font-weight: 700;">
            <i class="fa-solid fa-plus"></i> Thêm Phiếu Mới
        </a>
    </div>
</div>

<!-- Khối Thống Kê Tổng Quan KPI Tháng -->
<div class="kpi-grid" style="margin-bottom: 20px;">
    <div class="kpi-card">
        <div class="kpi-icon" style="background: rgba(99, 102, 241, 0.1); color: var(--primary);">
            <i class="fa-solid fa-calendar-check"></i>
        </div>
        <div class="kpi-content">
            <div class="kpi-label">Số Đợt Điều Chỉnh</div>
            <div class="kpi-value" style="color: var(--primary);"><?= count($groupedByDate) ?> <span style="font-size: 13px; font-weight: normal; color: var(--text-muted);">ngày</span></div>
        </div>
    </div>
    <div class="kpi-card">
        <div class="kpi-icon" style="background: rgba(14, 165, 233, 0.1); color: #0284c7;">
            <i class="fa-solid fa-boxes-stacked"></i>
        </div>
        <div class="kpi-content">
            <div class="kpi-label">Tổng Lượt Mặt Hàng</div>
            <div class="kpi-value" style="color: #0284c7;"><?= formatNumber($totalRows) ?> <span style="font-size: 13px; font-weight: normal; color: var(--text-muted);">lượt món</span></div>
        </div>
    </div>
    <div class="kpi-card">
        <div class="kpi-icon" style="background: rgba(239, 68, 68, 0.1); color: var(--danger);">
            <i class="fa-solid fa-ban"></i>
        </div>
        <div class="kpi-content">
            <div class="kpi-label">Tổng Hủy Kho</div>
            <div class="kpi-value" style="color: var(--danger);">-<?= formatNumber($totalHuy) ?> <span style="font-size: 13px; font-weight: normal; color: var(--text-muted);">đơn vị</span></div>
        </div>
    </div>
    <div class="kpi-card">
        <div class="kpi-icon" style="background: rgba(245, 158, 11, 0.1); color: #d97706;">
            <i class="fa-solid fa-handshake"></i>
        </div>
        <div class="kpi-content">
            <div class="kpi-label">Cho Mượn / Mượn Về</div>
            <div class="kpi-value" style="font-size: 17px; color: #d97706;">
                -<?= formatNumber($totalChoMuon) ?> / <span style="color: var(--success);">+<?= formatNumber($totalMuon) ?></span>
            </div>
        </div>
    </div>
</div>

<!-- Hướng dẫn sử dụng nhanh -->
<div style="margin-bottom: 16px; padding: 12px 18px; background: #f5f3ff; border-radius: 10px; border: 1px solid #ddd6fe; font-size: 13px; color: #5b21b6; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 8px;">
    <div style="display: flex; align-items: center; gap: 8px;">
        <i class="fa-solid fa-circle-info" style="font-size: 16px; color: #7c3aed;"></i>
        <span><strong>Giao diện thông minh:</strong> Các ngày điều chỉnh kho đã được <strong>thu gọn lại</strong>. Nhấp vào ngày để mở danh sách chi tiết các mặt hàng, hoặc bấm <strong>"✏️ Đổi Ngày"</strong> nếu lỡ tay nhập sai ngày điều chỉnh!</span>
    </div>
</div>

<?php if (empty($groupedByDate)): ?>
    <div class="card" style="text-align: center; padding: 50px 20px;">
        <div style="font-size: 48px; color: var(--text-muted); margin-bottom: 12px;">
            <i class="fa-solid fa-arrows-split-up-and-left"></i>
        </div>
        <h3 style="font-size: 17px; font-weight: 700; color: var(--text-primary); margin-bottom: 6px;">Chưa có phát sinh điều chỉnh nào phù hợp điều kiện lọc</h3>
        <p style="color: var(--text-secondary); font-size: 13px; max-width: 420px; margin: 0 auto 16px;">
            Bạn có thể tạo phiếu Số Hủy (hết hạn, rơi vỡ), Cho Mượn hoặc Mượn Về để hệ thống tự động cân đối tồn kho lý thuyết.
        </p>
        <a href="<?= BASE_URL ?>/dieuchinh/them.php<?= $loaiFilter ? '?loai=' . urlencode($loaiFilter) : '' ?>" class="btn btn-primary">
            <i class="fa-solid fa-plus"></i> Tạo Phiếu Điều Chỉnh Ngay
        </a>
    </div>
<?php else: ?>
    <!-- Danh Sách Các Đợt Điều Chỉnh Thu Gọn Dạng Accordion Theo Ngày -->
    <div class="adjustment-days-container" style="display: flex; flex-direction: column; gap: 14px;">
        <?php foreach ($groupedByDate as $dateKey => $group): ?>
            <?php
            // Xác định màu sắc gradient đại diện cho đợt ngày
            $hasHuy = $group['total_huy'] > 0;
            $hasChoMuon = $group['total_cho_muon'] > 0;
            $hasMuon = $group['total_muon'] > 0;

            if ($hasHuy && !$hasChoMuon && !$hasMuon) {
                $iconBg = "linear-gradient(135deg, #ef4444, #dc2626)";
                $cardIcon = "fa-solid fa-ban";
            } elseif ($hasChoMuon && !$hasHuy && !$hasMuon) {
                $iconBg = "linear-gradient(135deg, #f59e0b, #d97706)";
                $cardIcon = "fa-solid fa-handshake";
            } elseif ($hasMuon && !$hasHuy && !$hasChoMuon) {
                $iconBg = "linear-gradient(135deg, #10b981, #059669)";
                $cardIcon = "fa-solid fa-hand-holding-heart";
            } else {
                $iconBg = "linear-gradient(135deg, #6366f1, #4f46e5)";
                $cardIcon = "fa-solid fa-arrows-split-up-and-left";
            }
            ?>
            <div class="card adjustment-day-card" id="day-card-<?= $dateKey ?>" style="padding: 0; overflow: hidden; margin-bottom: 0; border: 1px solid var(--border-color);">
                <!-- Header Thẻ Ngày: Nhấp vào để Thu Gọn / Mở Rộng -->
                <div class="adjustment-day-header" onclick="toggleDateCard('<?= $dateKey ?>')" style="padding: 16px 20px; cursor: pointer; display: flex; align-items: center; justify-content: space-between; background: #ffffff; transition: background 0.15s ease; user-select: none;">
                    
                    <div style="display: flex; align-items: center; gap: 14px; flex-wrap: wrap;">
                        <!-- Icon Biểu tượng ngày -->
                        <div style="width: 42px; height: 42px; border-radius: 10px; background: <?= $iconBg ?>; color: #fff; display: flex; align-items: center; justify-content: center; font-size: 18px; box-shadow: 0 4px 10px rgba(0,0,0,0.15);">
                            <i class="<?= $cardIcon ?>"></i>
                        </div>

                        <div>
                            <div style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
                                <span style="font-size: 17px; font-weight: 800; color: var(--text-primary);">
                                    <?= formatDate($group['ngay']) ?>
                                </span>
                                <span style="font-size: 13px; font-weight: 600; color: var(--text-secondary); background: #f1f5f9; padding: 2px 8px; border-radius: 6px;">
                                    <?= getThuTrongTuanVN($group['ngay']) ?>
                                </span>
                                <span class="badge badge-primary" style="font-size: 12px;">
                                    <i class="fa-solid fa-boxes-stacked"></i> <?= count($group['items']) ?> Mặt Hàng
                                </span>

                                <?php if ($group['total_huy'] > 0): ?>
                                    <span class="badge badge-danger" style="font-size: 12px; font-weight: 700;">
                                        <i class="fa-solid fa-ban"></i> Hủy: -<?= formatNumber($group['total_huy']) ?>
                                    </span>
                                <?php endif; ?>

                                <?php if ($group['total_cho_muon'] > 0): ?>
                                    <span class="badge badge-warning" style="font-size: 12px; font-weight: 700;">
                                        <i class="fa-solid fa-handshake"></i> Cho mượn: -<?= formatNumber($group['total_cho_muon']) ?>
                                    </span>
                                <?php endif; ?>

                                <?php if ($group['total_muon'] > 0): ?>
                                    <span class="badge badge-success" style="font-size: 12px; font-weight: 700;">
                                        <i class="fa-solid fa-hand-holding-heart"></i> Mượn về: +<?= formatNumber($group['total_muon']) ?>
                                    </span>
                                <?php endif; ?>
                            </div>

                            <div style="font-size: 12px; color: var(--text-secondary); margin-top: 4px; display: flex; gap: 16px; flex-wrap: wrap;">
                                <?php if (!empty($group['nguoi_lien_quan'])): ?>
                                    <span><i class="fa-solid fa-user-tag" style="color: var(--primary);"></i> <strong>Đối tác / Người liên quan:</strong> <?= htmlspecialchars(implode(', ', $group['nguoi_lien_quan'])) ?></span>
                                <?php endif; ?>
                                <?php if (!empty($group['nguoi_tao'])): ?>
                                    <span><i class="fa-solid fa-user-check" style="color: var(--success);"></i> <strong>Người lập:</strong> <?= htmlspecialchars(implode(', ', $group['nguoi_tao'])) ?></span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Thao Tác Cấp Ngày: Đổi Ngày / Xóa Đợt / Mũi Tên Mở Rộng -->
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <!-- Nút Đổi Ngày Điều Chỉnh -->
                        <button type="button" class="btn btn-sm btn-outline-primary" onclick="event.stopPropagation(); openModalDoiNgayCuaNgay('<?= $group['ngay'] ?>', <?= count($group['items']) ?>)" title="Đổi ngày điều chỉnh khi lỡ tay nhập sai ngày" style="font-weight: 600; background: #f5f3ff; border-color: #ddd6fe; color: #5b21b6;">
                            <i class="fa-solid fa-calendar-pen"></i> Đổi Ngày
                        </button>

                        <!-- Nút Xóa Toàn Bộ Đợt Điều Chỉnh Của Ngày -->
                        <button type="button" class="btn btn-sm btn-outline-danger" onclick="event.stopPropagation(); confirmXoaNgay('<?= $group['ngay'] ?>', <?= count($group['items']) ?>)" title="Xóa toàn bộ các mặt hàng điều chỉnh ngày này" style="font-weight: 600;">
                            <i class="fa-solid fa-trash-can"></i> Xóa Đợt
                        </button>

                        <!-- Mũi tên toggle -->
                        <div class="toggle-chevron" style="width: 30px; height: 30px; border-radius: 50%; background: #f8fafc; border: 1px solid var(--border-color); display: flex; align-items: center; justify-content: center; color: var(--text-secondary); transition: transform 0.25s ease;">
                            <i class="fa-solid fa-chevron-down"></i>
                        </div>
                    </div>
                </div>

                <!-- Bảng Chi Tiết Mặt Hàng Của Ngày Đó (Mặc định thu gọn) -->
                <div class="adjustment-day-body" style="display: none; border-top: 1px solid var(--border-color); background: #fafafa;">
                    <div class="table-responsive">
                        <table class="table" style="margin: 0;">
                            <thead style="background: #f1f5f9;">
                                <tr>
                                    <th style="width: 120px;">Mã SP</th>
                                    <th>Tên Sản Phẩm</th>
                                    <th>Nhóm Sản Phẩm</th>
                                    <th style="width: 130px;">Loại Nghiệp Vụ</th>
                                    <th style="text-align: right; width: 120px;">Số Lượng</th>
                                    <th style="text-align: center; width: 130px;">Tác Động Kho</th>
                                    <th>Đối Tác / Người Liên Quan</th>
                                    <th>Ghi Chú</th>
                                    <th>Người Lập</th>
                                    <th style="text-align: center; width: 100px;">Thao Tác</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($group['items'] as $item): ?>
                                    <tr style="background: #ffffff;">
                                        <td>
                                            <strong style="color: var(--primary); font-family: monospace; font-size: 14px;">
                                                <?= htmlspecialchars($item['ma_sp']) ?>
                                            </strong>
                                        </td>
                                        <td>
                                            <div style="font-weight: 700; color: var(--text-primary);">
                                                <?= htmlspecialchars($item['ten_sp']) ?>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge badge-secondary" style="font-size: 11px;">
                                                <?= htmlspecialchars($item['nhom_sp']) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php if ($item['loai'] === 'huy'): ?>
                                                <span class="badge badge-danger"><i class="fa-solid fa-ban"></i> Hủy Hàng</span>
                                            <?php elseif ($item['loai'] === 'cho_muon'): ?>
                                                <span class="badge badge-warning"><i class="fa-solid fa-handshake"></i> Cho Mượn</span>
                                            <?php else: ?>
                                                <span class="badge badge-success"><i class="fa-solid fa-hand-holding-heart"></i> Mượn Về</span>
                                            <?php endif; ?>
                                        </td>
                                        <td style="text-align: right; font-weight: 800; font-size: 15px;">
                                            <?= formatNumber($item['so_luong']) ?> <span style="font-size: 12px; font-weight: normal; color: var(--text-secondary);"><?= htmlspecialchars($item['don_vi_tinh']) ?></span>
                                        </td>
                                        <td style="text-align: center;">
                                            <?php if ($item['loai'] === 'muon'): ?>
                                                <span style="color: var(--success); font-weight: 700; font-size: 13px;">+ Tăng Kho</span>
                                            <?php else: ?>
                                                <span style="color: var(--danger); font-weight: 700; font-size: 13px;">- Giảm Kho</span>
                                            <?php endif; ?>
                                        </td>
                                        <td style="font-size: 12.5px; color: var(--text-secondary);">
                                            <?= htmlspecialchars($item['nguoi_lien_quan'] ?? '-') ?>
                                        </td>
                                        <td style="font-size: 12px; color: var(--text-muted);">
                                            <?= htmlspecialchars($item['ghi_chu'] ?? '-') ?>
                                        </td>
                                        <td style="font-size: 12px; color: var(--text-secondary);">
                                            <?= htmlspecialchars($item['nguoi_tao'] ?? 'Admin') ?>
                                        </td>
                                        <td style="text-align: center;">
                                            <div style="display: inline-flex; gap: 5px;">
                                                <!-- Sửa ngày cho riêng món này -->
                                                <button type="button" class="btn btn-secondary btn-sm" onclick="openModalDoiNgayMonLe(<?= $item['id'] ?>, '<?= htmlspecialchars(addslashes($item['ten_sp'])) ?>', '<?= $item['ngay_dieu_chinh'] ?>')" title="Đổi ngày cho riêng món này" style="padding: 3px 8px;">
                                                    <i class="fa-solid fa-pen"></i>
                                                </button>
                                                <!-- Xóa riêng món này -->
                                                <a href="<?= BASE_URL ?>/dieuchinh/xoa.php?id=<?= $item['id'] ?><?= $loaiFilter ? '&loai=' . urlencode($loaiFilter) : '' ?>" class="btn btn-danger btn-sm" onclick="return confirm('Bạn có chắc muốn xóa phiếu điều chỉnh [<?= htmlspecialchars(addslashes($item['ten_sp'])) ?>]? Tồn kho lý thuyết sẽ được tự động hoàn nguyên.');" title="Xóa phiếu này" style="padding: 3px 8px;">
                                                    <i class="fa-solid fa-trash"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                            <tfoot style="background: #f8fafc; font-weight: 700; border-top: 2px solid var(--border-color);">
                                <tr>
                                    <td colspan="4" style="text-align: right; color: var(--text-secondary); font-size: 13px;">
                                        Tổng cộng ngày <?= formatDate($group['ngay']) ?> (<?= count($group['items']) ?> món):
                                    </td>
                                    <td colspan="2" style="font-size: 13px;">
                                        <?php if ($group['total_huy'] > 0): ?>
                                            <span style="color: var(--danger); margin-right: 10px;">Hủy: -<?= formatNumber($group['total_huy']) ?></span>
                                        <?php endif; ?>
                                        <?php if ($group['total_cho_muon'] > 0): ?>
                                            <span style="color: #d97706; margin-right: 10px;">Cho mượn: -<?= formatNumber($group['total_cho_muon']) ?></span>
                                        <?php endif; ?>
                                        <?php if ($group['total_muon'] > 0): ?>
                                            <span style="color: var(--success);">Mượn về: +<?= formatNumber($group['total_muon']) ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td colspan="4" style="font-size: 12px; color: var(--text-muted); font-weight: normal;">
                                        Đã tính toán và cân bằng vào tồn kho lý thuyết
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<!-- ========================================================================= -->
<!-- MODAL 1: ĐỔI NGÀY ĐIỀU CHỈNH CẢ ĐỢT (KHI NHẬP SAI NGÀY CẢ PHIẾU) -->
<!-- ========================================================================= -->
<div class="modal-overlay" id="modal-doi-ngay-batch">
    <div class="modal-card" style="max-width: 480px;">
        <div class="modal-header" style="background: linear-gradient(135deg, #f5f3ff, #ede9fe); border-bottom: 1px solid #ddd6fe;">
            <div class="modal-title" style="color: #5b21b6; display: flex; align-items: center; gap: 10px;">
                <i class="fa-solid fa-calendar-pen" style="font-size: 20px;"></i>
                <span>Chỉnh Sửa Ngày Điều Chỉnh Kho</span>
            </div>
            <button type="button" onclick="closeModal('modal-doi-ngay-batch')" style="background: none; border: none; font-size: 24px; cursor: pointer; color: #64748b; line-height: 1;">&times;</button>
        </div>
        
        <form method="POST" action="<?= BASE_URL ?>/dieuchinh/doi_ngay.php" style="padding: 24px;">
            <input type="hidden" name="action" value="batch">
            <input type="hidden" name="ngay_cu" id="batch-ngay-cu-input" value="">
            <input type="hidden" name="loai_filter" value="<?= htmlspecialchars($loaiFilter) ?>">

            <div style="margin-bottom: 16px;">
                <label class="form-label" style="font-size: 13px; font-weight: 600; color: var(--text-secondary);">Đợt điều chỉnh hiện tại:</label>
                <div style="padding: 10px 14px; background: #f1f5f9; border-radius: 8px; font-weight: 700; color: var(--text-primary); font-size: 15px; display: flex; justify-content: space-between; align-items: center;">
                    <span id="batch-ngay-cu-text">-</span>
                    <span class="badge badge-primary" id="batch-item-count-text">0 món</span>
                </div>
            </div>

            <div style="margin-bottom: 20px;">
                <label class="form-label" style="font-size: 13px; font-weight: 700; color: var(--text-primary);">
                    <i class="fa-solid fa-calendar-check" style="color: var(--primary);"></i> Chọn Ngày Mới Đúng Thực Tế:
                </label>
                <input type="date" name="ngay_moi" id="batch-ngay-moi-input" class="form-control" required style="font-size: 15px; font-weight: 600; padding: 10px 14px;">
            </div>

            <div style="padding: 12px 14px; background: #eff6ff; border-radius: 8px; border: 1px solid #bfdbfe; font-size: 12px; color: #1e40af; margin-bottom: 24px;">
                <i class="fa-solid fa-shield-halved"></i> <strong>Cơ chế tự động:</strong> Khi bạn lưu ngày mới, toàn bộ các phiếu điều chỉnh của đợt này sẽ chuyển sang ngày mới. <strong>Tồn kho lý thuyết và báo cáo chênh lệch</strong> sẽ được tự động tính toán lại hoàn toàn chuẩn xác.
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 10px;">
                <button type="button" class="btn btn-secondary" onclick="closeModal('modal-doi-ngay-batch')">Hủy Bỏ</button>
                <button type="submit" class="btn btn-primary" style="font-weight: 700;">
                    <i class="fa-solid fa-check"></i> Lưu & Cập Nhật Ngày Mới
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ========================================================================= -->
<!-- MODAL 2: ĐỔI NGÀY ĐIỀU CHỈNH CHO RIÊNG 1 MÓN LẺ -->
<!-- ========================================================================= -->
<div class="modal-overlay" id="modal-doi-ngay-single">
    <div class="modal-card" style="max-width: 440px;">
        <div class="modal-header">
            <div class="modal-title" style="display: flex; align-items: center; gap: 8px;">
                <i class="fa-solid fa-pen" style="color: var(--primary);"></i>
                <span>Đổi Ngày Cho Mặt Hàng Này</span>
            </div>
            <button type="button" onclick="closeModal('modal-doi-ngay-single')" style="background: none; border: none; font-size: 24px; cursor: pointer; color: #64748b; line-height: 1;">&times;</button>
        </div>
        
        <form method="POST" action="<?= BASE_URL ?>/dieuchinh/doi_ngay.php" style="padding: 24px;">
            <input type="hidden" name="action" value="single">
            <input type="hidden" name="id" id="single-item-id-input" value="">
            <input type="hidden" name="loai_filter" value="<?= htmlspecialchars($loaiFilter) ?>">

            <div style="margin-bottom: 16px;">
                <label class="form-label" style="font-size: 12px; color: var(--text-secondary);">Tên sản phẩm:</label>
                <div style="font-weight: 700; color: var(--text-primary); font-size: 15px;" id="single-item-name-text">-</div>
            </div>

            <div style="margin-bottom: 24px;">
                <label class="form-label" style="font-size: 13px; font-weight: 700; color: var(--text-primary);">
                    <i class="fa-solid fa-calendar-days" style="color: var(--primary);"></i> Chọn Ngày Điều Chỉnh Mới:
                </label>
                <input type="date" name="ngay_moi" id="single-ngay-moi-input" class="form-control" required style="font-size: 15px; font-weight: 600; padding: 10px 14px;">
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 10px;">
                <button type="button" class="btn btn-secondary" onclick="closeModal('modal-doi-ngay-single')">Hủy Bỏ</button>
                <button type="submit" class="btn btn-primary" style="font-weight: 700;">
                    <i class="fa-solid fa-check"></i> Lưu Thay Đổi
                </button>
            </div>
        </form>
    </div>
</div>

<script>
// Mở rộng / Thu gọn 1 ngày cụ thể khi nhấp vào
function toggleDateCard(dateKey) {
    const card = document.getElementById('day-card-' + dateKey);
    if (!card) return;
    
    const body = card.querySelector('.adjustment-day-body');
    const chevron = card.querySelector('.toggle-chevron i');
    
    if (body.style.display === 'none' || body.style.display === '') {
        body.style.display = 'block';
        chevron.className = 'fa-solid fa-chevron-up';
        card.style.boxShadow = '0 10px 20px -3px rgba(0, 0, 0, 0.08), 0 4px 6px -4px rgba(0, 0, 0, 0.03)';
        card.style.borderColor = 'var(--primary)';
    } else {
        body.style.display = 'none';
        chevron.className = 'fa-solid fa-chevron-down';
        card.style.boxShadow = 'none';
        card.style.borderColor = 'var(--border-color)';
    }
}

// Mở rộng toàn bộ các ngày
function expandAllDates() {
    document.querySelectorAll('.adjustment-day-card').forEach(card => {
        const body = card.querySelector('.adjustment-day-body');
        const chevron = card.querySelector('.toggle-chevron i');
        if (body) body.style.display = 'block';
        if (chevron) chevron.className = 'fa-solid fa-chevron-up';
        card.style.borderColor = 'var(--primary)';
    });
}

// Thu gọn toàn bộ các ngày
function collapseAllDates() {
    document.querySelectorAll('.adjustment-day-card').forEach(card => {
        const body = card.querySelector('.adjustment-day-body');
        const chevron = card.querySelector('.toggle-chevron i');
        if (body) body.style.display = 'none';
        if (chevron) chevron.className = 'fa-solid fa-chevron-down';
        card.style.borderColor = 'var(--border-color)';
        card.style.boxShadow = 'none';
    });
}

// Mở modal đổi ngày cho cả đợt
function openModalDoiNgayCuaNgay(ngayCu, itemCount) {
    document.getElementById('batch-ngay-cu-input').value = ngayCu;
    
    // Format hiển thị dd/mm/yyyy
    const parts = ngayCu.split('-');
    const dateFormatted = parts.length === 3 ? `${parts[2]}/${parts[1]}/${parts[0]}` : ngayCu;
    document.getElementById('batch-ngay-cu-text').textContent = dateFormatted;
    document.getElementById('batch-item-count-text').textContent = itemCount + ' món';
    document.getElementById('batch-ngay-moi-input').value = ngayCu;

    openModal('modal-doi-ngay-batch');
}

// Mở modal đổi ngày cho 1 món lẻ
function openModalDoiNgayMonLe(id, tenSp, ngayHienTai) {
    document.getElementById('single-item-id-input').value = id;
    document.getElementById('single-item-name-text').textContent = tenSp;
    document.getElementById('single-ngay-moi-input').value = ngayHienTai;

    openModal('modal-doi-ngay-single');
}

// Xác nhận xóa toàn bộ phiếu điều chỉnh của ngày
function confirmXoaNgay(ngay, itemCount) {
    const parts = ngay.split('-');
    const dateFormatted = parts.length === 3 ? `${parts[2]}/${parts[1]}/${parts[0]}` : ngay;
    const loaiParam = <?= json_encode($loaiFilter) ?>;
    if (confirm(`⚠️ CẢNH BÁO: Bạn có chắc chắn muốn xóa TOÀN BỘ phiếu điều chỉnh kho ngày [${dateFormatted}] gồm ${itemCount} mặt hàng?\n\nTác động điều chỉnh sẽ bị thu hồi và tồn kho lý thuyết sẽ được tự động hoàn nguyên.`)) {
        window.location.href = '<?= BASE_URL ?>/dieuchinh/xoa_ngay.php?ngay=' + encodeURIComponent(ngay) + (loaiParam ? '&loai=' + encodeURIComponent(loaiParam) : '');
    }
}
</script>

<style>
.adjustment-day-header:hover {
    background: #f8fafc !important;
}
.adjustment-day-card {
    transition: box-shadow 0.2s ease, border-color 0.2s ease;
}
</style>

<?php include __DIR__ . '/../includes/footer.php'; ?>
