<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (empty($username) || empty($password)) {
        $error = 'Vui lòng nhập đầy đủ tên đăng nhập và mật khẩu!';
    } else {
        $db = getDB();
        $stmt = $db->prepare("SELECT * FROM users WHERE username = :username AND trang_thai = 1 LIMIT 1");
        $stmt->execute(['username' => $username]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['ho_ten'] = $user['ho_ten'];
            $_SESSION['vai_tro'] = $user['vai_tro'];

            setFlash('success', 'Đăng nhập thành công! Chào mừng ' . $user['ho_ten']);
            header("Location: " . BASE_URL . "/dashboard.php");
            exit;
        } else {
            $error = 'Tên đăng nhập hoặc mật khẩu không chính xác!';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng Nhập | Hệ Thống Quản Lý Kho & Kiểm Soát Hàng Hóa</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/login.css">
</head>
<body class="login-body">
    <div class="login-card">
        <div class="login-brand">
            <div class="logo-icon">
                <i class="fa-solid fa-boxes-stacked"></i>
            </div>
            <h1>KIỂM SOÁT HÀNG HÓA</h1>
            <p>Hệ thống tự động lọc số bán & đối chiếu kho</p>
        </div>

        <?php if ($error): ?>
            <div class="alert alert-danger">
                <i class="fa-solid fa-circle-exclamation"></i>
                <span><?= htmlspecialchars($error) ?></span>
            </div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="form-group">
                <label class="form-label" for="username">Tên đăng nhập</label>
                <div style="position: relative;">
                    <input type="text" id="username" name="username" class="form-control" placeholder="admin" required autofocus value="<?= htmlspecialchars($_POST['username'] ?? 'admin') ?>">
                </div>
            </div>

            <div class="form-group">
                <label class="form-label" for="password">Mật khẩu</label>
                <div style="position: relative;">
                    <input type="password" id="password" name="password" class="form-control" placeholder="••••••••" required value="admin123">
                </div>
            </div>

            <button type="submit" class="btn btn-primary" style="width: 100%; margin-top: 10px; padding: 12px;">
                <i class="fa-solid fa-arrow-right-to-bracket"></i> Đăng Nhập
            </button>
        </form>

        <div style="margin-top: 24px; padding-top: 20px; border-top: 1px dashed var(--border-color); text-align: center; font-size: 13px; color: var(--text-muted);">
            <div> <strong></strong> |  <strong></strong></div>
        </div>
    </div>
</body>
</html>
