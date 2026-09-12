<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';
requireLogin();

$ngay = trim($_GET['ngay'] ?? $_POST['ngay'] ?? '');
$loai = trim($_GET['loai'] ?? $_POST['loai'] ?? '');

if (empty($ngay)) {
    setFlash('danger', 'Vui lòng chọn ngày cần xóa!');
    header("Location: " . BASE_URL . "/dieuchinh/danh_sach.php");
    exit;
}

$db = getDB();

try {
    $db->beginTransaction();

    // Điều kiện lọc
    $where = ["ngay_dieu_chinh = :ngay"];
    $params = ['ngay' => $ngay];
    if (!empty($loai) && in_array($loai, ['huy', 'cho_muon', 'muon'])) {
        $where[] = "loai = :loai";
        $params['loai'] = $loai;
    }
    $whereSql = implode(' AND ', $where);

    // Lấy các sản phẩm bị ảnh hưởng
    $stmtSp = $db->prepare("SELECT DISTINCT san_pham_id FROM dieu_chinh_kho WHERE $whereSql");
    $stmtSp->execute($params);
    $affectedSpIds = $stmtSp->fetchAll(PDO::FETCH_COLUMN);

    if (empty($affectedSpIds)) {
        $db->rollBack();
        setFlash('warning', 'Không có phiếu điều chỉnh nào trong ngày ' . formatDate($ngay));
        header("Location: " . BASE_URL . "/dieuchinh/danh_sach.php");
        exit;
    }

    // Xóa phiếu
    $stmtDel = $db->prepare("DELETE FROM dieu_chinh_kho WHERE $whereSql");
    $stmtDel->execute($params);
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
    setFlash('warning', "Đã xóa toàn bộ {$deletedCount} phiếu điều chỉnh kho ngày <strong>" . formatDate($ngay) . "</strong>. Tồn kho lý thuyết đã được tự động hoàn nguyên an toàn.");
    header("Location: " . BASE_URL . "/dieuchinh/danh_sach.php?thang=" . date('Y-m', strtotime($ngay)) . (!empty($loai) ? '&loai=' . urlencode($loai) : ''));
    exit;
} catch (Exception $e) {
    $db->rollBack();
    setFlash('danger', 'Lỗi khi xóa phiếu điều chỉnh: ' . $e->getMessage());
    header("Location: " . BASE_URL . "/dieuchinh/danh_sach.php");
    exit;
}
