<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';
requireLogin();

$pageTitle = "Tạo Phiếu Điều Chỉnh Kho (Nhiều Mặt Hàng)";
$error = '';
$db = getDB();
$user = currentUser();

$defaultLoai = $_GET['loai'] ?? 'huy';
if (!in_array($defaultLoai, ['huy', 'cho_muon', 'muon'])) {
    $defaultLoai = 'huy';
}

// Lấy danh sách toàn bộ sản phẩm đang hoạt động
$sanPhams = $db->query("
    SELECT id, ma_sp, ten_sp, don_vi_tinh, nhom_sp, can_kiem_ke 
    FROM san_pham 
    WHERE trang_thai = 1 
    ORDER BY can_kiem_ke DESC, nhom_sp ASC, ten_sp ASC
")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ngay = trim($_POST['ngay_dieu_chinh'] ?? date('Y-m-d'));
    $loai = trim($_POST['loai'] ?? 'huy');
    $nguoiLienQuan = trim($_POST['nguoi_lien_quan'] ?? '');
    $ghiChuChung = trim($_POST['ghi_chu_chung'] ?? '');
    $items = $_POST['items'] ?? [];

    if (empty($ngay)) {
        $error = 'Vui lòng chọn ngày thực hiện điều chỉnh!';
    } elseif (!in_array($loai, ['huy', 'cho_muon', 'muon'])) {
        $error = 'Loại nghiệp vụ điều chỉnh không hợp lệ!';
    } elseif (empty($items)) {
        $error = 'Vui lòng thêm ít nhất một mặt hàng vào phiếu điều chỉnh!';
    } else {
        try {
            $db->beginTransaction();

            $stmt = $db->prepare("
                INSERT INTO dieu_chinh_kho (san_pham_id, ngay_dieu_chinh, loai, so_luong, nguoi_lien_quan, ghi_chu, nguoi_tao_id)
                VALUES (:sp_id, :ngay, :loai, :sl, :nlq, :gc, :uid)
            ");

            $validCount = 0;
            $totalQty = 0;

            foreach ($items as $item) {
                $spId = (int)($item['san_pham_id'] ?? 0);
                $soLuong = (float)str_replace(',', '', $item['so_luong'] ?? 0);
                $itemNote = trim($item['ghi_chu'] ?? '');

                // Ghép ghi chú chung & ghi chú riêng nếu có
                $finalNote = $ghiChuChung;
                if (!empty($itemNote)) {
                    $finalNote = !empty($finalNote) ? ($finalNote . ' | ' . $itemNote) : $itemNote;
                }

                if ($spId > 0 && $soLuong > 0) {
                    $stmt->execute([
                        'sp_id' => $spId,
                        'ngay' => $ngay,
                        'loai' => $loai,
                        'sl' => $soLuong,
                        'nlq' => $nguoiLienQuan,
                        'gc' => $finalNote,
                        'uid' => $user['id']
                    ]);
                    $validCount++;
                    $totalQty += $soLuong;
                }
            }

            if ($validCount === 0) {
                throw new Exception("Vui lòng nhập số lượng lớn hơn 0 cho ít nhất một mặt hàng!");
            }

            $db->commit();

            $tenLoai = $loai === 'huy' ? 'Hủy hàng' : ($loai === 'cho_muon' ? 'Cho mượn' : 'Mượn về');
            setFlash('success', "Đã tạo thành công phiếu [$tenLoai] cho {$validCount} mặt hàng (Tổng số lượng: " . formatNumber($totalQty) . ") ngày " . formatDate($ngay) . "! Tồn kho lý thuyết đã được tự động cập nhật.");
            header("Location: " . BASE_URL . "/dieuchinh/danh_sach.php?loai=" . urlencode($loai));
            exit;
        } catch (Exception $e) {
            $db->rollBack();
            $error = 'Lỗi lưu phiếu điều chỉnh: ' . $e->getMessage();
        }
    }
}

include __DIR__ . '/../includes/header.php';
?>

<div style="max-width: 1050px; margin: 0 auto;">
    <!-- Tiêu đề và nút quay lại -->
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; flex-wrap: wrap; gap: 12px;">
        <div>
            <h2 style="font-size: 22px; font-weight: 800; color: var(--text-primary); letter-spacing: -0.5px; display: flex; align-items: center; gap: 10px;">
                <span id="title-icon-badge" style="display: inline-flex; align-items: center; justify-content: center; width: 38px; height: 38px; background: linear-gradient(135deg, #ef4444, #dc2626); color: #fff; border-radius: 10px; font-size: 18px; box-shadow: 0 4px 10px rgba(239, 68, 68, 0.3);">
                    <i class="fa-solid fa-arrows-split-up-and-left" id="title-icon"></i>
                </span>
                <span id="page-heading-text">Tạo Phiếu Điều Chỉnh Kho (Nhiều Mặt Hàng)</span>
            </h2>
            <div style="font-size: 13px; color: var(--text-secondary); margin-top: 4px;">
                Cho phép ghi nhận cùng lúc nhiều sản phẩm trong một đợt Hủy hàng hết hạn, Cho mượn hoặc Mượn kho khác.
            </div>
        </div>
        <a href="<?= BASE_URL ?>/dieuchinh/danh_sach.php?loai=<?= urlencode($defaultLoai) ?>" class="btn btn-secondary btn-sm">
            <i class="fa-solid fa-arrow-left"></i> Quay lại danh sách
        </a>
    </div>

    <?php if ($error): ?>
        <div class="alert alert-danger" style="margin-bottom: 20px;">
            <i class="fa-solid fa-circle-exclamation"></i>
            <span><?= htmlspecialchars($error) ?></span>
        </div>
    <?php endif; ?>

    <form method="POST" action="" id="form-dieu-chinh">
        <!-- 1. THÔNG TIN CHUNG CỦA PHIẾU ĐIỀU CHỈNH -->
        <div class="card" style="margin-bottom: 20px;">
            <div class="card-header" style="background: #f8fafc; border-bottom: 1px solid var(--border-color);">
                <h3 class="card-title" style="font-size: 15px; font-weight: 700; color: var(--text-primary);">
                    <i class="fa-solid fa-file-lines" style="color: var(--primary);"></i>
                    Thông Tin Chung Của Đợt Điều Chỉnh
                </h3>
            </div>
            <div class="card-body">
                <div style="display: grid; grid-template-columns: 1fr 1.3fr 1.3fr 1.4fr; gap: 16px;">
                    <div class="form-group" style="margin-bottom: 0;">
                        <label class="form-label" for="ngay_dieu_chinh" style="font-weight: 600;">Ngày thực hiện <span style="color: var(--danger);">*</span></label>
                        <input type="date" id="ngay_dieu_chinh" name="ngay_dieu_chinh" class="form-control" required value="<?= htmlspecialchars($_POST['ngay_dieu_chinh'] ?? date('Y-m-d')) ?>" style="font-weight: 600;">
                    </div>

                    <div class="form-group" style="margin-bottom: 0;">
                        <label class="form-label" for="loai" style="font-weight: 600;">Loại nghiệp vụ <span style="color: var(--danger);">*</span></label>
                        <select id="loai" name="loai" class="form-control" required style="font-weight: 700;">
                            <option value="huy" <?= (($_POST['loai'] ?? $defaultLoai) === 'huy') ? 'selected' : '' ?>>❌ Số HỦY (Giảm tồn)</option>
                            <option value="cho_muon" <?= (($_POST['loai'] ?? $defaultLoai) === 'cho_muon') ? 'selected' : '' ?>>🤝 CHO MƯỢN (Giảm tồn)</option>
                            <option value="muon" <?= (($_POST['loai'] ?? $defaultLoai) === 'muon') ? 'selected' : '' ?>>🤲 MƯỢN VỀ (Tăng tồn)</option>
                        </select>
                    </div>

                    <div class="form-group" style="margin-bottom: 0;">
                        <label class="form-label" for="nguoi_lien_quan" id="label-nlq" style="font-weight: 600;">Lý do / Người duyệt hủy</label>
                        <input type="text" id="nguoi_lien_quan" name="nguoi_lien_quan" class="form-control" placeholder="Tên đối tác hoặc lý do..." value="<?= htmlspecialchars($_POST['nguoi_lien_quan'] ?? '') ?>">
                    </div>

                    <div class="form-group" style="margin-bottom: 0;">
                        <label class="form-label" for="ghi_chu_chung" style="font-weight: 600;">Ghi chú chung cả đợt</label>
                        <input type="text" id="ghi_chu_chung" name="ghi_chu_chung" class="form-control" placeholder="Ca sáng, kiểm tra cuối ngày..." value="<?= htmlspecialchars($_POST['ghi_chu_chung'] ?? '') ?>">
                    </div>
                </div>

                <!-- Banner giải thích tác động tồn kho -->
                <div id="impact-banner" style="margin-top: 14px; padding: 10px 14px; border-radius: 8px; font-size: 13px; display: flex; align-items: center; gap: 8px; background: #fef2f2; color: #991b1b; border: 1px solid #fecaca;">
                    <i class="fa-solid fa-circle-info" id="impact-icon"></i>
                    <span id="impact-text">Tác động: Nghiệp vụ <strong>HỦY HÀNG</strong> sẽ <strong>LÀM GIẢM TỒN KHO LÝ THUYẾT</strong> của các mặt hàng bên dưới.</span>
                </div>
            </div>
        </div>

        <!-- 2. BẢNG CHI TIẾT CÁC MẶT HÀNG ĐIỀU CHỈNH -->
        <div class="card" style="margin-bottom: 24px;">
            <div class="card-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px;">
                <div>
                    <h3 class="card-title" style="font-size: 15px; font-weight: 700;">
                        <i class="fa-solid fa-list-check" style="color: var(--primary);"></i>
                        Danh Sách Mặt Hàng Điều Chỉnh
                    </h3>
                </div>
                <div style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
                    <button type="button" class="btn btn-outline-primary btn-sm" id="btn-load-all-inventory" title="Nạp nhanh tất cả mặt hàng kiểm kê vào bảng">
                        <i class="fa-solid fa-bolt"></i> Nạp Hàng Kiểm Kê
                    </button>
                    <button type="button" class="btn btn-success btn-sm" id="btn-add-item">
                        <i class="fa-solid fa-plus"></i> + Thêm Dòng Mặt Hàng
                    </button>
                    <button type="button" class="btn btn-secondary btn-sm" id="btn-clear-all" title="Xóa tất cả các dòng" style="display: none;">
                        <i class="fa-solid fa-trash-can"></i> Xóa Hết
                    </button>
                </div>
            </div>

            <div class="card-body" style="padding: 0;">
                <div class="table-responsive">
                    <table class="table" style="margin-bottom: 0;" id="table-items">
                        <thead style="background: #f8fafc;">
                            <tr>
                                <th style="width: 45px; text-align: center;">STT</th>
                                <th>Chọn Mặt Hàng <span style="color: var(--danger);">*</span></th>
                                <th style="width: 190px; text-align: right;">Số Lượng <span style="color: var(--danger);">*</span></th>
                                <th style="width: 250px;">Ghi Chú Riêng (Lô, lý do cụ thể)</th>
                                <th style="width: 60px; text-align: center;">Xóa</th>
                            </tr>
                        </thead>
                        <tbody id="tbody-items">
                            <!-- Hàng điều chỉnh đầu tiên -->
                            <tr class="item-row">
                                <td style="text-align: center; font-weight: 600; color: var(--text-muted);" class="row-stt">1</td>
                                <td>
                                    <select name="items[0][san_pham_id]" class="form-control select-sp" required>
                                        <option value="">-- Chọn sản phẩm cần điều chỉnh --</option>
                                        <?php foreach ($sanPhams as $sp): ?>
                                            <option value="<?= $sp['id'] ?>" data-dvt="<?= htmlspecialchars($sp['don_vi_tinh']) ?>">
                                                [<?= htmlspecialchars($sp['ma_sp']) ?>] <?= htmlspecialchars($sp['ten_sp']) ?> (<?= htmlspecialchars($sp['don_vi_tinh']) ?> - <?= htmlspecialchars($sp['nhom_sp']) ?>) <?= $sp['can_kiem_ke'] ? '★' : '' ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </td>
                                <td>
                                    <div style="display: flex; align-items: center; gap: 6px;">
                                        <input type="number" step="any" min="0.01" name="items[0][so_luong]" class="form-control text-right input-qty" placeholder="0" required style="font-weight: 700; color: #ef4444; font-size: 15px;">
                                        <span class="dvt-label" style="font-size: 12px; font-weight: 600; color: var(--text-muted); min-width: 42px;">đv</span>
                                    </div>
                                </td>
                                <td>
                                    <input type="text" name="items[0][ghi_chu]" class="form-control form-control-sm" placeholder="Hết date, rách vỏ, mượn 3 cái...">
                                </td>
                                <td style="text-align: center;">
                                    <button type="button" class="btn btn-danger btn-sm btn-remove-row" style="padding: 5px 10px;" title="Xóa dòng này">
                                        <i class="fa-solid fa-trash-can"></i>
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                        <tfoot style="background: #f8fafc; border-top: 2px solid var(--border-color);">
                            <tr>
                                <td colspan="2" style="font-weight: 700; font-size: 14px; text-align: right; color: var(--text-primary);">
                                    TỔNG CỘNG ĐỢT ĐIỀU CHỈNH:
                                </td>
                                <td style="text-align: right;">
                                    <div style="font-weight: 800; font-size: 18px; color: #ef4444;" id="total-qty-display">0</div>
                                </td>
                                <td colspan="2">
                                    <span class="badge badge-info" id="total-items-badge" style="font-size: 12px; padding: 6px 12px;">
                                        <i class="fa-solid fa-check"></i> 1 mặt hàng
                                    </span>
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>

        <!-- 3. THANH NÚT BẤM LƯU PHIẾU -->
        <div style="display: flex; justify-content: flex-end; align-items: center; gap: 14px; padding: 16px 0;">
            <a href="<?= BASE_URL ?>/dieuchinh/danh_sach.php?loai=<?= urlencode($defaultLoai) ?>" class="btn btn-secondary">
                Hủy bỏ
            </a>
            <button type="submit" class="btn btn-primary" id="btn-submit" style="padding: 12px 28px; font-size: 15px; font-weight: 700; box-shadow: 0 4px 14px rgba(79, 70, 229, 0.35);">
                <i class="fa-solid fa-floppy-disk"></i> Lưu Phiếu Điều Chỉnh Kho
            </button>
        </div>
    </form>
</div>

<!-- Dữ liệu JS để nạp động các mặt hàng -->
<script>
const allProducts = <?= json_encode($sanPhams, JSON_UNESCAPED_UNICODE) ?>;

document.addEventListener('DOMContentLoaded', function() {
    let rowIndex = 1;
    const tbody = document.getElementById('tbody-items');
    const btnAdd = document.getElementById('btn-add-item');
    const btnLoadAll = document.getElementById('btn-load-all-inventory');
    const btnClearAll = document.getElementById('btn-clear-all');
    const totalQtyDisplay = document.getElementById('total-qty-display');
    const totalItemsBadge = document.getElementById('total-items-badge');
    const btnSubmit = document.getElementById('btn-submit');
    const selectLoai = document.getElementById('loai');
    const labelNlq = document.getElementById('label-nlq');
    const inputNlq = document.getElementById('nguoi_lien_quan');
    const impactBanner = document.getElementById('impact-banner');
    const impactText = document.getElementById('impact-text');
    const titleBadge = document.getElementById('title-icon-badge');

    // Cấu hình giao diện theo loại nghiệp vụ
    function updateLoaiTheme() {
        const val = selectLoai.value;
        let color = '#ef4444';
        let bgGradient = 'linear-gradient(135deg, #ef4444, #dc2626)';
        let bannerBg = '#fef2f2';
        let bannerColor = '#991b1b';
        let bannerBorder = '#fecaca';

        if (val === 'huy') {
            labelNlq.innerText = 'Lý do hủy / Người duyệt hủy';
            inputNlq.placeholder = 'Hết hạn sử dụng, ẩm mốc, rơi vỡ...';
            impactText.innerHTML = 'Tác động: Nghiệp vụ <strong>HỦY HÀNG</strong> sẽ <strong>LÀM GIẢM TỒN KHO LÝ THUYẾT</strong> của các mặt hàng bên dưới.';
            color = '#ef4444';
            bgGradient = 'linear-gradient(135deg, #ef4444, #dc2626)';
            bannerBg = '#fef2f2';
            bannerColor = '#991b1b';
            bannerBorder = '#fecaca';
        } else if (val === 'cho_muon') {
            labelNlq.innerText = 'Bên nhận mượn / Đối tác';
            inputNlq.placeholder = 'Tên quán bạn, cơ sở 2, anh Nam...';
            impactText.innerHTML = 'Tác động: Nghiệp vụ <strong>CHO MƯỢN</strong> sẽ <strong>LÀM GIẢM TỒN KHO LÝ THUYẾT</strong> của các mặt hàng bên dưới.';
            color = '#f59e0b';
            bgGradient = 'linear-gradient(135deg, #f59e0b, #d97706)';
            bannerBg = '#fffbeb';
            bannerColor = '#92400e';
            bannerBorder = '#fde68a';
        } else {
            labelNlq.innerText = 'Nguồn cho mượn / Nơi nhận về';
            inputNlq.placeholder = 'Mượn từ cơ sở 2, quán đối tác B...';
            impactText.innerHTML = 'Tác động: Nghiệp vụ <strong>MƯỢN VỀ</strong> sẽ <strong>LÀM TĂNG TỒN KHO LÝ THUYẾT</strong> của các mặt hàng bên dưới.';
            color = '#10b981';
            bgGradient = 'linear-gradient(135deg, #10b981, #059669)';
            bannerBg = '#ecfdf5';
            bannerColor = '#065f46';
            bannerBorder = '#a7f3d0';
        }

        titleBadge.style.background = bgGradient;
        impactBanner.style.background = bannerBg;
        impactBanner.style.color = bannerColor;
        impactBanner.style.borderColor = bannerBorder;
        totalQtyDisplay.style.color = color;

        // Cập nhật màu số lượng của các dòng
        document.querySelectorAll('.input-qty').forEach(inp => {
            inp.style.color = color;
        });
    }

    selectLoai.addEventListener('change', updateLoaiTheme);
    updateLoaiTheme();

    // Tạo HTML options sản phẩm
    let optionsHtml = '<option value="">-- Chọn sản phẩm cần điều chỉnh --</option>';
    allProducts.forEach(p => {
        const star = p.can_kiem_ke == 1 ? '★' : '';
        optionsHtml += `<option value="${p.id}" data-dvt="${escapeHtml(p.don_vi_tinh || 'đv')}">[${escapeHtml(p.ma_sp)}] ${escapeHtml(p.ten_sp)} (${escapeHtml(p.don_vi_tinh || '')} - ${escapeHtml(p.nhom_sp || '')}) ${star}</option>`;
    });

    function escapeHtml(text) {
        if (!text) return '';
        return String(text).replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;").replace(/"/g, "&quot;");
    }

    // Cập nhật nhãn ĐVT khi chọn sản phẩm
    function handleProductChange(selectElem) {
        const selectedOpt = selectElem.options[selectElem.selectedIndex];
        const row = selectElem.closest('.item-row');
        const dvtLabel = row.querySelector('.dvt-label');
        if (selectedOpt && selectedOpt.dataset.dvt) {
            dvtLabel.textContent = selectedOpt.dataset.dvt;
        } else {
            dvtLabel.textContent = 'đv';
        }
        recalculateTotals();
    }

    // Đăng ký sự kiện đổi sản phẩm cho các dòng
    function bindProductSelect(selectElem) {
        // Khởi tạo ô tìm kiếm sản phẩm thông minh
        if (window.initSearchableSelect && !selectElem.searchableInstance) {
            initSearchableSelect(selectElem, {
                placeholder: '🔍 Gõ tên hoặc mã SP để tìm kiếm...',
                autoFocusNext: true
            });
        }

        selectElem.addEventListener('change', function() {
            handleProductChange(this);
        });
    }

    // Tính toán tổng số lượng và số mặt hàng hợp lệ trong thời gian thực
    function recalculateTotals() {
        let totalQty = 0;
        let validItemCount = 0;
        const rows = tbody.querySelectorAll('.item-row');

        rows.forEach((row, idx) => {
            // Cập nhật lại số thứ tự STT
            const sttCell = row.querySelector('.row-stt');
            if (sttCell) sttCell.textContent = idx + 1;

            const selectSp = row.querySelector('.select-sp');
            const inputQty = row.querySelector('.input-qty');

            if (selectSp && inputQty) {
                const spId = parseInt(selectSp.value) || 0;
                const qty = parseFloat(inputQty.value) || 0;

                if (spId > 0 && qty > 0) {
                    validItemCount++;
                    totalQty += qty;
                }
            }
        });

        // Cập nhật hiển thị tổng
        totalQtyDisplay.textContent = new Intl.NumberFormat('vi-VN').format(totalQty);
        totalItemsBadge.innerHTML = `<i class="fa-solid fa-check"></i> ${validItemCount} mặt hàng hợp lệ`;
        
        if (validItemCount > 0) {
            totalItemsBadge.className = 'badge badge-success';
            btnSubmit.innerHTML = `<i class="fa-solid fa-floppy-disk"></i> Lưu Phiếu (${validItemCount} Mặt Hàng - Tổng: ${new Intl.NumberFormat('vi-VN').format(totalQty)})`;
        } else {
            totalItemsBadge.className = 'badge badge-secondary';
            btnSubmit.innerHTML = `<i class="fa-solid fa-floppy-disk"></i> Lưu Phiếu Điều Chỉnh Kho`;
        }

        btnClearAll.style.display = rows.length > 3 ? 'inline-flex' : 'none';
    }

    // Thêm 1 dòng mới vào bảng
    function addNewRow(preselectedId = null, defaultDvt = 'đv') {
        const tr = document.createElement('tr');
        tr.className = 'item-row';
        const color = selectLoai.value === 'muon' ? '#10b981' : (selectLoai.value === 'cho_muon' ? '#f59e0b' : '#ef4444');

        tr.innerHTML = `
            <td style="text-align: center; font-weight: 600; color: var(--text-muted);" class="row-stt"></td>
            <td>
                <select name="items[${rowIndex}][san_pham_id]" class="form-control select-sp" required>
                    ${optionsHtml}
                </select>
            </td>
            <td>
                <div style="display: flex; align-items: center; gap: 6px;">
                    <input type="number" step="any" min="0.01" name="items[${rowIndex}][so_luong]" class="form-control text-right input-qty" placeholder="0" required style="font-weight: 700; color: ${color}; font-size: 15px;">
                    <span class="dvt-label" style="font-size: 12px; font-weight: 600; color: var(--text-muted); min-width: 42px;">${defaultDvt}</span>
                </div>
            </td>
            <td>
                <input type="text" name="items[${rowIndex}][ghi_chu]" class="form-control form-control-sm" placeholder="Hết date, rách vỏ, mượn...">
            </td>
            <td style="text-align: center;">
                <button type="button" class="btn btn-danger btn-sm btn-remove-row" style="padding: 5px 10px;" title="Xóa dòng này">
                    <i class="fa-solid fa-trash-can"></i>
                </button>
            </td>
        `;

        tbody.appendChild(tr);

        const select = tr.querySelector('.select-sp');
        const inputQty = tr.querySelector('.input-qty');
        const btnRemove = tr.querySelector('.btn-remove-row');

        if (preselectedId) {
            select.value = preselectedId;
            handleProductChange(select);
        }

        bindProductSelect(select);

        inputQty.addEventListener('input', recalculateTotals);

        btnRemove.addEventListener('click', function() {
            if (tbody.querySelectorAll('.item-row').length > 1) {
                if (select.searchableInstance) {
                    select.searchableInstance.destroy();
                }
                tr.remove();
                recalculateTotals();
            } else {
                alert('Phiếu điều chỉnh phải có ít nhất 1 dòng sản phẩm!');
            }
        });

        rowIndex++;
        recalculateTotals();
    }

    // Khởi tạo dòng đầu tiên
    const firstRow = tbody.querySelector('.item-row');
    if (firstRow) {
        const firstSelect = firstRow.querySelector('.select-sp');
        const firstQty = firstRow.querySelector('.input-qty');
        const firstRemove = firstRow.querySelector('.btn-remove-row');

        bindProductSelect(firstSelect);
        firstQty.addEventListener('input', recalculateTotals);
        firstRemove.addEventListener('click', function() {
            if (tbody.querySelectorAll('.item-row').length > 1) {
                if (firstSelect.searchableInstance) {
                    firstSelect.searchableInstance.destroy();
                }
                firstRow.remove();
                recalculateTotals();
            } else {
                alert('Phiếu điều chỉnh phải có ít nhất 1 dòng sản phẩm!');
            }
        });
    }

    // Bấm nút Thêm Dòng Mặt Hàng
    btnAdd.addEventListener('click', function() {
        addNewRow();
        // Focus vào input tìm kiếm của dòng mới thêm
        const rows = tbody.querySelectorAll('.item-row');
        const lastRow = rows[rows.length - 1];
        if (lastRow) {
            const searchInput = lastRow.querySelector('.searchable-select-input');
            if (searchInput) {
                searchInput.focus();
            }
        }
    });

    // Nút nạp nhanh tất cả mặt hàng kiểm kê (can_kiem_ke = 1)
    btnLoadAll.addEventListener('click', function() {
        const inventoryItems = allProducts.filter(p => p.can_kiem_ke == 1);
        if (inventoryItems.length === 0) {
            alert('Không tìm thấy mặt hàng nào được đánh dấu cần kiểm kê!');
            return;
        }

        if (tbody.querySelectorAll('.item-row').length > 1) {
            if (!confirm(`Hệ thống sẽ nạp danh sách ${inventoryItems.length} mặt hàng kiểm kê vào bảng. Bạn có muốn tiếp tục?`)) {
                return;
            }
        }

        // Hủy các instance dropdown cũ
        tbody.querySelectorAll('.select-sp').forEach(s => {
            if (s.searchableInstance) s.searchableInstance.destroy();
        });

        tbody.innerHTML = '';
        rowIndex = 0;

        inventoryItems.forEach(item => {
            addNewRow(item.id, item.don_vi_tinh || 'đv');
        });

        // Bỏ thuộc tính required trên các ô số lượng để tiện điền chỉ những món cần điều chỉnh
        tbody.querySelectorAll('.input-qty').forEach(inp => {
            inp.removeAttribute('required');
        });

        recalculateTotals();
    });

    // Nút xóa hết tất cả các dòng
    btnClearAll.addEventListener('click', function() {
        if (confirm('Bạn có chắc muốn xóa tất cả các dòng hiện tại và làm lại từ đầu?')) {
            tbody.querySelectorAll('.select-sp').forEach(s => {
                if (s.searchableInstance) s.searchableInstance.destroy();
            });
            tbody.innerHTML = '';
            rowIndex = 0;
            addNewRow();
        }
    });

    // Xử lý trước khi submit form:
    // Tự động bỏ các dòng không nhập số lượng để form submit mượt mà
    document.getElementById('form-dieu-chinh').addEventListener('submit', function(e) {
        const rows = tbody.querySelectorAll('.item-row');
        let validRows = 0;

        rows.forEach(row => {
            const selectSp = row.querySelector('.select-sp');
            const inputQty = row.querySelector('.input-qty');
            const spId = parseInt(selectSp.value) || 0;
            const qty = parseFloat(inputQty.value) || 0;

            if (spId > 0 && qty > 0) {
                validRows++;
            } else {
                // Tắt required ở các dòng trống để form gửi đi không bị chặn
                selectSp.removeAttribute('required');
                inputQty.removeAttribute('required');
            }
        });

        if (validRows === 0) {
            e.preventDefault();
            alert('Vui lòng nhập số lượng (> 0) cho ít nhất một mặt hàng cần điều chỉnh!');
            return false;
        }
    });

    recalculateTotals();
});
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
