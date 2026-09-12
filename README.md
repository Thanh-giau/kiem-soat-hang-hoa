# 🏪 HỆ THỐNG QUẢN LÝ KHO & KIỂM SOÁT HÀNG HÓA TỰ ĐỘNG
> **KHO HÀNG CỦA THANH GIÀU**  
> *Giải pháp chuyên sâu quản lý tồn kho F&B: Tự động bóc tách số bán từ POS (iPOS, KiotViet...), định lượng BOM nguyên vật liệu, kiểm kê thực tế di động và đối soát chênh lệch hàng hóa chống thất thoát.*  
> *Hỗ trợ biến thành App Mobile trên điện thoại, đóng gói 1-Click duy nhất cho máy POS chạy ngầm 24/7 và cơ chế cập nhật từ xa qua Web/GitHub.*

---

## 📌 MỤC LỤC
1. [Giới Thiệu Tổng Quan](#-1-giới-thiệu-tổng-quan)
2. [Điểm Nổi Bật & Tính Năng Đột Phá](#-2-điểm-nổi-bật--tính-năng-đột-phá)
3. [Đóng Gói 1-Click Cho Máy POS 24/7 (Không Cần VS Code)](#-3-đóng-gói-1-click-cho-máy-pos-247-không-cần-vs-code)
4. [Truy Cập Di Động & Hướng Dẫn Biến Web Thành App Mobile](#-4-truy-cập-di-động--hướng-dẫn-biến-web-thành-app-mobile)
5. [Thu Gọn Theo Ngày (Accordion) & Sửa Ngày Tự Động Tính Lại Kho](#-5-thu-gọn-theo-ngày-accordion--sửa-ngày-tự-động-tính-lại-kho)
6. [Cơ Chế Cập Nhật Hệ Thống Từ Xa Qua Web (Remote Updater)](#-6-cơ-chế-cập-nhật-hệ-thống-từ-xa-qua-web-remote-updater)
7. [Kiến Trúc & Công Nghệ Sử Dụng](#-7-kiến-trúc--công-nghệ-sử-dụng)
8. [Cấu Trúc Thư Mục Hệ Thống](#-8-cấu-trúc-thư-mục-hệ-thống)
9. [Chi Tiết Toàn Bộ Phân Hệ Chức Năng](#-9-chi-tiết-toàn-bộ-phân-hệ-chức-năng)
10. [Công Thức Tính Toán Kho Cốt Lõi](#-10-công-thức-tính-toán-kho-cốt-lõi)
11. [Hướng Dẫn Cài Đặt & Triển Khai Trên Máy POS Mới](#-11-hướng-dẫn-cài-đặt--triển-khai-trên-máy-pos-mới)
12. [Quy Trình Vận Hành Chuẩn Hàng Ngày](#-12-quy-trình-vận-hành-chuẩn-hàng-ngày)
13. [Tài Khoản Đăng Nhập Mặc Định](#-13-tài-khoản-đăng-nhập-mặc-định)

---

## 🌟 1. GIỚI THIỆU TỔNG QUAN

Trong ngành F&B (Nhà hàng, Quán Cà phê, Trà sữa, Tiệm bánh), việc kiểm soát tồn kho luôn gặp phải các khó khăn đặc thù:
- Máy POS bán món nước (vd: *Matcha Latte Tây Bắc Mochi*), nhưng kho cần kiểm kê lại là **Viên Bánh Mochi**.
- Các món đồ ăn (vd: *Sandwich Gà Phô Mai*) tiêu hao đồng thời nhiều nguyên vật liệu (1 Vỏ bánh + 2 Lát phô mai + 1 Gói xốt).
- File Excel xuất ra từ máy bán hàng POS (iPOS, KiotViet...) có hàng trăm món pha chế, làm nhân viên mất hàng giờ lọc tay để tìm 20 món Bánh/Đồ ăn cần đếm kho.
- Các mã hàng POS có tiền tố `PLT` (Plated/Bánh đĩa) dễ bị nhầm lẫn với thức uống nếu không có bộ lọc nhận diện thông minh.
- Nhân viên hay nhập nhầm ngày hóa đơn dẫn đến số liệu tồn kho lý thuyết của các ngày sau bị lệch với biên bản kiểm kê.
- Thất thoát không rõ nguyên nhân giữa tồn lý thuyết trên máy và tồn thực tế đếm được trong tủ/kệ.

**Hệ thống này được xây dựng để giải quyết triệt để 100% các vấn đề trên**, giúp chủ cửa hàng và quản lý kho nắm rõ từng chiếc bánh, từng lon nước ngọt đến từng gam nguyên liệu một cách hoàn toàn tự động, minh bạch và tiện lợi ngay trên điện thoại hoặc máy POS.

---

## 🚀 2. ĐIỂM NỔI BẬT & TÍNH NĂNG ĐỘT PHÁ

* 📦 **Đóng Gói 1 File Duy Nhất Cho Máy POS (`CHAY_HE_THONG_POS.bat`)**:
  - Không còn tình trạng nhiều file `.bat` gây bấm nhầm.
  - 1-Click tự động kích hoạt MySQL, tự động import CSDL nếu máy mới chưa có dữ liệu, tự động chạy PHP server port 8000, tự động mở đường hầm Cloudflare Tunnel và tự ghim vào khởi động cùng Windows.
  - Chạy ẩn ngầm 100% qua VBScript, không hiển thị cửa sổ đen CMD, không làm vướng màn hình bán hàng của thu ngân.
* 📱 **Quét QR Truy Cập Di Động Thông Minh (Dual-Tab)**:
  - Tích hợp sẵn nút "📱 Điện thoại" ở thanh Topbar và chân trang.
  - **Tab 1 - 🌐 Online (4G/5G / Ngoài Mạng)**: Tự động trích xuất link Cloudflare Quick Tunnel đang hoạt động, tạo mã QR trực tiếp để mở từ xa ở bất cứ đâu.
  - **Tab 2 - 📶 Wi-Fi Quán (LAN)**: Tự động nhận diện IP LAN của quán (`http://192.168.1.x:8000`) kèm mã QR cho nhân viên truy cập siêu tốc nội bộ.
  - Hướng dẫn cài đặt thành Web App (PWA) mở toàn màn hình không viền như ứng dụng tải từ App Store / Google Play.
* 🗂️ **Nhập Hàng & Điều Chỉnh Kho Thu Gọn Theo Ngày (Accordion Cards)**:
  - Tự động nhóm toàn bộ phiếu theo từng ngày: Thứ trong tuần, ngày tháng, tổng số lượng và số mặt hàng.
  - Mặc định thu gọn gọn gàng, click vào ngày nào bung chi tiết ngày đó.
  - Phân loại màu sắc sắc nét: Nhập hàng (Xanh ngọc), Hủy (Đỏ), Cho mượn (Cam hổ phách), Mượn về (Xanh cyan).
* ✏️ **Đổi Ngày Hàng Loạt & Tự Động Tính Lại Tồn Kho (Auto-Recalculation)**:
  - Cho phép sửa ngày toàn bộ phiếu trong ngày hoặc sửa ngày lẻ từng món hàng khi nhân viên lỡ tay chọn sai ngày.
  - **Cơ chế tính toán lại kho tự động**: Hệ thống tự động quét lại tồn lý thuyết (`ton_ly_thuyet`) và cập nhật chênh lệch (`chenh_lech`) cho tất cả các đợt kiểm kê bị ảnh hưởng, đảm bảo sổ sách kho luôn chuẩn xác 100%.
  - Hỗ trợ nút xóa toàn bộ phiếu của 1 ngày với modal cảnh báo an toàn.
* 🔄 **Cập Nhật Hệ Thống Từ Xa Qua Web (Remote Updater)**:
  - Menu quản trị Web **Hệ Thống -> Cập Nhật Hệ Thống** (`hethong/cap_nhat.php`).
  - 1-Click tự động backup CSDL trước khi update, tự động kéo code mới nhất từ GitHub (`git pull`), xem lịch sử commit trực quan mà không cần chạm tay vào bàn phím máy POS.
* 🧠 **Bóc Tách & Nhận Diện Mã POS Thông Minh**:
  - Hỗ trợ mã hàng tiền tố `PLT`, Shopee, Grab...
  - Phân loại chính xác các món Bánh/Đồ ăn cần kiểm kê (`can_kiem_ke = 1`) và bỏ qua đồ pha chế.
* 🧮 **Định Lượng Món Bán (BOM) & Đồng Bộ Hồi Tố (Retroactive Sync)**:
  - Bóc tách 1 món POS thành nhiều nguyên vật liệu kiểm kê.
  - Bất kỳ khi nào sửa đổi định lượng BOM, hệ thống tự động quét lại toàn bộ lịch sử bán quá khứ để cập nhật lại tồn kho mà không cần nạp lại file Excel.

---

## 🖥️ 3. ĐÓNG GÓI 1-CLICK CHO MÁY POS 24/7 (KHÔNG CẦN VS CODE)

Hệ thống được tối ưu hóa đặc biệt để **biến máy tính thu ngân POS tại quán thành máy chủ trung tâm 24/24** mà không cần cài đặt VS Code, Git GUI hay bất kỳ phần mềm lập trình phức tạp nào.

### 🌟 Thư Mục Gốc Siêu Gọn Gàng (Chống Bấm Nhầm)
Thư mục gốc của dự án được quy hoạch sạch sẽ, chỉ giữ lại đúng **2 file thực thi**:
1. `CHAY_HE_THONG_POS.bat`: File duy nhất để nhân viên / chủ quán chạy toàn bộ hệ thống trên máy POS.
2. `DAY_LEN_GITHUB.bat`: File dành cho máy phát triển để sao lưu DB và đẩy code lên GitHub.
*(Tất cả các file bảo trì phụ trợ được chuyển gọn vào thư mục `hethong/cong_cu/`)*.

### ⚡ Cơ Chế Vận Hành Của `CHAY_HE_THONG_POS.bat`
Khi bạn nhấp đúp vào `CHAY_HE_THONG_POS.bat`:
1. **Khởi động MySQL Daemon**: Tự động tìm MySQL trong XAMPP (`C:\xampp\mysql\bin\mysqld.exe`) hoặc dịch vụ MySQL của Windows và kích hoạt ngầm.
2. **Tự Động Nạp Dữ Liệu Lần Đầu**: Tự động kiểm tra CSDL `quan_ly_kho`. Nếu máy POS mới chưa có dữ liệu hoặc CSDL đang rỗng, file sẽ tự động nạp toàn bộ cấu trúc và dữ liệu từ `database/quan_ly_kho_backup.sql`.
3. **Khởi Động Web Server PHP**: Kích hoạt PHP nội bộ lắng nghe tại cổng `http://0.0.0.0:8000`.
4. **Mở Đường Hầm Cloudflare Tunnel**: Chạy `bin/cloudflared.exe` với cổng metrics `20241` để cấp đường link truy cập từ xa HTTPS mã hóa bảo mật toàn cầu.
5. **Đăng Ký Khởi Động Cùng Windows**: Tự động tạo lối tắt trong thư mục `shell:startup` của Windows. Từ lần sau, mỗi khi cắm điện bật máy POS, máy chủ sẽ tự khởi động mà không ai cần bấm file gì nữa!
6. **Chạy Ngầm 100% Không Cửa Sổ Đen**: Kích hoạt VBScript `hethong/cong_cu/CHAY_NGAM_CHO_POS.vbs` để đưa các tiến trình vào chế độ nền (Background Service), tự động đóng cửa sổ CMD sau 3 giây và tự động mở trình duyệt đến `http://localhost:8000`.

---

## 📱 4. TRUY CẬP DI ĐỘNG & HƯỚNG DẪN BIẾN WEB THÀNH APP MOBILE

### 4.1. Lấy Link & Mã QR Truy Cập Điện Thoại
Trên giao diện web (máy tính hoặc POS), nhấn vào nút **"📱 Điện thoại"** ở thanh điều hướng trên cùng hoặc chân trang:
- **Tab 🌐 Online (4G/5G)**: Quét mã QR bằng Camera điện thoại (iPhone/Android) để truy cập từ xa ở bất cứ đâu ngoài quán. Link được cấp phát tự động dạng `https://xxxx.trycloudflare.com`.
- **Tab 📶 Wi-Fi Quán (LAN)**: Quét mã QR để truy cập bằng mạng Wi-Fi nội bộ của quán qua địa chỉ IP (ví dụ: `http://192.168.1.26:8000`) với tốc độ phản hồi cực nhanh dưới 5ms.

### 4.2. Cài Đặt Làm Ứng Dụng Di Động (Web App / PWA)
Bạn có thể ghim hệ thống ra màn hình chính của điện thoại để sử dụng như một App chuyên nghiệp:

#### 🍏 Dành Cho iPhone / iPad (Trình duyệt Safari):
1. Mở link web trên trình duyệt **Safari**.
2. Bấm vào nút **Chia sẻ** (biểu tượng hình vuông có mũi tên chỉ lên ở thanh dưới cùng).
3. Cuộn xuống chọn **"Thêm vào Màn hình chính"** (*Add to Home Screen*).
4. Đặt tên (vd: **Kho Thanh Giàu**) và bấm **Thêm** (*Add*).
5. Ứng dụng sẽ xuất hiện ngoài màn hình chính, bấm vào là mở toàn màn hình, không hiện thanh địa chỉ trình duyệt!

#### 🤖 Dành Cho Android (Trình duyệt Google Chrome / Cốc Cốc):
1. Mở link web trên trình duyệt **Chrome**.
2. Nhấn vào biểu tượng **3 dấu chấm ⋮** ở góc trên cùng bên phải.
3. Chọn **"Cài đặt ứng dụng"** (*Install app*) hoặc **"Thêm vào Màn hình chính"** (*Add to Home screen*).
4. Bấm **Cài đặt** để hoàn tất.

---

## 🗂️ 5. THU GỌN THEO NGÀY (ACCORDION) & SỬA NGÀY TỰ ĐỘNG TÍNH LẠI KHO

### 5.1. Thiết Kế Thu Gọn Thông Minh (Accordion Card)
Cả hai phân hệ **Nhập Hàng** (`nhaphang/danh_sach.php`) và **Điều Chỉnh Kho** (`dieuchinh/danh_sach.php`) được nâng cấp với giao diện thẻ ngày hiện đại:
- Mỗi ngày là một khối (Card) riêng biệt, hiển thị tiêu đề rõ ràng: Thứ trong tuần (Thứ Hai, Thứ Ba... Chủ Nhật), ngày tháng năm.
- **Thống kê nhanh trên tiêu đề**: Hiển thị tổng số phiếu, tổng số lượng hàng, số loại mặt hàng trong ngày.
- **Mặc định thu gọn**: Giúp trang danh sách luôn gọn gàng dù có dữ liệu nhiều tháng. Bấm vào tiêu đề ngày để bung ra xem bảng chi tiết từng sản phẩm.
- **Huy hiệu nghiệp vụ trực quan**:
  - Nhập hàng: Màu xanh lá / Emerald.
  - Hủy hàng: Màu đỏ tươi kèm icon hủy.
  - Cho mượn: Màu vàng cam hổ phách.
  - Mượn về: Màu xanh ngọc Cyan.

### 5.2. Chức Năng Đổi Ngày & Tự Động Tính Lại Tồn Kho (Auto-Recalculation)
Nếu nhân viên lỡ tay chọn sai ngày khi nhập hàng hoặc điều chỉnh kho:
1. **Đổi ngày hàng loạt**: Bấm nút **"📅 Đổi Ngày"** ở đầu mỗi thẻ ngày, chọn ngày mới và bấm Lưu. Toàn bộ các dòng phiếu của ngày hôm đó sẽ được chuyển sang ngày mới.
2. **Đổi ngày từng sản phẩm**: Trong bảng chi tiết, bấm vào biểu tượng cây bút **✏️** cạnh ngày của từng mặt hàng để chỉnh sửa ngày cho riêng sản phẩm đó.
3. **Cơ Chế Tính Toán Lại Kho Thông Minh**:
   - Khi dời ngày giữa ngày cũ $D_1$ và ngày mới $D_2$, hệ thống tự động chạy lại động cơ `layBaoCaoTonKhoChiTiet()` cho tất cả các sản phẩm bị ảnh hưởng từ ngày nhỏ nhất đến ngày lớn nhất.
   - Tồn lý thuyết (`ton_ly_thuyet`) và chênh lệch (`chenh_lech`) của các đợt kiểm kê sau đó sẽ được cập nhật tự động ngay lập tức, triệt tiêu hoàn toàn nguy cơ sai lệch số liệu kho.
4. **Xóa Cả Ngày An Toàn**: Bấm nút **"🗑️ Xóa Ngày"** để xóa sạch các phiếu của ngày được chọn kèm hộp thoại xác nhận bảo vệ, dữ liệu tồn kho tự động được hoàn trả chuẩn xác.

---

## 🔄 6. CƠ CHẾ CẬP NHẬT HỆ THỐNG TỪ XA QUA WEB (REMOTE UPDATER)

Không cần phải điều khiển TeamViewer hay mở dòng lệnh trên máy POS để cập nhật tính năng mới:

### Quy Trình Cập Nhật 2 Chiều:
```
┌──────────────────────────────┐              ┌──────────────────────────────┐
│   MÁY PHÁT TRIỂN / CODE     │              │    MÁY POS BÁN HÀNG 24/7     │
│                              │              │                              │
│  1. Sửa code / Tính năng mới │              │  - Đang chạy ngầm phục vụ    │
│  2. Nhấp đúp chuột vào file: │              │  - Vào Menu Web:             │
│     DAY_LEN_GITHUB.bat       │              │    "Hệ Thống -> Cập Nhật"    │
│              │               │              │              ▲               │
└──────────────┼───────────────┘              └──────────────┼───────────────┘
               │ Git Push                                    │ Git Pull
               ▼                                             │
      ┌──────────────────────────────────────────────────────┴┐
      │             GITHUB REPOSITORY (Cloud)                │
      │    Thanh-giau/kiem-soat-hang-hoa (Branch: main)      │
      └───────────────────────────────────────────────────────┘
```

1. **Phía Máy Lập Trình**:
   - Chạy file `DAY_LEN_GITHUB.bat`.
   - File sẽ tự động xuất bản sao lưu CSDL mới nhất vào `database/quan_ly_kho_backup.sql`, tự động tạo commit với thời gian hiện tại và đẩy thẳng lên GitHub.
2. **Phía Máy POS (Cập Nhật Bằng Web)**:
   - Quản trị viên truy cập vào menu **Hệ Thống -> Cập Nhật Hệ Thống** (`hethong/cap_nhat.php`).
   - Màn hình hiển thị trạng thái kết nối GitHub và danh sách các lần cập nhật gần nhất.
   - Bấm nút **"🚀 Cập Nhật Hệ Thống Ngay"**:
     - Hệ thống tự động tạo bản sao lưu CSDL dự phòng trước khi cập nhật.
     - Tự động kéo toàn bộ code mới nhất từ GitHub (`git pull origin main`).
     - Báo cáo kết quả thành công và làm mới hệ thống ngay trên trình duyệt mà không cần khởi động lại máy!

---

## 🛠️ 7. KIẾN TRÚC & CÔNG NGHỆ SỬ DỤNG

```
┌─────────────────────────────────────────────────────────────┐
│                 GIAO DIỆN NGƯỜI DÙNG (UI)                    │
│    HTML5 Semantics • CSS3 Glassmorphism • Mobile-First      │
│    Vanilla JS Searchable Combobox • Accordion Date Cards    │
│    PWA Add to Home Screen • Font Awesome 6.5 • QR Code JS   │
└──────────────────────────────┬──────────────────────────────┘
                               │ HTTP / AJAX
┌──────────────────────────────▼──────────────────────────────┐
│                  TẦNG XỬ LÝ ỨNG DỤNG (PHP 8.2)               │
│  - Bóc tách Excel POS: SimpleXLSX / SimpleXLS / Regex        │
│  - Bộ lọc sản phẩm kiểm kê & Nhận diện PLT / Food Items      │
│  - Động cơ Định lượng BOM & Đồng bộ hồi tố Retroactive Sync  │
│  - Động cơ Tự động tính lại tồn kho khi sửa ngày             │
│  - Trích xuất Cloudflare Quick Tunnel metrics realtime      │
│  - Trình cập nhật hệ thống từ xa qua Git Web GUI             │
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

## 📁 8. CẤU TRÚC THƯ MỤC HỆ THỐNG

```text
c:\kiem-soat-hang-hoa\
├── assets/
│   ├── css/
│   │   ├── style.css             # Giao diện chính Indigo sang trọng
│   │   ├── responsive.css        # Quy tắc co giãn Mobile & Tablet
│   │   └── searchable-select.css # Dropdown tìm kiếm sản phẩm nổi thông minh
│   └── js/
│       ├── main.js               # Logic điều khiển, modal, accordion
│       └── searchable-select.js  # Thư viện gõ tìm kiếm tiếng Việt không dấu
├── baocao/
│   ├── bao_cao_ngay.php          # Báo cáo tổng hợp số liệu theo ngày
│   ├── bao_cao_thang.php         # Báo cáo luân chuyển kho theo tháng
│   ├── chenh_lech.php            # BẢNG ĐỐI SOÁT CHÊNH LỆCH 11 CỘT CỐT LÕI
│   └── xuat_excel.php            # Xuất báo cáo ra định dạng Excel
├── bin/
│   └── cloudflared.exe           # Trình mở đường hầm trực tuyến từ xa
├── config/
│   └── database.php              # Kết nối CSDL MySQL bảo mật qua PDO
├── database/
│   ├── quan_ly_kho.sql           # Cấu trúc CSDL ban đầu
│   └── quan_ly_kho_backup.sql    # Bản sao lưu toàn bộ dữ liệu thực tế mới nhất
├── dieuchinh/
│   ├── danh_sach.php             # Danh sách phiếu điều chỉnh (Accordion theo ngày)
│   ├── them.php                  # Form lập phiếu điều chỉnh (Hủy / Mượn)
│   ├── doi_ngay.php              # ĐỔI NGÀY ĐIỀU CHỈNH & TỰ TÍNH LẠI KHO
│   ├── xoa_ngay.php              # XÓA TOÀN BỘ PHIẾU ĐIỀU CHỈNH CỦA 1 NGÀY
│   └── xoa.php                   # Hủy 1 dòng điều chỉnh kho
├── dinhluong/
│   ├── danh_sach.php             # Danh mục công thức món bán (BOM)
│   ├── them.php                  # Thêm công thức định lượng mới
│   ├── sua.php                   # Sửa công thức định lượng
│   ├── xoa.php                   # Xóa công thức định lượng
│   └── dong_bo.php               # Kích hoạt đồng bộ hồi tố số bán thủ công
├── hethong/
│   ├── cap_nhat.php              # GIAO DIỆN CẬP NHẬT HỆ THỐNG TỪ XA QUA WEB
│   └── cong_cu/                  # THƯ MỤC CÔNG CỤ QUẢN TRỊ & BẢO TRÌ (11 Tools)
│       ├── CHAY_NGAM_CHO_POS.vbs # VBScript chạy ẩn ngầm không hiện CMD đen
│       ├── CHAY_MAY_CHU_LAN.bat  # Chạy máy chủ mạng Wi-Fi nội bộ
│       ├── CHAY_MAY_CHU_ONLINE.bat # Chạy máy chủ trực tuyến 4G
│       ├── DUNG_MAY_CHU.bat      # Dừng toàn bộ máy chủ an toàn
│       ├── BAT_KHOI_DONG_CUNG_WIN.bat # Bật tự chạy khi mở máy tính
│       ├── TAT_KHOI_DONG_CUNG_WIN.bat # Tắt tự chạy cùng máy tính
│       ├── CAI_DAT_MAY_POS_24_7.bat   # Cài đặt trọn gói máy POS
│       ├── KIEM_TRA_TRANG_THAI.bat    # Kiểm tra cổng và dịch vụ máy chủ
│       ├── SAO_LUU_DU_LIEU.bat        # Sao lưu thủ công CSDL ra file SQL
│       ├── KHOI_PHUC_DU_LIEU.bat      # Phục hồi CSDL từ file backup
│       └── CAP_NHAT_TU_GITHUB.bat     # Cập nhật từ GitHub bằng dòng lệnh
├── import/
│   ├── filter_inventory_products.php # Thuật toán lọc món POS & bóc tách BOM
│   ├── sync_bom_sales.php        # Động cơ tự động đồng bộ lại toàn bộ số bán
│   ├── SimpleXLSX.php            # Trình đọc Excel .xlsx
│   └── SimpleXLS.php             # Trình đọc Excel .xls
├── includes/
│   ├── functions.php             # Thư viện hàm tồn kho, chênh lệch, đổi ngày, QR
│   ├── header.php                # Khung đầu trang, Topbar, modal Mobile
│   ├── footer.php                # Khung chân trang, nhúng JS & QR Code Modal
│   └── sidebar.php               # Menu điều hướng đa cấp, link cập nhật
├── kiemke/
│   ├── kiem_ke.php               # GIAO DIỆN KIỂM KÊ DI ĐỘNG TRÊN ĐIỆN THOẠI
│   ├── lich_su.php               # Lịch sử các đợt kiểm kho
│   └── luu.php                   # Bộ xử lý lưu số liệu kiểm kê
├── nhaphang/
│   ├── danh_sach.php             # Danh sách phiếu nhập hàng (Accordion theo ngày)
│   ├── them.php                  # Form tạo phiếu nhập nhiều mặt hàng cùng lúc
│   ├── doi_ngay.php              # ĐỔI NGÀY NHẬP HÀNG & TỰ TÍNH LẠI KHO
│   ├── xoa_ngay.php              # XÓA TOÀN BỘ PHIẾU NHẬP CỦA 1 NGÀY
│   └── xoa.php                   # Hủy 1 dòng nhập hàng
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
│   └── xoa_file.php              # Xóa file upload & thu hồi số liệu tồn kho
├── ton_dau/
│   ├── nhap_ton.php              # Bảng nhập số lượng tồn kho đầu kỳ
│   └── luu.php                   # Lưu tồn đầu kỳ vào CSDL
├── CHAY_HE_THONG_POS.bat         # ⭐ FILE DUY NHẤT CHẠY TOÀN BỘ TRÊN MÁY POS 24/7
├── DAY_LEN_GITHUB.bat            # 🚀 1-CLICK ĐẨY BẢN CẬP NHẬT LÊN GITHUB
├── dashboard.php                 # BẢNG ĐIỀU KHIỂN TỔNG QUAN (7 KPI, Cảnh báo)
├── login.php                     # Trang đăng nhập bảo mật
├── logout.php                    # Đăng xuất an toàn
└── index.php                     # Điểm điều hướng trung tâm
```

---

## 📊 9. CHI TIẾT TOÀN BỘ PHÂN HỆ CHỨC NĂNG

### 9.1. Bảng Điều Khiển Tổng Quan (`dashboard.php`)
* **7 Thẻ KPI Thời Gian Thực**:
  - Số mặt hàng đang quản lý kiểm kê.
  - Tổng lượng tồn đầu kỳ.
  - Tổng lượng nhập thêm trong kỳ.
  - Tổng số đã bán ra (từ POS & bóc tách BOM).
  - Tổng số lượng điều chỉnh (Hủy/Mượn).
  - Tổng tồn kho lý thuyết hiện tại.
  - Tỷ lệ khớp kho thực tế (%).
* **Bảng Cảnh Báo Thông Minh**:
  - 🔴 **Top Sản phẩm thiếu nhiều nhất**: Cảnh báo ngay món nghi ngờ thất thoát hoặc quên quét mã.
  - 🟠 **Top Sản phẩm dư nhiều nhất**: Phát hiện món nghi ngờ sót hàng nhập hoặc giao nhầm.
  - **Danh sách chênh lệch hôm nay**: Hiển thị nhanh các mặt hàng lệch kho trong ngày.

### 9.2. Phân Hệ Upload & Xử Lý Số Bán Excel POS (`soban/`)
* Đọc định dạng Excel của hầu hết phần mềm POS: **iPOS**, **KiotViet**, **CukCuk**, **Sapo**.
* Bỏ qua dòng trống, header subtotal, tự động gom nhóm mã hàng.
* **Xử lý mã sản phẩm đặc thù**: Tự động nhận diện tiền tố `PLT` của các món Bánh/Đồ ăn đĩa để đưa vào kiểm kê, không bị nhầm lẫn với nước pha chế.
* **Quản lý & Thu Hồi Số Liệu (`xoa_file.php`)**: Khi xóa nhầm file số bán, toàn bộ số bán đã lưu sẽ được rút lại sạch sẽ, tồn lý thuyết và chênh lệch kiểm kê tự động phục hồi.

### 9.3. Phân Hệ Định Lượng Món Bán & Bóc Tách (BOM - `dinhluong/`)
* **Cài đặt 1 món bán ra tiêu hao nhiều NVL**: Ví dụ: 1 phần *Matcha Latte Mochi* dùng *1 Viên Bánh Mochi*; 1 phần *Sandwich Gà* dùng *1 Vỏ bánh + 2 Lát phô mai*.
* **Nhận diện kích thước thông minh**: Tự động bỏ qua các hậu tố `(Vừa)`, `(Lớn)`, `(M)`, `(L)` và tiền tố món `PLT`, `Shopee`, `Grab`.
* **Tự Động Đồng Bộ Hồi Tố (Retroactive Sync)**: Bất kỳ lúc nào thêm/sửa công thức BOM, hệ thống tự động quét lại toàn bộ file số bán quá khứ và cập nhật lại tồn lý thuyết trong nháy mắt.

### 9.4. Phân Hệ Nhập Hàng & Điều Chỉnh Kho Dạng Accordion (`nhaphang/`, `dieuchinh/`)
* **Lập phiếu nhập/điều chỉnh nhiều mã cùng lúc**: 1 phiếu có thể chứa hàng chục mã hàng.
* **Nút "⚡ Nạp Hàng Kiểm Kê"**: 1 click nạp toàn bộ danh mục sản phẩm vào form để điền số lượng nhanh.
* **Giao diện Accordion gom nhóm theo ngày**: Đóng mở chi tiết từng ngày trực quan.
* **Đổi ngày linh hoạt & tự động cân đối lại kho**: Sửa ngày cả lô hoặc sửa ngày từng món lẻ, bảo vệ toàn vẹn dữ liệu tồn kho.

### 9.5. Phân Hệ Kiểm Kê Di Động & Đối Soát Chênh Lệch (`kiemke/`, `baocao/`)
* **Kiểm Kê Thực Tế Trên Điện Thoại (`kiemke/kiem_ke.php`)**:
  - Giao diện nút bấm `+` và `-` to rõ, tối ưu thao tác một tay khi cầm điện thoại kiểm đếm trong tủ kho.
  - Phản hồi màu sắc tức thì khi gõ số: 🟢 Khớp (0) | 🔴 Thiếu (Âm) | 🟠 Dư (Dương).
* **Báo Cáo Chênh Lệch Đầy Đủ 11 Cột (`baocao/chenh_lech.php`)**:
  - `Mã SP`, `Tên Sản Phẩm`, `ĐVT`, `Tồn Đầu Kỳ`, `Nhập Thêm`, `Mượn Về`, `Số Đã Bán`, `Hủy Bỏ`, `Cho Mượn`, `TỒN LÝ THUYẾT`, `THỰC TẾ KIỂM ĐẾM`, `CHÊNH LỆCH`.
  - Hỗ trợ lọc theo ngày, theo tháng và xuất ra file Excel gửi ban quản lý.

---

## 📐 10. CÔNG THỨC TÍNH TOÁN KHO CỐT LÕI

Hệ thống tuân thủ nghiêm ngặt các nguyên lý kế toán kho F&B:

### 1. Tồn Kho Lý Thuyết:
$$\text{Tồn Lý Thuyết} = \text{Tồn Đầu} + \text{Tổng Nhập} + \text{Mượn Về} - \text{Tổng Bán} - \text{Hủy Bỏ} - \text{Cho Mượn}$$

*Trong đó:*
- $\text{Tổng Bán} = \text{Số bán lẻ trực tiếp} + \sum (\text{Số bán món POS} \times \text{Định lượng BOM})$

### 2. Chênh Lệch Kiểm Kê:
$$\text{Chênh Lệch} = \text{Tồn Thực Tế Kiểm Kê} - \text{Tồn Lý Thuyết}$$

*Ý nghĩa trạng thái:*
- **Chênh lệch = 0**: 🟢 **Khớp hoàn hảo** (Kho bảo quản chuẩn xác).
- **Chênh lệch < 0**: 🔴 **Thiếu hụt hàng** (Cảnh báo mất mát, rơi vỡ hoặc không bấm bill POS).
- **Chênh lệch > 0**: 🟠 **Dư thừa hàng** (Có thể do sót phiếu nhập hoặc làm sai định lượng).

---

## 🖥️ 11. HƯỚNG DẪN CÀI ĐẶT & TRIỂN KHAI TRÊN MÁY POS MỚI

Bạn chỉ mất **chưa đầy 2 phút** để thiết lập hệ thống trên một máy POS hoàn toàn mới:

### Bước 1: Cài đặt XAMPP (Chỉ cần MySQL)
1. Tải XAMPP bản dành cho Windows: [https://www.apachefriends.org/download.html](https://www.apachefriends.org/download.html)
2. Cài đặt vào thư mục mặc định `C:\xampp`.

### Bước 2: Tải/Copy Thư Mục Dự Án Vào Máy POS
- Copy thư mục `kiem-soat-hang-hoa` vào ổ đĩa máy POS (ví dụ: `C:\kiem-soat-hang-hoa`).

### Bước 3: Khởi Chạy Bằng 1-Click Duy Nhất
- Nhấp đúp chuột vào file:
  ```text
  C:\kiem-soat-hang-hoa\CHAY_HE_THONG_POS.bat
  ```
- **Hệ thống sẽ tự động hoàn toàn**:
  - Bật dịch vụ MySQL.
  - Tự động nạp CSDL ban đầu từ `database/quan_ly_kho_backup.sql`.
  - Khởi chạy máy chủ Web port 8000 và Cloudflare Tunnel kết nối điện thoại.
  - Ghim tự khởi động cùng Windows khi bật máy.
  - Tự động mở trình duyệt và đóng cửa sổ CMD sau 3 giây.
- **Xong!** Từ nay, bạn chỉ cần mở máy POS lên là cả quán có thể sử dụng bình thường.

---

## 🔄 12. QUY TRÌNH VẬN HÀNH CHUẨN HÀNG NGÀY

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
                - Xuất file bán hàng từ phần mềm POS
                - Kéo thả vào "Upload Số Bán (Excel)"
                - Hệ thống tự động lọc bánh & bóc tách BOM
                          │
                          ▼
            ✍️ BƯỚC 2: GHI NHẬN BIẾN ĐỘNG TRONG NGÀY
                - Nhập thêm hàng từ NCC/Xưởng (nhaphang/them.php)
                - Ghi nhận bánh hỏng/hết date (dieuchinh/them.php?loai=huy)
                - Ghi nhận cho mượn hoặc mượn hàng cơ sở khác
                *(Lỡ tay nhập sai ngày có thể bấm "Đổi Ngày" để sửa)*
                          │
                          ▼
            📱 BƯỚC 3: KIỂM KHO THỰC TẾ CUỐI CA / NGÀY
                - Mở App trên điện thoại (Quét QR Tab Wi-Fi hoặc Online)
                - Bấm nút +/- đếm số lượng thực tế trong tủ mát/kệ
                - Xem chênh lệch đổi màu tức thì & Bấm Lưu
                          │
                          ▼
            📊 BƯỚC 4: XEM BÁO CÁO & ĐỐI SOÁT
                - Mở Bảng đối soát chênh lệch 11 cột
                - Xuất file Excel gửi chủ quán/ban giám đốc
```

---

## 🔑 13. TÀI KHOẢN ĐĂNG NHẬP MẶC ĐỊNH

| Thông Tin | Giá Trị |
| :--- | :--- |
| **Đường dẫn truy cập tại máy POS** | `http://localhost:8000` |
| **Đường dẫn truy cập nội bộ (Wi-Fi Quán)** | `http://192.168.1.x:8000` *(Xem trong nút 📱 Điện thoại)* |
| **Đường dẫn truy cập từ xa (4G/5G)** | `https://xxxx.trycloudflare.com` *(Quét mã QR trong nút 📱 Điện thoại)* |
| **Tài khoản (Username)** | `admin` |
| **Mật khẩu (Password)** | `admin123` |
| **Quyền hạn** | Toàn quyền Quản Trị Viên (Admin) |

---

<div align="center">
  <b>HỆ THỐNG QUẢN LÝ KHO & KIỂM SOÁT HÀNG HÓA TỰ ĐỘNG</b><br>
  <i>Giải pháp công nghệ chuyên biệt, bền bỉ và tiện lợi hàng đầu cho mô hình kinh doanh F&B</i>
</div>
