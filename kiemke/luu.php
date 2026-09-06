<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';
requireLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: " . BASE_URL . "/kiemke/kiem_ke.php");
    exit;
}

$db = getDB();
$user = currentUser();

$ngayKiemKe = trim($_POST['ngay_kiem_ke'] ?? date('Y-m-d'));
$items = $_POST['items'] ?? [];

if (empty($items)) {
    setFlash('warning', 'Không có sản phẩm nào được gửi lên để lưu kiểm kê!');
    header("Location: " . BASE_URL . "/kiemke/kiem_ke.php?ngay=" . urlencode($ngayKiemKe));
    exit;
}

try {
    $db->beginTransaction();

    $stmt = $db->prepare("
        INSERT INTO kiem_ke (san_pham_id, ngay_kiem_ke, ton_ly_thuyet, ton_thuc_te, chenh_lech, ghi_chu, nguoi_kiem_ke_id)
        VALUES (:sp_id, :ngay, :ton_lt, :ton_tt, :chenh_lech, :ghi_chu, :uid)
        ON DUPLICATE KEY UPDATE 
            ton_ly_thuyet = VALUES(ton_ly_thuyet),
            ton_thuc_te = VALUES(ton_thuc_te),
            chenh_lech = VALUES(chenh_lech),
            ghi_chu = VALUES(ghi_chu),
            nguoi_kiem_ke_id = VALUES(nguoi_kiem_ke_id),
            updated_at = CURRENT_TIMESTAMP
    ");

    $countSaved = 0;
    foreach ($items as $item) {
        $spId = (int)$item['san_pham_id'];
        $tonLT = (float)$item['ton_ly_thuyet'];
        $tonTT = (float)$item['ton_thuc_te'];
        // CÔNG THỨC 15: CHÊNH LỆCH = TỒN THỰC TẾ - TỒN LÝ THUYẾT
        $chenhLech = $tonTT - $tonLT;
        $ghiChu = trim($item['ghi_chu'] ?? '');

        $stmt->execute([
            'sp_id' => $spId,
            'ngay' => $ngayKiemKe,
            'ton_lt' => $tonLT,
            'ton_tt' => $tonTT,
            'chenh_lech' => $chenhLech,
            'ghi_chu' => $ghiChu,
            'uid' => $user['id']
        ]);
        $countSaved++;
    }

    $db->commit();
    setFlash('success', "Đã lưu thành công kết quả kiểm kê ngày " . formatDate($ngayKiemKe) . " cho $countSaved sản phẩm!");
    header("Location: " . BASE_URL . "/baocao/chenh_lech.php?ngay=" . urlencode($ngayKiemKe));
    exit;
} catch (Exception $e) {
    $db->rollBack();
    setFlash('danger', 'Lỗi lưu kiểm kê: ' . $e->getMessage());
    header("Location: " . BASE_URL . "/kiemke/kiem_ke.php?ngay=" . urlencode($ngayKiemKe));
    exit;
}
