<?php
/**
 * Lưu dữ liệu số bán vào Database
 * Quản lý ghi đè / cập nhật và lưu vết upload_files
 */

require_once __DIR__ . '/../config/database.php';

function saveSalesData($ngayBan, $danhSachKiemKe, $fileInfo, $nguoiUploadId, $ghiDe = false) {
    $db = getDB();

    try {
        $db->beginTransaction();

        // 1. Kiểm tra nếu đã có số bán ngày này và người dùng chọn ghi đè
        if ($ghiDe) {
            $stmtDel = $db->prepare("DELETE FROM so_ban WHERE ngay_ban = :ngay_ban");
            $stmtDel->execute(['ngay_ban' => $ngayBan]);
        }

        // 2. Lưu thông tin file vào upload_files
        $stmtFile = $db->prepare("
            INSERT INTO upload_files (
                ten_file_goc, ten_file_luu, ngay_ban, file_hash, 
                tong_dong, dong_kiem_ke, dong_bo_qua, nguoi_upload_id
            ) VALUES (
                :ten_file_goc, :ten_file_luu, :ngay_ban, :file_hash, 
                :tong_dong, :dong_kiem_ke, :dong_bo_qua, :nguoi_upload_id
            )
        ");
        $stmtFile->execute([
            'ten_file_goc' => $fileInfo['ten_file_goc'],
            'ten_file_luu' => $fileInfo['ten_file_luu'],
            'ngay_ban' => $ngayBan,
            'file_hash' => $fileInfo['file_hash'],
            'tong_dong' => $fileInfo['tong_dong'],
            'dong_kiem_ke' => count($danhSachKiemKe),
            'dong_bo_qua' => $fileInfo['dong_bo_qua'],
            'nguoi_upload_id' => $nguoiUploadId
        ]);
        $uploadFileId = $db->lastInsertId();

        // 3. Lưu từng sản phẩm cần kiểm kê vào bảng so_ban
        $stmtInsert = $db->prepare("
            INSERT INTO so_ban (upload_file_id, san_pham_id, ngay_ban, so_luong, ghi_chu)
            VALUES (:upload_file_id, :san_pham_id, :ngay_ban, :so_luong, :ghi_chu)
            ON DUPLICATE KEY UPDATE 
                upload_file_id = VALUES(upload_file_id),
                so_luong = VALUES(so_luong),
                ghi_chu = VALUES(ghi_chu),
                updated_at = CURRENT_TIMESTAMP
        ");

        $soLuongLuu = 0;
        foreach ($danhSachKiemKe as $item) {
            $stmtInsert->execute([
                'upload_file_id' => $uploadFileId,
                'san_pham_id' => $item['san_pham_id'],
                'ngay_ban' => $ngayBan,
                'so_luong' => $item['so_luong'],
                'ghi_chu' => 'Tự động từ file: ' . $fileInfo['ten_file_goc']
            ]);
            $soLuongLuu++;
        }

        $db->commit();
        return [
            'success' => true,
            'upload_file_id' => $uploadFileId,
            'so_luong_luu' => $soLuongLuu
        ];
    } catch (Exception $e) {
        $db->rollBack();
        return [
            'success' => false,
            'message' => 'Lỗi lưu dữ liệu: ' . $e->getMessage()
        ];
    }
}
