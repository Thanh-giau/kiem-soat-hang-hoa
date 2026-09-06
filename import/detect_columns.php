<?php
/**
 * Tự động nhận diện cột Mã Sản Phẩm và Số Lượng Bán
 */

function detectColumns($rows) {
    if (empty($rows)) {
        return null;
    }

    $headerIndex = -1;
    $colMaSP = -1;
    $colSoLuong = -1;
    $colTenSP = -1;

    // Các từ khóa nhận diện mã sản phẩm
    $keywordsMaSP = ['mã sp', 'ma sp', 'mã sản phẩm', 'ma san pham', 'mã hàng', 'ma hang', 'product code', 'item code', 'code', 'sku'];
    // Các từ khóa nhận diện số lượng bán
    $keywordsSoLuong = ['số lượng bán', 'so luong ban', 'số lượng', 'so luong', 'số bán', 'so ban', 'sl bán', 'sl ban', 'sl', 'qty', 'quantity', 'thực bán'];
    // Các từ khóa nhận diện tên sản phẩm
    $keywordsTenSP = ['tên sp', 'ten sp', 'tên sản phẩm', 'ten san pham', 'tên hàng', 'ten hang', 'product name', 'item name', 'name', 'sản phẩm'];

    // Quét 5 dòng đầu tiên để tìm dòng tiêu đề
    $limit = min(count($rows), 6);
    for ($i = 0; $i < $limit; $i++) {
        $row = $rows[$i];
        $foundMa = -1;
        $foundSL = -1;
        $foundTen = -1;

        foreach ($row as $colIdx => $colVal) {
            $valLower = mb_strtolower(trim($colVal), 'UTF-8');
            if (empty($valLower)) continue;

            if ($foundMa === -1) {
                foreach ($keywordsMaSP as $kw) {
                    if ($valLower === $kw || strpos($valLower, $kw) !== false) {
                        $foundMa = $colIdx;
                        break;
                    }
                }
            }

            if ($foundSL === -1) {
                foreach ($keywordsSoLuong as $kw) {
                    if ($valLower === $kw || strpos($valLower, $kw) !== false) {
                        $foundSL = $colIdx;
                        break;
                    }
                }
            }

            if ($foundTen === -1) {
                foreach ($keywordsTenSP as $kw) {
                    if ($valLower === $kw || strpos($valLower, $kw) !== false) {
                        $foundTen = $colIdx;
                        break;
                    }
                }
            }
        }

        // Nếu tìm thấy ít nhất cột Mã SP hoặc cả 2
        if ($foundMa !== -1 && $foundSL !== -1) {
            $headerIndex = $i;
            $colMaSP = $foundMa;
            $colSoLuong = $foundSL;
            $colTenSP = $foundTen;
            break;
        }
    }

    // Nếu không tìm thấy qua từ khóa chính xác, dự phòng:
    // Cột 0 là Mã SP, cột 1 là Tên SP (nếu có), cột kế tiếp là Số Lượng
    if ($colMaSP === -1 || $colSoLuong === -1) {
        // Kiểm tra dòng 0 xem có dữ liệu không
        $headerIndex = 0;
        $colMaSP = 0;
        $colTenSP = (count($rows[0]) > 2) ? 1 : -1;
        $colSoLuong = (count($rows[0]) > 2) ? 2 : 1;
    }

    return [
        'header_index' => $headerIndex,
        'col_ma_sp' => $colMaSP,
        'col_so_luong' => $colSoLuong,
        'col_ten_sp' => $colTenSP
    ];
}
