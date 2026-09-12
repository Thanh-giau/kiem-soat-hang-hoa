<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';
requireLogin();

$ngay = trim($_GET['ngay'] ?? $_POST['ngay'] ?? '');
if (empty($ngay)) {
    setFlash('danger', 'Vui lòng chọn ngày nhập cần xóa!');
    header("Location: " . BASE_URL . "/nhaphang/danh_sach.php");
    exit;
}

$db = getDB();

try {
    $db->beginTransaction();

    // Lấy các sản phẩm bị ảnh hưởng
    $stmtSp = $db->prepare("SELECT DISTINCT san_pham_id FROM nhap_hang WHERE ngay_nhap = :ngay");
    $stmtSp->execute(['ngay' => $ngay]);
    $affectedSpIds = $stmtSp->fetchAll(PDO::FETCH_COLUMN);

    if (empty($affectedSpIds)) {
        $db->rollBack();
        setFlash('warning', 'Không có phiếu nhập nào trong ngày ' . formatDate($ngay));
        header("Location: " . BASE_URL . "/nhaphang/danh_sach.php");
        exit;
    }

    // Xóa toàn bộ phiếu nhập của ngày đó
    $stmtDel = $db->prepare("DELETE FROM nhap_hang WHERE ngay_nhap = :ngay");
    $stmtDel->execute(['ngay' => $ngay]);
    $deletedCount = $stmtDel->rowCount();

    // Cập nhật lại tồn lý thuyết và chênh lệch kiểm kê
    $stmtKK = $db->prepare("SELECT DISTINCT ngay_kiem_ke FROM kiem_ke WHERE ngay_kiem_ke >= :ngay ORDER BY ngay_kiem_ke ASC");
    $stmtKK->execute(['ngay' => $ngay]);
    $checkDates = $stmtKK->fetchAll(PDO::FETCH_COLUMN);

    foreach ($checkDates as $d) {
        foreach ($affectedSpIds as $spId) {
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
    }

    $db->commit();
    setFlash('warning', "Đã xóa toàn bộ phiếu nhập hàng ngày <strong>" . formatDate($ngay) . "</strong> ({$deletedCount} mặt hàng). Tồn kho lý thuyết đã được tự động tính toán lại an toàn.");
    header("Location: " . BASE_URL . "/nhaphang/danh_sach.php?thang=" . date('Y-m', strtotime($ngay)));
    exit;
} catch (Exception $e) {
    $db->rollBack();
    setFlash('danger', 'Lỗi khi xóa phiếu nhập: ' . $e->getMessage());
    header("Location: " . BASE_URL . "/nhaphang/danh_sach.php");
    exit;
}
