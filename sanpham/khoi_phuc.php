<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';
requireLogin();

$id = (int)($_GET['id'] ?? 0);
$db = getDB();

$stmt = $db->prepare("SELECT ma_sp, ten_sp FROM san_pham WHERE id = :id");
$stmt->execute(['id' => $id]);
$sp = $stmt->fetch();

if ($sp) {
    // KHÔI PHỤC SẢN PHẨM ĐÃ XÓA MỀM (MỤC 7)
    $stmtRestore = $db->prepare("UPDATE san_pham SET trang_thai = 1 WHERE id = :id");
    $stmtRestore->execute(['id' => $id]);
    setFlash('success', "Đã khôi phục sản phẩm [{$sp['ma_sp']} - {$sp['ten_sp']}] thành công!");
} else {
    setFlash('danger', "Không tìm thấy sản phẩm cần khôi phục!");
}

header("Location: " . BASE_URL . "/sanpham/danh_sach.php?tab=active");
exit;
