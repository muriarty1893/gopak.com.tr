<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../config/database.php';
require_once '../admin_auth.php';

// Admin kimlik doğrulaması (tüm işlemler için)
checkAdminAuth();

// GET: Tüm müşterileri getir
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    try {
        // Müşterileri sipariş sayılarıyla birlikte getir
        $stmt = $pdo->prepare("
            SELECT 
                c.id,
                c.first_name,
                c.last_name,
                c.email,
                c.phone,
                c.address,
                c.created_at,
                c.updated_at,
                COUNT(o.id) as total_orders,
                COALESCE(SUM(o.total), 0) as total_spent
            FROM customers c
            LEFT JOIN orders o ON c.id = o.customer_id
            GROUP BY c.id
            ORDER BY c.created_at DESC
        ");
        
        $stmt->execute();
        $customers = $stmt->fetchAll();
        
        echo json_encode([
            'success' => true,
            'customers' => $customers,
            'count' => count($customers)
        ]);
        
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Müşteriler yüklenirken hata oluştu: ' . $e->getMessage()
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
