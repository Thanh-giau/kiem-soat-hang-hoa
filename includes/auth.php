<?php
/**
 * Authentication Middleware
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function requireLogin() {
    if (!isset($_SESSION['user_id'])) {
        $loginUrl = BASE_URL . '/login.php';
        header("Location: $loginUrl");
        exit;
    }
}

function currentUser() {
    if (isset($_SESSION['user_id'])) {
        if (empty($_SESSION['ho_ten']) || strpos($_SESSION['ho_ten'], '?') !== false) {
            require_once __DIR__ . '/../config/database.php';
            $db = getDB();
            $stmt = $db->prepare("SELECT ho_ten, vai_tro FROM users WHERE id = ?");
            $stmt->execute([$_SESSION['user_id']]);
            $u = $stmt->fetch();
            if ($u) {
                $_SESSION['ho_ten'] = $u['ho_ten'];
                $_SESSION['vai_tro'] = $u['vai_tro'];
            }
        }
        return [
            'id' => $_SESSION['user_id'],
            'username' => $_SESSION['username'] ?? '',
            'ho_ten' => !empty($_SESSION['ho_ten']) ? $_SESSION['ho_ten'] : 'Quản Trị Viên',
            'vai_tro' => $_SESSION['vai_tro'] ?? 'admin'
        ];
    }
    return null;
}

function isAdmin() {
    return isset($_SESSION['vai_tro']) && $_SESSION['vai_tro'] === 'admin';
}
