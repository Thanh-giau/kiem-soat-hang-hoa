<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';
requireLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: " . BASE_URL . "/nhaphang/danh_sach.php");
    exit;
}

$db = getDB();
$action = $_POST['action'] ?? 'batch'; // 'batch' (cả ngày) hoặc 'single' (1 món)

if ($action === 'batch') {
    $ngayCu = trim($_POST['ngay_cu'] ?? '');
    $ngayMoi = trim($_POST['ngay_moi'] ?? '');

    if (empty($ngayCu) || empty($ngayMoi)) {
        setFlash('danger', 'Vui lòng chọn đầy đủ ngày cũ và ngày mới!');
        header("Location: " . BASE_URL . "/nhaphang/danh_sach.php");
        exit;
    }

    if ($ngayCu === $ngayMoi) {
        setFlash('warning', 'Ngày mới trùng với ngày hiện tại!');
        header("Location: " . BASE_URL . "/nhaphang/danh_sach.php?thang=" . date('Y-m', strtotime($ngayMoi)));
        exit;
    }

    try {
        $db->beginTransaction();

        // 1. Lấy danh sách sản phẩm bị ảnh hưởng
        $stmtSp = $db->prepare("SELECT DISTINCT san_pham_id FROM nhap_hang WHERE ngay_nhap = :ngay_cu");
        $stmtSp->execute(['ngay_cu' => $ngayCu]);
        $affectedSpIds = $stmtSp->fetchAll(PDO::FETCH_COLUMN);

        if (empty($affectedSpIds)) {
            $db->rollBack();
            setFlash('danger', 'Không tìm thấy dữ liệu nhập hàng của ngày ' . formatDate($ngayCu) . '!');
            header("Location: " . BASE_URL . "/nhaphang/danh_sach.php");
            exit;
        }

        // 2. Cập nhật ngày nhập mới cho toàn bộ các dòng của ngày cũ
        $stmtUpd = $db->prepare("
            UPDATE nhap_hang 
            SET ngay_nhap = :ngay_moi, updated_at = CURRENT_TIMESTAMP 
            WHERE ngay_nhap = :ngay_cu
        ");
        $stmtUpd->execute([
            'ngay_moi' => $ngayMoi,
            'ngay_cu' => $ngayCu
        ]);
        $updatedCount = $stmtUpd->rowCount();

        // 3. Tự động tính toán lại tồn lý thuyết và chênh lệch kiểm kê (nếu các ngày liên quan đã kiểm kho)
        $minDate = min($ngayCu, $ngayMoi);
        $stmtKK = $db->prepare("SELECT DISTINCT ngay_kiem_ke FROM kiem_ke WHERE ngay_kiem_ke >= :min_date ORDER BY ngay_kiem_ke ASC");
        $stmtKK->execute(['min_date' => $minDate]);
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
        setFlash('success', "Đã đổi ngày nhập thành công từ <strong>" . formatDate($ngayCu) . "</strong> sang <strong>" . formatDate($ngayMoi) . "</strong> cho {$updatedCount} mặt hàng!");
        header("Location: " . BASE_URL . "/nhaphang/danh_sach.php?thang=" . date('Y-m', strtotime($ngayMoi)));
        exit;
    } catch (Exception $e) {
        $db->rollBack();
        setFlash('danger', 'Lỗi khi cập nhật ngày nhập: ' . $e->getMessage());
        header("Location: " . BASE_URL . "/nhaphang/danh_sach.php");
        exit;
    }
} elseif ($action === 'single') {
    $id = (int)($_POST['id'] ?? 0);
    $ngayMoi = trim($_POST['ngay_moi'] ?? '');

    if ($id <= 0 || empty($ngayMoi)) {
        setFlash('danger', 'Dữ liệu không hợp lệ!');
        header("Location: " . BASE_URL . "/nhaphang/danh_sach.php");
        exit;
    }

    try {
        $db->beginTransaction();

        $stmtItem = $db->prepare("SELECT san_pham_id, ngay_nhap FROM nhap_hang WHERE id = :id");
        $stmtItem->execute(['id' => $id]);
        $item = $stmtItem->fetch();

        if (!$item) {
            $db->rollBack();
            setFlash('danger', 'Không tìm thấy dòng nhập hàng!');
            header("Location: " . BASE_URL . "/nhaphang/danh_sach.php");
            exit;
        }

        $ngayCu = $item['ngay_nhap'];
        $spId = $item['san_pham_id'];

        $stmtUpd = $db->prepare("
            UPDATE nhap_hang 
            SET ngay_nhap = :ngay_moi, updated_at = CURRENT_TIMESTAMP 
            WHERE id = :id
        ");
        $stmtUpd->execute([
            'ngay_moi' => $ngayMoi,
            'id' => $id
        ]);

        // Cập nhật lại kiểm kê nếu có
        $minDate = min($ngayCu, $ngayMoi);
        $stmtKK = $db->prepare("SELECT DISTINCT ngay_kiem_ke FROM kiem_ke WHERE ngay_kiem_ke >= :min_date ORDER BY ngay_kiem_ke ASC");
        $stmtKK->execute(['min_date' => $minDate]);
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
        setFlash('success', "Đã đổi ngày nhập của mặt hàng sang ngày <strong>" . formatDate($ngayMoi) . "</strong> thành công!");
        header("Location: " . BASE_URL . "/nhaphang/danh_sach.php?thang=" . date('Y-m', strtotime($ngayMoi)));
        exit;
    } catch (Exception $e) {
        $db->rollBack();
        setFlash('danger', 'Lỗi khi cập nhật ngày: ' . $e->getMessage());
        header("Location: " . BASE_URL . "/nhaphang/danh_sach.php");
        exit;
    }
}
