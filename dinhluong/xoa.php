<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../import/sync_bom_sales.php';
requireLogin();

$db = getDB();
$ma = trim($_GET['ma'] ?? '');
$ten = trim($_GET['ten'] ?? '');

if (empty($ten) && empty($ma)) {
    setFlash('error', 'Không tìm thấy thông tin công thức cần xóa!');
    header("Location: " . BASE_URL . "/dinhluong/danh_sach.php");
    exit;
}

try {
    $stmt = $db->prepare("
        DELETE FROM dinh_luong_mon 
        WHERE (ma_mon_pos = :ma OR (:ma = '' AND (ma_mon_pos IS NULL OR ma_mon_pos = '')))
          AND ten_mon_pos = :ten
    ");
    $stmt->execute(['ma' => $ma, 'ten' => $ten]);

    // Nếu không xóa được theo cả 2, thử xóa theo tên
    if ($stmt->rowCount() === 0 && !empty($ten)) {
        $stmtName = $db->prepare("DELETE FROM dinh_luong_mon WHERE ten_mon_pos = :ten");
        $stmtName->execute(['ten' => $ten]);
    }

    // Tự động đồng bộ lại các file số bán đã tải lên
    $syncRes = syncAllUploadedSalesWithBOM();
    $syncedMsg = !empty($syncRes['synced_files']) ? " (Đã cập nhật lại {$syncRes['synced_files']} file số bán trong kho)" : "";

    setFlash('success', "Đã xóa công thức định lượng của món [{$ten}] thành công!{$syncedMsg}");
} catch (Exception $e) {
    setFlash('error', 'Lỗi khi xóa công thức: ' . $e->getMessage());
}

header("Location: " . BASE_URL . "/dinhluong/danh_sach.php");
exit;
