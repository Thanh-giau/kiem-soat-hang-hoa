<?php
/**
 * Đọc file Excel (.xlsx, .xls) hoặc file CSV
 * Hỗ trợ toàn diện:
 * 1. File .xlsx hiện đại (OpenXML / Zip)
 * 2. File .xls nhị phân truyền thống (BIFF8 / Excel 97-2004) qua SimpleXLS
 * 3. File .xls định dạng HTML Table (Rất phổ biến từ các phần mềm POS KiotViet, CukCuk, Sapo, iPOS...)
 * 4. File .xls định dạng XML Spreadsheet 2003 (SpreadsheetML)
 * 5. File .csv chuẩn
 */

require_once __DIR__ . '/SimpleXLSX.php';
if (file_exists(__DIR__ . '/SimpleXLS.php')) {
    require_once __DIR__ . '/SimpleXLS.php';
}

function readDataFromFile($filePath) {
    if (!file_exists($filePath)) {
        throw new Exception("File không tồn tại: $filePath");
    }

    $ext = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
    $fileHeader = file_get_contents($filePath, false, null, 0, 1024);

    // 1. Kiểm tra nếu là file ZIP / XLSX thực tế (bất kể đuôi .xlsx hay .xls)
    if (strncmp($fileHeader, "PK\x03\x04", 4) === 0 || $ext === 'xlsx') {
        if ($xlsx = SimpleXLSX::parse($filePath)) {
            $rows = $xlsx->rows();
            if (!empty($rows)) return $rows;
        }
    }

    // 2. Kiểm tra nếu là định dạng HTML Table (Các phần mềm POS thường xuất HTML với đuôi .xls)
    if (stripos($fileHeader, '<table') !== false || stripos($fileHeader, '<html') !== false || stripos($fileHeader, '<tr') !== false) {
        $rows = parseHtmlTableFile($filePath);
        if (!empty($rows)) return $rows;
    }

    // 3. Kiểm tra nếu là XML Spreadsheet 2003 (SpreadsheetML)
    if (stripos($fileHeader, '<?xml') !== false && (stripos($fileHeader, 'Workbook') !== false || stripos($fileHeader, 'Worksheet') !== false)) {
        $rows = parseXmlSpreadsheetFile($filePath);
        if (!empty($rows)) return $rows;
    }

    // 4. Kiểm tra file nhị phân BIFF8 / OLE (.xls Excel 97-2004)
    if ($ext === 'xls' || strncmp($fileHeader, "\xD0\xCF\x11\xE0\xA1\xB1\x1A\xE1", 8) === 0) {
        if (class_exists('Shuchkin\SimpleXLS')) {
            if ($xls = \Shuchkin\SimpleXLS::parse($filePath)) {
                $rows = $xls->rows();
                if (!empty($rows)) return $rows;
            }
        }
    }

    // 5. Nếu đuôi là CSV hoặc văn bản phân tách
    if ($ext === 'csv' || strpos($fileHeader, ',') !== false || strpos($fileHeader, ';') !== false || strpos($fileHeader, "\t") !== false) {
        $rows = parseCsvFile($filePath);
        if (!empty($rows)) return $rows;
    }

    // Nếu các cách trên chưa đọc được và đuôi là .xls, thử lần lượt các bộ giải mã
    if ($ext === 'xls') {
        // Thử lại dạng HTML
        $rows = parseHtmlTableFile($filePath);
        if (!empty($rows)) return $rows;

        // Thử lại dạng CSV
        $rows = parseCsvFile($filePath);
        if (!empty($rows)) return $rows;
    }

    throw new Exception("Không thể đọc cấu trúc file Excel (.xls/.xlsx). Vui lòng kiểm tra lại tính toàn vẹn của file!");
}

/**
 * Đọc file CSV
 */
function parseCsvFile($filePath) {
    $rows = [];
    if (($handle = fopen($filePath, "r")) !== FALSE) {
        $bom = fread($handle, 3);
        if ($bom !== "\xEF\xBB\xBF") {
            rewind($handle);
        }
        while (($data = fgetcsv($handle, 10000, ",")) !== FALSE) {
            if (count($data) === 1 && strpos($data[0], ';') !== false) {
                $data = explode(';', $data[0]);
            } elseif (count($data) === 1 && strpos($data[0], "\t") !== false) {
                $data = explode("\t", $data[0]);
            }
            $rows[] = array_map('trim', $data);
        }
        fclose($handle);
    }
    return $rows;
}

/**
 * Đọc bảng HTML được lưu với phần mở rộng .xls
 */
function parseHtmlTableFile($filePath) {
    $content = file_get_contents($filePath);
    if (empty($content)) return [];

    $rows = [];
    $dom = new DOMDocument();
    libxml_use_internal_errors(true);

    // Chuyển đổi mã hóa về UTF-8 nếu file chứa ANSI/Windows-1258/CP1252
    if (!mb_check_encoding($content, 'UTF-8')) {
        $converted = mb_convert_encoding($content, 'UTF-8', ['Windows-1258', 'Windows-1252', 'ISO-8859-1', 'ASCII']);
        if ($converted) $content = $converted;
    }

    if (stripos($content, 'charset') === false) {
        $content = '<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">' . $content;
    }

    $dom->loadHTML($content);
    libxml_clear_errors();

    $tables = $dom->getElementsByTagName('table');
    if ($tables->length > 0) {
        $table = $tables->item(0);
        $trList = $table->getElementsByTagName('tr');
        foreach ($trList as $tr) {
            $row = [];
            foreach ($tr->childNodes as $node) {
                if ($node->nodeName === 'td' || $node->nodeName === 'th') {
                    $row[] = trim(html_entity_decode($node->textContent, ENT_QUOTES, 'UTF-8'));
                }
            }
            if (!empty($row)) {
                $rows[] = $row;
            }
        }
    }
    return $rows;
}

/**
 * Đọc XML Spreadsheet 2003
 */
function parseXmlSpreadsheetFile($filePath) {
    $content = file_get_contents($filePath);
    if (empty($content)) return [];

    $cleanXml = preg_replace('/xmlns[^=]*="[^"]*"/i', '', $content);
    $cleanXml = preg_replace('/[a-zA-Z0-9]+:([a-zA-Z0-9]+)/', '$1', $cleanXml);

    $xml = @simplexml_load_string($cleanXml);
    $rows = [];

    if ($xml && isset($xml->Worksheet)) {
        foreach ($xml->Worksheet as $ws) {
            if (isset($ws->Table->Row)) {
                foreach ($ws->Table->Row as $row) {
                    $r = [];
                    foreach ($row->Cell as $cell) {
                        $r[] = isset($cell->Data) ? trim((string)$cell->Data) : '';
                    }
                    if (!empty($r)) {
                        $rows[] = $r;
                    }
                }
                break;
            }
        }
    }
    return $rows;
}
