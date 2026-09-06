<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';
requireLogin();

$pageTitle = "Nhập Hàng Vào Kho (Nhiều Mã Hàng)";
$error = '';
$db = getDB();
$user = currentUser();

// Lấy danh sách toàn bộ sản phẩm đang hoạt động
$sanPhams = $db->query("
    SELECT id, ma_sp, ten_sp, don_vi_tinh, nhom_sp, can_kiem_ke 
    FROM san_pham 
    WHERE trang_thai = 1 
    ORDER BY can_kiem_ke DESC, nhom_sp ASC, ten_sp ASC
")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ngayNhap = trim($_POST['ngay_nhap'] ?? date('Y-m-d'));
    $nhaCungCap = trim($_POST['nha_cung_cap'] ?? '');
    $ghiChuChung = trim($_POST['ghi_chu_chung'] ?? '');
    $items = $_POST['items'] ?? [];

    if (empty($ngayNhap)) {
        $error = 'Vui lòng chọn ngày nhập hàng!';
    } elseif (empty($items)) {
        $error = 'Vui lòng thêm ít nhất một mặt hàng nhập vào phiếu!';
    } else {
        try {
            $db->beginTransaction();

            $stmt = $db->prepare("
                INSERT INTO nhap_hang (san_pham_id, ngay_nhap, so_luong, nha_cung_cap, ghi_chu, nguoi_nhap_id)
                VALUES (:sp_id, :ngay_nhap, :so_luong, :ncc, :ghi_chu, :user_id)
            ");

            $validCount = 0;
            $totalQty = 0;

            foreach ($items as $item) {
                $spId = (int)($item['san_pham_id'] ?? 0);
                $soLuong = (float)str_replace(',', '', $item['so_luong'] ?? 0);
                $noteItem = trim($item['ghi_chu'] ?? '');

                // Gộp ghi chú chung & ghi chú riêng nếu có
                $finalNote = $ghiChuChung;
                if (!empty($noteItem)) {
                    $finalNote = !empty($finalNote) ? ($finalNote . ' | ' . $noteItem) : $noteItem;
                }

                if ($spId > 0 && $soLuong > 0) {
                    $stmt->execute([
                        'sp_id' => $spId,
                        'ngay_nhap' => $ngayNhap,
                        'so_luong' => $soLuong,
                        'ncc' => $nhaCungCap,
                        'ghi_chu' => $finalNote,
                        'user_id' => $user['id']
                    ]);
                    $validCount++;
                    $totalQty += $soLuong;
                }
            }

            if ($validCount === 0) {
                throw new Exception("Vui lòng nhập số lượng lớn hơn 0 cho ít nhất một mặt hàng!");
            }

            $db->commit();
            setFlash('success', "Đã tạo phiếu nhập thành công cho {$validCount} mặt hàng (tổng cộng " . formatNumber($totalQty) . ") ngày " . formatDate($ngayNhap) . "!");
            header("Location: " . BASE_URL . "/nhaphang/danh_sach.php");
            exit;
        } catch (Exception $e) {
            $db->rollBack();
            $error = 'Lỗi lưu phiếu nhập hàng: ' . $e->getMessage();
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
                <span style="display: inline-flex; align-items: center; justify-content: center; width: 38px; height: 38px; background: linear-gradient(135deg, #10b981, #059669); color: #fff; border-radius: 10px; font-size: 18px; box-shadow: 0 4px 10px rgba(16, 185, 129, 0.3);">
                    <i class="fa-solid fa-truck-ramp-box"></i>
                </span>
                Tạo Phiếu Nhập Hàng (Nhiều Mặt Hàng)
            </h2>
            <div style="font-size: 13px; color: var(--text-secondary); margin-top: 4px;">
                Cho phép nhập cùng lúc nhiều mã sản phẩm khác nhau trong một đợt giao nhận của nhà cung cấp/xưởng.
            </div>
        </div>
        <a href="<?= BASE_URL ?>/nhaphang/danh_sach.php" class="btn btn-secondary btn-sm">
            <i class="fa-solid fa-arrow-left"></i> Quay lại danh sách
        </a>
    </div>

    <?php if ($error): ?>
        <div class="alert alert-danger" style="margin-bottom: 20px;">
            <i class="fa-solid fa-circle-exclamation"></i>
            <span><?= htmlspecialchars($error) ?></span>
        </div>
    <?php endif; ?>

    <form method="POST" action="" id="form-nhap-hang">
        <!-- 1. THÔNG TIN CHUNG CỦA ĐỢT NHẬP HÀNG -->
        <div class="card" style="margin-bottom: 20px;">
            <div class="card-header" style="background: #f8fafc; border-bottom: 1px solid var(--border-color);">
                <h3 class="card-title" style="font-size: 15px; font-weight: 700; color: var(--text-primary);">
                    <i class="fa-solid fa-file-invoice" style="color: var(--primary);"></i>
                    Thông Tin Chung Đợt Giao Nhận
                </h3>
            </div>
            <div class="card-body">
                <div style="display: grid; grid-template-columns: 1fr 1.5fr 1.5fr; gap: 16px;">
                    <div class="form-group" style="margin-bottom: 0;">
                        <label class="form-label" for="ngay_nhap">Ngày nhập hàng <span style="color: var(--danger);">*</span></label>
                        <input type="date" id="ngay_nhap" name="ngay_nhap" class="form-control" required value="<?= htmlspecialchars($_POST['ngay_nhap'] ?? date('Y-m-d')) ?>" style="font-weight: 600;">
                    </div>

                    <div class="form-group" style="margin-bottom: 0;">
                        <label class="form-label" for="nha_cung_cap">Nhà cung cấp / Đơn vị giao</label>
                        <input type="text" id="nha_cung_cap" name="nha_cung_cap" class="form-control" placeholder="Tên xưởng bánh, cty nước suối, NCC..." value="<?= htmlspecialchars($_POST['nha_cung_cap'] ?? '') ?>">
                    </div>

                    <div class="form-group" style="margin-bottom: 0;">
                        <label class="form-label" for="ghi_chu_chung">Ghi chú chung phiếu nhập</label>
                        <input type="text" id="ghi_chu_chung" name="ghi_chu_chung" class="form-control" placeholder="Giao ca sáng, bù đơn thiếu hôm qua..." value="<?= htmlspecialchars($_POST['ghi_chu_chung'] ?? '') ?>">
                    </div>
                </div>
            </div>
        </div>

        <!-- 2. BẢNG CHI TIẾT CÁC MẶT HÀNG NHẬP -->
        <div class="card" style="margin-bottom: 24px;">
            <div class="card-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px;">
                <div>
                    <h3 class="card-title" style="font-size: 15px; font-weight: 700;">
                        <i class="fa-solid fa-boxes-stacked" style="color: var(--success);"></i>
                        Danh Sách Mặt Hàng Nhập Kho
                    </h3>
                </div>
                <div style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
                    <button type="button" class="btn btn-outline-primary btn-sm" id="btn-load-all-inventory" title="Nạp nhanh tất cả mặt hàng kiểm kê vào bảng">
                        <i class="fa-solid fa-bolt"></i> Nạp Hàng Kiểm Kê
                    </button>
                    <button type="button" class="btn btn-success btn-sm" id="btn-add-item">
                        <i class="fa-solid fa-plus"></i> + Thêm Dòng Mặt Hàng
                    </button>
                </div>
            </div>

            <div class="card-body" style="padding: 0;">
                <div class="table-responsive">
                    <table class="table" style="margin-bottom: 0;" id="table-items">
                        <thead style="background: #f8fafc;">
                            <tr>
                                <th style="width: 45px; text-align: center;">STT</th>
                                <th>Chọn Mặt Hàng Nhập <span style="color: var(--danger);">*</span></th>
                                <th style="width: 190px; text-align: right;">Số Lượng Nhập <span style="color: var(--danger);">*</span></th>
                                <th style="width: 250px;">Ghi Chú Dòng (HSD, lô...)</th>
                                <th style="width: 60px; text-align: center;">Xóa</th>
                            </tr>
                        </thead>
                        <tbody id="tbody-items">
                            <!-- Hàng nhập đầu tiên -->
                            <tr class="item-row">
                                <td style="text-align: center; font-weight: 600; color: var(--text-muted);" class="row-stt">1</td>
                                <td>
                                    <select name="items[0][san_pham_id]" class="form-control select-sp" required>
                                        <option value="">-- Chọn sản phẩm cần nhập --</option>
                                        <?php foreach ($sanPhams as $sp): ?>
                                            <option value="<?= $sp['id'] ?>" data-dvt="<?= htmlspecialchars($sp['don_vi_tinh']) ?>">
                                                [<?= htmlspecialchars($sp['ma_sp']) ?>] <?= htmlspecialchars($sp['ten_sp']) ?> (<?= htmlspecialchars($sp['don_vi_tinh']) ?> - <?= htmlspecialchars($sp['nhom_sp']) ?>) <?= $sp['can_kiem_ke'] ? '★' : '' ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </td>
                                <td>
                                    <div style="display: flex; align-items: center; gap: 6px;">
                                        <input type="number" step="any" min="0.01" name="items[0][so_luong]" class="form-control text-right input-qty" placeholder="0" required style="font-weight: 700; color: var(--success); font-size: 15px;">
                                        <span class="dvt-label" style="font-size: 12px; font-weight: 600; color: var(--text-muted); min-width: 42px;">đv</span>
                                    </div>
                                </td>
                                <td>
                                    <input type="text" name="items[0][ghi_chu]" class="form-control form-control-sm" placeholder="Ghi chú (tùy chọn)">
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
                                    TỔNG CỘNG ĐỢT NHẬP:
                                </td>
                                <td style="text-align: right;">
                                    <div style="font-weight: 800; font-size: 18px; color: var(--success);" id="total-qty-display">0</div>
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
            <a href="<?= BASE_URL ?>/nhaphang/danh_sach.php" class="btn btn-secondary">
                Hủy bỏ
            </a>
            <button type="submit" class="btn btn-primary" id="btn-submit" style="padding: 12px 28px; font-size: 15px; font-weight: 700; box-shadow: 0 4px 14px rgba(79, 70, 229, 0.35);">
                <i class="fa-solid fa-floppy-disk"></i> Lưu Phiếu Nhập Hàng
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
    const totalQtyDisplay = document.getElementById('total-qty-display');
    const totalItemsBadge = document.getElementById('total-items-badge');
    const btnSubmit = document.getElementById('btn-submit');

    // Tạo HTML các thẻ option sản phẩm
    let optionsHtml = '<option value="">-- Chọn sản phẩm cần nhập --</option>';
    allProducts.forEach(p => {
        const star = p.can_kiem_ke == 1 ? '★' : '';
        optionsHtml += `<option value="${p.id}" data-dvt="${escapeHtml(p.don_vi_tinh || 'đv')}">[${escapeHtml(p.ma_sp)}] ${escapeHtml(p.ten_sp)} (${escapeHtml(p.don_vi_tinh || '')} - ${escapeHtml(p.nhom_sp || '')}) ${star}</option>`;
    });

    // Hàm tạo 1 dòng HTML mới
    function createRow(index, selectedId = '', qty = '', note = '') {
        const tr = document.createElement('tr');
        tr.className = 'item-row';
        tr.innerHTML = `
            <td style="text-align: center; font-weight: 600; color: var(--text-muted);" class="row-stt">${index + 1}</td>
            <td>
                <select name="items[${index}][san_pham_id]" class="form-control select-sp" required>
                    ${optionsHtml}
                </select>
            </td>
            <td>
                <div style="display: flex; align-items: center; gap: 6px;">
                    <input type="number" step="any" min="0" name="items[${index}][so_luong]" class="form-control text-right input-qty" placeholder="0" value="${qty}" required style="font-weight: 700; color: var(--success); font-size: 15px;">
                    <span class="dvt-label" style="font-size: 12px; font-weight: 600; color: var(--text-muted); min-width: 42px;">đv</span>
                </div>
            </td>
            <td>
                <input type="text" name="items[${index}][ghi_chu]" class="form-control form-control-sm" placeholder="Ghi chú (tùy chọn)" value="${escapeHtml(note)}">
            </td>
            <td style="text-align: center;">
                <button type="button" class="btn btn-danger btn-sm btn-remove-row" style="padding: 5px 10px;" title="Xóa dòng này">
                    <i class="fa-solid fa-trash-can"></i>
                </button>
            </td>
        `;

        if (selectedId) {
            const select = tr.querySelector('.select-sp');
            select.value = selectedId;
            const opt = select.options[select.selectedIndex];
            if (opt) {
                tr.querySelector('.dvt-label').textContent = opt.getAttribute('data-dvt') || 'đv';
            }
        }

        bindRowEvents(tr);
        return tr;
    }

    // Gắn sự kiện cho dòng
    function bindRowEvents(tr) {
        const select = tr.querySelector('.select-sp');
        const dvtLabel = tr.querySelector('.dvt-label');
        const inputQty = tr.querySelector('.input-qty');
        const btnRemove = tr.querySelector('.btn-remove-row');

        // Khởi tạo ô tìm kiếm thông minh cho sản phẩm
        if (window.initSearchableSelect && !select.searchableInstance) {
            initSearchableSelect(select, {
                placeholder: '🔍 Gõ tên hoặc mã SP để tìm kiếm...',
                autoFocusNext: true
            });
        }

        select.addEventListener('change', function() {
            const opt = select.options[select.selectedIndex];
            dvtLabel.textContent = opt ? (opt.getAttribute('data-dvt') || 'đv') : 'đv';
            recalcTotals();
        });

        inputQty.addEventListener('input', recalcTotals);

        btnRemove.addEventListener('click', function() {
            const allRows = tbody.querySelectorAll('.item-row');
            if (allRows.length <= 1) {
                alert('Phiếu nhập phải có ít nhất 1 mặt hàng!');
                return;
            }
            if (select.searchableInstance) {
                select.searchableInstance.destroy();
            }
            tr.remove();
            reindexRows();
            recalcTotals();
        });
    }

    // Đánh lại số thứ tự STT
    function reindexRows() {
        const rows = tbody.querySelectorAll('.item-row');
        rows.forEach((r, idx) => {
            r.querySelector('.row-stt').textContent = idx + 1;
        });
    }

    // Tính tổng số lượng và số mặt hàng
    function recalcTotals() {
        const rows = tbody.querySelectorAll('.item-row');
        let totalQty = 0;
        let validItemCount = 0;

        rows.forEach(r => {
            const sel = r.querySelector('.select-sp');
            const qtyVal = parseFloat(r.querySelector('.input-qty').value) || 0;
            if (sel.value && qtyVal > 0) {
                totalQty += qtyVal;
                validItemCount++;
            }
        });

        totalQtyDisplay.textContent = totalQty.toLocaleString('vi-VN', { maximumFractionDigits: 2 });
        totalItemsBadge.innerHTML = `<i class="fa-solid fa-check"></i> ${validItemCount} mặt hàng hợp lệ`;
        btnSubmit.innerHTML = `<i class="fa-solid fa-floppy-disk"></i> Lưu Phiếu Nhập (${validItemCount} Mặt Hàng)`;
    }

    // Nút thêm 1 dòng trắng
    btnAdd.addEventListener('click', function() {
        const newRow = createRow(rowIndex);
        tbody.appendChild(newRow);
        rowIndex++;
        reindexRows();
        recalcTotals();
        const searchInput = newRow.querySelector('.searchable-select-input');
        if (searchInput) {
            searchInput.focus();
        }
    });

    // Nút nạp nhanh tất cả các mặt hàng kiểm kê (can_kiem_ke = 1)
    btnLoadAll.addEventListener('click', function() {
        const inventoryItems = allProducts.filter(p => p.can_kiem_ke == 1);
        if (inventoryItems.length === 0) {
            alert('Không có mặt hàng kiểm kê nào trong danh mục!');
            return;
        }

        const firstSelect = tbody.querySelector('.select-sp');
        if (tbody.querySelectorAll('.item-row').length > 1 || (firstSelect && firstSelect.value !== '')) {
            if (!confirm(`Bạn có muốn nạp sẵn toàn bộ ${inventoryItems.length} mặt hàng kiểm kê vào bảng để nhập nhanh không? (Dòng cũ sẽ được thay thế)`)) {
                return;
            }
        }

        // Hủy các instance dropdown cũ trước khi làm trống bảng
        tbody.querySelectorAll('.select-sp').forEach(s => {
            if (s.searchableInstance) s.searchableInstance.destroy();
        });

        tbody.innerHTML = '';
        rowIndex = 0;

        inventoryItems.forEach(item => {
            const row = createRow(rowIndex, item.id, '');
            // Với nạp hàng loạt, cho phép trường số lượng không required để chỉ lưu những món có gõ số lượng
            row.querySelector('.input-qty').removeAttribute('required');
            tbody.appendChild(row);
            rowIndex++;
        });

        reindexRows();
        recalcTotals();
    });

    // Xử lý thông minh khi Submit form
    document.getElementById('form-nhap-hang').addEventListener('submit', function(e) {
        let validRows = 0;
        const rows = tbody.querySelectorAll('.item-row');
        rows.forEach(r => {
            const sel = r.querySelector('.select-sp');
            const qty = parseFloat(r.querySelector('.input-qty').value) || 0;
            if (sel.value && qty > 0) {
                validRows++;
            }
        });

        if (validRows === 0) {
            e.preventDefault();
            alert('Vui lòng chọn sản phẩm và nhập số lượng lớn hơn 0 cho ít nhất một mặt hàng!');
            return;
        }

        // Tự động gỡ bỏ thuộc tính required ở các dòng trống hoặc số lượng = 0 để trình duyệt cho phép submit
        rows.forEach(r => {
            const sel = r.querySelector('.select-sp');
            const qty = parseFloat(r.querySelector('.input-qty').value) || 0;
            if (!sel.value || qty <= 0) {
                r.querySelector('.select-sp').removeAttribute('required');
                r.querySelector('.input-qty').removeAttribute('required');
            }
        });
    });

    // Escape HTML helper
    function escapeHtml(text) {
        if (!text) return '';
        return String(text)
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#039;");
    }

    // Gắn sự kiện dòng đầu tiên
    tbody.querySelectorAll('.item-row').forEach(bindRowEvents);
});
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
