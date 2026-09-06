<?php
/**
 * Global Header Component
 */
if (!defined('BASE_URL')) {
    require_once __DIR__ . '/functions.php';
}
$user = currentUser();
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($pageTitle) ? htmlspecialchars($pageTitle) . " | " : "" ?>Hệ Thống Quản Lý Kho & Kiểm Soát Hàng Hóa</title>
    <!-- FontAwesome 6 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <!-- Main Style -->
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css?v=<?= time() ?>">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/responsive.css?v=<?= time() ?>">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/searchable-select.css?v=<?= time() ?>">
    <?php if (isset($extraCss)): ?>
        <?php foreach ($extraCss as $css): ?>
            <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/<?= $css ?>?v=<?= time() ?>">
        <?php endforeach; ?>
    <?php endif; ?>
</head>
<body>
<div class="app-container">
    <?php include __DIR__ . '/sidebar.php'; ?>
    <div class="sidebar-backdrop" id="sidebar-backdrop"></div>
    <div class="main-wrapper">
        <header class="topbar">
            <div class="topbar-left">
                <button type="button" class="menu-toggle-btn" aria-label="Toggle Menu">
                    <i class="fa-solid fa-bars"></i>
                </button>
                <h1 class="page-title"><?= htmlspecialchars($pageTitle ?? 'Quản Lý Kho') ?></h1>
            </div>
            <div class="topbar-right">
                <div class="badge badge-info topbar-date-badge">
                    <i class="fa-regular fa-calendar"></i> <span class="date-text"><?= date('d/m/Y') ?></span>
                </div>
                <button type="button" class="btn btn-secondary btn-sm topbar-mobile-btn" onclick="openModal('modal-mobile-qr')" title="Kết nối điện thoại">
                    <i class="fa-solid fa-mobile-screen-button" style="color: var(--primary);"></i> <span class="mobile-text">Điện thoại</span>
                </button>
                <a href="<?= BASE_URL ?>/logout.php" class="btn btn-secondary btn-sm topbar-logout-btn" title="Đăng xuất">
                    <i class="fa-solid fa-right-from-bracket"></i> <span class="logout-text">Đăng xuất</span>
                </a>
            </div>
        </header>
        <main class="content-body">
            <?php $flash = getFlash(); if ($flash): ?>
                <div class="alert alert-<?= $flash['type'] ?>">
                    <i class="fa-solid <?= $flash['type'] === 'success' ? 'fa-circle-check' : ($flash['type'] === 'warning' ? 'fa-triangle-exclamation' : 'fa-circle-exclamation') ?>"></i>
                    <span><?= htmlspecialchars($flash['message']) ?></span>
                </div>
            <?php endif; ?>
