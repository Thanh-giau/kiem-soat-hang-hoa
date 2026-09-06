<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';
requireLogin();

$pageTitle = "Báo Cáo Đối Chiếu Chênh Lệch Kho";
$extraCss = ['baocao.css'];
$db = getDB();

$ngayBaoCao = $_GET['ngay'] ?? date('Y-m-d');
$locTinhTrang = $_GET['tinh_trang'] ?? ''; // 'tat_ca', 'chenh_lech', 'thieu', 'du', 'khop'
$locNhom = $_GET['nhom'] ?? '';

// Lấy toàn bộ dữ liệu đối chiếu kiểm kê
$danhSach = layBaoCaoTonKhoChiTiet($ngayBaoCao, null, true);

// Lọc theo điều kiện
$filteredList = [];
$tongTonDau = 0;
$tongNhap = 0;
$tongMuon = 0;
$tongBan = 0;
$tongHuy = 0;
$tongChoMuon = 0;
$tongLyThuyet = 0;
$tongThucTe = 0;

foreach ($danhSach as $item) {
    if ($locNhom !== '' && $item['nhom_sp'] !== $locNhom) {
        continue;
    }

    $chenh = $item['chenh_lech'];

    if ($locTinhTrang === 'chenh_lech' && ($chenh === null || $chenh == 0)) {
        continue;
    }
    if ($locTinhTrang === 'thieu' && ($chenh === null || $chenh >= 0)) {
        continue;
    }
    if ($locTinhTrang === 'du' && ($chenh === null || $chenh <= 0)) {
        continue;
    }
    if ($locTinhTrang === 'khop' && ($chenh === null || $chenh != 0)) {
        continue;
    }

    $filteredList[] = $item;

    $tongTonDau += (float)$item['ton_dau'];
    $tongNhap += (float)$item['tong_nhap'];
    $tongMuon += (float)$item['tong_muon'];
    $tongBan += (float)$item['tong_ban'];
    $tongHuy += (float)$item['tong_huy'];
    $tongChoMuon += (float)$item['tong_cho_muon'];
    $tongLyThuyet += (float)$item['ton_ly_thuyet'];
    if ($item['ton_thuc_te'] !== null) {
        $tongThucTe += (float)$item['ton_thuc_te'];
    }
}

// Lấy danh sách nhóm sản phẩm
$nhomList = $db->query("SELECT DISTINCT nhom_sp FROM san_pham WHERE can_kiem_ke = 1 ORDER BY nhom_sp ASC")->fetchAll(PDO::FETCH_COLUMN);

include __DIR__ . '/../includes/header.php';
?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; flex-wrap: wrap; gap: 12px;">
    <!-- Form Lọc Báo Cáo -->
    <form method="GET" action="" style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
        <div style="display: flex; align-items: center; gap: 6px;">
            <label class="form-label" style="margin: 0; font-weight: 600;">Ngày:</label>
            <input type="date" name="ngay" class="form-control" value="<?= htmlspecialchars($ngayBaoCao) ?>" style="width: 160px;">
        </div>

        <div style="display: flex; align-items: center; gap: 6px;">
            <label class="form-label" style="margin: 0; font-weight: 600;">Nhóm:</label>
            <select name="nhom" class="form-control" style="width: 150px;">
                <option value="">-- Tất cả nhóm --</option>
                <?php foreach ($nhomList as $n): ?>
                    <option value="<?= htmlspecialchars($n) ?>" <?= $locNhom === $n ? 'selected' : '' ?>><?= htmlspecialchars($n) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div style="display: flex; align-items: center; gap: 6px;">
            <label class="form-label" style="margin: 0; font-weight: 600;">Tình trạng:</label>
            <select name="tinh_trang" class="form-control" style="width: 180px;">
                <option value="">-- Tất cả mặt hàng --</option>
                <option value="chenh_lech" <?= $locTinhTrang === 'chenh_lech' ? 'selected' : '' ?>>⚠️ Chỉ hàng chênh lệch</option>
                <option value="thieu" <?= $locTinhTrang === 'thieu' ? 'selected' : '' ?>>🔴 Chỉ hàng bị thiếu</option>
                <option value="du" <?= $locTinhTrang === 'du' ? 'selected' : '' ?>>🟠 Chỉ hàng bị dư</option>
                <option value="khop" <?= $locTinhTrang === 'khop' ? 'selected' : '' ?>>🟢 Chỉ hàng khớp (0)</option>
            </select>
        </div>

        <button type="submit" class="btn btn-primary">
            <i class="fa-solid fa-filter"></i> Lọc
        </button>
    </form>

    <div style="display: flex; gap: 10px;">
        <a href="<?= BASE_URL ?>/kiemke/kiem_ke.php?ngay=<?= urlencode($ngayBaoCao) ?>" class="btn btn-secondary">
            <i class="fa-solid fa-pen"></i> Chỉnh Sửa Kiểm Kê
        </a>
        <a href="<?= BASE_URL ?>/baocao/xuat_excel.php?type=chenh_lech&ngay=<?= urlencode($ngayBaoCao) ?>" class="btn btn-success">
            <i class="fa-solid fa-file-excel"></i> Xuất File Excel
        </a>
    </div>
</div>

<!-- BẢNG BÁO CÁO CHÍNH XÁC THEO MỤC 16 -->
<div class="card">
    <div class="card-header">
        <h2 class="card-title">
            <i class="fa-solid fa-scale-unbalanced" style="color: var(--primary);"></i>
            Bảng Báo Cáo Đối Chiếu Tồn Kho & Chênh Lệch Ngày <?= formatDate($ngayBaoCao) ?>
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
                        <th style="text-align: right; background: #f8fafc;">Tồn Đầu</th>
                        <th style="text-align: right; background: #ecfdf5; color: #065f46;">Nhập (+)</th>
                        <th style="text-align: right; background: #ecfdf5; color: #065f46;">Mượn (+)</th>
                        <th style="text-align: right; background: #fef2f2; color: #991b1b;">Bán (-)</th>
                        <th style="text-align: right; background: #fef2f2; color: #991b1b;">Hủy (-)</th>
                        <th style="text-align: right; background: #fef2f2; color: #991b1b;">Cho Mượn (-)</th>
                        <th style="text-align: right; background: #eef2ff; color: var(--primary); font-weight: 800;">Tồn Lý Thuyết</th>
                        <th style="text-align: right; font-weight: 800;">Thực Tế</th>
                        <th style="text-align: center;">Chênh Lệch</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($filteredList)): ?>
                        <tr>
                            <td colspan="12" style="text-align: center; padding: 30px; color: var(--text-muted);">
                                Không tìm thấy sản phẩm nào theo điều kiện lọc.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($filteredList as $item): ?>
                            <tr>
                                <td><strong><?= htmlspecialchars($item['ma_sp']) ?></strong></td>
                                <td><?= htmlspecialchars($item['ten_sp']) ?></td>
                                <td><span class="badge badge-secondary"><?= htmlspecialchars($item['nhom_sp']) ?></span></td>
                                <td style="text-align: right; background: #f8fafc;"><?= formatNumber($item['ton_dau']) ?></td>
                                <td style="text-align: right; background: #ecfdf5; font-weight: 600; color: #065f46;"><?= formatNumber($item['tong_nhap']) ?></td>
                                <td style="text-align: right; background: #ecfdf5; font-weight: 600; color: #065f46;"><?= formatNumber($item['tong_muon']) ?></td>
                                <td style="text-align: right; background: #fef2f2; font-weight: 600; color: #991b1b;"><?= formatNumber($item['tong_ban']) ?></td>
                                <td style="text-align: right; background: #fef2f2; font-weight: 600; color: #991b1b;"><?= formatNumber($item['tong_huy']) ?></td>
                                <td style="text-align: right; background: #fef2f2; font-weight: 600; color: #991b1b;"><?= formatNumber($item['tong_cho_muon']) ?></td>
                                <td style="text-align: right; background: #eef2ff; font-weight: 800; font-size: 15px; color: var(--primary);">
                                    <?= formatNumber($item['ton_ly_thuyet']) ?>
                                </td>
                                <td style="text-align: right; font-weight: 800; font-size: 15px;">
                                    <?= ($item['ton_thuc_te'] !== null) ? formatNumber($item['ton_thuc_te']) : '<span style="color:var(--text-muted);">-</span>' ?>
                                </td>
                                <td style="text-align: center;">
                                    <?= renderChenhLechBadge($item['chenh_lech']) ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
                <tfoot>
                    <tr style="background: #f1f5f9; font-weight: 700;">
                        <td colspan="3" style="text-align: right;">TỔNG CỘNG:</td>
                        <td style="text-align: right;"><?= formatNumber($tongTonDau) ?></td>
                        <td style="text-align: right; color: #065f46;"><?= formatNumber($tongNhap) ?></td>
                        <td style="text-align: right; color: #065f46;"><?= formatNumber($tongMuon) ?></td>
                        <td style="text-align: right; color: #991b1b;"><?= formatNumber($tongBan) ?></td>
                        <td style="text-align: right; color: #991b1b;"><?= formatNumber($tongHuy) ?></td>
                        <td style="text-align: right; color: #991b1b;"><?= formatNumber($tongChoMuon) ?></td>
                        <td style="text-align: right; color: var(--primary); font-size: 15px;"><?= formatNumber($tongLyThuyet) ?></td>
                        <td style="text-align: right; font-size: 15px;"><?= formatNumber($tongThucTe) ?></td>
                        <td style="text-align: center;">
                            <?php 
                            $tongChenh = $tongThucTe - $tongLyThuyet;
                            echo renderChenhLechBadge($tongChenh);
                            ?>
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>

<div style="margin-top: 20px; font-size: 13px; color: var(--text-secondary);">
    <strong>Công thức đối chiếu:</strong> <code>Tồn lý thuyết = Tồn đầu + Nhập + Mượn - Bán - Hủy - Cho mượn</code> | <code>Chênh lệch = Tồn thực tế - Tồn lý thuyết</code>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
