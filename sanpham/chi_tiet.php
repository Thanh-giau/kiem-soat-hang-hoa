<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';
requireLogin();

$id = (int)($_GET['id'] ?? 0);
$db = getDB();

$stmt = $db->prepare("SELECT * FROM san_pham WHERE id = :id LIMIT 1");
$stmt->execute(['id' => $id]);
$sanPham = $stmt->fetch();

if (!$sanPham) {
    setFlash('danger', 'Không tìm thấy sản phẩm yêu cầu!');
    header("Location: " . BASE_URL . "/sanpham/danh_sach.php");
    exit;
}

$pageTitle = "Chi Tiết Sản Phẩm: " . $sanPham['ma_sp'];

// Tồn lý thuyết hiện tại
$today = date('Y-m-d');
$tonInfo = layBaoCaoTonKhoChiTiet($today, $id, false);
$itemTon = !empty($tonInfo) ? $tonInfo[0] : null;

// Lịch sử số bán gần đây
$stmtSB = $db->prepare("SELECT * FROM so_ban WHERE san_pham_id = :id ORDER BY ngay_ban DESC LIMIT 15");
$stmtSB->execute(['id' => $id]);
$lichSuSoBan = $stmtSB->fetchAll();

// Lịch sử nhập hàng gần đây
$stmtNH = $db->prepare("SELECT * FROM nhap_hang WHERE san_pham_id = :id ORDER BY ngay_nhap DESC LIMIT 15");
$stmtNH->execute(['id' => $id]);
$lichSuNhap = $stmtNH->fetchAll();

// Lịch sử điều chỉnh (hủy, mượn, cho mượn)
$stmtDC = $db->prepare("SELECT * FROM dieu_chinh_kho WHERE san_pham_id = :id ORDER BY ngay_dieu_chinh DESC LIMIT 15");
$stmtDC->execute(['id' => $id]);
$lichSuDC = $stmtDC->fetchAll();

include __DIR__ . '/../includes/header.php';
?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
    <div>
        <h2 style="font-size: 22px; font-weight: 700;"><?= htmlspecialchars($sanPham['ten_sp']) ?></h2>
        <div style="color: var(--text-secondary); font-size: 14px;">
            Mã SP: <strong style="color: var(--primary);"><?= htmlspecialchars($sanPham['ma_sp']) ?></strong> | 
            Nhóm: <span class="badge badge-secondary"><?= htmlspecialchars($sanPham['nhom_sp']) ?></span> | 
            ĐVT: <strong><?= htmlspecialchars($sanPham['don_vi_tinh']) ?></strong>
        </div>
    </div>
    <div style="display: flex; gap: 8px;">
        <a href="<?= BASE_URL ?>/sanpham/sua.php?id=<?= $sanPham['id'] ?>" class="btn btn-secondary">
            <i class="fa-solid fa-pen"></i> Chỉnh sửa
        </a>
        <a href="<?= BASE_URL ?>/sanpham/danh_sach.php" class="btn btn-secondary">
            <i class="fa-solid fa-arrow-left"></i> Danh sách
        </a>
    </div>
</div>

<!-- Thẻ trạng thái tồn kho hiện tại -->
<div class="card" style="margin-bottom: 24px; border-top: 4px solid var(--primary);">
    <div class="card-header">
        <h3 class="card-title"><i class="fa-solid fa-calculator"></i> Tồn Kho Lý Thuyết Hiện Tại (<?= formatDate($today) ?>)</h3>
        <div>
            <?php if ($sanPham['can_kiem_ke'] == 1): ?>
                <span class="badge badge-success"><i class="fa-solid fa-check"></i> Thuộc diện kiểm kê hàng ngày</span>
            <?php else: ?>
                <span class="badge badge-secondary"><i class="fa-solid fa-xmark"></i> Đồ uống pha chế (Bỏ qua kiểm kê)</span>
            <?php endif; ?>
        </div>
    </div>
    <div class="card-body">
        <?php if ($itemTon): ?>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(130px, 1fr)); gap: 12px; text-align: center;">
                <div style="background: #f8fafc; padding: 12px; border-radius: 8px; border: 1px solid var(--border-color);">
                    <div style="font-size: 11px; color: var(--text-muted); font-weight: 600;">TỒN ĐẦU</div>
                    <div style="font-size: 20px; font-weight: 700;"><?= formatNumber($itemTon['ton_dau']) ?></div>
                </div>
                <div style="background: #ecfdf5; padding: 12px; border-radius: 8px; border: 1px solid #a7f3d0; color: #065f46;">
                    <div style="font-size: 11px; font-weight: 600;">+ NHẬP HÀNG</div>
                    <div style="font-size: 20px; font-weight: 700;"><?= formatNumber($itemTon['tong_nhap']) ?></div>
                </div>
                <div style="background: #ecfdf5; padding: 12px; border-radius: 8px; border: 1px solid #a7f3d0; color: #065f46;">
                    <div style="font-size: 11px; font-weight: 600;">+ MƯỢN VỀ</div>
                    <div style="font-size: 20px; font-weight: 700;"><?= formatNumber($itemTon['tong_muon']) ?></div>
                </div>
                <div style="background: #fef2f2; padding: 12px; border-radius: 8px; border: 1px solid #fecaca; color: #991b1b;">
                    <div style="font-size: 11px; font-weight: 600;">- SỐ BÁN</div>
                    <div style="font-size: 20px; font-weight: 700;"><?= formatNumber($itemTon['tong_ban']) ?></div>
                </div>
                <div style="background: #fef2f2; padding: 12px; border-radius: 8px; border: 1px solid #fecaca; color: #991b1b;">
                    <div style="font-size: 11px; font-weight: 600;">- HỦY HÀNG</div>
                    <div style="font-size: 20px; font-weight: 700;"><?= formatNumber($itemTon['tong_huy']) ?></div>
                </div>
                <div style="background: #fef2f2; padding: 12px; border-radius: 8px; border: 1px solid #fecaca; color: #991b1b;">
                    <div style="font-size: 11px; font-weight: 600;">- CHO MƯỢN</div>
                    <div style="font-size: 20px; font-weight: 700;"><?= formatNumber($itemTon['tong_cho_muon']) ?></div>
                </div>
                <div style="background: var(--primary-light); padding: 12px; border-radius: 8px; border: 1px solid #c7d2fe; color: var(--primary);">
                    <div style="font-size: 11px; font-weight: 700;">= TỒN LÝ THUYẾT</div>
                    <div style="font-size: 22px; font-weight: 800;"><?= formatNumber($itemTon['ton_ly_thuyet']) ?></div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
    <!-- Lịch sử số bán -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title"><i class="fa-solid fa-chart-line"></i> Lịch Sử Số Bán Gần Đây</h3>
        </div>
        <div class="card-body" style="padding: 0;">
            <table class="table">
                <thead>
                    <tr>
                        <th>Ngày Bán</th>
                        <th style="text-align: right;">Số Lượng Bán</th>
                        <th>Ghi Chú</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($lichSuSoBan)): ?>
                        <tr><td colspan="3" style="text-align: center; color: var(--text-muted);">Chưa có dữ liệu số bán.</td></tr>
                    <?php else: ?>
                        <?php foreach ($lichSuSoBan as $b): ?>
                            <tr>
                                <td><?= formatDate($b['ngay_ban']) ?></td>
                                <td style="text-align: right; font-weight: 700; color: var(--danger);"><?= formatNumber($b['so_luong']) ?></td>
                                <td style="font-size: 12px; color: var(--text-muted);"><?= htmlspecialchars($b['ghi_chu'] ?? '') ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Lịch sử nhập hàng & điều chỉnh -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title"><i class="fa-solid fa-clock-rotate-left"></i> Nhập Hàng & Điều Chỉnh Gần Đây</h3>
        </div>
        <div class="card-body" style="padding: 0;">
            <table class="table">
                <thead>
                    <tr>
                        <th>Ngày</th>
                        <th>Loại</th>
                        <th style="text-align: right;">Số Lượng</th>
                        <th>Chi Tiết</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $bienDong = [];
                    foreach ($lichSuNhap as $n) {
                        $bienDong[] = [
                            'ngay' => $n['ngay_nhap'],
                            'loai' => 'Nhập hàng',
                            'color' => 'success',
                            'so_luong' => '+' . formatNumber($n['so_luong']),
                            'ghi_chu' => $n['ghi_chu'] ?? $n['nha_cung_cap']
                        ];
                    }
                    foreach ($lichSuDC as $dc) {
                        $tenLoai = $dc['loai'] === 'muon' ? 'Mượn về' : ($dc['loai'] === 'cho_muon' ? 'Cho mượn' : 'Hủy hàng');
                        $color = $dc['loai'] === 'muon' ? 'success' : 'danger';
                        $dau = $dc['loai'] === 'muon' ? '+' : '-';
                        $bienDong[] = [
                            'ngay' => $dc['ngay_dieu_chinh'],
                            'loai' => $tenLoai,
                            'color' => $color,
                            'so_luong' => $dau . formatNumber($dc['so_luong']),
                            'ghi_chu' => $dc['ghi_chu'] ?? $dc['nguoi_lien_quan']
                        ];
                    }
                    usort($bienDong, fn($a, $b) => strcmp($b['ngay'], $a['ngay']));
                    ?>
                    <?php if (empty($bienDong)): ?>
                        <tr><td colspan="4" style="text-align: center; color: var(--text-muted);">Chưa có lịch sử nhập hoặc điều chỉnh.</td></tr>
                    <?php else: ?>
                        <?php foreach (array_slice($bienDong, 0, 10) as $bd): ?>
                            <tr>
                                <td><?= formatDate($bd['ngay']) ?></td>
                                <td><span class="badge badge-<?= $bd['color'] ?>"><?= $bd['loai'] ?></span></td>
                                <td style="text-align: right; font-weight: 700;"><?= $bd['so_luong'] ?></td>
                                <td style="font-size: 12px; color: var(--text-muted);"><?= htmlspecialchars($bd['ghi_chu'] ?? '') ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
