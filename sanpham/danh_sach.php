<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';
requireLogin();

$pageTitle = "Danh Sách Sản Phẩm";
$db = getDB();

$tab = $_GET['tab'] ?? 'active'; // 'active' hoặc 'deleted'
$search = trim($_GET['search'] ?? '');
$filterNhom = trim($_GET['nhom'] ?? '');
$filterKiemKe = $_GET['kiem_ke'] ?? '';

$where = [];
$params = [];

if ($tab === 'deleted') {
    $where[] = "sp.trang_thai = 0";
} else {
    $where[] = "sp.trang_thai = 1";
}

if ($search !== '') {
    $where[] = "(sp.ma_sp LIKE :search OR sp.ten_sp LIKE :search)";
    $params['search'] = "%$search%";
}

if ($filterNhom !== '') {
    $where[] = "sp.nhom_sp = :nhom";
    $params['nhom'] = $filterNhom;
}

if ($filterKiemKe !== '') {
    $where[] = "sp.can_kiem_ke = :kiem_ke";
    $params['kiem_ke'] = (int)$filterKiemKe;
}

$whereSql = implode(' AND ', $where);

$sql = "
    SELECT sp.*, COALESCE(td.so_luong, 0) AS ton_dau_hien_tai
    FROM san_pham sp
    LEFT JOIN ton_dau td ON td.san_pham_id = sp.id AND td.ky_kiem_ke = :ky_kiem_ke
    WHERE $whereSql
    ORDER BY sp.nhom_sp ASC, sp.ma_sp ASC
";
$params['ky_kiem_ke'] = date('Y-m');

$stmt = $db->prepare($sql);
$stmt->execute($params);
$sanPhamList = $stmt->fetchAll();

// Lấy danh sách nhóm sản phẩm để lọc
$nhomList = $db->query("SELECT DISTINCT nhom_sp FROM san_pham WHERE nhom_sp != '' ORDER BY nhom_sp ASC")->fetchAll(PDO::FETCH_COLUMN);

// Đếm số sản phẩm đã xóa mềm
$countDeleted = $db->query("SELECT COUNT(*) FROM san_pham WHERE trang_thai = 0")->fetchColumn();

// Lập bản đồ các sản phẩm kiểm kê gốc trong kho để hiển thị liên kết món PLT
require_once __DIR__ . '/../import/filter_inventory_products.php';
$stmtInv = $db->query("SELECT id, ma_sp, ten_sp FROM san_pham WHERE can_kiem_ke = 1 AND trang_thai = 1");
$invCleanMap = [];
while ($inv = $stmtInv->fetch()) {
    $cn = mb_strtolower(cleanDishSizeAndPlatform($inv['ten_sp']), 'UTF-8');
    $invCleanMap[$cn] = $inv;
    $invCleanMap[removeVietnameseTonesHelper($cn)] = $inv;
}

include __DIR__ . '/../includes/header.php';
?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; flex-wrap: wrap; gap: 12px;">
    <!-- Tabs hoạt động / đã xóa -->
    <div style="display: flex; gap: 8px;">
        <a href="?tab=active" class="btn <?= $tab === 'active' ? 'btn-primary' : 'btn-secondary' ?>">
            <i class="fa-solid fa-box-open"></i> Đang Hoạt Động
        </a>
        <a href="?tab=deleted" class="btn <?= $tab === 'deleted' ? 'btn-danger' : 'btn-secondary' ?>">
            <i class="fa-solid fa-trash-can"></i> Đã Xóa Mềm 
            <?php if ($countDeleted > 0): ?>
                <span class="badge" style="background: rgba(255,255,255,0.25); color: inherit; padding: 2px 7px;"><?= $countDeleted ?></span>
            <?php endif; ?>
        </a>
    </div>

    <a href="<?= BASE_URL ?>/sanpham/them.php" class="btn btn-success">
        <i class="fa-solid fa-circle-plus"></i> Thêm Sản Phẩm Mới
    </a>
</div>

<!-- Bộ lọc tìm kiếm -->
<div class="card" style="margin-bottom: 20px;">
    <div class="card-body" style="padding: 16px 20px;">
        <form method="GET" action="" style="display: flex; gap: 12px; flex-wrap: wrap; align-items: flex-end;">
            <input type="hidden" name="tab" value="<?= htmlspecialchars($tab) ?>">
            
            <div style="flex: 2; min-width: 200px;">
                <label class="form-label" style="font-size: 12px;">Tìm kiếm mã hoặc tên</label>
                <input type="text" name="search" class="form-control" placeholder="Nhập mã SP, tên sản phẩm..." value="<?= htmlspecialchars($search) ?>">
            </div>

            <div style="flex: 1; min-width: 150px;">
                <label class="form-label" style="font-size: 12px;">Nhóm sản phẩm</label>
                <select name="nhom" class="form-control">
                    <option value="">-- Tất cả nhóm --</option>
                    <?php foreach ($nhomList as $nhom): ?>
                        <option value="<?= htmlspecialchars($nhom) ?>" <?= $filterNhom === $nhom ? 'selected' : '' ?>>
                            <?= htmlspecialchars($nhom) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div style="flex: 1; min-width: 150px;">
                <label class="form-label" style="font-size: 12px;">Trạng thái kiểm kê</label>
                <select name="kiem_ke" class="form-control">
                    <option value="">-- Tất cả --</option>
                    <option value="1" <?= $filterKiemKe === '1' ? 'selected' : '' ?>>✅ Cần kiểm kê</option>
                    <option value="0" <?= $filterKiemKe === '0' ? 'selected' : '' ?>>❌ Bỏ qua kiểm kê</option>
                </select>
            </div>

            <div>
                <button type="submit" class="btn btn-primary">
                    <i class="fa-solid fa-filter"></i> Lọc
                </button>
                <a href="?tab=<?= $tab ?>" class="btn btn-secondary">
                    <i class="fa-solid fa-rotate-left"></i>
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Bảng danh sách sản phẩm -->
<div class="card">
    <div class="card-header">
        <h2 class="card-title">
            <i class="fa-solid fa-boxes-stacked"></i>
            <?= $tab === 'deleted' ? 'Danh Sách Sản Phẩm Đã Xóa Mềm' : 'Danh Sách Sản Phẩm Hoạt Động' ?> (<?= count($sanPhamList) ?>)
        </h2>
    </div>
    <div class="card-body" style="padding: 0;">
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th style="width: 110px;">Mã SP</th>
                        <th>Tên Sản Phẩm</th>
                        <th>Nhóm Sản Phẩm</th>
                        <th style="text-align: center;">ĐVT</th>
                        <th style="text-align: right;">Tồn Đầu (<?= date('m/Y') ?>)</th>
                        <th style="text-align: center;">Cần Kiểm Kê?</th>
                        <th style="text-align: center; width: 140px;">Thao Tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($sanPhamList)): ?>
                        <tr>
                            <td colspan="7" style="text-align: center; padding: 30px; color: var(--text-muted);">
                                Không tìm thấy sản phẩm nào phù hợp điều kiện lọc.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($sanPhamList as $sp): 
                            $isPlt = preg_match('/^(PLT|GRAB|SHOPEE|BAEMIN)\s+/ui', $sp['ten_sp']) || strcasecmp($sp['nhom_sp'], 'Platform') === 0;
                            $cleanName = cleanDishSizeAndPlatform($sp['ten_sp']);
                            $normName = mb_strtolower($cleanName, 'UTF-8');
                            $normNoTone = removeVietnameseTonesHelper($normName);

                            $linkedParent = null;
                            if ($isPlt && (int)$sp['can_kiem_ke'] === 0) {
                                if (isset($invCleanMap[$normName]) && (int)$invCleanMap[$normName]['id'] !== (int)$sp['id']) {
                                    $linkedParent = $invCleanMap[$normName];
                                } elseif (isset($invCleanMap[$normNoTone]) && (int)$invCleanMap[$normNoTone]['id'] !== (int)$sp['id']) {
                                    $linkedParent = $invCleanMap[$normNoTone];
                                }
                            }
                        ?>
                            <tr>
                                <td><strong style="color: var(--primary);"><?= htmlspecialchars($sp['ma_sp']) ?></strong></td>
                                <td>
                                    <div style="font-weight: 600;"><?= htmlspecialchars($sp['ten_sp']) ?></div>
                                    <?php if ($linkedParent): ?>
                                        <div style="font-size: 11px; color: #4338ca; margin-top: 4px; font-weight: 600; background: #eef2ff; padding: 2px 8px; border-radius: 4px; display: inline-flex; align-items: center; gap: 5px; border: 1px solid #c7d2fe;">
                                            <i class="fa-solid fa-code-merge"></i> Tự động gộp số bán vào: <strong>[<?= htmlspecialchars($linkedParent['ma_sp']) ?>] <?= htmlspecialchars($linkedParent['ten_sp']) ?></strong>
                                        </div>
                                    <?php elseif (!empty($sp['ghi_chu'])): ?>
                                        <div style="font-size: 11px; color: var(--text-muted);"><?= htmlspecialchars($sp['ghi_chu']) ?></div>
                                    <?php endif; ?>
                                </td>
                                <td><span class="badge badge-secondary"><?= htmlspecialchars($sp['nhom_sp']) ?></span></td>
                                <td style="text-align: center;"><?= htmlspecialchars($sp['don_vi_tinh']) ?></td>
                                <td style="text-align: right; font-weight: 600;"><?= formatNumber($sp['ton_dau_hien_tai']) ?></td>
                                <td style="text-align: center;">
                                    <?php if ($sp['can_kiem_ke'] == 1): ?>
                                        <span class="badge badge-success"><i class="fa-solid fa-check"></i> Cần kiểm kê</span>
                                    <?php elseif ($linkedParent): ?>
                                        <span class="badge" style="background: #eef2ff; color: #4338ca; border: 1px solid #c7d2fe;" title="Món bán trên app - Số bán sẽ tự động cộng dồn vào món gốc trong kho">
                                            <i class="fa-solid fa-link"></i> Đã gộp kho
                                        </span>
                                    <?php else: ?>
                                        <span class="badge badge-secondary"><i class="fa-solid fa-xmark"></i> Bỏ qua</span>
                                    <?php endif; ?>
                                </td>
                                <td style="text-align: center;">
                                    <?php if ($tab === 'deleted'): ?>
                                        <!-- Khôi phục sản phẩm -->
                                        <a href="<?= BASE_URL ?>/sanpham/khoi_phuc.php?id=<?= $sp['id'] ?>" class="btn btn-success btn-sm" onclick="return confirm('Bạn có chắc chắn muốn khôi phục sản phẩm [<?= htmlspecialchars($sp['ma_sp']) ?>] về trạng thái hoạt động?');" title="Khôi phục sản phẩm">
                                            <i class="fa-solid fa-trash-can-arrow-up"></i> Khôi Phục
                                        </a>
                                    <?php else: ?>
                                        <div style="display: inline-flex; gap: 6px;">
                                            <a href="<?= BASE_URL ?>/sanpham/chi_tiet.php?id=<?= $sp['id'] ?>" class="btn btn-secondary btn-sm" title="Lịch sử sản phẩm">
                                                <i class="fa-solid fa-eye"></i>
                                            </a>
                                            <a href="<?= BASE_URL ?>/sanpham/sua.php?id=<?= $sp['id'] ?>" class="btn btn-secondary btn-sm" title="Sửa sản phẩm">
                                                <i class="fa-solid fa-pen"></i>
                                            </a>
                                            <a href="<?= BASE_URL ?>/sanpham/xoa.php?id=<?= $sp['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Bạn có chắc muốn xóa mềm sản phẩm này? Lịch sử số bán và báo cáo cũ vẫn sẽ được giữ nguyên an toàn.');" title="Xóa mềm">
                                                <i class="fa-solid fa-trash"></i>
                                            </a>
                                        </div>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
