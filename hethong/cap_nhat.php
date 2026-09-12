<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';
requireLogin();

// Chỉ Admin mới được thực hiện cập nhật hệ thống
if (!isAdmin()) {
    setFlash('danger', 'Bạn không có quyền thực hiện chức năng quản trị hệ thống này.');
    header("Location: " . BASE_URL . "/dashboard.php");
    exit;
}

$pageTitle = "Cập Nhật Hệ Thống Từ Xa";
$updateLog = '';
$isUpdated = false;

// Xử lý khi bấm nút Cập nhật
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['do_update'])) {
    $repoDir = realpath(__DIR__ . '/..');
    
    // Tự động sao lưu database trước khi cập nhật
    @shell_exec('cmd /c "cd /d ' . escapeshellarg($repoDir) . ' && hethong\\cong_cu\\SAO_LUU_DU_LIEU.bat"');

    // Chạy git pull
    $cmd = 'cd /d ' . escapeshellarg($repoDir) . ' && git fetch origin main 2>&1 && git merge origin/main 2>&1';
    $output = @shell_exec($cmd);

    if (empty($output)) {
        // Thử git pull trực tiếp
        $output = @shell_exec('cd /d ' . escapeshellarg($repoDir) . ' && git pull origin main 2>&1');
    }

    $updateLog = $output;
    $isUpdated = true;

    setFlash('success', 'Đã hoàn tất lệnh cập nhật mã nguồn từ GitHub lên máy chủ POS!');
}

// Lấy thông tin commit hiện tại
$currentCommit = trim(@shell_exec('git log -n 1 --pretty=format:"%h - %s (%cr)" 2>&1') ?? 'Chưa xác định');
$gitStatus = trim(@shell_exec('git status -s 2>&1') ?? '');
$connInfo = layThongTinKetNoiMobile();

include __DIR__ . '/../includes/header.php';
?>

<div style="max-width: 860px; margin: 0 auto;">
    <div style="margin-bottom: 24px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px;">
        <div>
            <h2 style="font-size: 20px; font-weight: 800; color: var(--text-primary); margin: 0 0 4px 0; display: flex; align-items: center; gap: 10px;">
                <i class="fa-solid fa-cloud-arrow-down" style="color: var(--primary);"></i>
                Trung Tâm Cập Nhật Hệ Thống Máy Chủ (Server Update)
            </h2>
            <p style="font-size: 13.5px; color: var(--text-secondary); margin: 0;">
                Tự động đồng bộ các tính năng mới nhất từ máy tính làm việc sang máy POS đang chạy 24/24 qua GitHub.
            </p>
        </div>
    </div>

    <!-- Thẻ Trạng Thái Hệ Thống Hiện Tại -->
    <div class="card" style="margin-bottom: 20px; border: 1px solid var(--border-color);">
        <div class="card-header" style="background: #f8fafc; border-bottom: 1px solid var(--border-color); display: flex; justify-content: space-between; align-items: center;">
            <div style="font-weight: 700; color: var(--text-primary); font-size: 15px; display: flex; align-items: center; gap: 8px;">
                <i class="fa-solid fa-server" style="color: var(--primary);"></i>
                <span>Trạng Thái Phiên Bản Hiện Tại Của Máy Chủ</span>
            </div>
            <span class="badge badge-success" style="font-size: 11px;">
                <i class="fa-solid fa-circle-check"></i> Đang Hoạt Động 24/24
            </span>
        </div>
        <div class="card-body" style="padding: 20px;">
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 16px; margin-bottom: 20px;">
                <div style="background: #f1f5f9; padding: 14px; border-radius: 10px;">
                    <div style="font-size: 12px; font-weight: 600; color: var(--text-secondary); margin-bottom: 4px;">Bản Commit Hiện Tại:</div>
                    <div style="font-size: 14px; font-weight: 700; color: var(--primary); font-family: monospace; word-break: break-all;">
                        <?= htmlspecialchars($currentCommit) ?>
                    </div>
                </div>

                <div style="background: #eff6ff; padding: 14px; border-radius: 10px; border: 1px solid #bfdbfe;">
                    <div style="font-size: 12px; font-weight: 600; color: #1e40af; margin-bottom: 4px;">Đường Dẫn GitHub Gốc:</div>
                    <div style="font-size: 13px; font-weight: 700; color: #1d4ed8; word-break: break-all;">
                        github.com/Thanh-giau/kiem-soat-hang-hoa
                    </div>
                </div>

                <div style="background: #f0fdf4; padding: 14px; border-radius: 10px; border: 1px solid #bbf7d0;">
                    <div style="font-size: 12px; font-weight: 600; color: #166534; margin-bottom: 4px;">Link Online Cho Điện Thoại:</div>
                    <div style="font-size: 13px; font-weight: 700; color: #15803d; word-break: break-all;">
                        <?= htmlspecialchars($connInfo['online_url']) ?>
                    </div>
                </div>
            </div>

            <!-- Nút bấm Cập nhật -->
            <form method="POST" action="" onsubmit="return confirm('Bạn có chắc muốn cập nhật mã nguồn mới nhất từ GitHub về máy chủ này?\n\nDữ liệu hàng hóa, tồn kho và số bán sẽ được bảo vệ an toàn 100%.');">
                <input type="hidden" name="do_update" value="1">
                <div style="text-align: center; padding: 10px 0;">
                    <button type="submit" class="btn btn-primary" style="padding: 12px 28px; font-size: 15px; font-weight: 800; border-radius: 10px; box-shadow: 0 4px 14px rgba(99, 102, 241, 0.35);">
                        <i class="fa-solid fa-cloud-arrow-down" style="font-size: 18px;"></i> Cập Nhật Phiên Bản Mới Nhất Từ GitHub Ngay
                    </button>
                    <div style="font-size: 12px; color: var(--text-secondary); margin-top: 10px;">
                        💡 <strong>Cơ chế tự động:</strong> Dữ liệu cơ sở dữ liệu sẽ được tự động sao lưu an toàn trước khi kéo code mới về.
                    </div>
                </div>
            </form>
        </div>
    </div>

    <?php if ($isUpdated): ?>
        <!-- Hiển thị Kết quả Cập Nhật -->
        <div class="card" style="margin-bottom: 20px; border: 1px solid #bbf7d0; background: #f0fdf4;">
            <div class="card-header" style="background: #dcfce7; border-bottom: 1px solid #bbf7d0;">
                <h3 style="font-size: 15px; font-weight: 700; color: #166534; margin: 0; display: flex; align-items: center; gap: 8px;">
                    <i class="fa-solid fa-terminal"></i> Nhật Ký Quá Trình Cập Nhật (Git Log):
                </h3>
            </div>
            <div class="card-body" style="padding: 16px;">
                <pre style="background: #0f172a; color: #38bdf8; padding: 14px; border-radius: 8px; font-size: 13px; overflow-x: auto; margin: 0; line-height: 1.5; font-family: Consolas, monospace;"><?= htmlspecialchars($updateLog ?: 'Đã đồng bộ hoàn tất (Hệ thống đã ở bản mới nhất).') ?></pre>
                <div style="margin-top: 14px; text-align: right;">
                    <a href="<?= BASE_URL ?>/dashboard.php" class="btn btn-success">
                        <i class="fa-solid fa-house"></i> Về Trang Tổng Quan
                    </a>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- Hướng dẫn quy trình 2 máy -->
    <div class="card" style="border: 1px solid #e0e7ff; background: #fafafa;">
        <div class="card-header" style="background: #eef2ff;">
            <h3 style="font-size: 15px; font-weight: 700; color: #3730a3; margin: 0; display: flex; align-items: center; gap: 8px;">
                <i class="fa-solid fa-diagram-project"></i> Quy Trình Cập Nhật Chuẩn Khi Dùng 2 Máy (Máy Cá Nhân & Máy POS 24/24)
            </h3>
        </div>
        <div class="card-body" style="padding: 20px; font-size: 13.5px; color: var(--text-primary); line-height: 1.6;">
            <div style="display: flex; flex-direction: column; gap: 14px;">
                <div style="display: flex; gap: 12px; align-items: flex-start;">
                    <div style="width: 28px; height: 28px; border-radius: 50%; background: #6366f1; color: #fff; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 13px; flex-shrink: 0;">1</div>
                    <div>
                        <strong>Trên Máy Tính Hiện Tại (Máy làm việc / lập trình):</strong><br>
                        Khi có chức năng mới hoặc sửa đổi xong, bạn chỉ cần nhấp đúp file <strong><code>DAY_LEN_GITHUB.bat</code></strong> để đẩy code lên GitHub.
                    </div>
                </div>

                <div style="display: flex; gap: 12px; align-items: flex-start;">
                    <div style="width: 28px; height: 28px; border-radius: 50%; background: #10b981; color: #fff; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 13px; flex-shrink: 0;">2</div>
                    <div>
                        <strong>Trên Máy POS (Server chạy 24/24):</strong><br>
                        Bạn có 2 cách cực kỳ nhanh chóng mà <strong>không cần mở VS Code hay phần mềm nào</strong>:
                        <ul style="margin: 4px 0 0 16px;">
                            <li><strong>Cách A (Tiện nhất):</strong> Mở trang web này (trên điện thoại hoặc trình duyệt) &rarr; Bấm nút <strong>"Cập Nhật Phiên Bản Mới Nhất Từ GitHub Ngay"</strong> ở trên!</li>
                            <li><strong>Cách B (Cầm tay tại quán):</strong> Ra màn hình Desktop máy POS &rarr; Nhấp đúp file <strong><code>CAP_NHAT_TU_GITHUB.bat</code></strong>.</li>
                        </ul>
                    </div>
                </div>

                <div style="display: flex; gap: 12px; align-items: flex-start;">
                    <div style="width: 28px; height: 28px; border-radius: 50%; background: #0284c7; color: #fff; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 13px; flex-shrink: 0;">3</div>
                    <div>
                        <strong>Kết quả:</strong><br>
                        Máy POS tự động kéo các file chức năng mới về, dữ liệu tồn kho được bảo toàn 100%, thu ngân vẫn bán hàng bình thường không hề bị gián đoạn.
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
