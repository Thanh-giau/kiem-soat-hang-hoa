<?php
/**
 * SimpleXLSX parser - Lightweight XLSX Reader
 * Based on Shuchkin SimpleXLSX (Zero dependencies, PHP ZipArchive + SimpleXML)
 */

class SimpleXLSX {
    public static $error = '';
    private $sheets = [];
    private $sharedstrings = [];
    private $sheetNames = [];
    private $workbookXML = null;

    public static function parse($filename, $is_data = false) {
        $xlsx = new self();
        if ($xlsx->load($filename, $is_data)) {
            return $xlsx;
        }
        return false;
    }

    public static function parseError() {
        return self::$error;
    }

    public function load($filename, $is_data = false) {
        $zip = new ZipArchive();
        $status = $zip->open($filename);
        if ($status !== true) {
            self::$error = 'Không thể mở file XLSX (Mã lỗi zip: ' . $status . ')';
            return false;
        }

        // Đọc sharedStrings.xml nếu có
        $xmlSharedStrings = $zip->getFromName('xl/sharedStrings.xml');
        if ($xmlSharedStrings) {
            $xml = simplexml_load_string($xmlSharedStrings);
            if ($xml && isset($xml->si)) {
                foreach ($xml->si as $si) {
                    if (isset($si->t)) {
                        $this->sharedstrings[] = (string)$si->t;
                    } elseif (isset($si->r)) {
                        $str = '';
                        foreach ($si->r as $r) {
                            $str .= (string)$r->t;
                        }
                        $this->sharedstrings[] = $str;
                    } else {
                        $this->sharedstrings[] = '';
                    }
                }
            }
        }

        // Đọc sheet1.xml
        $sheetXML = $zip->getFromName('xl/worksheets/sheet1.xml');
        if (!$sheetXML) {
            // Thử sheet đầu tiên trong workbook
            $sheetXML = $zip->getFromName('xl/worksheets/sheet0.xml');
        }
        $zip->close();

        if (!$sheetXML) {
            self::$error = 'Không tìm thấy worksheet hợp lệ trong file Excel';
            return false;
        }

        $xml = simplexml_load_string($sheetXML);
        if (!$xml || !isset($xml->sheetData)) {
            self::$error = 'Dữ liệu trang tính rỗng';
            return false;
        }

        $rows = [];
        foreach ($xml->sheetData->row as $row) {
            $r = [];
            $curCol = 0;
            foreach ($row->c as $c) {
                // Xác định vị trí cột theo ký tự (A, B, C...)
                $rRef = (string)$c['r'];
                preg_match('/([A-Z]+)(\d+)/', $rRef, $matches);
                if (!empty($matches[1])) {
                    $colIndex = $this->colToNumber($matches[1]);
                    while ($curCol < $colIndex) {
                        $r[$curCol] = '';
                        $curCol++;
                    }
                }

                $val = '';
                $type = (string)$c['t'];
                if ($type === 's') {
                    // Shared string
                    $sIndex = (int)$c->v;
                    $val = $this->sharedstrings[$sIndex] ?? '';
                } elseif ($type === 'inlineStr' && isset($c->is->t)) {
                    $val = (string)$c->is->t;
                } else {
                    $val = (string)$c->v;
                }
                $r[$curCol] = trim($val);
                $curCol++;
            }
            $rows[] = $r;
        }

        $this->sheets[0] = $rows;
        return true;
    }

    private function colToNumber($col) {
        $len = strlen($col);
        $num = 0;
        for ($i = 0; $i < $len; $i++) {
            $num = $num * 26 + (ord($col[$i]) - 64);
        }
        return $num - 1;
    }

    public function rows($sheetIndex = 0) {
        return $this->sheets[$sheetIndex] ?? [];
    }
}
