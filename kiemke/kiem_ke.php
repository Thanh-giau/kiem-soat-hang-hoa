<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';
requireLogin();

$pageTitle = "Kiểm Kê Kho Thực Tế";
$extraCss = ['kiemke.css'];
$db = getDB();

$ngayKiemKe = $_GET['ngay'] ?? date('Y-m-d');

// Lấy danh sách chỉ các sản phẩm CẦN KIỂM KÊ (Mục 14 trong yêu cầu: Bánh, Đồ ăn, Nước suối...)
// Kèm theo tồn lý thuyết đã được tự động tính toán theo công thức lõi:
// TỒN LÝ THUYẾT = TỒN ĐẦU + NHẬP + MƯỢN - BÁN - HỦY - CHO MƯỢN
$danhSachKiemKe = layBaoCaoTonKhoChiTiet($ngayKiemKe, null, true);

include __DIR__ . '/../includes/header.php';
?>

<form method="POST" action="<?= BASE_URL ?>/kiemke/luu.php" id="form-kiem-ke">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; flex-wrap: wrap; gap: 12px;">
        <div style="display: flex; align-items: center; gap: 10px;">
            <label class="form-label" style="margin: 0; font-weight: 700; font-size: 15px;">
                <i class="fa-regular fa-calendar-check"></i> Ngày kiểm kê:
            </label>
            <input type="date" name="ngay_kiem_ke" class="form-control" value="<?= htmlspecialchars($ngayKiemKe) ?>" onchange="location.href='?ngay=' + this.value" style="width: 180px; font-weight: 600;">
        </div>

        <div style="display: flex; gap: 10px;">
            <a href="<?= BASE_URL ?>/kiemke/lich_su.php" class="btn btn-secondary">
                <i class="fa-solid fa-clock-rotate-left"></i> Lịch Sử Kiểm Kê
            </a>
            <button type="submit" class="btn btn-success btn-lg">
                <i class="fa-solid fa-floppy-disk"></i> Lưu Kết Quả Kiểm Kê
            </button>
        </div>
    </div>

    <!-- Thông tin chỉ dẫn kiểm kê -->
    <div class="alert alert-info" style="margin-bottom: 20px;">
        <i class="fa-solid fa-circle-info" style="font-size: 20px;"></i>
        <div>
            <strong>LƯU Ý KHI ĐI KIỂM KHO:</strong> Danh sách bên dưới chỉ hiển thị các sản phẩm thuộc diện <strong>CẦN KIỂM KÊ</strong> (Bánh, Đồ ăn, Nước suối...). Các món nước pha chế đã được tự động loại trừ. Nhập số thực tế đếm được, hệ thống sẽ tự động tính toán chênh lệch ngay tức thì!
        </div>
    </div>

    <!-- BẢNG KIỂM KÊ (Desktop & Tablet) -->
    <div class="card">
        <div class="card-header">
            <h2 class="card-title">
                <i class="fa-solid fa-clipboard-list" style="color: var(--primary);"></i>
                Biểu Mẫu Kiểm Kê Ngày <?= formatDate($ngayKiemKe) ?> (<?= count($danhSachKiemKe) ?> Mặt Hàng)
            </h2>
        </div>
        <div class="card-body" style="padding: 0;">
            <div class="table-responsive">
                <table class="table" id="table-kiem-ke">
                    <thead>
                        <tr>
                            <th style="width: 100px;">Mã SP</th>
                            <th>Tên Sản Phẩm</th>
                            <th>Nhóm</th>
                            <th style="text-align: center;">ĐVT</th>
                            <th style="text-align: right; width: 140px;">Tồn Lý Thuyết</th>
                            <th style="text-align: center; width: 220px;">Số Thực Tế Kiểm Được</th>
                            <th style="text-align: center; width: 170px;">Chênh Lệch</th>
                            <th style="width: 200px;">Ghi Chú</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($danhSachKiemKe)): ?>
                            <tr>
                                <td colspan="8" style="text-align: center; padding: 30px; color: var(--text-muted);">
                                    Không có sản phẩm nào thuộc diện kiểm kê.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($danhSachKiemKe as $item): 
                                $lyThuyet = (float)$item['ton_ly_thuyet'];
                                $thucTe = ($item['ton_thuc_te'] !== null) ? (float)$item['ton_thuc_te'] : $lyThuyet;
                                $chenhLech = $thucTe - $lyThuyet;
                            ?>
                                <tr class="kiem-ke-row" data-sp-id="<?= $item['san_pham_id'] ?>" data-ly-thuyet="<?= $lyThuyet ?>">
                                    <input type="hidden" name="items[<?= $item['san_pham_id'] ?>][san_pham_id]" value="<?= $item['san_pham_id'] ?>">
                                    <input type="hidden" name="items[<?= $item['san_pham_id'] ?>][ton_ly_thuyet]" value="<?= $lyThuyet ?>">
                                    
                                    <td><strong style="color: var(--primary); font-size: 15px;"><?= htmlspecialchars($item['ma_sp']) ?></strong></td>
                                    <td>
                                        <div style="font-weight: 600; font-size: 15px;"><?= htmlspecialchars($item['ten_sp']) ?></div>
                                    </td>
                                    <td><span class="badge badge-secondary"><?= htmlspecialchars($item['nhom_sp']) ?></span></td>
                                    <td style="text-align: center; font-weight: 600;"><?= htmlspecialchars($item['don_vi_tinh']) ?></td>
                                    
                                    <td style="text-align: right; font-weight: 700; font-size: 16px; color: #1e293b;">
                                        <?= formatNumber($lyThuyet) ?>
                                    </td>
                                    
                                    <!-- Cụm nhập số thực tế với nút tăng giảm to bản cho điện thoại -->
                                    <td style="text-align: center;">
                                        <div style="display: flex; align-items: center; justify-content: center; gap: 6px;">
                                            <button type="button" class="btn-step" onclick="stepCount(<?= $item['san_pham_id'] ?>, -1)">-</button>
                                            <input type="number" 
                                                   step="any" 
                                                   name="items[<?= $item['san_pham_id'] ?>][ton_thuc_te]" 
                                                   id="input-thuc-te-<?= $item['san_pham_id'] ?>" 
                                                   class="form-control input-stock" 
                                                   value="<?= $thucTe ?>" 
                                                   oninput="recalcDiff(<?= $item['san_pham_id'] ?>)" 
                                                   required>
                                            <button type="button" class="btn-step" onclick="stepCount(<?= $item['san_pham_id'] ?>, 1)">+</button>
                                        </div>
                                    </td>

                                    <!-- Vùng hiển thị chênh lệch tự động đổi màu -->
                                    <td style="text-align: center;" id="diff-box-<?= $item['san_pham_id'] ?>">
                                        <?= renderChenhLechBadge($chenhLech) ?>
                                    </td>

                                    <td>
                                        <input type="text" 
                                               name="items[<?= $item['san_pham_id'] ?>][ghi_chu]" 
                                               class="form-control" 
                                               placeholder="Ghi chú nếu có..." 
                                               value="<?= htmlspecialchars($item['ghi_chu_kiem_ke'] ?? '') ?>">
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div style="display: flex; justify-content: flex-end; gap: 12px; margin-top: 24px;">
        <button type="submit" class="btn btn-success btn-lg" style="padding: 14px 30px; font-size: 17px;">
            <i class="fa-solid fa-floppy-disk"></i> Lưu Toàn Bộ Kết Quả Kiểm Kê
        </button>
    </div>
</form>

<script>
// Tăng giảm số lượng bằng nút +/-
function stepCount(spId, delta) {
    const input = document.getElementById('input-thuc-te-' + spId);
    let val = parseFloat(input.value) || 0;
    val += delta;
    if (val < 0) val = 0;
    input.value = val;
    recalcDiff(spId);
}

// Tự động tính chênh lệch = Thực tế - Tồn lý thuyết ngay khi gõ
function recalcDiff(spId) {
    const row = document.querySelector(`.kiem-ke-row[data-sp-id="${spId}"]`);
    if (!row) return;

    const lyThuyet = parseFloat(row.getAttribute('data-ly-thuyet')) || 0;
    const input = document.getElementById('input-thuc-te-' + spId);
    const thucTe = parseFloat(input.value) || 0;
    const diff = thucTe - lyThuyet;

    const box = document.getElementById('diff-box-' + spId);
    if (diff === 0) {
        box.innerHTML = '<span class="badge badge-success"><i class="fa-solid fa-check"></i> Khớp (0)</span>';
    } else if (diff < 0) {
        box.innerHTML = `<span class="badge badge-danger"><i class="fa-solid fa-circle-down"></i> Thiếu (${diff})</span>`;
    } else {
        box.innerHTML = `<span class="badge badge-warning"><i class="fa-solid fa-circle-up"></i> Dư (+${diff})</span>`;
    }
}
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
