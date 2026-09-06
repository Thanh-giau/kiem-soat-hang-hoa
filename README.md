# 🏪 HỆ THỐNG QUẢN LÝ KHO & KIỂM SOÁT HÀNG HÓA TỰ ĐỘNG
> **KHO HÀNG CỦA THANH GIÀU**  
> *Giải pháp chuyên sâu quản lý tồn kho F&B: Tự động bóc tách số bán từ POS (iPOS, KiotViet...), định lượng BOM nguyên vật liệu, kiểm kê thực tế di động và đối soát chênh lệch hàng hóa chống thất thoát.*

---

## 📌 MỤC LỤC
1. [Giới Thiệu Tổng Quan](#-1-giới-thiệu-tổng-quan)
2. [Điểm Nổi Bật & Tính Năng Đột Phá](#-2-điểm-nổi-bật--tính-năng-đột-phá)
3. [Kiến Trúc & Công Nghệ Sử Dụng](#-3-kiến-trúc--công-nghệ-sử-dụng)
4. [Cấu Trúc Thư Mục Hệ Thống](#-4-cấu-trúc-thư-mục-hệ-thống)
5. [Chi Tiết Toàn Bộ Phân Hệ Chức Năng](#-5-chi-tiết-toàn-bộ-phân-hệ-chức-năng)
6. [Công Thức Tính Toán Kho Cốt Lõi](#-6-công-thức-tính-toán-kho-cốt-lõi)
7. [Hướng Dẫn Cài Đặt & Chạy Làm Máy Chủ (Server)](#-7-hướng-dẫn-cài-đặt--chạy-làm-máy-chủ-server)
8. [Quy Trình Vận Hành Chuẩn Hàng Ngày](#-8-quy-trình-vận-hành-chuẩn-hàng-ngày)
9. [Tài Khoản Đăng Nhập Mặc Định](#-9-tài-khoản-đăng-nhập-mặc-định)

---

## 🌟 1. GIỚI THIỆU TỔNG QUAN

Trong ngành F&B (Nhà hàng, Quán Cà phê, Trà sữa, Tiệm bánh), việc kiểm soát tồn kho luôn gặp phải các khó khăn đặc thù:
- Máy POS bán món nước (vd: *Matcha Latte Tây Bắc Mochi*), nhưng kho cần kiểm kê lại là **Viên Bánh Mochi**.
- Các món đồ ăn (vd: *Sandwich Gà Phô Mai*) tiêu hao đồng thời nhiều nguyên vật liệu (1 Vỏ bánh + 2 Lát phô mai + 1 Gói xốt).
- File Excel xuất ra từ máy bán hàng POS (iPOS, KiotViet...) có hàng trăm món pha chế, làm nhân viên mất hàng giờ lọc tay để tìm 20 món Bánh/Đồ ăn cần đếm kho.
- Thất thoát không rõ nguyên nhân giữa tồn lý thuyết trên máy và tồn thực tế đếm được trong tủ/kệ.

**Hệ thống này được xây dựng để giải quyết triệt để 100% các vấn đề trên**, giúp chủ cửa hàng và quản lý kho nắm rõ từng chiếc bánh, từng lon nước ngọt đến từng gam nguyên liệu một cách tự động và minh bạch.

---

## 🚀 2. ĐIỂM NỔI BẬT & TÍNH NĂNG ĐỘT PHÁ

* 📂 **Đọc Mọi Định Dạng File Excel POS**: Xử lý mượt mà `.xlsx`, `.xls` (BIFF8 nhị phân, HTML Table xuất từ iPOS/CukCuk, XML Spreadsheet 2003) và `.csv`. Tự động nhận diện cấu trúc file iPOS, bỏ qua subtotal header, kế thừa nhóm món.
* 🧠 **Bộ Lọc Thông Minh (Smart Inventory Filter)**: Tự động phân tách danh mục: Giữ lại các món Bánh/Đồ ăn cần kiểm kê (`can_kiem_ke = 1`) và bỏ qua các món Nước pha chế (`can_kiem_ke = 0`). Có còi báo động khi xuất hiện mã hàng mới ngoài menu.
* 🧮 **Định Lượng Món Bán (BOM - Bill of Materials)**:
  - Tự động bóc tách từ 1 món bán trên POS thành các nguyên vật liệu kiểm kê.
  - **Nhận diện đa kích thước thông minh**: Tự động nhận diện các size `(Vừa)`, `(Lớn)`, `(M)`, `(L)`, tiền tố `PLT`, ShopeeFood...
  - **Đồng bộ hồi tố (Retroactive Sync)**: Bất kỳ lúc nào thêm/sửa công thức BOM, hệ thống tự động quét lại toàn bộ file số bán quá khứ và cập nhật lại tồn lý thuyết mà không cần upload lại file!
* 📱 **Tối Ưu Hoàn Hảo Cho Điện Thoại & Tablet (Mobile-First)**:
  - Nhân viên cầm điện thoại đi quanh quầy/tủ lạnh để đếm kho với phím bấm cảm ứng to rõ.
  - Sidebar dạng ngăn kéo trượt (Off-canvas) có nền mờ mượt mà trên iPad/Tablet.
* 🔍 **Tìm Kiếm Sản Phẩm Siêu Tốc (Smart Searchable Combobox)**:
  - Gõ tên tiếng Việt không dấu (vd: `banh mi`, `mochi`, `pho mai`) hoặc gõ mã sản phẩm là ra ngay.
  - Menu thả nổi (Floating Portal) không bị che khuất bởi bảng, tự động nhảy con trỏ sang ô Số lượng.
* 📦 **Thao Tác Nhiều Mã Hàng Cùng Lúc (Batch Processing)**:
  - Cho phép lập 1 phiếu nhập hàng hoặc 1 phiếu điều chỉnh (Hủy / Cho mượn / Mượn về) chứa hàng chục sản phẩm cùng lúc.
  - Nút **"⚡ Nạp Hàng Kiểm Kê"** giúp đưa toàn bộ sản phẩm vào form chỉ với 1 click.
* 🗑️ **Xóa File Upload Thu Hồi Số Liệu An Toàn**: Khi xóa nhầm file số bán, toàn bộ số bán đã lưu sẽ được rút lại sạch sẽ, tồn lý thuyết và chênh lệch kiểm kê tự động phục hồi như ban đầu.
* ⚡ **Bộ Khởi Động 1-Click Không Cần Cài Đặt Lập Trình**: Có sẵn các file `.bat` để chạy máy chủ nội bộ hoặc đưa lên mạng Online toàn cầu trong 3 giây.

---

## 🛠️ 3. KIẾN TRÚC & CÔNG NGHỆ SỬ DỤNG

```
┌─────────────────────────────────────────────────────────────┐
│                 GIAO DIỆN NGƯỜI DÙNG (UI)                    │
│    HTML5 Semantics • CSS3 Glassmorphism • Responsive        │
│    Vanilla JS Searchable Combobox • Font Awesome 6.5        │
└──────────────────────────────┬──────────────────────────────┘
                               │ HTTP / AJAX
┌──────────────────────────────▼──────────────────────────────┐
│                  TẦNG XỬ LÝ ỨNG DỤNG (PHP 8.2)               │
│  - Bóc tách Excel: SimpleXLSX / SimpleXLS / Regex Stream     │
│  - Bộ lọc số bán & Động cơ BOM: filter_inventory_products   │
│  - Động cơ Đồng bộ hồi tố: sync_bom_sales.php                │
│  - Lõi tính toán tồn kho 11 cột: layBaoCaoTonKhoChiTiet()    │
└──────────────────────────────┬──────────────────────────────┘
                               │ PDO Prepared Statements
┌──────────────────────────────▼──────────────────────────────┐
│              CƠ SỞ DỮ LIỆU (MariaDB / MySQL 8.0)            │
│  CSDL: quan_ly_kho (utf8mb4_unicode_ci)                      │
│  Bảng: san_pham, ton_dau, so_ban, nhap_hang, dieu_chinh_kho,│
│        kiem_ke, dinh_luong, dinh_luong_chi_tiet, upload_files│
└─────────────────────────────────────────────────────────────┘
```

---

## 📁 4. CẤU TRÚC THƯ MỤC HỆ THỐNG

```text
c:\kiem-soat-hang-hoa\
├── assets/
│   ├── css/
│   │   ├── style.css             # Giao diện chính, tông màu Indigo hiện đại
│   │   ├── responsive.css        # Quy tắc co giãn Mobile (320px) & Tablet (768px-1024px)
│   │   └── searchable-select.css # Giao diện Dropdown tìm kiếm sản phẩm nổi
│   └── js/
│       ├── main.js               # Logic điều khiển, modal, responsive drawer
│       └── searchable-select.js  # Thư viện gõ tên tìm kiếm sản phẩm không dấu
├── baocao/
│   ├── bao_cao_ngay.php          # Báo cáo tổng hợp số liệu theo ngày cụ thể
│   ├── bao_cao_thang.php         # Báo cáo luân chuyển kho theo tháng
│   ├── chenh_lech.php            # BẢNG ĐỐI SOÁT CHÊNH LỆCH 11 CỘT (Cốt lõi)
│   └── xuat_excel.php            # Xuất báo cáo ra định dạng Excel
├── bin/
│   └── cloudflared.exe           # Công cụ mở link máy chủ trực tuyến từ xa
├── config/
│   └── database.php              # Kết nối PDO bảo mật tới CSDL MySQL
├── database/
│   ├── quan_ly_kho.sql           # File cấu trúc CSDL khởi tạo ban đầu
│   └── quan_ly_kho_backup.sql    # Bản sao lưu toàn bộ dữ liệu thực tế mới nhất
├── dieuchinh/
│   ├── danh_sach.php             # Lịch sử các phiếu Hủy, Cho Mượn, Mượn Về
│   ├── them.php                  # Form lập phiếu điều chỉnh nhiều mã hàng cùng lúc
│   └── xoa.php                   # Hủy phiếu điều chỉnh kho
├── dinhluong/
│   ├── danh_sach.php             # Bảng danh mục công thức món bán (BOM)
│   ├── them.php                  # Thêm mới công thức định lượng (nhiều NVL)
│   ├── sua.php                   # Chỉnh sửa công thức định lượng
│   ├── xoa.php                   # Xóa công thức định lượng
│   └── dong_bo.php               # Kích hoạt đồng bộ hồi tố số bán thủ công
├── import/
│   ├── filter_inventory_products.php # Thuật toán lõi lọc món & bóc tách BOM
│   ├── sync_bom_sales.php        # Động cơ tự động đồng bộ lại toàn bộ số bán
│   ├── SimpleXLSX.php            # Trình đọc file Excel định dạng .xlsx
│   └── SimpleXLS.php             # Trình đọc file Excel định dạng .xls
├── includes/
│   ├── functions.php             # Thư viện hàm tồn kho, chênh lệch, định dạng
│   ├── header.php                # Khung đầu trang, thanh Topbar, thông báo
│   ├── footer.php                # Khung chân trang, nhúng JS
│   └── sidebar.php               # Menu điều hướng đa cấp, thanh cuộn siêu mảnh
├── kiemke/
│   ├── kiem_ke.php               # GIAO DIỆN KIỂM KÊ THỰC TẾ TRÊN ĐIỆN THOẠI
│   ├── lich_su.php               # Lịch sử các đợt kiểm kho
│   └── luu.php                   # Bộ xử lý lưu số liệu kiểm kê
├── nhaphang/
│   ├── danh_sach.php             # Lịch sử các phiếu nhập hàng từ nhà cung cấp
│   ├── them.php                  # Form tạo phiếu nhập nhiều mặt hàng cùng lúc
│   └── xoa.php                   # Hủy phiếu nhập kho
├── sanpham/
│   ├── danh_sach.php             # Quản lý danh mục hàng hóa (Bánh, Nước, NVL)
│   ├── them.php                  # Thêm sản phẩm mới vào danh mục
│   ├── sua.php                   # Sửa thông tin sản phẩm (ĐVT, cờ kiểm kê)
│   └── xoa.php                   # Xóa mềm sản phẩm
├── soban/
│   ├── upload.php                # Kéo thả file Excel số bán mỗi ngày từ máy POS
│   ├── xu_ly.php                 # Phân tích file, cảnh báo mã mới, lọc kiểm kê
│   ├── lich_su.php               # Quản lý danh sách file Excel đã tải lên
│   ├── chi_tiet.php              # Xem chi tiết từng mặt hàng được lưu từ file
│   └── xoa_file.php              # XÓA FILE UPLOAD & THU HỒI SỐ LIỆU TỒN KHO
├── ton_dau/
│   ├── nhap_ton.php              # Bảng nhập số lượng tồn kho đầu kỳ
│   └── luu.php                   # Lưu tồn đầu kỳ vào CSDL
├── CHAY_MAY_CHU_LAN.bat          # 1-Click chạy máy chủ mạng Wi-Fi nội bộ
├── CHAY_MAY_CHU_ONLINE.bat       # 1-Click chạy máy chủ trực tuyến 4G toàn cầu
├── DUNG_MAY_CHU.bat              # 1-Click dừng toàn bộ máy chủ an toàn
├── SAO_LUU_DU_LIEU.bat           # 1-Click sao lưu toàn bộ dữ liệu ra file SQL
├── KHOI_PHUC_DU_LIEU.bat         # 1-Click phục hồi dữ liệu sang máy mới
├── dashboard.php                 # BẢNG ĐIỀU KHIỂN TỔNG QUAN (7 KPI, Cảnh báo)
├── login.php                     # Trang đăng nhập bảo mật
├── logout.php                    # Đăng xuất an toàn
└── index.php                     # Điểm điều hướng trung tâm
```

---

## 📊 5. CHI TIẾT TOÀN BỘ PHÂN HỆ CHỨC NĂNG

### 5.1. Bảng Điều Khiển Tổng Quan (`dashboard.php`)
* **7 Thẻ KPI Realtime**:
  - Tổng số mặt hàng đang theo dõi kiểm kê.
  - Tổng lượng tồn đầu kỳ.
  - Tổng lượng nhập thêm trong kỳ.
  - Tổng số đã bán ra (từ POS & bóc tách BOM).
  - Tổng số hao hụt/hủy.
  - Tổng tồn kho lý thuyết hiện tại.
  - Tỷ lệ khớp kho (%).
* **Bảng Cảnh Báo Thông Minh**:
  - 🔴 **Top Sản phẩm thiếu nhiều nhất**: Cảnh báo ngay món nghi ngờ thất thoát hoặc nhân viên quên quét mã.
  - 🟠 **Top Sản phẩm dư nhiều nhất**: Phát hiện món nghi ngờ sót hàng nhập hoặc giao nhầm.
  - **Danh sách chênh lệch hôm nay**: Hiển thị nhanh các mặt hàng lệch kho trong ngày.

### 5.2. Phân Hệ Upload & Xử Lý Số Bán Excel (`soban/`)
* Đọc định dạng Excel của hầu hết các phần mềm quản lý bán hàng: **iPOS (BesReportViewer)**, **KiotViet**, **CukCuk**, **Sapo**.
* Bỏ qua dòng trống, dòng header subtotal, tự động gom nhóm.
* **Cơ chế phân loại cờ `can_kiem_ke`**:
  - `can_kiem_ke = 1`: Tự động cộng vào số bán để trừ kho (Bánh mì, Bánh ngọt, Nước đóng lon...).
  - `can_kiem_ke = 0`: Món nước pha chế được đưa vào danh sách kiểm tra BOM.
* **Phát hiện mã mới**: Nếu file Excel có món chưa từng tồn tại, màn hình sẽ hiển thị bảng cảnh báo cho phép bạn chọn đưa vào kiểm kê hoặc bỏ qua chỉ với 1 click.
* **Quản lý & Xóa file (`lich_su.php`, `xoa_file.php`)**: Mỗi file đã tải có nút xem chi tiết và nút Xóa. Khi xóa, toàn bộ số liệu bán được thu hồi sạch sẽ, kho tự động tính lại số liệu chuẩn.

### 5.3. Phân Hệ Định Lượng Món Bán & Bóc Tách (BOM - `dinhluong/`)
* **Cài đặt 1 món bán ra tiêu hao nhiều NVL**: Ví dụ: 1 phần *Matcha Latte Tây Bắc Mochi* dùng *1 Viên Mochi Kem Matcha*; 1 phần *Sandwich* dùng *1 Vỏ bánh + 2 Lát phô mai*.
* **Smart Multi-Level Matching**:
  - Tự động bỏ qua các hậu tố: `(Vừa)`, `(Lớn)`, `(Nhỏ)`, `(M)`, `(L)`, `(Size L)`...
  - Tự động bỏ qua các tiền tố: `PLT`, `Shopee`, `Grab`, `Baemin`...
  - So khớp từ khóa tiếng Việt không dấu.
* **Tự Động Đồng Bộ Hồi Tố (Retroactive Sync Engine)**:
  - Khi bạn cập nhật bất kỳ công thức nào, hệ thống tự động quét lại toàn bộ file số bán quá khứ.
  - Tự động cập nhật lại bảng số bán và cập nhật chênh lệch kiểm kê trong nháy mắt.

### 5.4. Phân Hệ Nhập Hàng Vào Kho (`nhaphang/`)
* **Lập phiếu nhập nhiều mã cùng lúc**: Không cần tạo từng phiếu lẻ tẻ, 1 phiếu nhập duy nhất có thể chứa 20+ món khác nhau.
* **Nút "⚡ Nạp Hàng Kiểm Kê"**: 1 click nạp toàn bộ danh mục hàng cần kiểm kê vào phiếu, nhân viên chỉ việc điền số lượng nhận từ xưởng/NCC.
* **Tìm kiếm sản phẩm nhanh**: Gõ tên món không dấu là ra ngay, hỗ trợ phím mũi tên và phím `Enter`.

### 5.5. Phân Hệ Điều Chỉnh Kho: Hủy / Cho Mượn / Mượn Về (`dieuchinh/`)
* Giao diện đổi màu sắc trực quan theo nghiệp vụ:
  - ❌ **Hủy hàng (Đỏ)**: Ghi nhận bánh hỏng, hết date, làm **GIẢM** tồn lý thuyết.
  - 🤝 **Cho mượn (Vàng hổ phách)**: Xuất cho cơ sở khác mượn, làm **GIẢM** tồn lý thuyết.
  - 🤲 **Mượn về (Xanh ngọc)**: Nhận mượn từ cơ sở khác, làm **TĂNG** tồn lý thuyết.
* Hỗ trợ thêm nhiều mã hàng trong 1 phiếu, tự động tính tổng số lượng điều chỉnh.

### 5.6. Phân Hệ Kiểm Kê Thực Tế & Đối Soát Chênh Lệch (`kiemke/`, `baocao/`)
* **Kiểm Kê Di Động (`kiemke/kiem_ke.php`)**:
  - Giao diện tối ưu ngón tay chạm trên điện thoại.
  - Có nút `+` và `-` to rõ để đếm nhanh từng đơn vị.
  - Hiển thị ngay chênh lệch đổi màu khi vừa gõ số: 🟢 Khớp (0) | 🔴 Thiếu (Âm) | 🟠 Dư (Dương).
* **Báo Cáo Chênh Lệch Đầy Đủ 11 Cột (`baocao/chenh_lech.php`)**:
  - `Mã SP`, `Tên Sản Phẩm`, `ĐVT`, `Tồn Đầu Kỳ`, `Nhập Thêm`, `Mượn Về`, `Số Đã Bán`, `Hủy Bỏ`, `Cho Mượn`, `TỒN LÝ THUYẾT`, `THỰC TẾ KIỂM ĐẾM`, `CHÊNH LỆCH`.
  - Hỗ trợ xem theo ngày, lọc theo tháng, xuất ra file Excel để gửi ban giám đốc.

---

## 📐 6. CÔNG THỨC TÍNH TOÁN KHO CỐT LÕI

Hệ thống tuân thủ nghiêm ngặt các nguyên lý kế toán kho F&B:

### 1. Tồn Kho Lý Thuyết:
$$\text{Tồn Lý Thuyết} = \text{Tồn Đầu} + \text{Tổng Nhập} + \text{Mượn Về} - \text{Tổng Bán} - \text{Hủy Bỏ} - \text{Cho Mượn}$$

*Trong đó:*
- $\text{Tổng Bán} = \text{Số bán lẻ trực tiếp} + \sum (\text{Số bán món POS} \times \text{Định lượng BOM})$

### 2. Chênh Lệch Kiểm Kê:
$$\text{Chênh Lệch} = \text{Tồn Thực Tế Kiểm Kê} - \text{Tồn Lý Thuyết}$$

*Ý nghĩa trạng thái:*
- **Chênh lệch = 0**: 🟢 **Khớp hoàn hảo** (Kho bảo quản chuẩn xác).
- **Chênh lệch < 0**: 🔴 **Thiếu hụt hàng** (Cảnh báo mất mát, rơi vỡ hoặc không bấm bill).
- **Chênh lệch > 0**: 🟠 **Dư thừa hàng** (Có thể do sót phiếu nhập hoặc làm sai định lượng).

---

## 🖥️ 7. HƯỚNG DẪN CÀI ĐẶT & CHẠY LÀM MÁY CHỦ (SERVER)

Hệ thống được thiết kế theo dạng **Standalone Portable**, bạn **KHÔNG CẦN VS Code hay bất kỳ công cụ lập trình nào**.

### Cách triển khai máy chủ trên một máy tính bất kỳ (Laptop / PC văn phòng / PC thu ngân):

#### Bước 1: Chuẩn bị môi trường
1. Tải và cài đặt **XAMPP** (bản cho Windows): [https://www.apachefriends.org/download.html](https://www.apachefriends.org/download.html)
   *(Cài đặt mặc định vào ổ đĩa `C:\xampp`)*.
2. Copy thư mục `kiem-soat-hang-hoa` vào máy (ví dụ tại `C:\kiem-soat-hang-hoa`).

#### Bước 2: Nạp dữ liệu vào máy mới (Chỉ làm lần đầu)
- Nhấp đúp chuột vào file:
  ```text
  c:\kiem-soat-hang-hoa\KHOI_PHUC_DU_LIEU.bat
  ```
  *(Hệ thống sẽ tự động tạo database `quan_ly_kho` và nạp toàn bộ dữ liệu mẫu, công thức BOM và danh mục hàng hóa).*

#### Bước 3: Khởi động máy chủ bằng 1 Click
Bạn có sẵn 2 chế độ máy chủ để lựa chọn tùy nhu cầu:

* **Chế độ 1: Dùng nội bộ trong quán (Cùng mạng Wi-Fi)**:
  - Nhấp đúp chuột vào file `CHAY_MAY_CHU_LAN.bat`.
  - Màn hình sẽ hiện địa chỉ IP (Ví dụ: `http://192.168.1.15:8000`).
  - Mọi điện thoại, máy tính bảng của nhân viên trong quán chỉ cần mở trình duyệt gõ link trên là dùng ngay.

* **Chế độ 2: Dùng Online từ xa qua 4G (Ở bất kỳ đâu)**:
  - Nhấp đúp chuột vào file `CHAY_MAY_CHU_ONLINE.bat`.
  - Hệ thống tự động tạo đường link bảo mật HTTPS (Cloudflare Tunnel).
  - Bạn gửi link đó cho nhân viên hoặc mở trên điện thoại khi đi ra ngoài đường.

* **Khi muốn tắt máy chủ**: Nhấp đúp chuột vào `DUNG_MAY_CHU.bat`.

> 💡 **Mẹo tự động chạy khi bật máy tính**:
> 1. Bấm `Windows + R`, gõ `shell:startup` rồi ấn Enter.
> 2. Kéo lối tắt (Shortcut) của file `CHAY_MAY_CHU_LAN.bat` thả vào thư mục đó.
> -> Từ nay, mỗi khi cắm điện bật máy tính lên, máy chủ sẽ tự động chạy ngầm, không cần thao tác gì thêm!

---

## 🔄 8. QUY TRÌNH VẬN HÀNH CHUẨN HÀNG NGÀY

```
                     🌅 ĐẦU THÁNG
                          │
                          ▼
                   📦 NHẬP TỒN ĐẦU KỲ
                (ton_dau/nhap_ton.php)
                          │
                          ▼
            ══════════════════════════════
            📅 MỖI NGÀY HOẠT ĐỘNG
            ══════════════════════════════
                          │
                          ▼
            📤 BƯỚC 1: TẢI FILE EXCEL SỐ BÁN
                - Vào "Upload Số Bán (Excel)"
                - Tải file bán hàng xuất từ máy POS
                - Hệ thống tự động lọc bánh & bóc tách BOM
                          │
                          ▼
            ✍️ BƯỚC 2: GHI NHẬN BIẾN ĐỘNG TRONG NGÀY
                - Nhập thêm hàng từ xưởng/NCC (nhaphang/them.php)
                - Hủy bánh hỏng/hết date (dieuchinh/them.php?loai=huy)
                - Cho mượn hoặc mượn hàng cơ sở khác
                          │
                          ▼
            📦 BƯỚC 3: KIỂM KHO THỰC TẾ CUỐI CA / NGÀY
                - Cầm điện thoại mở link "Kiểm Kê Thực Tế"
                - Bấm nút +/- đếm số lượng thực tế trong tủ/kệ
                - Xem chênh lệch đổi màu tức thì & Lưu kết quả
                          │
                          ▼
            📊 BƯỚC 4: XEM BÁO CÁO & ĐỐI SOÁT
                - Xem báo cáo chênh lệch 11 cột
                - Xuất file Excel gửi ban quản lý
```

---

## 🔑 9. TÀI KHOẢN ĐĂNG NHẬP MẶC ĐỊNH

| Thông Tin | Giá Trị |
| :--- | :--- |
| **Đường dẫn truy cập** | `http://localhost:8000` (hoặc qua IP nội bộ / link Cloudflare) |
| **Tài khoản (Username)** | `admin` |
| **Mật khẩu (Password)** | `admin123` |
| **Quyền hạn** | Toàn quyền Quản Trị Viên (Admin) |

---

<div align="center">
  <b>HỆ THỐNG QUẢN LÝ KHO & KIỂM SOÁT HÀNG HÓA TỰ ĐỘNG</b><br>
  <i>Được thiết kế và hoàn thiện tối ưu cho mô hình kinh doanh F&B</i>
</div>
