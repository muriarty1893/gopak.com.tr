<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../config/database.php';
require_once '../admin_auth.php';

// Admin kimlik doğrulaması (GET hariç tüm işlemler için)
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    checkAdminAuth();
}

// GET: Tüm siparişleri getir
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    try {
        // Siparişleri müşteri bilgileriyle birlikte getir
        $stmt = $pdo->prepare("
            SELECT 
                o.id,
                o.order_number,
                o.customer_id,
                o.product_type,
                o.product_color,
                o.product_size,
                o.quantity,
                o.unit_price,
                o.size_multiplier,
                o.subtotal,
                o.discount,
                o.total,
                o.status,
                o.notes,
                o.created_at,
                o.updated_at,
                CONCAT(c.first_name, ' ', c.last_name) as customer_name,
                c.email as customer_email,
                c.phone as customer_phone
            FROM orders o
            LEFT JOIN customers c ON o.customer_id = c.id
            ORDER BY o.created_at DESC
        ");
        
        $stmt->execute();
        $orders = $stmt->fetchAll();
        
        echo json_encode([
            'success' => true,
            'orders' => $orders,
            'count' => count($orders)
        ]);
        
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Siparişler yüklenirken hata oluştu: ' . $e->getMessage()
        ]);
    }
}

// PUT: Sipariş durumunu güncelle
else if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);
    
    if (!$data || !isset($_GET['id'])) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Geçersiz veri veya ID'
        ]);
        exit;
    }
    
    $orderId = (int)$_GET['id'];
    $newStatus = $data['status'] ?? '';
    
    if (!in_array($newStatus, ['pending', 'confirmed', 'production', 'shipped', 'delivered', 'cancelled'])) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Geçersiz durum'
        ]);
        exit;
    }
    
    try {
        $stmt = $pdo->prepare("
            UPDATE orders 
            SET status = ?, updated_at = NOW() 
            WHERE id = ?
        ");
        
        $stmt->execute([$newStatus, $orderId]);
        
        if ($stmt->rowCount() > 0) {
            echo json_encode([
                'success' => true,
                'message' => 'Sipariş durumu başarıyla güncellendi'
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Sipariş bulunamadı'
            ]);
        }
        
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Durum güncellenirken hata oluştu: ' . $e->getMessage()
        ]);
    }
}

// DELETE: Siparişi sil (iptal et)
else if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    if (!isset($_GET['id'])) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Sipariş ID gerekli'
        ]);
        exit;
    }
    
    $orderId = (int)$_GET['id'];
    
    try {
        // Siparişi silmek yerine durumunu iptal olarak işaretle
        $stmt = $pdo->prepare("
            UPDATE orders 
            SET status = 'cancelled', updated_at = NOW() 
            WHERE id = ?
        ");
        
        $stmt->execute([$orderId]);
        
        if ($stmt->rowCount() > 0) {
            echo json_encode([
                'success' => true,
                'message' => 'Sipariş başarıyla iptal edildi'
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Sipariş bulunamadı'
            ]);
        }
        
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Sipariş iptal edilirken hata oluştu: ' . $e->getMessage()
        ]);
    }
}

// Desteklenmeyen HTTP metodu
else {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'message' => 'Desteklenmeyen HTTP metodu'
    ]);
}
?>
