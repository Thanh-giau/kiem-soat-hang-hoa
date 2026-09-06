<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';
requireLogin();

$pageTitle = "Lịch Sử File Upload Số Bán";
$db = getDB();

$stmt = $db->query("
    SELECT uf.*, u.ho_ten AS nguoi_upload
    FROM upload_files uf
    LEFT JOIN users u ON u.id = uf.nguoi_upload_id
    ORDER BY uf.created_at DESC
");
$uploadHistory = $stmt->fetchAll();

include __DIR__ . '/../includes/header.php';
?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
    <h2 style="font-size: 18px; font-weight: 700;">Danh Sách File Đã Tải Lên Hệ Thống</h2>
    <a href="<?= BASE_URL ?>/soban/upload.php" class="btn btn-primary">
        <i class="fa-solid fa-cloud-arrow-up"></i> Upload File Mới
    </a>
</div>

<div class="card">
    <div class="card-body" style="padding: 0;">
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Tên File Gốc</th>
                        <th>Ngày Áp Dụng Số Bán</th>
                        <th style="text-align: right;">Tổng Số Dòng</th>
                        <th style="text-align: right;">Dòng Kiểm Kê (Đã Lưu)</th>
                        <th style="text-align: right;">Dòng Bỏ Qua</th>
                        <th>Người Tải Lên</th>
                        <th>Thời Gian Tải Lên</th>
                        <th style="text-align: center; width: 150px;">Thao Tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($uploadHistory)): ?>
                        <tr>
                            <td colspan="8" style="text-align: center; padding: 30px; color: var(--text-muted);">
                                Chưa có file số bán nào được upload lên hệ thống.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($uploadHistory as $uf): ?>
                            <tr>
                                <td>
                                    <strong><i class="fa-regular fa-file-excel" style="color: var(--success);"></i> <?= htmlspecialchars($uf['ten_file_goc']) ?></strong>
                                </td>
                                <td><span class="badge badge-info"><?= formatDate($uf['ngay_ban']) ?></span></td>
                                <td style="text-align: right;"><?= formatNumber($uf['tong_dong']) ?></td>
                                <td style="text-align: right; font-weight: 700; color: var(--success);">
                                    <?= formatNumber($uf['dong_kiem_ke']) ?>
                                </td>
                                <td style="text-align: right; color: var(--text-muted);">
                                    <?= formatNumber($uf['dong_bo_qua']) ?>
                                </td>
                                <td><?= htmlspecialchars($uf['nguoi_upload'] ?? 'Admin') ?></td>
                                <td style="font-size: 13px; color: var(--text-secondary);"><?= date('H:i d/m/Y', strtotime($uf['created_at'])) ?></td>
                                <td style="text-align: center; white-space: nowrap;">
                                    <a href="<?= BASE_URL ?>/soban/chi_tiet.php?id=<?= $uf['id'] ?>" class="btn btn-secondary btn-sm" title="Xem chi tiết số bán">
                                        <i class="fa-solid fa-eye"></i> Xem
                                    </a>
                                    <button type="button" class="btn btn-danger btn-sm" style="margin-left: 6px;" 
                                            onclick="moModalXoaFile(<?= $uf['id'] ?>, '<?= htmlspecialchars(addslashes($uf['ten_file_goc'])) ?>', '<?= formatDate($uf['ngay_ban']) ?>', <?= (int)$uf['dong_kiem_ke'] ?>)" 
                                            title="Xóa file và thu hồi số bán">
                                        <i class="fa-solid fa-trash-can"></i> Xóa
                                    </button>
                                </td>
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
            <input type="hidden" name="id" id="xoa-file-id" value="">
            <div class="modal-body">
                <p style="margin-bottom: 16px; font-size: 14px; color: var(--text-primary);">
                    Bạn có chắc chắn muốn xóa vĩnh viễn file số bán sau khỏi hệ thống?
                </p>

                <div style="background: #f8fafc; border: 1px solid var(--border-color); border-radius: var(--border-radius-md); padding: 14px 18px; margin-bottom: 18px; line-height: 1.8; font-size: 14px;">
                    <div>📄 <strong>Tên file gốc:</strong> <span id="xoa-ten-file" style="color: var(--primary); font-weight: 700;"></span></div>
                    <div>📅 <strong>Ngày áp dụng số bán:</strong> <span id="xoa-ngay-ban" style="font-weight: 600;"></span></div>
                    <div>📊 <strong>Số dòng kiểm kê đã ghi nhận:</strong> <span id="xoa-so-dong" style="color: #e11d48; font-weight: 700;"></span> dòng</div>
                </div>

                <div class="alert alert-danger" style="margin-bottom: 0; font-size: 13px; line-height: 1.6;">
                    <i class="fa-solid fa-circle-exclamation" style="font-size: 20px; flex-shrink: 0; margin-top: 2px;"></i>
                    <div>
                        <strong>HỆ THỐNG SẼ TỰ ĐỘNG THU HỒI SỐ LIỆU:</strong>
                        <ul style="margin-top: 6px; margin-left: 18px; list-style-type: disc;">
                            <li>Toàn bộ số lượng bán hàng lưu từ file này sẽ <strong>bị xóa bỏ hoàn toàn</strong>.</li>
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

<script>
function moModalXoaFile(id, tenFile, ngayBan, soDong) {
    document.getElementById('xoa-file-id').value = id;
    document.getElementById('xoa-ten-file').textContent = tenFile;
    document.getElementById('xoa-ngay-ban').textContent = ngayBan;
    document.getElementById('xoa-so-dong').textContent = soDong;
    openModal('modal-xoa-file');
}
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
