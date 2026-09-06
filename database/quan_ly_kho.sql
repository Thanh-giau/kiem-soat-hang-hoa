-- ============================================================
-- DATABASE: quan_ly_kho
-- HỆ THỐNG QUẢN LÝ KHO VÀ KIỂM SOÁT HÀNG HÓA TỰ ĐỘNG
-- ============================================================

CREATE DATABASE IF NOT EXISTS `quan_ly_kho` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `quan_ly_kho`;

-- 1. Bảng người dùng
CREATE TABLE IF NOT EXISTS `users` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `username` VARCHAR(50) NOT NULL UNIQUE,
    `password` VARCHAR(255) NOT NULL,
    `ho_ten` VARCHAR(100) NOT NULL,
    `email` VARCHAR(100) NULL,
    `vai_tro` ENUM('admin', 'nhan_vien') NOT NULL DEFAULT 'admin',
    `trang_thai` TINYINT(1) NOT NULL DEFAULT 1 COMMENT '1: Hoạt động, 0: Khóa',
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NULL ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Bảng sản phẩm
CREATE TABLE IF NOT EXISTS `san_pham` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `ma_sp` VARCHAR(50) NOT NULL UNIQUE,
    `ten_sp` VARCHAR(255) NOT NULL,
    `nhom_sp` VARCHAR(100) NOT NULL COMMENT 'Bánh, Đồ ăn, Nước suối, Đồ uống pha chế...',
    `don_vi_tinh` VARCHAR(50) NOT NULL DEFAULT 'Cái',
    `can_kiem_ke` TINYINT(1) NOT NULL DEFAULT 1 COMMENT '1: Cần kiểm kê, 0: Bỏ qua khi kiểm kê',
    `trang_thai` TINYINT(1) NOT NULL DEFAULT 1 COMMENT '1: Hoạt động, 0: Đã xóa mềm',
    `ghi_chu` TEXT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NULL ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_ma_sp` (`ma_sp`),
    INDEX `idx_can_kiem_ke` (`can_kiem_ke`),
    INDEX `idx_trang_thai` (`trang_thai`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Bảng tồn đầu theo kỳ (ví dụ: '2026-09')
CREATE TABLE IF NOT EXISTS `ton_dau` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `san_pham_id` INT NOT NULL,
    `ky_kiem_ke` VARCHAR(7) NOT NULL COMMENT 'Định dạng YYYY-MM ví dụ 2026-09',
    `so_luong` DECIMAL(12, 2) NOT NULL DEFAULT 0,
    `ghi_chu` VARCHAR(255) NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NULL ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY `uk_sp_ky` (`san_pham_id`, `ky_kiem_ke`),
    FOREIGN KEY (`san_pham_id`) REFERENCES `san_pham`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. Bảng lưu thông tin file đã upload
CREATE TABLE IF NOT EXISTS `upload_files` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `ten_file_goc` VARCHAR(255) NOT NULL,
    `ten_file_luu` VARCHAR(255) NOT NULL,
    `ngay_ban` DATE NOT NULL,
    `file_hash` VARCHAR(64) NOT NULL COMMENT 'MD5/SHA256 để chống upload trùng',
    `tong_dong` INT NOT NULL DEFAULT 0,
    `dong_kiem_ke` INT NOT NULL DEFAULT 0,
    `dong_bo_qua` INT NOT NULL DEFAULT 0,
    `nguoi_upload_id` INT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_ngay_ban` (`ngay_ban`),
    INDEX `idx_file_hash` (`file_hash`),
    FOREIGN KEY (`nguoi_upload_id`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5. Bảng số bán tự động từ Excel
CREATE TABLE IF NOT EXISTS `so_ban` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `upload_file_id` INT NULL,
    `san_pham_id` INT NOT NULL,
    `ngay_ban` DATE NOT NULL,
    `so_luong` DECIMAL(12, 2) NOT NULL DEFAULT 0,
    `ghi_chu` VARCHAR(255) NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NULL ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_ngay_ban` (`ngay_ban`),
    INDEX `idx_san_pham` (`san_pham_id`),
    UNIQUE KEY `uk_sp_ngay_ban` (`san_pham_id`, `ngay_ban`),
    FOREIGN KEY (`upload_file_id`) REFERENCES `upload_files`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`san_pham_id`) REFERENCES `san_pham`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 6. Bảng nhập hàng thủ công
CREATE TABLE IF NOT EXISTS `nhap_hang` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `san_pham_id` INT NOT NULL,
    `ngay_nhap` DATE NOT NULL,
    `so_luong` DECIMAL(12, 2) NOT NULL DEFAULT 0,
    `nha_cung_cap` VARCHAR(255) NULL,
    `ghi_chu` VARCHAR(255) NULL,
    `nguoi_nhap_id` INT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NULL ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_ngay_nhap` (`ngay_nhap`),
    INDEX `idx_san_pham` (`san_pham_id`),
    FOREIGN KEY (`san_pham_id`) REFERENCES `san_pham`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`nguoi_nhap_id`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 7. Bảng điều chỉnh kho: Hủy, Cho mượn, Mượn
CREATE TABLE IF NOT EXISTS `dieu_chinh_kho` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `san_pham_id` INT NOT NULL,
    `ngay_dieu_chinh` DATE NOT NULL,
    `loai` ENUM('huy', 'cho_muon', 'muon') NOT NULL COMMENT 'huy: giảm kho, cho_muon: giảm kho, muon: tăng kho',
    `so_luong` DECIMAL(12, 2) NOT NULL DEFAULT 0,
    `nguoi_lien_quan` VARCHAR(150) NULL COMMENT 'Đối tác/nhân viên mượn hoặc người duyệt hủy',
    `ghi_chu` VARCHAR(255) NULL,
    `nguoi_tao_id` INT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NULL ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_ngay_dieu_chinh` (`ngay_dieu_chinh`),
    INDEX `idx_loai` (`loai`),
    INDEX `idx_san_pham` (`san_pham_id`),
    FOREIGN KEY (`san_pham_id`) REFERENCES `san_pham`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`nguoi_tao_id`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 8. Bảng kiểm kê thực tế & chênh lệch
CREATE TABLE IF NOT EXISTS `kiem_ke` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `san_pham_id` INT NOT NULL,
    `ngay_kiem_ke` DATE NOT NULL,
    `ton_ly_thuyet` DECIMAL(12, 2) NOT NULL DEFAULT 0,
    `ton_thuc_te` DECIMAL(12, 2) NOT NULL DEFAULT 0,
    `chenh_lech` DECIMAL(12, 2) NOT NULL DEFAULT 0 COMMENT 'ton_thuc_te - ton_ly_thuyet',
    `ghi_chu` VARCHAR(255) NULL,
    `nguoi_kiem_ke_id` INT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NULL ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_ngay_kiem_ke` (`ngay_kiem_ke`),
    INDEX `idx_san_pham` (`san_pham_id`),
    UNIQUE KEY `uk_sp_ngay_kiem_ke` (`san_pham_id`, `ngay_kiem_ke`),
    FOREIGN KEY (`san_pham_id`) REFERENCES `san_pham`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`nguoi_kiem_ke_id`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 9. Bảng định lượng công thức món (BOM / Recipe)
CREATE TABLE IF NOT EXISTS `dinh_luong_mon` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `ma_mon_pos` VARCHAR(100) NOT NULL COMMENT 'Mã món bán trên POS / File Excel',
    `ten_mon_pos` VARCHAR(255) NOT NULL COMMENT 'Tên món trên POS (vd: Matcha Mochi, Sandwich)',
    `san_pham_id` INT NOT NULL COMMENT 'ID của nguyên vật liệu/sản phẩm kiểm kê trong kho',
    `so_luong_tieu_hao` DECIMAL(12, 3) NOT NULL DEFAULT 1.000 COMMENT 'Số lượng NVL dùng cho 1 phần bán',
    `ghi_chu` VARCHAR(255) NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NULL ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_ma_pos` (`ma_mon_pos`),
    INDEX `idx_sp_id` (`san_pham_id`),
    FOREIGN KEY (`san_pham_id`) REFERENCES `san_pham`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
