<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';
requireLogin();

$id = (int)($_GET['id'] ?? 0);
$db = getDB();

$stmt = $db->prepare("DELETE FROM dieu_chinh_kho WHERE id = :id");
$stmt->execute(['id' => $id]);

setFlash('warning', 'Đã xóa phiếu điều chỉnh kho. Tồn kho lý thuyết đã được tự động hoàn nguyên.');
header("Location: " . BASE_URL . "/dieuchinh/danh_sach.php");
exit;
