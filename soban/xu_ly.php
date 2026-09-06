<?php
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../import/excel_reader.php';
require_once __DIR__ . '/../import/detect_columns.php';
require_once __DIR__ . '/../import/validate_data.php';
require_once __DIR__ . '/../import/filter_inventory_products.php';
require_once __DIR__ . '/../import/save_sales.php';

if (session_status() === PHP_SESSION_NONE) session_start();
$user = currentUser();

if (!$user) {
    echo json_encode(['success' => false, 'message' => 'Phiên đăng nhập đã hết hạn!']);
    exit;
}

$action = $_POST['action'] ?? '';

// ACTION 1: PHÂN TÍCH FILE EXCEL
if ($action === 'analyze') {
    if (!isset($_FILES['excel_file']) || $_FILES['excel_file']['error'] !== UPLOAD_ERR_OK) {
        echo json_encode(['success' => false, 'message' => 'Lỗi tải lên file từ thiết bị!']);
        exit;
    }

    $ngayBan = trim($_POST['ngay_ban'] ?? date('Y-m-d'));
    $file = $_FILES['excel_file'];
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

    if (!in_array($ext, ['xlsx', 'xls', 'csv'])) {
        echo json_encode(['success' => false, 'message' => 'Chỉ chấp nhận file định dạng .xlsx, .xls hoặc .csv!']);
        exit;
    }

    $tempDir = __DIR__ . '/../uploads/temp';
    if (!is_dir($tempDir)) mkdir($tempDir, 0777, true);

    $fileHash = md5_file($file['tmp_name']);
    $tempFileName = 'upload_' . time() . '_' . uniqid() . '.' . $ext;
    $tempFilePath = $tempDir . '/' . $tempFileName;

    if (!move_uploaded_file($file['tmp_name'], $tempFilePath)) {
        echo json_encode(['success' => false, 'message' => 'Không thể lưu file tạm trên máy chủ!']);
        exit;
    }

    try {
        // Đọc toàn bộ nội dung file
        $rows = readDataFromFile($tempFilePath);
        if (empty($rows)) {
            echo json_encode(['success' => false, 'message' => 'File Excel rỗng!']);
            exit;
        }

        // Tự nhận diện các cột
        $colMap = detectColumns($rows);

        // Chuẩn hóa và cộng dồn số bán
        $extractedItems = validateAndExtractSalesData($rows, $colMap);
        if (empty($extractedItems)) {
            echo json_encode(['success' => false, 'message' => 'Không tìm thấy dữ liệu số bán hợp lệ trong file!']);
            exit;
        }

        // LINH HỒN CỦA HỆ THỐNG: Lọc can_kiem_ke = 1 và phát hiện mã mới
        $filterResult = filterInventoryProducts($extractedItems);

        // KIỂM TRA CHỐNG UPLOAD TRÙNG (MỤC 6 TRONG YÊU CẦU)
        $db = getDB();
        $stmtCheckHash = $db->prepare("SELECT id, ten_file_goc, ngay_ban FROM upload_files WHERE file_hash = :hash LIMIT 1");
        $stmtCheckHash->execute(['hash' => $fileHash]);
        $duplicateFile = $stmtCheckHash->fetch();

        $stmtCheckDate = $db->prepare("SELECT COUNT(*) FROM so_ban WHERE ngay_ban = :ngay_ban");
        $stmtCheckDate->execute(['ngay_ban' => $ngayBan]);
        $countDateSales = $stmtCheckDate->fetchColumn();

        $isDuplicate = false;
        $dupMessage = '';

        if ($duplicateFile) {
            $isDuplicate = true;
            $dupMessage = "⚠️ CẢNH BÁO: File này có nội dung trùng khớp hoàn toàn với file [{$duplicateFile['ten_file_goc']}] đã upload ngày " . formatDate($duplicateFile['ngay_ban']) . "!";
        } elseif ($countDateSales > 0) {
            $isDuplicate = true;
            $dupMessage = "⚠️ CẢNH BÁO: Dữ liệu ngày " . formatDate($ngayBan) . " hiện đã có $countDateSales sản phẩm được ghi nhận trong cơ sở dữ liệu!";
        }

        // Lưu dữ liệu phân tích vào session
        $sessionKey = uniqid('upload_session_');
        $_SESSION[$sessionKey] = [
            'file_name_origin' => $file['name'],
            'temp_file_path' => $tempFilePath,
            'file_hash' => $fileHash,
            'ngay_ban' => $ngayBan,
            'extracted_items' => $extractedItems,
            'filter_result' => $filterResult,
            'is_duplicate' => $isDuplicate,
            'dup_message' => $dupMessage
        ];

        echo json_encode([
            'success' => true,
            'session_key' => $sessionKey,
            'duplicate' => $isDuplicate,
            'duplicate_message' => $dupMessage,
            'data' => $filterResult
        ]);
        exit;
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Lỗi xử lý file: ' . $e->getMessage()]);
        exit;
    }
}

// ACTION 2: XỬ LÝ SẢN PHẨM MỚI (MỤC 5 TRONG YÊU CẦU)
if ($action === 'resolve_new_item') {
    $sessionKey = $_POST['session_key'] ?? '';
    $maSP = strtoupper(trim($_POST['ma_sp'] ?? ''));
    $choice = (int)($_POST['choice'] ?? 0); // 1: Cần kiểm kê, 0: Bỏ qua, -1: Bỏ qua mã này trong file

    if (!isset($_SESSION[$sessionKey])) {
        echo json_encode(['success' => false, 'message' => 'Phiên làm việc đã hết hạn. Vui lòng upload lại!']);
        exit;
    }

    $sessData = &$_SESSION[$sessionKey];
    $db = getDB();

    if ($choice === 1 || $choice === 0) {
        // Tìm tên trong danh sách mới
        $tenSP = $maSP;
        foreach ($sessData['filter_result']['san_pham_moi'] as $item) {
            if (strtoupper($item['ma_sp']) === $maSP) {
                $tenSP = $item['ten_sp'];
                break;
            }
        }

        $nhomSP = ($choice === 1) ? 'Bánh / Đồ ăn / Nước suối' : 'Đồ uống pha chế / Khác';

        // Thêm vào bảng san_pham
        $stmtInsert = $db->prepare("
            INSERT INTO san_pham (ma_sp, ten_sp, nhom_sp, don_vi_tinh, can_kiem_ke, trang_thai, ghi_chu)
            VALUES (:ma_sp, :ten_sp, :nhom_sp, 'Cái', :can_kiem_ke, 1, 'Tự động thêm từ file Excel số bán')
            ON DUPLICATE KEY UPDATE can_kiem_ke = VALUES(can_kiem_ke)
        ");
        $stmtInsert->execute([
            'ma_sp' => $maSP,
            'ten_sp' => $tenSP,
            'nhom_sp' => $nhomSP,
            'can_kiem_ke' => $choice
        ]);
    }

    // Tự động lọc lại toàn bộ extracted_items sau khi đã thêm sản phẩm mới vào DB
    $filterResult = filterInventoryProducts($sessData['extracted_items']);
    $sessData['filter_result'] = $filterResult;

    echo json_encode([
        'success' => true,
        'updated_data' => [
            'session_key' => $sessionKey,
            'duplicate' => $sessData['is_duplicate'],
            'duplicate_message' => $sessData['dup_message'],
            'data' => $filterResult
        ]
    ]);
    exit;
}

// ACTION 2B: XỬ LÝ HÀNG LOẠT SẢN PHẨM MỚI (DÀNH CHO FILE NHIỀU MÓN NHƯ IPOS)
if ($action === 'resolve_all_new') {
    $sessionKey = $_POST['session_key'] ?? '';
    $mode = $_POST['mode'] ?? 'auto'; // 'auto', 'all_inventory', 'all_skip'

    if (!isset($_SESSION[$sessionKey])) {
        echo json_encode(['success' => false, 'message' => 'Phiên làm việc đã hết hạn. Vui lòng upload lại!']);
        exit;
    }

    $sessData = &$_SESSION[$sessionKey];
    $db = getDB();

    $stmtInsert = $db->prepare("
        INSERT INTO san_pham (ma_sp, ten_sp, nhom_sp, don_vi_tinh, can_kiem_ke, trang_thai, ghi_chu)
        VALUES (:ma_sp, :ten_sp, :nhom_sp, 'Cái', :can_kiem_ke, 1, 'Tự động thêm từ file Excel số bán')
        ON DUPLICATE KEY UPDATE 
            ten_sp = VALUES(ten_sp),
            nhom_sp = VALUES(nhom_sp),
            can_kiem_ke = VALUES(can_kiem_ke)
    ");

    foreach ($sessData['filter_result']['san_pham_moi'] as $item) {
        $canKiemKe = 0;
        if ($mode === 'all_inventory') {
            $canKiemKe = 1;
        } elseif ($mode === 'all_skip') {
            $canKiemKe = 0;
        } else {
            // Mode 'auto': Dùng phân loại thông minh theo từ khóa nhóm (Bánh/Đồ ăn/Nước suối -> 1, Đồ pha chế -> 0)
            $canKiemKe = $item['auto_kiem_ke'] ?? 0;
        }

        $stmtInsert->execute([
            'ma_sp' => $item['ma_sp'],
            'ten_sp' => $item['ten_sp'],
            'nhom_sp' => $item['nhom_sp'] ?? 'Khác',
            'can_kiem_ke' => $canKiemKe
        ]);
    }

    // Lọc lại toàn bộ danh sách sau khi đã đăng ký hàng loạt vào DB
    $filterResult = filterInventoryProducts($sessData['extracted_items']);
    $sessData['filter_result'] = $filterResult;

    echo json_encode([
        'success' => true,
        'updated_data' => [
            'session_key' => $sessionKey,
            'duplicate' => $sessData['is_duplicate'],
            'duplicate_message' => $sessData['dup_message'],
            'data' => $filterResult
        ]
    ]);
    exit;
}

// ACTION 3: XÁC NHẬN LƯU SỐ BÁN VÀO CSDL
if ($action === 'commit_save') {
    $sessionKey = $_POST['session_key'] ?? '';
    $ghiDe = ($_POST['ghi_de'] ?? '0') === '1';

    if (!isset($_SESSION[$sessionKey])) {
        echo json_encode(['success' => false, 'message' => 'Phiên làm việc không tồn tại hoặc đã hết hạn!']);
        exit;
    }

    $sessData = $_SESSION[$sessionKey];
    $targetDir = __DIR__ . '/../uploads/so_ban';
    if (!is_dir($targetDir)) mkdir($targetDir, 0777, true);

    $finalFileName = date('Ymd_His') . '_' . preg_replace('/[^a-zA-Z0-9_\.-]/', '_', $sessData['file_name_origin']);
    $finalPath = $targetDir . '/' . $finalFileName;

    // Chuyển từ file temp sang thư mục uploads/so_ban chính thức
    if (file_exists($sessData['temp_file_path'])) {
        rename($sessData['temp_file_path'], $finalPath);
    }

    $fileInfo = [
        'ten_file_goc' => $sessData['file_name_origin'],
        'ten_file_luu' => $finalFileName,
        'file_hash' => $sessData['file_hash'],
        'tong_dong' => $sessData['filter_result']['tong_so_ma'],
        'dong_bo_qua' => count($sessData['filter_result']['bo_qua'])
    ];

    $saveResult = saveSalesData(
        $sessData['ngay_ban'],
        $sessData['filter_result']['kiem_ke'],
        $fileInfo,
        $user['id'],
        $ghiDe
    );

    if ($saveResult['success']) {
        unset($_SESSION[$sessionKey]);
        echo json_encode([
            'success' => true,
            'message' => "Đã lưu thành công {$saveResult['so_luong_luu']} sản phẩm cần kiểm kê vào cơ sở dữ liệu!"
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => $saveResult['message']]);
    }
    exit;
}
