<?php
/**
 * Xóa File Upload Số Bán và Thu Hồi Toàn Bộ Số Liệu Bán Hàng Liên Quan
 */
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';
requireLogin();

$id = (int)($_POST['id'] ?? $_GET['id'] ?? 0);
$db = getDB();

if ($id <= 0) {
    setFlash('danger', 'Mã file không hợp lệ!');
    header("Location: " . BASE_URL . "/soban/lich_su.php");
    exit;
}

// 1. Kiểm tra file có tồn tại trong hệ thống không
$stmt = $db->prepare("SELECT * FROM upload_files WHERE id = :id");
$stmt->execute(['id' => $id]);
$file = $stmt->fetch();

if (!$file) {
    setFlash('danger', 'Không tìm thấy file cần xóa hoặc file này đã được xóa trước đó!');
    header("Location: " . BASE_URL . "/soban/lich_su.php");
    exit;
}

$ngayBan = $file['ngay_ban'];
$tenFileGoc = $file['ten_file_goc'];

try {
    $db->beginTransaction();

    // 2. Lấy danh sách sản phẩm bị ảnh hưởng trong so_ban trước khi xóa
    $stmtGet = $db->prepare("SELECT DISTINCT san_pham_id FROM so_ban WHERE upload_file_id = :id");
    $stmtGet->execute(['id' => $id]);
    $affectedProductIds = $stmtGet->fetchAll(PDO::FETCH_COLUMN);

    // 3. Xóa toàn bộ số bán liên kết với file này trong bảng so_ban
    $stmtDelSales = $db->prepare("DELETE FROM so_ban WHERE upload_file_id = :id");
    $stmtDelSales->execute(['id' => $id]);
    $deletedSalesCount = $stmtDelSales->rowCount();

    // 4. Xóa bản ghi trong upload_files
    $stmtDelFile = $db->prepare("DELETE FROM upload_files WHERE id = :id");
    $stmtDelFile->execute(['id' => $id]);

    // 5. Xóa file vật lý đã lưu trên máy chủ (nếu có)
    if (!empty($file['ten_file_luu'])) {
        $filePath = __DIR__ . '/../uploads/so_ban/' . $file['ten_file_luu'];
        if (file_exists($filePath)) {
            @unlink($filePath);
        }
    }

    // 6. Đồng bộ lại bảng kiem_ke (nếu đã có đợt kiểm kê thực tế ngày hôm đó)
    // Khi xóa số bán, tồn lý thuyết của các sản phẩm sẽ tự động tăng lại tương ứng.
    // Nếu kiểm kê đã lưu, cập nhật lại ton_ly_thuyet và chenh_lech để đảm bảo tính nhất quán tuyệt đối.
    if (!empty($affectedProductIds)) {
        foreach ($affectedProductIds as $spId) {
            $report = layBaoCaoTonKhoChiTiet($ngayBan, $spId, false);
            if (!empty($report)) {
                $row = $report[0];
                if ($row['ton_thuc_te'] !== null) {
                    $newTonLT = (float)$row['ton_ly_thuyet'];
                    $newChenhLech = (float)$row['ton_thuc_te'] - $newTonLT;
                    $stmtUpdKK = $db->prepare("
                        UPDATE kiem_ke 
                        SET ton_ly_thuyet = :ton_lt, 
                            chenh_lech = :chenh_lech, 
                            updated_at = CURRENT_TIMESTAMP
                        WHERE san_pham_id = :sp_id AND ngay_kiem_ke = :ngay
                    ");
                    $stmtUpdKK->execute([
                        'ton_lt' => $newTonLT,
                        'chenh_lech' => $newChenhLech,
                        'sp_id' => $spId,
                        'ngay' => $ngayBan
                    ]);
                }
            }
        }
    }

    $db->commit();

    setFlash('success', "Đã xóa hoàn toàn file \"{$tenFileGoc}\" và hủy bỏ {$deletedSalesCount} dòng số bán ngày " . formatDate($ngayBan) . " khỏi hệ thống. Tồn lý thuyết và chênh lệch kiểm kê đã được khôi phục!");
} catch (Exception $e) {
    $db->rollBack();
    setFlash('danger', 'Lỗi khi xóa file và số liệu: ' . $e->getMessage());
}

header("Location: " . BASE_URL . "/soban/lich_su.php");
exit;
