USE `quan_ly_kho`;

-- Tài khoản quản trị mặc định: admin / admin123
INSERT INTO `users` (`id`, `username`, `password`, `ho_ten`, `email`, `vai_tro`, `trang_thai`) VALUES
(1, 'admin', '$2y$10$XaHmGLIfEkkfSLzZbt7T0u/AYn0nUI7i/AI3Xo3ETiO/bEVe1ztYW', 'Quản Trị Viên', 'admin@quanlykho.vn', 'admin', 1)
ON DUPLICATE KEY UPDATE `ho_ten` = VALUES(`ho_ten`);

-- Danh mục sản phẩm mẫu theo đúng kịch bản tài liệu
INSERT INTO `san_pham` (`id`, `ma_sp`, `ten_sp`, `nhom_sp`, `don_vi_tinh`, `can_kiem_ke`, `trang_thai`, `ghi_chu`) VALUES
(1, 'CAKE01', 'Bánh Croissant Bơ Pháp', 'Bánh', 'Cái', 1, 1, 'Hàng cần kiểm kê hàng ngày'),
(2, 'FOOD01', 'Bánh Mì Sandwich Thịt Nguội', 'Đồ ăn', 'Cái', 1, 1, 'Hàng cần kiểm kê hàng ngày'),
(3, 'WATER01', 'Nước Suối Aquafina 500ml', 'Nước suối', 'Chai', 1, 1, 'Hàng cần kiểm kê hàng ngày'),
(4, 'CF001', 'Cà Phê Sữa Pha Phin', 'Đồ uống pha chế', 'Ly', 0, 1, 'Món nước pha chế - tự động bỏ qua kiểm kê'),
(5, 'TEA01', 'Trà Đào Cam Sả', 'Đồ uống pha chế', 'Ly', 0, 1, 'Món nước pha chế - tự động bỏ qua kiểm kê')
ON DUPLICATE KEY UPDATE `ten_sp` = VALUES(`ten_sp`), `can_kiem_ke` = VALUES(`can_kiem_ke`);

-- Tồn đầu kỳ tháng 09/2026 cho các sản phẩm kiểm kê
INSERT INTO `ton_dau` (`san_pham_id`, `ky_kiem_ke`, `so_luong`, `ghi_chu`) VALUES
(1, '2026-09', 100.00, 'Tồn đầu kỳ tháng 9'),
(2, '2026-09', 50.00, 'Tồn đầu kỳ tháng 9'),
(3, '2026-09', 80.00, 'Tồn đầu kỳ tháng 9')
ON DUPLICATE KEY UPDATE `so_luong` = VALUES(`so_luong`);
