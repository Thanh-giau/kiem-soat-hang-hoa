<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';
requireLogin();

$pageTitle = "Thêm Sản Phẩm Mới";
$error = '';
$db = getDB();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $maSP = strtoupper(trim($_POST['ma_sp'] ?? ''));
    $tenSP = trim($_POST['ten_sp'] ?? '');
    $nhomSP = trim($_POST['nhom_sp'] ?? '');
    $donViTinh = trim($_POST['don_vi_tinh'] ?? 'Cái');
    $canKiemKe = isset($_POST['can_kiem_ke']) ? 1 : 0;
    $tonDau = (float)($_POST['ton_dau'] ?? 0);
    $ghiChu = trim($_POST['ghi_chu'] ?? '');

    if (empty($maSP) || empty($tenSP) || empty($nhomSP)) {
        $error = 'Vui lòng nhập đầy đủ: Mã sản phẩm, Tên sản phẩm và Nhóm sản phẩm!';
    } else {
        // Kiểm tra trùng mã
        $stmtCheck = $db->prepare("SELECT id, trang_thai FROM san_pham WHERE ma_sp = :ma_sp");
        $stmtCheck->execute(['ma_sp' => $maSP]);
        $existing = $stmtCheck->fetch();

        if ($existing) {
            if ($existing['trang_thai'] == 0) {
                $error = "Mã sản phẩm [$maSP] đã tồn tại trong danh sách đã xóa mềm. Bạn có thể sang tab 'Đã xóa mềm' để khôi phục!";
            } else {
                $error = "Mã sản phẩm [$maSP] đã tồn tại trong hệ thống. Vui lòng chọn mã khác!";
            }
        } else {
            try {
                $db->beginTransaction();

                $stmtInsert = $db->prepare("
                    INSERT INTO san_pham (ma_sp, ten_sp, nhom_sp, don_vi_tinh, can_kiem_ke, trang_thai, ghi_chu)
                    VALUES (:ma_sp, :ten_sp, :nhom_sp, :don_vi_tinh, :can_kiem_ke, 1, :ghi_chu)
                ");
                $stmtInsert->execute([
                    'ma_sp' => $maSP,
                    'ten_sp' => $tenSP,
                    'nhom_sp' => $nhomSP,
                    'don_vi_tinh' => $donViTinh,
                    'can_kiem_ke' => $canKiemKe,
                    'ghi_chu' => $ghiChu
                ]);
                $spId = $db->lastInsertId();

                // Lưu tồn đầu kỳ tháng hiện tại nếu có
                if ($tonDau > 0) {
                    $kyKiemKe = date('Y-m');
                    $stmtTon = $db->prepare("
                        INSERT INTO ton_dau (san_pham_id, ky_kiem_ke, so_luong, ghi_chu)
                        VALUES (:san_pham_id, :ky_kiem_ke, :so_luong, 'Tồn đầu kỳ khởi tạo ban đầu')
                    ");
                    $stmtTon->execute([
                        'san_pham_id' => $spId,
                        'ky_kiem_ke' => $kyKiemKe,
                        'so_luong' => $tonDau
                    ]);
                }

                $db->commit();
                setFlash('success', "Thêm sản phẩm [$maSP - $tenSP] thành công!");
                header("Location: " . BASE_URL . "/sanpham/danh_sach.php");
                exit;
            } catch (Exception $e) {
                $db->rollBack();
                $error = "Lỗi thêm sản phẩm: " . $e->getMessage();
            }
        }
    }
}

include __DIR__ . '/../includes/header.php';
?>

<div style="max-width: 700px; margin: 0 auto;">
    <div class="card">
        <div class="card-header">
            <h2 class="card-title">
                <i class="fa-solid fa-circle-plus" style="color: var(--success);"></i>
                Thêm Sản Phẩm Mới Vào Hệ Thống
            </h2>
            <a href="<?= BASE_URL ?>/sanpham/danh_sach.php" class="btn btn-secondary btn-sm">
                <i class="fa-solid fa-arrow-left"></i> Quay lại
            </a>
        </div>
        <div class="card-body">
            <?php if ($error): ?>
                <div class="alert alert-danger">
                    <i class="fa-solid fa-circle-exclamation"></i>
                    <span><?= htmlspecialchars($error) ?></span>
                </div>
            <?php endif; ?>

            <form method="POST" action="">
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                    <div class="form-group">
                        <label class="form-label" for="ma_sp">Mã sản phẩm <span style="color: var(--danger);">*</span></label>
                        <input type="text" id="ma_sp" name="ma_sp" class="form-control" placeholder="Ví dụ: CAKE02, FOOD02" required value="<?= htmlspecialchars($_POST['ma_sp'] ?? '') ?>">
                        <small style="font-size: 11px; color: var(--text-muted);">Mã định danh duy nhất trong hệ thống</small>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="don_vi_tinh">Đơn vị tính <span style="color: var(--danger);">*</span></label>
                        <input type="text" id="don_vi_tinh" name="don_vi_tinh" class="form-control" placeholder="Cái, Chai, Ly, Hộp, Gói..." required value="<?= htmlspecialchars($_POST['don_vi_tinh'] ?? 'Cái') ?>">
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label" for="ten_sp">Tên sản phẩm <span style="color: var(--danger);">*</span></label>
                    <input type="text" id="ten_sp" name="ten_sp" class="form-control" placeholder="Ví dụ: Bánh Mì Hoa Cúc, Sandwich Gà..." required value="<?= htmlspecialchars($_POST['ten_sp'] ?? '') ?>">
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                    <div class="form-group">
                        <label class="form-label" for="nhom_sp">Nhóm sản phẩm <span style="color: var(--danger);">*</span></label>
                        <input type="text" id="nhom_sp" name="nhom_sp" list="nhom_list" class="form-control" placeholder="Bánh, Đồ ăn, Nước suối..." required value="<?= htmlspecialchars($_POST['nhom_sp'] ?? '') ?>">
                        <datalist id="nhom_list">
                            <option value="Bánh">
                            <option value="Đồ ăn">
                            <option value="Nước suối">
                            <option value="Đồ uống pha chế">
                            <option value="Nguyên vật liệu">
                        </datalist>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="ton_dau">Tồn đầu kỳ ban đầu (Tháng <?= date('m/Y') ?>)</label>
                        <input type="number" step="any" min="0" id="ton_dau" name="ton_dau" class="form-control" placeholder="0" value="<?= htmlspecialchars($_POST['ton_dau'] ?? '0') ?>">
                    </div>
                </div>

                <!-- CỜ CAN_KIEM_KE (TRỌNG TÂM HỆ THỐNG) -->
                <div class="form-group" style="background: var(--bg-main); border: 1px solid var(--border-color); border-radius: var(--border-radius-sm); padding: 16px; margin-top: 10px;">
                    <label style="display: flex; align-items: flex-start; gap: 12px; cursor: pointer;">
                        <input type="checkbox" name="can_kiem_ke" value="1" <?= (!isset($_POST['ma_sp']) || !empty($_POST['can_kiem_ke'])) ? 'checked' : '' ?> style="width: 20px; height: 20px; margin-top: 2px; accent-color: var(--primary);">
                        <div>
                            <div style="font-weight: 700; color: var(--text-primary); font-size: 15px;">
                                🎯 Có cần kiểm kê trong kho hay không?
                            </div>
                            <div style="font-size: 13px; color: var(--text-secondary); margin-top: 4px;">
                                <strong>BẬT</strong>: Dành cho Bánh, Đồ ăn, Nước suối... Hệ thống sẽ tự động lọc từ Excel và đưa vào bảng kiểm kê.<br>
                                <strong>TẮT</strong>: Dành cho Cà phê, Trà, Nước pha chế... Hệ thống sẽ tự động bỏ qua khi đọc file bán hàng.
                            </div>
                        </div>
                    </label>
                </div>

                <div class="form-group">
                    <label class="form-label" for="ghi_chu">Ghi chú</label>
                    <textarea id="ghi_chu" name="ghi_chu" class="form-control" rows="2" placeholder="Thông tin bổ sung nếu có..."><?= htmlspecialchars($_POST['ghi_chu'] ?? '') ?></textarea>
                </div>

                <div style="display: flex; justify-content: flex-end; gap: 12px; margin-top: 24px;">
                    <a href="<?= BASE_URL ?>/sanpham/danh_sach.php" class="btn btn-secondary">Hủy bỏ</a>
                    <button type="submit" class="btn btn-success">
                        <i class="fa-solid fa-check"></i> Thêm Sản Phẩm
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
