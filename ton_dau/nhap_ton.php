<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';
requireLogin();

$pageTitle = "Nhập Tồn Đầu Kỳ";
$db = getDB();

$ky = $_GET['ky'] ?? date('Y-m');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $kyPost = $_POST['ky_kiem_ke'] ?? $ky;
    $tonDauValues = $_POST['ton_dau'] ?? [];
    $ghiChuValues = $_POST['ghi_chu'] ?? [];

    try {
        $db->beginTransaction();

        $stmt = $db->prepare("
            INSERT INTO ton_dau (san_pham_id, ky_kiem_ke, so_luong, ghi_chu)
            VALUES (:san_pham_id, :ky_kiem_ke, :so_luong, :ghi_chu)
            ON DUPLICATE KEY UPDATE 
                so_luong = VALUES(so_luong),
                ghi_chu = VALUES(ghi_chu),
                updated_at = CURRENT_TIMESTAMP
        ");

        foreach ($tonDauValues as $spId => $val) {
            $sl = (float)str_replace(',', '', $val);
            $gc = trim($ghiChuValues[$spId] ?? '');

            $stmt->execute([
                'san_pham_id' => $spId,
                'ky_kiem_ke' => $kyPost,
                'so_luong' => $sl,
                'ghi_chu' => $gc
            ]);
        }

        $db->commit();
        setFlash('success', "Đã lưu thông tin tồn đầu kỳ tháng " . date('m/Y', strtotime($kyPost . '-01')) . " thành công!");
        header("Location: " . BASE_URL . "/ton_dau/danh_sach.php?ky=" . urlencode($kyPost));
        exit;
    } catch (Exception $e) {
        $db->rollBack();
        $error = "Lỗi cập nhật tồn đầu: " . $e->getMessage();
    }
}

// Lấy danh sách sản phẩm và tồn đầu kỳ hiện tại
$stmt = $db->prepare("
    SELECT 
        sp.id,
        sp.ma_sp,
        sp.ten_sp,
        sp.nhom_sp,
        sp.don_vi_tinh,
        sp.can_kiem_ke,
        COALESCE(td.so_luong, 0) AS ton_dau,
        td.ghi_chu
    FROM san_pham sp
    LEFT JOIN ton_dau td ON td.san_pham_id = sp.id AND td.ky_kiem_ke = :ky
    WHERE sp.trang_thai = 1
    ORDER BY sp.can_kiem_ke DESC, sp.nhom_sp ASC, sp.ma_sp ASC
");
$stmt->execute(['ky' => $ky]);
$sanPhams = $stmt->fetchAll();

include __DIR__ . '/../includes/header.php';
?>

<div class="card">
    <div class="card-header">
        <h2 class="card-title">
            <i class="fa-solid fa-boxes-packing" style="color: var(--primary);"></i>
            Nhập Tồn Đầu Kỳ Tháng <?= date('m/Y', strtotime($ky . '-01')) ?>
        </h2>
        <a href="<?= BASE_URL ?>/ton_dau/danh_sach.php?ky=<?= htmlspecialchars($ky) ?>" class="btn btn-secondary btn-sm">
            <i class="fa-solid fa-arrow-left"></i> Quay lại
        </a>
    </div>
    <div class="card-body">
        <?php if (isset($error)): ?>
            <div class="alert alert-danger">
                <i class="fa-solid fa-circle-exclamation"></i>
                <span><?= htmlspecialchars($error) ?></span>
            </div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="form-group" style="max-width: 250px; margin-bottom: 20px;">
                <label class="form-label">Kỳ kiểm kê áp dụng:</label>
                <input type="month" name="ky_kiem_ke" class="form-control" value="<?= htmlspecialchars($ky) ?>" required>
            </div>

            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th style="width: 120px;">Mã SP</th>
                            <th>Tên Sản Phẩm</th>
                            <th>Nhóm</th>
                            <th style="text-align: center;">ĐVT</th>
                            <th style="text-align: center; width: 100px;">Cần Kiểm Kê</th>
                            <th style="width: 180px; text-align: right;">Số Lượng Tồn Đầu</th>
                            <th style="width: 250px;">Ghi Chú</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($sanPhams as $sp): ?>
                            <tr>
                                <td><strong><?= htmlspecialchars($sp['ma_sp']) ?></strong></td>
                                <td><?= htmlspecialchars($sp['ten_sp']) ?></td>
                                <td><span class="badge badge-secondary"><?= htmlspecialchars($sp['nhom_sp']) ?></span></td>
                                <td style="text-align: center;"><?= htmlspecialchars($sp['don_vi_tinh']) ?></td>
                                <td style="text-align: center;">
                                    <?php if ($sp['can_kiem_ke'] == 1): ?>
                                        <span class="badge badge-success">✅ Cần</span>
                                    <?php else: ?>
                                        <span class="badge badge-secondary">❌ Bỏ qua</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <input type="number" step="any" min="0" 
                                           name="ton_dau[<?= $sp['id'] ?>]" 
                                           class="form-control" 
                                           style="text-align: right; font-weight: 700; color: var(--primary);" 
                                           value="<?= (float)$sp['ton_dau'] ?>">
                                </td>
                                <td>
                                    <input type="text" 
                                           name="ghi_chu[<?= $sp['id'] ?>]" 
                                           class="form-control" 
                                           placeholder="Ghi chú nếu có..." 
                                           value="<?= htmlspecialchars($sp['ghi_chu'] ?? '') ?>">
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 12px; margin-top: 24px;">
                <a href="<?= BASE_URL ?>/ton_dau/danh_sach.php" class="btn btn-secondary">Hủy bỏ</a>
                <button type="submit" class="btn btn-primary">
                    <i class="fa-solid fa-floppy-disk"></i> Lưu Toàn Bộ Tồn Đầu Kỳ
                </button>
            </div>
        </form>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
