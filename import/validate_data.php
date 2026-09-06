<?php
/**
 * Làm sạch và chuẩn hóa dữ liệu đọc từ file Excel / POS export
 * Tự động nhận diện dòng tiêu đề nhóm (iPOS / POS format) và gán danh mục sản phẩm
 */

function validateAndExtractSalesData($rows, $colMap) {
    $headerIndex = $colMap['header_index'];
    $colMa = $colMap['col_ma_sp'];
    $colSL = $colMap['col_so_luong'];
    $colTen = $colMap['col_ten_sp'];

    $extracted = [];
    $totalRows = count($rows);
    $currentCategory = 'Khác';

    // Các từ khóa nhóm cần kiểm kê (Bánh, Đồ ăn, Nước suối...)
    $inventoryKeywords = ['BÁNH', 'BANH', 'SNACK', 'ĐỒ ĂN', 'DO AN', 'BỮA TRƯA', 'BUA TRUA', 'FOOD', 'CAKE', 'NƯỚC SUỐI', 'NUOC SUOI', 'ĐÓNG CHAI', 'DONG CHAI'];

    for ($i = $headerIndex + 1; $i < $totalRows; $i++) {
        $row = $rows[$i];
        if (empty($row)) continue;

        $col0 = isset($row[$colMa]) ? trim((string)$row[$colMa]) : '';
        $colName = ($colTen !== -1 && isset($row[$colTen])) ? trim((string)$row[$colTen]) : '';
        $rawSL = isset($row[$colSL]) ? trim((string)$row[$colSL]) : '0';
        $rawSL = str_replace([',', ' '], '', $rawSL);
        $soLuong = (float)$rawSL;

        // Nếu cả mã và tên đều rỗng -> bỏ qua
        if (empty($col0) && empty($colName)) continue;

        // NẾU TÊN HÀNG RỖNG NHƯNG MÃ HÀNG CÓ KÝ TỰ (Đặc trưng báo cáo iPOS/POS)
        // Đây là dòng tiêu đề nhóm hàng (ví dụ 'BÁNH VÀ SNACK', 'CÀ PHÊ MÁY'...)
        if (!empty($col0) && empty($colName)) {
            $currentCategory = $col0;
            continue;
        }

        $maSP = strtoupper($col0);
        $tenSP = $colName;

        // Bỏ qua dòng nếu số lượng <= 0
        if ($soLuong <= 0) continue;

        // Xác định nhóm có thuộc diện kiểm kê tự nhiên hay không
        $catUpper = mb_strtoupper($currentCategory, 'UTF-8');
        $isInventoryGroup = false;
        foreach ($inventoryKeywords as $kw) {
            if (mb_strpos($catUpper, $kw, 0, 'UTF-8') !== false) {
                $isInventoryGroup = true;
                break;
            }
        }

        // Nếu trong file có nhiều dòng cùng 1 mã SP (ví dụ bán nhiều ca), cộng dồn số lượng!
        if (isset($extracted[$maSP])) {
            $extracted[$maSP]['so_luong'] += $soLuong;
            if (empty($extracted[$maSP]['ten_sp']) && !empty($tenSP)) {
                $extracted[$maSP]['ten_sp'] = $tenSP;
            }
        } else {
            $extracted[$maSP] = [
                'ma_sp' => $maSP,
                'ten_sp' => $tenSP,
                'nhom_sp' => $currentCategory,
                'so_luong' => $soLuong,
                'auto_kiem_ke' => $isInventoryGroup ? 1 : 0
            ];
        }
    }

    return array_values($extracted);
}
