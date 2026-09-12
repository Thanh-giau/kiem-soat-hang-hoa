<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';
requireLogin();

$id = (int)($_GET['id'] ?? 0);
$loaiFilter = trim($_GET['loai'] ?? '');
$db = getDB();

if ($id > 0) {
    try {
        $db->beginTransaction();

        $stmt = $db->prepare("SELECT san_pham_id, ngay_dieu_chinh, loai, so_luong FROM dieu_chinh_kho WHERE id = :id");
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();

        if ($row) {
            $spId = $row['san_pham_id'];
            $ngay = $row['ngay_dieu_chinh'];

            $delStmt = $db->prepare("DELETE FROM dieu_chinh_kho WHERE id = :id");
            $delStmt->execute(['id' => $id]);

            // Cập nhật lại tồn lý thuyết và chênh lệch kiểm kê
            $stmtKK = $db->prepare("SELECT DISTINCT ngay_kiem_ke FROM kiem_ke WHERE ngay_kiem_ke >= :ngay ORDER BY ngay_kiem_ke ASC");
            $stmtKK->execute(['ngay' => $ngay]);
            $checkDates = $stmtKK->fetchAll(PDO::FETCH_COLUMN);

            foreach ($checkDates as $d) {
                $report = layBaoCaoTonKhoChiTiet($d, $spId, false);
                if (!empty($report) && $report[0]['ton_thuc_te'] !== null) {
                    $newTonLT = (float)$report[0]['ton_ly_thuyet'];
                    $newChenhLech = (float)$report[0]['ton_thuc_te'] - $newTonLT;
                    $stmtUpdKK = $db->prepare("
                        UPDATE kiem_ke 
                        SET ton_ly_thuyet = :tlt, chenh_lech = :cl, updated_at = CURRENT_TIMESTAMP 
                        WHERE san_pham_id = :sp_id AND ngay_kiem_ke = :ngay
                    ");
                    $stmtUpdKK->execute([
                        'tlt' => $newTonLT,
                        'cl' => $newChenhLech,
                        'sp_id' => $spId,
                        'ngay' => $d
                    ]);
                }
            }

            $db->commit();
            setFlash('warning', 'Đã xóa phiếu điều chỉnh kho. Tồn kho lý thuyết đã được tự động hoàn nguyên.');
        } else {
            $db->rollBack();
            setFlash('danger', 'Không tìm thấy phiếu điều chỉnh cần xóa.');
        }
    } catch (Exception $e) {
        $db->rollBack();
        setFlash('danger', 'Lỗi khi xóa phiếu điều chỉnh: ' . $e->getMessage());
    }
}

header("Location: " . BASE_URL . "/dieuchinh/danh_sach.php" . ($loaiFilter ? '?loai=' . urlencode($loaiFilter) : ''));
exit;
