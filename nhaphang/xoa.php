<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';
requireLogin();

$id = (int)($_GET['id'] ?? 0);
$db = getDB();

$stmt = $db->prepare("DELETE FROM nhap_hang WHERE id = :id");
$stmt->execute(['id' => $id]);

setFlash('warning', 'Đã xóa phiếu nhập hàng. Tồn lý thuyết đã được tự động tính lại.');
header("Location: " . BASE_URL . "/nhaphang/danh_sach.php");
exit;
