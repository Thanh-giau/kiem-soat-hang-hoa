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

// Gom nhóm các mặt hàng theo ngày nhập
$groupedByDate = [];
$totalMonthQty = 0;
$totalMonthRows = count($nhapList);

foreach ($nhapList as $item) {
    $d = $item['ngay_nhap'];
    if (!isset($groupedByDate[$d])) {
        $groupedByDate[$d] = [
            'ngay' => $d,
            'items' => [],
            'total_qty' => 0,
            'item_count' => 0,
            'nha_cung_cap' => [],
            'nguoi_nhap' => []
        ];
    }
    $groupedByDate[$d]['items'][] = $item;
    $groupedByDate[$d]['total_qty'] += (float)$item['so_luong'];
    $groupedByDate[$d]['item_count']++;
    
    if (!empty($item['nha_cung_cap']) && !in_array($item['nha_cung_cap'], $groupedByDate[$d]['nha_cung_cap'])) {
        $groupedByDate[$d]['nha_cung_cap'][] = $item['nha_cung_cap'];
    }
    if (!empty($item['nguoi_nhap']) && !in_array($item['nguoi_nhap'], $groupedByDate[$d]['nguoi_nhap'])) {
        $groupedByDate[$d]['nguoi_nhap'][] = $item['nguoi_nhap'];
    }
    $totalMonthQty += (float)$item['so_luong'];
}

function getThuTrongTuanVN($dateStr) {
    $days = ['Chủ Nhật', 'Thứ Hai', 'Thứ Ba', 'Thứ Tư', 'Thứ Năm', 'Thứ Sáu', 'Thứ Bảy'];
    $w = date('w', strtotime($dateStr));
    return $days[$w] ?? '';
}

include __DIR__ . '/../includes/header.php';
?>

<!-- Thanh công cụ & bộ lọc tháng -->
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; flex-wrap: wrap; gap: 14px;">
    <div style="display: flex; align-items: center; gap: 12px; flex-wrap: wrap;">
        <form method="GET" action="" style="display: flex; align-items: center; gap: 8px;">
            <label class="form-label" style="margin: 0; font-weight: 700; color: var(--text-primary); font-size: 13px;">
                <i class="fa-solid fa-calendar-days" style="color: var(--primary);"></i> Tháng:
            </label>
            <input type="month" name="thang" class="form-control form-control-sm" value="<?= htmlspecialchars($thang) ?>" onchange="this.form.submit()" style="width: 165px; font-weight: 600;">
            <button type="submit" class="btn btn-secondary btn-sm">Xem</button>
        </form>

        <?php if (!empty($groupedByDate)): ?>
            <div style="display: flex; gap: 6px;">
                <button type="button" class="btn btn-secondary btn-sm" onclick="expandAllDates()">
                    <i class="fa-solid fa-angles-down"></i> Mở rộng tất cả
                </button>
                <button type="button" class="btn btn-secondary btn-sm" onclick="collapseAllDates()">
                    <i class="fa-solid fa-angles-up"></i> Thu gọn tất cả
                </button>
            </div>
        <?php endif; ?>
    </div>

    <a href="<?= BASE_URL ?>/nhaphang/them.php" class="btn btn-success" style="box-shadow: 0 4px 12px rgba(16, 185, 129, 0.25); font-weight: 600;">
        <i class="fa-solid fa-circle-plus"></i> Nhập Hàng Mới
    </a>
</div>

<!-- Thẻ KPI Thống Kê Tổng Quan Trong Tháng -->
<div class="kpi-grid" style="grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); margin-bottom: 24px;">
    <div class="kpi-card">
        <div class="kpi-icon" style="background: rgba(99, 102, 241, 0.1); color: var(--primary);">
            <i class="fa-solid fa-calendar-check"></i>
        </div>
        <div class="kpi-content">
            <div class="kpi-label">Số Đợt Nhập Hàng</div>
            <div class="kpi-value" style="color: var(--primary);"><?= count($groupedByDate) ?> <span style="font-size: 13px; font-weight: normal; color: var(--text-muted);">ngày</span></div>
        </div>
    </div>
    <div class="kpi-card">
        <div class="kpi-icon" style="background: rgba(16, 185, 129, 0.1); color: var(--success);">
            <i class="fa-solid fa-boxes-stacked"></i>
        </div>
        <div class="kpi-content">
            <div class="kpi-label">Tổng Lượt Mặt Hàng</div>
            <div class="kpi-value" style="color: var(--success);"><?= formatNumber($totalMonthRows) ?> <span style="font-size: 13px; font-weight: normal; color: var(--text-muted);">lượt món</span></div>
        </div>
    </div>
    <div class="kpi-card">
        <div class="kpi-icon" style="background: rgba(14, 165, 233, 0.1); color: #0284c7;">
            <i class="fa-solid fa-truck-ramp-box"></i>
        </div>
        <div class="kpi-content">
            <div class="kpi-label">Tổng Số Lượng Nhập</div>
            <div class="kpi-value" style="color: #0284c7;">+<?= formatNumber($totalMonthQty) ?> <span style="font-size: 13px; font-weight: normal; color: var(--text-muted);">đơn vị</span></div>
        </div>
    </div>
</div>

<!-- Hướng dẫn sử dụng nhanh -->
<div style="margin-bottom: 16px; padding: 12px 18px; background: #f0fdf4; border-radius: 10px; border: 1px solid #bbf7d0; font-size: 13px; color: #166534; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 8px;">
    <div style="display: flex; align-items: center; gap: 8px;">
        <i class="fa-solid fa-circle-info" style="font-size: 16px; color: #16a34a;"></i>
        <span><strong>Giao diện thông minh:</strong> Các ngày nhập hàng đã được <strong>thu gọn lại</strong>. Bạn chỉ cần <strong>nhấp vào ngày</strong> để xem các mặt hàng đã nhập, hoặc bấm nút <strong>"✏️ Đổi Ngày"</strong> nếu lỡ tay chọn sai ngày nhập!</span>
    </div>
</div>

<?php if (empty($groupedByDate)): ?>
    <div class="card" style="text-align: center; padding: 50px 20px;">
        <div style="font-size: 48px; color: var(--text-muted); margin-bottom: 12px;">
            <i class="fa-solid fa-box-open"></i>
        </div>
        <h3 style="font-size: 17px; font-weight: 700; color: var(--text-primary); margin-bottom: 6px;">Chưa có phát sinh nhập hàng trong tháng <?= date('m/Y', strtotime($thang . '-01')) ?></h3>
        <p style="color: var(--text-secondary); font-size: 13px; max-width: 400px; margin: 0 auto 16px;">
            Bạn có thể tạo phiếu nhập hàng mới cho nhà cung cấp hoặc xưởng bánh để cộng tồn kho lý thuyết.
        </p>
        <a href="<?= BASE_URL ?>/nhaphang/them.php" class="btn btn-success">
            <i class="fa-solid fa-plus"></i> Tạo Phiếu Nhập Hàng Ngay
        </a>
    </div>
<?php else: ?>
    <!-- Danh Sách Các Đợt Nhập Hàng Thu Gọn Dạng Accordion Theo Ngày -->
    <div class="import-days-container" style="display: flex; flex-direction: column; gap: 14px;">
        <?php foreach ($groupedByDate as $dateKey => $group): ?>
            <div class="card import-day-card" id="day-card-<?= $dateKey ?>" style="overflow: hidden; border: 1px solid var(--border-color); border-radius: 12px; transition: all 0.2s ease;">
                <!-- Header của Ngày (Click để Mở Rộng / Thu Gọn) -->
                <div class="import-day-header" onclick="toggleDateCard('<?= $dateKey ?>')" style="padding: 14px 20px; background: #ffffff; cursor: pointer; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px; user-select: none;">
                    <div style="display: flex; align-items: center; gap: 14px; flex-wrap: wrap;">
                        <!-- Icon Lịch Xanh -->
                        <div style="width: 42px; height: 42px; border-radius: 10px; background: linear-gradient(135deg, #10b981, #059669); color: #fff; display: flex; align-items: center; justify-content: center; font-size: 18px; box-shadow: 0 4px 10px rgba(16, 185, 129, 0.25);">
                            <i class="fa-solid fa-calendar-day"></i>
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
                                    <i class="fa-solid fa-boxes-stacked"></i> <?= $group['item_count'] ?> Mặt Hàng
                                </span>
                                <span class="badge badge-success" style="font-size: 13px; font-weight: 800;">
                                    +<?= formatNumber($group['total_qty']) ?> Cái
                                </span>
                            </div>

                            <div style="font-size: 12px; color: var(--text-secondary); margin-top: 4px; display: flex; gap: 16px; flex-wrap: wrap;">
                                <?php if (!empty($group['nha_cung_cap'])): ?>
                                    <span><i class="fa-solid fa-truck" style="color: var(--primary);"></i> <strong>NCC:</strong> <?= htmlspecialchars(implode(', ', $group['nha_cung_cap'])) ?></span>
                                <?php endif; ?>
                                <?php if (!empty($group['nguoi_nhap'])): ?>
                                    <span><i class="fa-solid fa-user-check" style="color: var(--success);"></i> <strong>Người nhập:</strong> <?= htmlspecialchars(implode(', ', $group['nguoi_nhap'])) ?></span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Thao Tác Cấp Ngày: Đổi Ngày / Xóa Đợt / Mũi Tên Mở Rộng -->
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <!-- Nút Đổi Ngày Nhập -->
                        <button type="button" class="btn btn-sm btn-outline-primary" onclick="event.stopPropagation(); openModalDoiNgayCuaNgay('<?= $group['ngay'] ?>', <?= $group['item_count'] ?>)" title="Đổi ngày nhập khi lỡ tay chọn sai ngày" style="font-weight: 600; background: #eef2ff; border-color: #c7d2fe; color: #4338ca;">
                            <i class="fa-solid fa-calendar-pen"></i> Đổi Ngày Nhập
                        </button>

                        <!-- Nút Xóa Toàn Bộ Đợt Nhập Của Ngày -->
                        <button type="button" class="btn btn-sm btn-outline-danger" onclick="event.stopPropagation(); confirmXoaNgay('<?= $group['ngay'] ?>', <?= $group['item_count'] ?>)" title="Xóa toàn bộ các mặt hàng nhập ngày này" style="font-weight: 600;">
                            <i class="fa-solid fa-trash-can"></i> Xóa Đợt
                        </button>

                        <!-- Mũi tên toggle -->
                        <div class="toggle-chevron" style="width: 30px; height: 30px; border-radius: 50%; background: #f8fafc; border: 1px solid var(--border-color); display: flex; align-items: center; justify-content: center; color: var(--text-secondary); transition: transform 0.25s ease;">
                            <i class="fa-solid fa-chevron-down"></i>
                        </div>
                    </div>
                </div>

                <!-- Bảng Chi Tiết Mặt Hàng Của Ngày Đó (Mặc định thu gọn) -->
                <div class="import-day-body" style="display: none; border-top: 1px solid var(--border-color); background: #fafafa;">
                    <div class="table-responsive">
                        <table class="table" style="margin: 0;">
                            <thead style="background: #f1f5f9;">
                                <tr>
                                    <th style="width: 120px;">Mã SP</th>
                                    <th>Tên Sản Phẩm</th>
                                    <th>Nhóm Sản Phẩm</th>
                                    <th style="text-align: right; width: 140px;">Số Lượng Nhập</th>
                                    <th style="text-align: center; width: 70px;">ĐVT</th>
                                    <th>Nhà Cung Cấp</th>
                                    <th>Ghi Chú</th>
                                    <th>Người Nhập</th>
                                    <th style="text-align: center; width: 110px;">Thao Tác</th>
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
                                        <td style="text-align: right; font-weight: 800; color: var(--success); font-size: 15px;">
                                            +<?= formatNumber($item['so_luong']) ?>
                                        </td>
                                        <td style="text-align: center; font-weight: 600; color: var(--text-secondary);">
                                            <?= htmlspecialchars($item['don_vi_tinh']) ?>
                                        </td>
                                        <td style="font-size: 12px; color: var(--text-secondary);">
                                            <?= htmlspecialchars(!empty($item['nha_cung_cap']) ? $item['nha_cung_cap'] : '-') ?>
                                        </td>
                                        <td style="font-size: 12px; color: var(--text-muted);">
                                            <?= htmlspecialchars(!empty($item['ghi_chu']) ? $item['ghi_chu'] : '-') ?>
                                        </td>
                                        <td style="font-size: 12px; color: var(--text-secondary);">
                                            <?= htmlspecialchars($item['nguoi_nhap'] ?? 'Admin') ?>
                                        </td>
                                        <td style="text-align: center;">
                                            <div style="display: inline-flex; gap: 5px;">
                                                <!-- Sửa ngày cho riêng món này -->
                                                <button type="button" class="btn btn-secondary btn-sm" onclick="openModalDoiNgayMonLe(<?= $item['id'] ?>, '<?= htmlspecialchars(addslashes($item['ten_sp'])) ?>', '<?= $item['ngay_nhap'] ?>')" title="Đổi ngày cho riêng món này" style="padding: 3px 8px;">
                                                    <i class="fa-solid fa-pen"></i>
                                                </button>
                                                <!-- Xóa riêng món này -->
                                                <a href="<?= BASE_URL ?>/nhaphang/xoa.php?id=<?= $item['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Bạn có chắc muốn xóa dòng nhập hàng [<?= htmlspecialchars(addslashes($item['ten_sp'])) ?>] (+<?= $item['so_luong'] ?>)? Tồn kho lý thuyết sẽ được tự động tính lại.');" title="Xóa mặt hàng này" style="padding: 3px 8px;">
                                                    <i class="fa-solid fa-trash"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                            <tfoot style="background: #f8fafc; font-weight: 700; border-top: 2px solid var(--border-color);">
                                <tr>
                                    <td colspan="3" style="text-align: right; color: var(--text-secondary); font-size: 13px;">
                                        Tổng cộng ngày <?= formatDate($group['ngay']) ?> (<?= $group['item_count'] ?> món):
                                    </td>
                                    <td style="text-align: right; color: var(--success); font-size: 16px;">
                                        +<?= formatNumber($group['total_qty']) ?>
                                    </td>
                                    <td colspan="5" style="font-size: 12px; color: var(--text-muted); font-weight: normal;">
                                        Đã cộng dồn vào tồn kho lý thuyết
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
<!-- MODAL 1: ĐỔI NGÀY NHẬP HÀNG CẢ ĐỢT (DÀNH CHO KHI NHẬP SAI NGÀY CẢ PHIẾU) -->
<!-- ========================================================================= -->
<div class="modal-overlay" id="modal-doi-ngay-batch">
    <div class="modal-card" style="max-width: 480px;">
        <div class="modal-header" style="background: linear-gradient(135deg, #eef2ff, #e0e7ff); border-bottom: 1px solid #c7d2fe;">
            <div class="modal-title" style="color: #3730a3; display: flex; align-items: center; gap: 10px;">
                <i class="fa-solid fa-calendar-pen" style="font-size: 20px;"></i>
                <span>Chỉnh Sửa Ngày Nhập Hàng</span>
            </div>
            <button type="button" onclick="closeModal('modal-doi-ngay-batch')" style="background: none; border: none; font-size: 24px; cursor: pointer; color: #64748b; line-height: 1;">&times;</button>
        </div>
        
        <form method="POST" action="<?= BASE_URL ?>/nhaphang/doi_ngay.php" style="padding: 24px;">
            <input type="hidden" name="action" value="batch">
            <input type="hidden" name="ngay_cu" id="batch-ngay-cu-input" value="">

            <div style="margin-bottom: 16px;">
                <label class="form-label" style="font-size: 13px; font-weight: 600; color: var(--text-secondary);">Đợt nhập hiện tại:</label>
                <div style="padding: 10px 14px; background: #f1f5f9; border-radius: 8px; font-weight: 700; color: var(--text-primary); font-size: 15px; display: flex; justify-content: space-between; align-items: center;">
                    <span id="batch-ngay-cu-text">-</span>
                    <span class="badge badge-primary" id="batch-item-count-text">0 món</span>
                </div>
            </div>

            <div style="margin-bottom: 20px;">
                <label class="form-label" style="font-size: 13px; font-weight: 700; color: var(--text-primary);">
                    <i class="fa-solid fa-calendar-check" style="color: var(--primary);"></i> Chọn Ngày Nhập Mới Đúng Thực Tế:
                </label>
                <input type="date" name="ngay_moi" id="batch-ngay-moi-input" class="form-control" required style="font-size: 15px; font-weight: 600; padding: 10px 14px;">
            </div>

            <div style="padding: 12px 14px; background: #eff6ff; border-radius: 8px; border: 1px solid #bfdbfe; font-size: 12px; color: #1e40af; margin-bottom: 24px;">
                <i class="fa-solid fa-shield-halved"></i> <strong>Cơ chế tự động:</strong> Khi bạn lưu ngày mới, toàn bộ các mặt hàng của đợt này sẽ chuyển sang ngày mới. <strong>Tồn kho lý thuyết và báo cáo chênh lệch</strong> sẽ được tự động tính toán lại hoàn toàn chuẩn xác.
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
<!-- MODAL 2: ĐỔI NGÀY NHẬP CHO RIÊNG 1 MÓN LẺ -->
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
        
        <form method="POST" action="<?= BASE_URL ?>/nhaphang/doi_ngay.php" style="padding: 24px;">
            <input type="hidden" name="action" value="single">
            <input type="hidden" name="id" id="single-item-id-input" value="">

            <div style="margin-bottom: 16px;">
                <label class="form-label" style="font-size: 12px; color: var(--text-secondary);">Tên sản phẩm:</label>
                <div style="font-weight: 700; color: var(--text-primary); font-size: 15px;" id="single-item-name-text">-</div>
            </div>

            <div style="margin-bottom: 24px;">
                <label class="form-label" style="font-size: 13px; font-weight: 700; color: var(--text-primary);">
                    <i class="fa-solid fa-calendar-days" style="color: var(--primary);"></i> Chọn Ngày Nhập Mới:
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
    
    const body = card.querySelector('.import-day-body');
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
    document.querySelectorAll('.import-day-card').forEach(card => {
        const body = card.querySelector('.import-day-body');
        const chevron = card.querySelector('.toggle-chevron i');
        if (body) body.style.display = 'block';
        if (chevron) chevron.className = 'fa-solid fa-chevron-up';
        card.style.borderColor = 'var(--primary)';
    });
}

// Thu gọn toàn bộ các ngày
function collapseAllDates() {
    document.querySelectorAll('.import-day-card').forEach(card => {
        const body = card.querySelector('.import-day-body');
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

// Xác nhận xóa toàn bộ phiếu nhập của ngày
function confirmXoaNgay(ngay, itemCount) {
    const parts = ngay.split('-');
    const dateFormatted = parts.length === 3 ? `${parts[2]}/${parts[1]}/${parts[0]}` : ngay;
    if (confirm(`⚠️ CẢNH BÁO: Bạn có chắc chắn muốn xóa TOÀN BỘ phiếu nhập hàng ngày [${dateFormatted}] gồm ${itemCount} mặt hàng?\n\nSố lượng nhập sẽ bị thu hồi và tồn kho lý thuyết sẽ được tự động tính lại.`)) {
        window.location.href = '<?= BASE_URL ?>/nhaphang/xoa_ngay.php?ngay=' + encodeURIComponent(ngay);
    }
}
</script>

<style>
.import-day-header:hover {
    background: #f8fafc !important;
}
.import-day-card {
    transition: box-shadow 0.2s ease, border-color 0.2s ease;
}
</style>

<?php include __DIR__ . '/../includes/footer.php'; ?>
