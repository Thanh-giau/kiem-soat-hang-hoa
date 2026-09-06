<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../import/sync_bom_sales.php';
requireLogin();

$res = syncAllUploadedSalesWithBOM();

if (!empty($res['errors'])) {
    setFlash('warning', "Đã đồng bộ {$res['synced_files']} / {$res['total_files']} file số bán với công thức BOM. Có một số lưu ý: " . implode('; ', $res['errors']));
} else {
    $countDays = count($res['updated_days']);
    setFlash('success', "⚡ Đã đồng bộ thành công công thức BOM cho toàn bộ {$res['synced_files']} file số bán ({$countDays} ngày)! Số lượng bán của các nguyên vật liệu kiểm kê và tồn kho đã được cập nhật chính xác.");
}

header("Location: " . BASE_URL . "/dinhluong/danh_sach.php");
exit;
