<?php
session_start();

// Admin giriş kontrolü
function checkAdminAuth() {
    if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
        http_response_code(401);
        echo json_encode([
            'success' => false,
            'message' => 'Yetkisiz erişim. Lütfen giriş yapın.',
            'redirect' => 'admin_login.php'
        ]);
        exit();
    }
    
    return [
        'admin_id' => $_SESSION['admin_id'],
        'admin_username' => $_SESSION['admin_username'],
        'admin_role' => $_SESSION['admin_role']
    ];
}

// Admin rol kontrolü
function checkAdminRole($requiredRole = 'admin') {
    $admin = checkAdminAuth();
    
    if ($admin['admin_role'] !== $requiredRole && $admin['admin_role'] !== 'super_admin') {
        http_response_code(403);
        echo json_encode([
            'success' => false,
            'message' => 'Bu işlem için yetkiniz bulunmuyor.',
            'required_role' => $requiredRole
        ]);
        exit();
    }
    
    return $admin;
}

// Logout fonksiyonu
function adminLogout() {
    session_destroy();
    return [
        'success' => true,
        'message' => 'Başarıyla çıkış yapıldı.',
        'redirect' => 'admin_login.php'
    ];
}

// Admin giriş durumu kontrolü
function isAdminLoggedIn() {
    return isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;
}

// Admin bilgilerini getir
function getAdminInfo() {
    if (!isAdminLoggedIn()) {
        return null;
    }
    
    return [
        'id' => $_SESSION['admin_id'],
        'username' => $_SESSION['admin_username'],
        'role' => $_SESSION['admin_role']
    ];
}
?>
