<?php
/**
 * ⭐ ĐỒNG BỘ TOÀN BỘ SỐ BÁN ĐÃ TẢI LÊN VỚI CÔNG THỨC BOM MỚI NHẤT
 * 
 * Tự động đọc lại các file Excel số bán đã lưu trên đĩa máy chủ:
 * 1. Bóc tách lại các món có cài đặt công thức định lượng (BOM).
 * 2. Cập nhật số bán chính xác của các nguyên vật liệu kiểm kê (ví dụ: Mochi Kem Matcha).
 * 3. Loại bỏ các món nước pha chế không thuộc diện kiểm kho.
 * 4. Tự động tính toán lại Tồn lý thuyết và Chênh lệch kiểm kê tương ứng.
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/excel_reader.php';
require_once __DIR__ . '/detect_columns.php';
require_once __DIR__ . '/validate_data.php';
require_once __DIR__ . '/filter_inventory_products.php';

function syncAllUploadedSalesWithBOM() {
    $db = getDB();
    $stmt = $db->query("SELECT * FROM upload_files ORDER BY ngay_ban ASC, id ASC");
    $uploadFiles = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $results = [
        'total_files' => count($uploadFiles),
        'synced_files' => 0,
        'updated_days' => [],
        'errors' => []
    ];

    foreach ($uploadFiles as $file) {
        $filePath = __DIR__ . '/../uploads/so_ban/' . $file['ten_file_luu'];
        if (!file_exists($filePath)) {
            $filePath = __DIR__ . '/../uploads/temp/' . $file['ten_file_luu'];
            if (!file_exists($filePath)) {
                $results['errors'][] = "File {$file['ten_file_goc']} (ID: {$file['id']}) không còn trên máy chủ!";
                continue;
            }
        }

        try {
            $rows = readDataFromFile($filePath);
            if (empty($rows)) continue;

            $colMap = detectColumns($rows);
            $extracted = validateAndExtractSalesData($rows, $colMap);
            if (empty($extracted)) continue;

            // Chạy qua bộ lọc thông minh đã tích hợp BOM đa cấp
            $filterResult = filterInventoryProducts($extracted);
            $kiemKeItems = $filterResult['kiem_ke'];
            $boQuaItems = $filterResult['bo_qua'];

            $db->beginTransaction();

            // 1. Lấy các sản phẩm cũ liên kết với file này
            $stmtOld = $db->prepare("SELECT DISTINCT san_pham_id FROM so_ban WHERE upload_file_id = :id");
            $stmtOld->execute(['id' => $file['id']]);
            $affectedOld = $stmtOld->fetchAll(PDO::FETCH_COLUMN);

            // 2. Xóa số bán cũ của file này
            $stmtDel = $db->prepare("DELETE FROM so_ban WHERE upload_file_id = :id");
            $stmtDel->execute(['id' => $file['id']]);

            // 3. Chèn lại các mặt hàng kiểm kê chuẩn (gồm cả NVL bóc tách từ BOM)
            $stmtInsert = $db->prepare("
                INSERT INTO so_ban (upload_file_id, san_pham_id, ngay_ban, so_luong, ghi_chu)
                VALUES (:upload_file_id, :san_pham_id, :ngay_ban, :so_luong, :ghi_chu)
                ON DUPLICATE KEY UPDATE
                    upload_file_id = VALUES(upload_file_id),
                    so_luong = VALUES(so_luong),
                    ghi_chu = VALUES(ghi_chu),
                    updated_at = CURRENT_TIMESTAMP
            ");

            $newProductIds = [];
            foreach ($kiemKeItems as $item) {
                $note = 'Tự động từ file: ' . $file['ten_file_goc'];
                if (!empty($item['ghi_chu_dinh_luong'])) {
                    $note .= ' | ' . implode('; ', $item['ghi_chu_dinh_luong']);
                }
                $stmtInsert->execute([
                    'upload_file_id' => $file['id'],
                    'san_pham_id' => $item['san_pham_id'],
                    'ngay_ban' => $file['ngay_ban'],
                    'so_luong' => $item['so_luong'],
                    'ghi_chu' => $note
                ]);
                $newProductIds[] = $item['san_pham_id'];
            }

            // 4. Cập nhật số dòng trong upload_files
            $stmtUpdFile = $db->prepare("
                UPDATE upload_files 
                SET dong_kiem_ke = :dkk, dong_bo_qua = :dbq 
                WHERE id = :id
            ");
            $stmtUpdFile->execute([
                'dkk' => count($kiemKeItems),
                'dbq' => count($boQuaItems),
                'id' => $file['id']
            ]);

            // 5. Cập nhật chênh lệch kiểm kê (nếu ngày này đã kiểm kho)
            $allAffected = array_unique(array_merge($affectedOld, $newProductIds));
            foreach ($allAffected as $spId) {
                $report = layBaoCaoTonKhoChiTiet($file['ngay_ban'], $spId, false);
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
                            'ngay' => $file['ngay_ban']
                        ]);
                    }
                }
            }

            $db->commit();
            $results['synced_files']++;
            $results['updated_days'][] = $file['ngay_ban'];
        } catch (Exception $e) {
            $db->rollBack();
            $results['errors'][] = "Lỗi file {$file['ten_file_goc']}: " . $e->getMessage();
        }
    }

    $results['updated_days'] = array_unique($results['updated_days']);
    return $results;
}
