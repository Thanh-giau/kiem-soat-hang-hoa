<?php
/**
 * Global Functions & Business Formulas
 */

// Định nghĩa BASE_URL linh hoạt (Tự động nhận diện HTTPS qua Cloudflare Tunnel / Reverse Proxy)
if (!defined('BASE_URL')) {
    $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && strtolower($_SERVER['HTTP_X_FORWARDED_PROTO']) === 'https')
        || (!empty($_SERVER['HTTP_X_FORWARDED_SSL']) && strtolower($_SERVER['HTTP_X_FORWARDED_SSL']) === 'on')
        || (!empty($_SERVER['HTTP_CF_VISITOR']) && strpos($_SERVER['HTTP_CF_VISITOR'], 'https') !== false)
        || (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443);

    $protocol = $isHttps ? "https://" : "http://";
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost:8000';
    
    $base = '';
    $projectRoot = str_replace('\\', '/', realpath(__DIR__ . '/..') ?: dirname(__DIR__));
    $docRoot = !empty($_SERVER['DOCUMENT_ROOT']) ? str_replace('\\', '/', realpath($_SERVER['DOCUMENT_ROOT']) ?: $_SERVER['DOCUMENT_ROOT']) : '';

    if ($docRoot && strpos($projectRoot, $docRoot) === 0) {
        $sub = trim(substr($projectRoot, strlen($docRoot)), '/');
        $base = $sub !== '' ? '/' . $sub : '';
    } else {
        $scriptDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? ''));
        $base = rtrim($scriptDir, '/');
        $subfolders = ['/sanpham', '/dinhluong', '/ton_dau', '/soban', '/nhaphang', '/dieuchinh', '/kiemke', '/baocao', '/import', '/config', '/includes', '/database'];
        foreach ($subfolders as $sub) {
            if (substr($base, -strlen($sub)) === $sub) {
                $base = substr($base, 0, -strlen($sub));
                break;
            }
        }
    }
    define('BASE_URL', $protocol . $host . $base);
}

/**
 * Flash messages
 */
function setFlash($type, $msg) {
    if (session_status() === PHP_SESSION_NONE) session_start();
    $_SESSION['flash'] = ['type' => $type, 'message' => $msg];
}

function getFlash() {
    if (session_status() === PHP_SESSION_NONE) session_start();
    if (isset($_SESSION['flash'])) {
        $f = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $f;
    }
    return null;
}

/**
 * Format số đẹp mắt (bỏ số 0 thừa sau dấu phẩy)
 */
function formatNumber($number, $decimals = 2) {
    if ($number === null || $number === '') return '0';
    $val = (float)$number;
    if (floor($val) == $val) {
        return number_format($val, 0, ',', '.');
    }
    return rtrim(rtrim(number_format($val, $decimals, ',', '.'), '0'), ',');
}

/**
 * Format ngày tháng d/m/Y
 */
function formatDate($dateStr) {
    if (empty($dateStr)) return '-';
    $time = strtotime($dateStr);
    return $time ? date('d/m/Y', $time) : $dateStr;
}

/**
 * =========================================================================
 * CÔNG THỨC TỒN KHO LÕI (CÔNG THỨC 13 TRONG YÊU CẦU)
 * TỒN LÝ THUYẾT = TỒN ĐẦU + NHẬP HÀNG + SỐ MƯỢN - SỐ BÁN - SỐ HỦY - SỐ CHO MƯỢN
 * =========================================================================
 */
function layBaoCaoTonKhoChiTiet($ngayXem = null, $sanPhamId = null, $chiLayCanKiemKe = true) {
    $db = getDB();
    if (!$ngayXem) {
        $ngayXem = date('Y-m-d');
    }
    $kyKiemKe = date('Y-m', strtotime($ngayXem));
    $ngayDauThang = date('Y-m-01', strtotime($ngayXem));

    $where = ["sp.trang_thai = 1"];
    $params = [
        'ky_kiem_ke' => $kyKiemKe,
        'ngay_dau_thang' => $ngayDauThang,
        'ngay_xem' => $ngayXem,
        'ngay_kiem_ke' => $ngayXem
    ];

    if ($chiLayCanKiemKe) {
        $where[] = "sp.can_kiem_ke = 1";
    }

    if ($sanPhamId) {
        $where[] = "sp.id = :sp_id";
        $params['sp_id'] = $sanPhamId;
    }

    $whereSql = implode(" AND ", $where);

    $sql = "
        SELECT 
            sp.id AS san_pham_id,
            sp.ma_sp,
            sp.ten_sp,
            sp.nhom_sp,
            sp.don_vi_tinh,
            sp.can_kiem_ke,
            COALESCE(td.so_luong, 0) AS ton_dau,
            COALESCE(nh.tong_nhap, 0) AS tong_nhap,
            COALESCE(muon.tong_muon, 0) AS tong_muon,
            COALESCE(sb.tong_ban, 0) AS tong_ban,
            COALESCE(huy.tong_huy, 0) AS tong_huy,
            COALESCE(chomuon.tong_cho_muon, 0) AS tong_cho_muon,
            -- TỒN LÝ THUYẾT = TỒN ĐẦU + NHẬP + MƯỢN - BÁN - HỦY - CHO MƯỢN
            (
                COALESCE(td.so_luong, 0) 
                + COALESCE(nh.tong_nhap, 0) 
                + COALESCE(muon.tong_muon, 0) 
                - COALESCE(sb.tong_ban, 0) 
                - COALESCE(huy.tong_huy, 0) 
                - COALESCE(chomuon.tong_cho_muon, 0)
            ) AS ton_ly_thuyet,
            kk.ton_thuc_te,
            kk.chenh_lech,
            kk.ghi_chu AS ghi_chu_kiem_ke
        FROM san_pham sp
        -- Tồn đầu kỳ
        LEFT JOIN ton_dau td 
            ON td.san_pham_id = sp.id AND td.ky_kiem_ke = :ky_kiem_ke
        -- Hàng nhập từ đầu tháng đến ngày xem
        LEFT JOIN (
            SELECT san_pham_id, SUM(so_luong) AS tong_nhap 
            FROM nhap_hang 
            WHERE ngay_nhap BETWEEN :ngay_dau_thang AND :ngay_xem
            GROUP BY san_pham_id
        ) nh ON nh.san_pham_id = sp.id
        -- Hàng mượn từ đầu tháng đến ngày xem (tăng kho)
        LEFT JOIN (
            SELECT san_pham_id, SUM(so_luong) AS tong_muon 
            FROM dieu_chinh_kho 
            WHERE loai = 'muon' AND ngay_dieu_chinh BETWEEN :ngay_dau_thang AND :ngay_xem
            GROUP BY san_pham_id
        ) muon ON muon.san_pham_id = sp.id
        -- Số bán từ đầu tháng đến ngày xem (giảm kho)
        LEFT JOIN (
            SELECT san_pham_id, SUM(so_luong) AS tong_ban 
            FROM so_ban 
            WHERE ngay_ban BETWEEN :ngay_dau_thang AND :ngay_xem
            GROUP BY san_pham_id
        ) sb ON sb.san_pham_id = sp.id
        -- Hàng hủy từ đầu tháng đến ngày xem (giảm kho)
        LEFT JOIN (
            SELECT san_pham_id, SUM(so_luong) AS tong_huy 
            FROM dieu_chinh_kho 
            WHERE loai = 'huy' AND ngay_dieu_chinh BETWEEN :ngay_dau_thang AND :ngay_xem
            GROUP BY san_pham_id
        ) huy ON huy.san_pham_id = sp.id
        -- Hàng cho mượn từ đầu tháng đến ngày xem (giảm kho)
        LEFT JOIN (
            SELECT san_pham_id, SUM(so_luong) AS tong_cho_muon 
            FROM dieu_chinh_kho 
            WHERE loai = 'cho_muon' AND ngay_dieu_chinh BETWEEN :ngay_dau_thang AND :ngay_xem
            GROUP BY san_pham_id
        ) chomuon ON chomuon.san_pham_id = sp.id
        -- Kiểm kê thực tế của ngày xem
        LEFT JOIN kiem_ke kk 
            ON kk.san_pham_id = sp.id AND kk.ngay_kiem_ke = :ngay_kiem_ke
        WHERE $whereSql
        ORDER BY sp.nhom_sp ASC, sp.ten_sp ASC
    ";

    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    $results = $stmt->fetchAll();

    // Chuẩn hóa chênh lệch nếu đã có kiểm kê thực tế
    foreach ($results as &$item) {
        if ($item['ton_thuc_te'] !== null) {
            $item['chenh_lech'] = (float)$item['ton_thuc_te'] - (float)$item['ton_ly_thuyet'];
        } else {
            $item['chenh_lech'] = null;
        }
    }

    return $results;
}

/**
 * Hiển thị huy hiệu Chênh lệch trực quan
 * 🟢 0 : Khớp
 * 🔴 < 0 : Thiếu
 * 🟠 > 0 : Dư
 */
function renderChenhLechBadge($chenhLech) {
    if ($chenhLech === null) {
        return '<span class="badge badge-secondary">Chưa kiểm kê</span>';
    }
    $val = (float)$chenhLech;
    if ($val == 0) {
        return '<span class="badge badge-success"><i class="fa-solid fa-check"></i> Khớp (0)</span>';
    } elseif ($val < 0) {
        return '<span class="badge badge-danger"><i class="fa-solid fa-circle-down"></i> Thiếu (' . formatNumber($val) . ')</span>';
    } else {
        return '<span class="badge badge-warning"><i class="fa-solid fa-circle-up"></i> Dư (+' . formatNumber($val) . ')</span>';
    }
}

/**
 * Chuyển ngày Y-m-d thành Thứ trong tuần tiếng Việt
 */
if (!function_exists('getThuTrongTuanVN')) {
    function getThuTrongTuanVN($dateStr) {
        $dayOfWeek = date('w', strtotime($dateStr));
        $days = [
            0 => 'Chủ Nhật',
            1 => 'Thứ Hai',
            2 => 'Thứ Ba',
            3 => 'Thứ Tư',
            4 => 'Thứ Năm',
            5 => 'Thứ Sáu',
            6 => 'Thứ Bảy'
        ];
        return $days[$dayOfWeek] ?? '';
    }
}

/**
 * Tự động phát hiện thông tin kết nối điện thoại (Cloudflare Online + Wi-Fi LAN)
 */
function layThongTinKetNoiMobile() {
    $onlineUrl = null;

    // 1. Kiểm tra Cloudflare Quick Tunnel (cổng metrics 20241)
    $ctx = stream_context_create(['http' => ['timeout' => 0.3]]);
    $res = @file_get_contents('http://127.0.0.1:20241/quicktunnel', false, $ctx);
    if ($res) {
        $json = @json_decode($res, true);
        if (!empty($json['hostname'])) {
            $onlineUrl = 'https://' . $json['hostname'];
        }
    }

    if (!$onlineUrl) {
        // Fallback về tunnel hiện tại nếu cổng metrics tạm thời bận
        $onlineUrl = 'https://statistical-fit-warrant-mainly.trycloudflare.com';
    }

    // 2. IP mạng Wi-Fi tại quán
    $lanIp = '192.168.1.26';
    $lanUrl = 'http://' . $lanIp . ':8000';

    return [
        'online_url' => $onlineUrl,
        'lan_url' => $lanUrl,
        'lan_ip' => $lanIp
    ];
}


