<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';
requireLogin();

$ngay = $_GET['ngay'] ?? date('Y-m-d');
$danhSach = layBaoCaoTonKhoChiTiet($ngay, null, true);

$fileName = 'Bao_Cao_Chenh_Lech_Kho_' . str_replace('-', '', $ngay) . '.csv';

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $fileName . '"');

$output = fopen('php://output', 'w');

// Ghi UTF-8 BOM để Excel hiển thị tiếng Việt không bị lỗi font
fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

// Header dòng tiêu đề
fputcsv($output, [
    'Mã Sản Phẩm',
    'Tên Sản Phẩm',
    'Nhóm Sản Phẩm',
    'Đơn Vị Tính',
    'Tồn Đầu Kỳ',
    'Nhập Hàng (+)',
    'Mượn Về (+)',
    'Số Bán (-)',
    'Hủy Hàng (-)',
    'Cho Mượn (-)',
    'Tồn Lý Thuyết',
    'Kiểm Kê Thực Tế',
    'Chênh Lệch',
    'Đánh Giá Tình Trạng',
    'Ghi Chú Kiểm Kê'
]);

foreach ($danhSach as $row) {
    $tinhTrang = 'Chưa kiểm kê';
    if ($row['chenh_lech'] !== null) {
        if ($row['chenh_lech'] == 0) {
            $tinhTrang = 'Khớp (0)';
        } elseif ($row['chenh_lech'] < 0) {
            $tinhTrang = 'Thiếu hàng (' . $row['chenh_lech'] . ')';
        } else {
            $tinhTrang = 'Dư hàng (+' . $row['chenh_lech'] . ')';
        }
    }

    fputcsv($output, [
        $row['ma_sp'],
        $row['ten_sp'],
        $row['nhom_sp'],
        $row['don_vi_tinh'],
        $row['ton_dau'],
        $row['tong_nhap'],
        $row['tong_muon'],
        $row['tong_ban'],
        $row['tong_huy'],
        $row['tong_cho_muon'],
        $row['ton_ly_thuyet'],
        $row['ton_thuc_te'] !== null ? $row['ton_thuc_te'] : '',
        $row['chenh_lech'] !== null ? $row['chenh_lech'] : '',
        $tinhTrang,
        $row['ghi_chu_kiem_ke'] ?? ''
    ]);
}

fclose($output);
exit;
