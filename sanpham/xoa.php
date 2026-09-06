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
    // XÓA MỀM (SOFT DELETE) THEO YÊU CẦU MỤC 7
    // trang_thai = 0: Không còn sử dụng trong dữ liệu mới, không hiện kiểm kê
    // Nhưng vẫn giữ nguyên toàn bộ lịch sử số bán, nhập hàng, kiểm kê cũ!
    $stmtDel = $db->prepare("UPDATE san_pham SET trang_thai = 0 WHERE id = :id");
    $stmtDel->execute(['id' => $id]);
    setFlash('warning', "Đã chuyển sản phẩm [{$sp['ma_sp']} - {$sp['ten_sp']}] vào danh sách đã xóa mềm (lịch sử vẫn được lưu trữ an toàn).");
} else {
    setFlash('danger', "Không tìm thấy sản phẩm yêu cầu!");
}

header("Location: " . BASE_URL . "/sanpham/danh_sach.php");
exit;
