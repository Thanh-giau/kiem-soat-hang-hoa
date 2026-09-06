<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';
requireLogin();

$id = (int)($_GET['id'] ?? 0);
$db = getDB();

$stmt = $db->prepare("
    SELECT uf.*, u.ho_ten AS nguoi_upload
    FROM upload_files uf
    LEFT JOIN users u ON u.id = uf.nguoi_upload_id
    WHERE uf.id = :id
");
$stmt->execute(['id' => $id]);
$file = $stmt->fetch();

if (!$file) {
    setFlash('danger', 'Không tìm thấy file yêu cầu!');
    header("Location: " . BASE_URL . "/soban/lich_su.php");
    exit;
}

$pageTitle = "Chi Tiết Số Bán: " . $file['ten_file_goc'];

// Lấy danh sách số bán gắn với file này
$stmtSales = $db->prepare("
    SELECT sb.*, sp.ma_sp, sp.ten_sp, sp.nhom_sp, sp.don_vi_tinh
    FROM so_ban sb
    JOIN san_pham sp ON sp.id = sb.san_pham_id
    WHERE sb.upload_file_id = :id
    ORDER BY sp.nhom_sp ASC, sp.ma_sp ASC
");
$stmtSales->execute(['id' => $id]);
$salesList = $stmtSales->fetchAll();

include __DIR__ . '/../includes/header.php';
?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
    <div>
        <h2 style="font-size: 20px; font-weight: 700;"><?= htmlspecialchars($file['ten_file_goc']) ?></h2>
        <div style="color: var(--text-secondary); font-size: 13px;">
            Ngày bán: <strong style="color: var(--primary);"><?= formatDate($file['ngay_ban']) ?></strong> | 
            Tải lên bởi: <strong><?= htmlspecialchars($file['nguoi_upload'] ?? 'Admin') ?></strong> lúc <?= date('H:i d/m/Y', strtotime($file['created_at'])) ?>
        </div>
    </div>
    <div style="display: flex; gap: 8px;">
        <a href="<?= BASE_URL ?>/soban/lich_su.php" class="btn btn-secondary btn-sm">
            <i class="fa-solid fa-arrow-left"></i> Quay lại lịch sử
        </a>
        <button type="button" class="btn btn-danger btn-sm" onclick="openModal('modal-xoa-file')">
            <i class="fa-solid fa-trash-can"></i> Xóa File Này
        </button>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h3 class="card-title">
            <i class="fa-solid fa-list-check" style="color: var(--success);"></i>
            Danh Sách Sản Phẩm Được Lưu Số Bán (<?= count($salesList) ?>)
        </h3>
    </div>
    <div class="card-body" style="padding: 0;">
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Mã SP</th>
                        <th>Tên Sản Phẩm</th>
                        <th>Nhóm</th>
                        <th style="text-align: center;">ĐVT</th>
                        <th style="text-align: right;">Số Lượng Bán</th>
                        <th>Ghi Chú</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($salesList)): ?>
                        <tr><td colspan="6" style="text-align:center; padding: 20px; color: var(--text-muted);">Không có dòng dữ liệu nào được lưu cho file này.</td></tr>
                    <?php else: ?>
                        <?php foreach ($salesList as $item): ?>
                            <tr>
                                <td><strong><?= htmlspecialchars($item['ma_sp']) ?></strong></td>
                                <td><?= htmlspecialchars($item['ten_sp']) ?></td>
                                <td><span class="badge badge-secondary"><?= htmlspecialchars($item['nhom_sp']) ?></span></td>
                                <td style="text-align: center;"><?= htmlspecialchars($item['don_vi_tinh']) ?></td>
                                <td style="text-align: right; font-weight: 700; color: var(--primary); font-size: 15px;">
                                    <?= formatNumber($item['so_luong']) ?>
                                </td>
                                <td style="font-size: 12px; color: var(--text-muted);"><?= htmlspecialchars($item['ghi_chu'] ?? '-') ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Xác Nhận Xóa File -->
<div class="modal-overlay" id="modal-xoa-file">
    <div class="modal-card">
        <div class="modal-header" style="background: #fff1f2; border-bottom: 1px solid #fecdd3;">
            <div class="modal-title" style="color: #e11d48; display: flex; align-items: center; gap: 8px;">
                <i class="fa-solid fa-triangle-exclamation"></i>
                <span>Xác Nhận Xóa File Upload Số Bán</span>
            </div>
            <button type="button" onclick="closeModal('modal-xoa-file')" style="background: none; border: none; font-size: 20px; cursor: pointer; color: var(--text-muted);">&times;</button>
        </div>
        <form method="POST" action="<?= BASE_URL ?>/soban/xoa.php" id="form-xoa-file">
            <input type="hidden" name="id" value="<?= $file['id'] ?>">
            <div class="modal-body">
                <p style="margin-bottom: 16px; font-size: 14px; color: var(--text-primary);">
                    Bạn có chắc chắn muốn xóa vĩnh viễn file số bán sau khỏi hệ thống?
                </p>

                <div style="background: #f8fafc; border: 1px solid var(--border-color); border-radius: var(--border-radius-md); padding: 14px 18px; margin-bottom: 18px; line-height: 1.8; font-size: 14px;">
                    <div>📄 <strong>Tên file:</strong> <span style="color: var(--primary); font-weight: 700;"><?= htmlspecialchars($file['ten_file_goc']) ?></span></div>
                    <div>📅 <strong>Ngày áp dụng số bán:</strong> <span style="font-weight: 600;"><?= formatDate($file['ngay_ban']) ?></span></div>
                    <div>📊 <strong>Số dòng kiểm kê đã ghi nhận:</strong> <span style="color: #e11d48; font-weight: 700;"><?= count($salesList) ?></span> dòng</div>
                </div>

                <div class="alert alert-danger" style="margin-bottom: 0; font-size: 13px; line-height: 1.6;">
                    <i class="fa-solid fa-circle-exclamation" style="font-size: 20px; flex-shrink: 0; margin-top: 2px;"></i>
                    <div>
                        <strong>HỆ THỐNG SẼ TỰ ĐỘNG THU HỒI SỐ LIỆU:</strong>
                        <ul style="margin-top: 6px; margin-left: 18px; list-style-type: disc;">
                            <li>Toàn bộ <?= count($salesList) ?> dòng số lượng bán hàng lưu từ file này sẽ <strong>bị xóa bỏ hoàn toàn</strong>.</li>
                            <li>Tồn kho lý thuyết và chênh lệch kiểm kê sẽ <strong>tự động khôi phục</strong> như khi chưa từng có file này.</li>
                            <li>Hành động này <strong>không thể khôi phục lại</strong> sau khi xóa!</li>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('modal-xoa-file')">
                    <i class="fa-solid fa-xmark"></i> Hủy Bỏ
                </button>
                <button type="submit" class="btn btn-danger">
                    <i class="fa-solid fa-trash-can"></i> Xác Nhận Xóa Vĩnh Viễn
                </button>
            </div>
        </form>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
