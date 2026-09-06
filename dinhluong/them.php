<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../import/sync_bom_sales.php';
requireLogin();

$pageTitle = "Tạo Định Lượng Món Bán";
$error = '';
$db = getDB();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $maMonPos = trim($_POST['ma_mon_pos'] ?? '');
    $tenMonPos = trim($_POST['ten_mon_pos'] ?? '');
    $nvlItems = $_POST['nvl'] ?? [];

    if (empty($tenMonPos)) {
        $error = 'Vui lòng nhập Tên món bán trên POS!';
    } elseif (empty($nvlItems)) {
        $error = 'Vui lòng thêm ít nhất một nguyên vật liệu kiểm kê cho món này!';
    } else {
        try {
            $db->beginTransaction();

            $stmtInsert = $db->prepare("
                INSERT INTO dinh_luong_mon (ma_mon_pos, ten_mon_pos, san_pham_id, so_luong_tieu_hao, ghi_chu)
                VALUES (:ma_pos, :ten_pos, :sp_id, :sl, :ghi_chu)
            ");

            $validCount = 0;
            foreach ($nvlItems as $item) {
                $spId = (int)($item['san_pham_id'] ?? 0);
                $soLuong = (float)str_replace(',', '', $item['so_luong'] ?? 0);
                $ghiChu = trim($item['ghi_chu'] ?? '');

                if ($spId > 0 && $soLuong > 0) {
                    $stmtInsert->execute([
                        'ma_pos' => $maMonPos,
                        'ten_pos' => $tenMonPos,
                        'sp_id' => $spId,
                        'sl' => $soLuong,
                        'ghi_chu' => $ghiChu
                    ]);
                    $validCount++;
                }
            }

            if ($validCount === 0) {
                throw new Exception("Chưa có nguyên vật liệu hợp lệ nào được chọn!");
            }

            $db->commit();

            // Tự động đồng bộ lại các file số bán đã tải lên trước đó
            $syncRes = syncAllUploadedSalesWithBOM();
            $syncedMsg = !empty($syncRes['synced_files']) ? " (Đã tự động cập nhật lại {$syncRes['synced_files']} file số bán trong kho)" : "";

            setFlash('success', "Đã tạo công thức định lượng cho món [{$tenMonPos}] với {$validCount} nguyên vật liệu kiểm kê thành công!{$syncedMsg}");
            header("Location: " . BASE_URL . "/dinhluong/danh_sach.php");
            exit;
        } catch (Exception $e) {
            $db->rollBack();
            $error = 'Lỗi lưu công thức: ' . $e->getMessage();
        }
    }
}

// Lấy danh sách nguyên vật liệu / sản phẩm kiểm kê trong kho (can_kiem_ke = 1)
$inventoryProducts = $db->query("
    SELECT id, ma_sp, ten_sp, don_vi_tinh, nhom_sp 
    FROM san_pham 
    WHERE trang_thai = 1 AND can_kiem_ke = 1 
    ORDER BY nhom_sp ASC, ten_sp ASC
")->fetchAll();

// Lấy danh sách gợi ý món bán trên POS (can_kiem_ke = 0)
$posDishes = $db->query("
    SELECT id, ma_sp, ten_sp, nhom_sp 
    FROM san_pham 
    WHERE trang_thai = 1 AND can_kiem_ke = 0 
    ORDER BY nhom_sp ASC, ten_sp ASC
")->fetchAll();

include __DIR__ . '/../includes/header.php';
?>

<div style="max-width: 850px; margin: 0 auto;">
    <div class="card">
        <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
            <h2 class="card-title" style="font-size: 18px; font-weight: 700; display: flex; align-items: center; gap: 8px;">
                <span style="display: inline-flex; align-items: center; justify-content: center; width: 32px; height: 32px; background: #ecfdf5; color: var(--success); border-radius: 8px;">
                    <i class="fa-solid fa-calculator"></i>
                </span>
                Tạo Công Thức Định Lượng Món Bán
            </h2>
            <a href="<?= BASE_URL ?>/dinhluong/danh_sach.php" class="btn btn-secondary btn-sm">
                <i class="fa-solid fa-arrow-left"></i> Quay lại
            </a>
        </div>
        <div class="card-body">
            <?php if ($error): ?>
                <div class="alert alert-danger">
                    <i class="fa-solid fa-circle-exclamation"></i>
                    <span><?= htmlspecialchars($error) ?></span>
                </div>
            <?php endif; ?>

            <form method="POST" action="" id="form-dinh-luong">
                <!-- Danh sách gợi ý món POS -->
                <datalist id="pos-dishes-list">
                    <?php foreach ($posDishes as $d): ?>
                        <option value="<?= htmlspecialchars($d['ten_sp']) ?>" data-code="<?= htmlspecialchars($d['ma_sp']) ?>">[<?= htmlspecialchars($d['ma_sp']) ?>] <?= htmlspecialchars($d['nhom_sp']) ?></option>
                    <?php endforeach; ?>
                </datalist>

                <!-- Thông tin món bán trên POS -->
                <div style="display: grid; grid-template-columns: 1fr 2fr; gap: 16px; margin-bottom: 24px; background: #f8fafc; padding: 18px; border-radius: var(--border-radius); border: 1px solid var(--border-color);">
                    <div class="form-group" style="margin-bottom: 0;">
                        <label class="form-label" for="ma_mon_pos">Mã món trên POS (Nếu có)</label>
                        <input type="text" id="ma_mon_pos" name="ma_mon_pos" class="form-control" placeholder="Ví dụ: 10010341, MATCHA01" value="<?= htmlspecialchars($_POST['ma_mon_pos'] ?? '') ?>">
                        <small style="font-size: 11px; color: var(--text-muted);">Mã hàng xuất ra trong file Excel bán</small>
                    </div>

                    <div class="form-group" style="margin-bottom: 0;">
                        <label class="form-label" for="ten_mon_pos">Tên món bán trên POS <span style="color: var(--danger);">*</span></label>
                        <input type="text" id="ten_mon_pos" name="ten_mon_pos" list="pos-dishes-list" class="form-control" required placeholder="Gõ để chọn hoặc nhập: Matcha Tây Bắc Mochi, Sandwich..." value="<?= htmlspecialchars($_POST['ten_mon_pos'] ?? '') ?>" style="font-weight: 700; font-size: 15px;">
                        <small style="font-size: 11px; color: var(--text-muted);">Có thể gõ tên chung (vd: <em>Matcha Tây Bắc Mochi</em> sẽ tự khớp cả Size Vừa & Size Lớn!)</small>
                    </div>
                </div>

                <!-- Danh sách nguyên vật liệu kiểm kê bóc tách -->
                <div style="margin-bottom: 14px; display: flex; justify-content: space-between; align-items: center;">
                    <label class="form-label" style="font-size: 15px; font-weight: 700; margin: 0;">
                        <i class="fa-solid fa-cubes-stacked" style="color: var(--primary);"></i>
                        Định Lượng Nguyên Vật Liệu Kiểm Kê Tiêu Hao (Cho 1 Phần Bán)
                    </label>
                    <button type="button" class="btn btn-outline-primary btn-sm" id="btn-add-nvl">
                        <i class="fa-solid fa-plus"></i> Thêm Nguyên Liệu
                    </button>
                </div>

                <div id="nvl-container" style="display: flex; flex-direction: column; gap: 12px; margin-bottom: 24px;">
                    <div class="nvl-row" style="display: grid; grid-template-columns: 3fr 1.5fr 2fr 44px; gap: 10px; align-items: center; background: #fff; padding: 12px; border-radius: 8px; border: 1px solid var(--border-color); box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
                        <div>
                            <select name="nvl[0][san_pham_id]" class="form-control select-nvl" required>
                                <option value="">-- Chọn mặt hàng kiểm kê trong kho --</option>
                                <?php foreach ($inventoryProducts as $p): ?>
                                    <option value="<?= $p['id'] ?>" data-dvt="<?= htmlspecialchars($p['don_vi_tinh']) ?>">
                                        [<?= htmlspecialchars($p['ma_sp']) ?>] <?= htmlspecialchars($p['ten_sp']) ?> (<?= htmlspecialchars($p['don_vi_tinh']) ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div style="display: flex; align-items: center; gap: 6px;">
                            <input type="number" step="any" min="0.001" name="nvl[0][so_luong]" class="form-control text-right" required value="1" placeholder="SL">
                            <span class="dvt-label" style="font-size: 12px; font-weight: 600; color: var(--text-muted); min-width: 40px;">đv</span>
                        </div>
                        <div>
                            <input type="text" name="nvl[0][ghi_chu]" class="form-control" placeholder="Ghi chú (vd: 1 viên, 2 lát...)">
                        </div>
                        <div>
                            <button type="button" class="btn btn-danger btn-sm btn-remove-nvl" style="width: 100%; padding: 8px 0;" title="Xóa dòng">
                                <i class="fa-solid fa-trash"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <div style="display: flex; justify-content: flex-end; gap: 12px; border-top: 1px solid var(--border-color); padding-top: 18px;">
                    <a href="<?= BASE_URL ?>/dinhluong/danh_sach.php" class="btn btn-secondary">Hủy bỏ</a>
                    <button type="submit" class="btn btn-primary" style="padding: 10px 22px; font-weight: 700; box-shadow: 0 4px 12px rgba(79, 70, 229, 0.3);">
                        <i class="fa-solid fa-floppy-disk"></i> Lưu Công Thức Món
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    let rowIndex = 1;
    const container = document.getElementById('nvl-container');
    const btnAdd = document.getElementById('btn-add-nvl');

    const optionsHtml = `
        <option value="">-- Chọn mặt hàng kiểm kê trong kho --</option>
        <?php foreach ($inventoryProducts as $p): ?>
            <option value="<?= $p['id'] ?>" data-dvt="<?= htmlspecialchars($p['don_vi_tinh']) ?>">
                [<?= htmlspecialchars($p['ma_sp']) ?>] <?= htmlspecialchars($p['ten_sp']) ?> (<?= htmlspecialchars($p['don_vi_tinh']) ?>)
            </option>
        <?php endforeach; ?>
    `;

    btnAdd.addEventListener('click', function() {
        const row = document.createElement('div');
        row.className = 'nvl-row';
        row.style.cssText = 'display: grid; grid-template-columns: 3fr 1.5fr 2fr 44px; gap: 10px; align-items: center; background: #fff; padding: 12px; border-radius: 8px; border: 1px solid var(--border-color); box-shadow: 0 1px 3px rgba(0,0,0,0.05);';
        row.innerHTML = `
            <div>
                <select name="nvl[${rowIndex}][san_pham_id]" class="form-control select-nvl" required>
                    ${optionsHtml}
                </select>
            </div>
            <div style="display: flex; align-items: center; gap: 6px;">
                <input type="number" step="any" min="0.001" name="nvl[${rowIndex}][so_luong]" class="form-control text-right" required value="1" placeholder="SL">
                <span class="dvt-label" style="font-size: 12px; font-weight: 600; color: var(--text-muted); min-width: 40px;">đv</span>
            </div>
            <div>
                <input type="text" name="nvl[${rowIndex}][ghi_chu]" class="form-control" placeholder="Ghi chú (vd: 1 viên, 2 lát...)">
            </div>
            <div>
                <button type="button" class="btn btn-danger btn-sm btn-remove-nvl" style="width: 100%; padding: 8px 0;" title="Xóa dòng">
                    <i class="fa-solid fa-trash"></i>
                </button>
            </div>
        `;
        container.appendChild(row);
        bindRowEvents(row);
        rowIndex++;
        const sInput = row.querySelector('.searchable-select-input');
        if (sInput) sInput.focus();
    });

    function bindRowEvents(row) {
        const select = row.querySelector('.select-nvl');
        const dvtLabel = row.querySelector('.dvt-label');
        const btnRemove = row.querySelector('.btn-remove-nvl');

        if (window.initSearchableSelect && !select.searchableInstance) {
            initSearchableSelect(select, {
                placeholder: '🔍 Gõ tên hoặc mã NVL để tìm...',
                autoFocusNext: true
            });
        }

        select.addEventListener('change', function() {
            const opt = select.options[select.selectedIndex];
            const dvt = opt ? opt.getAttribute('data-dvt') : 'đv';
            dvtLabel.textContent = dvt || 'đv';
        });

        btnRemove.addEventListener('click', function() {
            const allRows = container.querySelectorAll('.nvl-row');
            if (allRows.length <= 1) {
                alert('Món phải có ít nhất 1 nguyên vật liệu kiểm kê!');
                return;
            }
            if (select.searchableInstance) {
                select.searchableInstance.destroy();
            }
            row.remove();
        });
    }

    // Bind initial row
    container.querySelectorAll('.nvl-row').forEach(bindRowEvents);
});
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
