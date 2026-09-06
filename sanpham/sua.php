<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';
requireLogin();

$pageTitle = "Chỉnh Sửa Sản Phẩm";
$error = '';
$db = getDB();

$id = (int)($_GET['id'] ?? 0);
$stmt = $db->prepare("SELECT * FROM san_pham WHERE id = :id LIMIT 1");
$stmt->execute(['id' => $id]);
$sanPham = $stmt->fetch();

if (!$sanPham) {
    setFlash('danger', 'Không tìm thấy sản phẩm yêu cầu!');
    header("Location: " . BASE_URL . "/sanpham/danh_sach.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tenSP = trim($_POST['ten_sp'] ?? '');
    $nhomSP = trim($_POST['nhom_sp'] ?? '');
    $donViTinh = trim($_POST['don_vi_tinh'] ?? 'Cái');
    $canKiemKe = isset($_POST['can_kiem_ke']) ? 1 : 0;
    $ghiChu = trim($_POST['ghi_chu'] ?? '');

    if (empty($tenSP) || empty($nhomSP) || empty($donViTinh)) {
        $error = 'Vui lòng điền đầy đủ Tên sản phẩm, Nhóm và Đơn vị tính!';
    } else {
        try {
            $stmtUpdate = $db->prepare("
                UPDATE san_pham 
                SET ten_sp = :ten_sp, nhom_sp = :nhom_sp, don_vi_tinh = :don_vi_tinh, 
                    can_kiem_ke = :can_kiem_ke, ghi_chu = :ghi_chu
                WHERE id = :id
            ");
            $stmtUpdate->execute([
                'ten_sp' => $tenSP,
                'nhom_sp' => $nhomSP,
                'don_vi_tinh' => $donViTinh,
                'can_kiem_ke' => $canKiemKe,
                'ghi_chu' => $ghiChu,
                'id' => $id
            ]);

            setFlash('success', "Cập nhật thông tin sản phẩm [{$sanPham['ma_sp']}] thành công!");
            header("Location: " . BASE_URL . "/sanpham/danh_sach.php");
            exit;
        } catch (Exception $e) {
            $error = "Lỗi cập nhật: " . $e->getMessage();
        }
    }
}

include __DIR__ . '/../includes/header.php';
?>

<div style="max-width: 700px; margin: 0 auto;">
    <div class="card">
        <div class="card-header">
            <h2 class="card-title">
                <i class="fa-solid fa-pen-to-square" style="color: var(--primary);"></i>
                Chỉnh Sửa Sản Phẩm: <?= htmlspecialchars($sanPham['ma_sp']) ?>
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
                        <label class="form-label">Mã sản phẩm (Không đổi)</label>
                        <input type="text" class="form-control" value="<?= htmlspecialchars($sanPham['ma_sp']) ?>" disabled style="background: #f1f5f9; font-weight: 700;">
                        <small style="font-size: 11px; color: var(--text-muted);">Mã SP cố định để bảo toàn lịch sử số bán & kiểm kê</small>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="don_vi_tinh">Đơn vị tính <span style="color: var(--danger);">*</span></label>
                        <input type="text" id="don_vi_tinh" name="don_vi_tinh" class="form-control" required value="<?= htmlspecialchars($_POST['don_vi_tinh'] ?? $sanPham['don_vi_tinh']) ?>">
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label" for="ten_sp">Tên sản phẩm <span style="color: var(--danger);">*</span></label>
                    <input type="text" id="ten_sp" name="ten_sp" class="form-control" required value="<?= htmlspecialchars($_POST['ten_sp'] ?? $sanPham['ten_sp']) ?>">
                </div>

                <div class="form-group">
                    <label class="form-label" for="nhom_sp">Nhóm sản phẩm <span style="color: var(--danger);">*</span></label>
                    <input type="text" id="nhom_sp" name="nhom_sp" class="form-control" required value="<?= htmlspecialchars($_POST['nhom_sp'] ?? $sanPham['nhom_sp']) ?>">
                </div>

                <!-- CỜ CẦN KIỂM KÊ -->
                <div class="form-group" style="background: var(--bg-main); border: 1px solid var(--border-color); border-radius: var(--border-radius-sm); padding: 16px; margin-top: 10px;">
                    <label style="display: flex; align-items: flex-start; gap: 12px; cursor: pointer;">
                        <input type="checkbox" name="can_kiem_ke" value="1" <?= (isset($_POST['can_kiem_ke']) ? $_POST['can_kiem_ke'] : $sanPham['can_kiem_ke']) == 1 ? 'checked' : '' ?> style="width: 20px; height: 20px; margin-top: 2px; accent-color: var(--primary);">
                        <div>
                            <div style="font-weight: 700; color: var(--text-primary); font-size: 15px;">
                                🎯 Có cần kiểm kê trong kho hay không?
                            </div>
                            <div style="font-size: 13px; color: var(--text-secondary); margin-top: 4px;">
                                Đánh dấu tích nếu muốn hệ thống tự động lưu số bán từ file Excel và hiện mã này trong biểu mẫu kiểm kê hàng ngày.
                            </div>
                        </div>
                    </label>
                </div>

                <div class="form-group">
                    <label class="form-label" for="ghi_chu">Ghi chú</label>
                    <textarea id="ghi_chu" name="ghi_chu" class="form-control" rows="2"><?= htmlspecialchars($_POST['ghi_chu'] ?? $sanPham['ghi_chu']) ?></textarea>
                </div>

                <div style="display: flex; justify-content: flex-end; gap: 12px; margin-top: 24px;">
                    <a href="<?= BASE_URL ?>/sanpham/danh_sach.php" class="btn btn-secondary">Hủy bỏ</a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fa-solid fa-floppy-disk"></i> Lưu Thay Đổi
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
