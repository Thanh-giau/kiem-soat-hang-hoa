<?php
/**
 * ⭐ LINH HỒN CỦA HỆ THỐNG: filter_inventory_products.php
 * 
 * Đối chiếu danh sách sản phẩm từ file Excel với Database:
 * 1. BÓC TÁCH ĐỊNH LƯỢNG MÓN BÁN (BOM / RECIPE):
 *    Nếu món bán trên POS (vd: Matcha Tây Bắc Mochi, Sandwich Gà...) có cài đặt định lượng:
 *    -> Tự động bóc tách thành các nguyên vật liệu kiểm kê tương ứng (Viên Mochi, Vỏ bánh, Phô mai, Nước xốt...)
 *    -> Tự động nhân số lượng bán với định lượng và cộng dồn vào mặt hàng kiểm kê!
 * 2. Nhóm CẦN KIỂM KÊ (can_kiem_ke = 1): Bánh, Đồ ăn, Nước suối... -> Lưu & kiểm kê
 * 3. Nhóm BỎ QUA (can_kiem_ke = 0): Cà phê, Trà, Nước pha chế không cài định lượng -> Tự động bỏ qua
 * 4. Nhóm CHƯA CÓ TRONG HỆ THỐNG: Mã mới xuất hiện -> Báo động & gợi ý thông minh
 */

require_once __DIR__ . '/../config/database.php';

// Helper: Chuẩn hóa tên món POS, loại bỏ hậu tố size (Vừa, Lớn, Nhỏ...) và tiền tố nền tảng giao hàng
function cleanDishSizeAndPlatform($str) {
    if (!$str) return '';
    // Bỏ dấu cộng phía trước nếu có: + PLT
    $str = preg_replace('/^\+\s*/u', '', $str);
    // Bỏ tiền tố PLT / Grab / Shopee / Baemin
    $str = preg_replace('/^(PLT|GRAB|SHOPEE|BAEMIN)\s+/ui', '', $str);
    // Bỏ các hậu tố size trong ngoặc: (Vừa), (Lớn), (Nhỏ), (Nóng), (Lạnh), (M), (L), (S)...
    $str = preg_replace('/\s*\([^)]*\)/u', '', $str);
    // Chuẩn hóa khoảng trắng
    $str = preg_replace('/\s+/', ' ', $str);
    return trim($str);
}

// Helper: Bỏ dấu tiếng Việt cho so sánh mờ từ khóa món
function removeVietnameseTonesHelper($str) {
    if (!$str) return '';
    $str = mb_strtolower($str, 'UTF-8');
    $str = preg_replace('/(à|á|ạ|ả|ã|â|ầ|ấ|ậ|ẩ|ẫ|ă|ằ|ắ|ặ|ẳ|ẵ)/u', 'a', $str);
    $str = preg_replace('/(è|é|ẹ|ẻ|ẽ|ê|ề|ế|ệ|ể|ễ)/u', 'e', $str);
    $str = preg_replace('/(ì|í|ị|ỉ|ĩ)/u', 'i', $str);
    $str = preg_replace('/(ò|ó|ọ|ỏ|õ|ô|ồ|ố|ộ|ổ|ỗ|ơ|ờ|ớ|ợ|ở|ỡ)/u', 'o', $str);
    $str = preg_replace('/(ù|ú|ụ|ủ|ũ|ư|ừ|ứ|ự|ử|ữ)/u', 'u', $str);
    $str = preg_replace('/(ỳ|ý|ỵ|ỷ|ỹ)/u', 'y', $str);
    $str = preg_replace('/(đ)/u', 'd', $str);
    return trim($str);
}

// Hàm so khớp thông minh món POS với các công thức định lượng (BOM)
function findMatchingRecipesForDish($maSP, $tenSP, $allRecipes) {
    $maSP = strtoupper(trim($maSP));
    $tenSP = trim($tenSP);

    // 1. Khớp theo mã món POS nếu có
    if (!empty($maSP)) {
        $codeMatches = [];
        foreach ($allRecipes as $r) {
            $rCode = strtoupper(trim($r['ma_mon_pos']));
            if (!empty($rCode)) {
                $codes = array_map('trim', explode(',', $rCode));
                if (in_array($maSP, $codes)) {
                    $codeMatches[] = $r;
                }
            }
        }
        if (!empty($codeMatches)) {
            return $codeMatches;
        }
    }

    // 2. Khớp chính xác theo tên món bán POS
    $cleanItemName = mb_strtolower($tenSP, 'UTF-8');
    $exactMatches = [];
    foreach ($allRecipes as $r) {
        $rName = mb_strtolower(trim($r['ten_mon_pos']), 'UTF-8');
        if ($cleanItemName === $rName) {
            $exactMatches[] = $r;
        }
    }
    if (!empty($exactMatches)) {
        return $exactMatches;
    }

    // 3. Khớp sau khi loại bỏ hậu tố size (Vừa, Lớn, Nhỏ, Nóng, Lạnh...) và tiền tố PLT
    $baseItemName = cleanDishSizeAndPlatform($tenSP);
    $normItemName = mb_strtolower($baseItemName, 'UTF-8');
    $baseMatches = [];
    foreach ($allRecipes as $r) {
        $baseRName = mb_strtolower(cleanDishSizeAndPlatform($r['ten_mon_pos']), 'UTF-8');
        if ($normItemName === $baseRName) {
            $baseMatches[] = $r;
        }
    }
    if (!empty($baseMatches)) {
        return $baseMatches;
    }

    // 4. Khớp theo quan hệ chuỗi chứa nhau (Substring)
    $subMatches = [];
    foreach ($allRecipes as $r) {
        $baseRName = mb_strtolower(cleanDishSizeAndPlatform($r['ten_mon_pos']), 'UTF-8');
        if (!empty($baseRName) && !empty($normItemName)) {
            if (mb_strpos($normItemName, $baseRName, 0, 'UTF-8') !== false || mb_strpos($baseRName, $normItemName, 0, 'UTF-8') !== false) {
                $subMatches[] = $r;
            }
        }
    }
    if (!empty($subMatches)) {
        return $subMatches;
    }

    // 5. Khớp thông minh theo từ khóa cốt lõi (Keywords token matching)
    // Ví dụ: Recipe "Matcha Tây Bắc Mochi" -> khớp cả "Matcha Latte Tây Bắc Mochi (Vừa)" & "(Lớn)"
    // nhưng KHÔNG khớp "Matcha Latte Tây Bắc (Vừa)" vì thiếu từ Mochi!
    $normNoToneItem = removeVietnameseTonesHelper($normItemName);
    $keywordMatches = [];
    foreach ($allRecipes as $r) {
        // Không tự khớp với chính món nguyên liệu bán lẻ (ví dụ: Mochi Kem Matcha bán lẻ)
        if (strtoupper($r['sp_ma']) === $maSP) {
            continue;
        }
        $baseRName = mb_strtolower(cleanDishSizeAndPlatform($r['ten_mon_pos']), 'UTF-8');
        $normNoToneRecipe = removeVietnameseTonesHelper($baseRName);

        $recipeWords = array_filter(explode(' ', $normNoToneRecipe), function($w) {
            return mb_strlen($w, 'UTF-8') >= 2;
        });

        if (count($recipeWords) >= 2) {
            $allWordsMatch = true;
            foreach ($recipeWords as $w) {
                if (strpos($normNoToneItem, $w) === false) {
                    $allWordsMatch = false;
                    break;
                }
            }
            if ($allWordsMatch) {
                $keywordMatches[] = $r;
            }
        }
    }
    if (!empty($keywordMatches)) {
        return $keywordMatches;
    }

    return [];
}

function filterInventoryProducts($extractedItems) {
    $db = getDB();

    // 1. Lấy toàn bộ sản phẩm đang có trong hệ thống
    $stmt = $db->query("SELECT id, ma_sp, ten_sp, nhom_sp, can_kiem_ke, trang_thai FROM san_pham");
    $allDbProducts = [];
    $allDbProductsById = [];
    $inventoryCleanNameMap = [];
    $inventoryNoToneMap = [];

    while ($row = $stmt->fetch()) {
        $allDbProducts[strtoupper($row['ma_sp'])] = $row;
        $allDbProductsById[$row['id']] = $row;
        // Lập chỉ mục các sản phẩm kiểm kê gốc trong kho (can_kiem_ke = 1)
        if ((int)$row['can_kiem_ke'] === 1 && (int)$row['trang_thai'] === 1) {
            $cName = mb_strtolower(cleanDishSizeAndPlatform($row['ten_sp']), 'UTF-8');
            $inventoryCleanNameMap[$cName] = $row;
            $inventoryNoToneMap[removeVietnameseTonesHelper($cName)] = $row;
        }
    }

    // 2. Lấy toàn bộ định lượng công thức món (BOM / Recipe)
    $stmtRecipes = $db->query("
        SELECT dlm.*, sp.ma_sp AS sp_ma, sp.ten_sp AS sp_ten, sp.nhom_sp AS sp_nhom, sp.can_kiem_ke 
        FROM dinh_luong_mon dlm 
        JOIN san_pham sp ON sp.id = dlm.san_pham_id 
        WHERE sp.trang_thai = 1
    ");
    $allRecipes = $stmtRecipes->fetchAll();

    $danhSachKiemKeMap = [];  // Gộp theo san_pham_id: [san_pham_id => item]
    $danhSachBoQua = [];      // Các món không kiểm kê hoặc món thành phẩm đã bóc tách định lượng
    $danhSachSanPhamMoi = []; // Chưa có trong Database và chưa có định lượng

    foreach ($extractedItems as $item) {
        $maSP = strtoupper(trim($item['ma_sp']));
        $tenSP = trim($item['ten_sp'] ?? '');
        $soLuong = (float)$item['so_luong'];
        $nhomSP = $item['nhom_sp'] ?? 'Khác';
        $autoKiemKe = $item['auto_kiem_ke'] ?? 0;

        // KIỂM TRA MÓN CÓ CÀI ĐỊNH LƯỢNG CÔNG THỨC HAY KHÔNG (SO KHỚP ĐA CẤP THÔNG MINH)
        $matchedRecipes = findMatchingRecipesForDish($maSP, $tenSP, $allRecipes);

        if (!empty($matchedRecipes)) {
            // MÓN NÀY CÓ CÔNG THỨC! TỰ ĐỘNG BÓC TÁCH NGUYÊN VẬT LIỆU KIỂM KÊ!
            $tenNVLList = [];
            foreach ($matchedRecipes as $recipe) {
                $spId = $recipe['san_pham_id'];
                $tieuHaoMotPhan = (float)$recipe['so_luong_tieu_hao'];
                $tongTieuHao = $soLuong * $tieuHaoMotPhan;

                $tenNVLList[] = "{$recipe['sp_ten']} (+{$tongTieuHao})";

                if (isset($danhSachKiemKeMap[$spId])) {
                    $danhSachKiemKeMap[$spId]['so_luong'] += $tongTieuHao;
                    $danhSachKiemKeMap[$spId]['ghi_chu_dinh_luong'][] = "Bao gồm {$tongTieuHao} từ món [{$tenSP}]";
                } else {
                    $spInfo = $allDbProductsById[$spId] ?? [
                        'id' => $spId,
                        'ma_sp' => $recipe['sp_ma'],
                        'ten_sp' => $recipe['sp_ten'],
                        'nhom_sp' => $recipe['sp_nhom'],
                        'can_kiem_ke' => 1
                    ];
                    $danhSachKiemKeMap[$spId] = [
                        'san_pham_id' => $spId,
                        'ma_sp' => $spInfo['ma_sp'],
                        'ten_sp' => $spInfo['ten_sp'],
                        'nhom_sp' => $spInfo['nhom_sp'],
                        'so_luong' => $tongTieuHao,
                        'can_kiem_ke' => 1,
                        'ghi_chu_dinh_luong' => ["Từ {$soLuong} phần [{$tenSP}]"]
                    ];
                }
            }

            // Đưa món bán POS vào danh sách đã bóc tách định lượng
            $danhSachBoQua[] = [
                'san_pham_id' => 0,
                'ma_sp' => $maSP,
                'ten_sp' => $tenSP . ' (Đã bóc tách: ' . implode(', ', $tenNVLList) . ')',
                'nhom_sp' => $nhomSP . ' [Có công thức NVL]',
                'so_luong' => $soLuong,
                'can_kiem_ke' => 0,
                'is_recipe_dish' => true
            ];
            continue;
        }

        // ⭐ 1.5. TỰ ĐỘNG GỘP MÓN NỀN TẢNG (PLT / GRAB / SHOPEE...) VÀO MÓN BÁNH GỐC TRONG KHO
        $isPlatformItem = preg_match('/^(PLT|GRAB|SHOPEE|BAEMIN)\s+/ui', $tenSP) 
            || strcasecmp($nhomSP, 'Platform') === 0 
            || strpos($maSP, 'PLT') !== false;

        $cleanName = cleanDishSizeAndPlatform($tenSP);
        $normCleanName = mb_strtolower($cleanName, 'UTF-8');
        $normCleanNoTone = removeVietnameseTonesHelper($normCleanName);

        $parentProduct = null;
        if (isset($inventoryCleanNameMap[$normCleanName])) {
            $parentProduct = $inventoryCleanNameMap[$normCleanName];
        } elseif (isset($inventoryNoToneMap[$normCleanNoTone])) {
            $parentProduct = $inventoryNoToneMap[$normCleanNoTone];
        }

        // Nếu tìm thấy món kiểm kê gốc trong kho tương ứng:
        // (Ví dụ: 'PLT Bánh Mì Que Pate Cột Đèn' -> khớp với món gốc 'Bánh Mì Que Pate Cột Đèn')
        if ($parentProduct && (empty($allDbProducts[$maSP]) || (int)$allDbProducts[$maSP]['id'] !== (int)$parentProduct['id'] || $isPlatformItem)) {
            $parentSpId = (int)$parentProduct['id'];

            if (isset($danhSachKiemKeMap[$parentSpId])) {
                $danhSachKiemKeMap[$parentSpId]['so_luong'] += $soLuong;
                $danhSachKiemKeMap[$parentSpId]['ghi_chu_dinh_luong'][] = "Bao gồm {$soLuong} từ món app [{$tenSP}]";
            } else {
                $danhSachKiemKeMap[$parentSpId] = [
                    'san_pham_id' => $parentSpId,
                    'ma_sp' => $parentProduct['ma_sp'],
                    'ten_sp' => $parentProduct['ten_sp'],
                    'nhom_sp' => $parentProduct['nhom_sp'],
                    'so_luong' => $soLuong,
                    'can_kiem_ke' => 1,
                    'ghi_chu_dinh_luong' => ["Từ {$soLuong} món app [{$tenSP}]"]
                ];
            }

            // Đưa món PLT vào danh sách đã gộp vào kho (không tạo dòng kiểm kê riêng)
            $danhSachBoQua[] = [
                'san_pham_id' => 0,
                'ma_sp' => $maSP,
                'ten_sp' => $tenSP . ' (Đã tự động gộp vào kho: ' . $parentProduct['ten_sp'] . ')',
                'nhom_sp' => $nhomSP . ' [Đã gộp vào kho]',
                'so_luong' => $soLuong,
                'can_kiem_ke' => 0,
                'is_recipe_dish' => true
            ];
            continue;
        }

        // NẾU KHÔNG CÀI ĐỊNH LƯỢNG VÀ KHÔNG PHẢI MÓN GỘP -> XỬ LÝ THEO MẶT HÀNG BÌNH THƯỜNG
        if (isset($allDbProducts[$maSP])) {
            $dbProduct = $allDbProducts[$maSP];
            $spId = $dbProduct['id'];

            if ((int)$dbProduct['can_kiem_ke'] === 1) {
                // Sản phẩm kiểm kê bán trực tiếp (Bánh, Nước suối chai...)
                if (isset($danhSachKiemKeMap[$spId])) {
                    $danhSachKiemKeMap[$spId]['so_luong'] += $soLuong;
                } else {
                    $danhSachKiemKeMap[$spId] = [
                        'san_pham_id' => $spId,
                        'ma_sp' => $dbProduct['ma_sp'],
                        'ten_sp' => $dbProduct['ten_sp'],
                        'nhom_sp' => $dbProduct['nhom_sp'],
                        'so_luong' => $soLuong,
                        'can_kiem_ke' => 1,
                        'ghi_chu_dinh_luong' => []
                    ];
                }
            } else {
                // Sản phẩm đồ uống không kiểm kê và không cài định lượng -> BỎ QUA
                $danhSachBoQua[] = [
                    'san_pham_id' => $spId,
                    'ma_sp' => $dbProduct['ma_sp'],
                    'ten_sp' => $dbProduct['ten_sp'],
                    'nhom_sp' => $dbProduct['nhom_sp'],
                    'so_luong' => $soLuong,
                    'can_kiem_ke' => 0,
                    'is_recipe_dish' => false
                ];
            }
        } else {
            // Mã mới chưa có trong hệ thống
            $danhSachSanPhamMoi[] = [
                'ma_sp' => $maSP,
                'ten_sp' => !empty($tenSP) ? $tenSP : $maSP,
                'nhom_sp' => $nhomSP,
                'so_luong' => $soLuong,
                'auto_kiem_ke' => $autoKiemKe
            ];
        }
    }

    $danhSachKiemKe = array_values($danhSachKiemKeMap);

    return [
        'kiem_ke' => $danhSachKiemKe,
        'bo_qua' => $danhSachBoQua,
        'san_pham_moi' => $danhSachSanPhamMoi,
        'tong_so_ma' => count($extractedItems)
    ];
}
