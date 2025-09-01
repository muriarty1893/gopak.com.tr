<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

try {
    // Çanta boyutlarını getir
    $stmt = $pdo->prepare("
        SELECT 
            id,
            category,
            size_name,
            dimensions,
            min_quantity,
            base_price,
            description,
            is_active
        FROM products 
        WHERE bag_type IS NOT NULL 
        AND is_active = 1
        ORDER BY 
            CASE 
                WHEN bag_type = '3D Çanta (Yan Körüklü)' THEN 1
                WHEN bag_type = 'Düz Çanta (Yan Körüksüz)' THEN 2
                ELSE 3
            END,
            min_quantity ASC
    ");
    
    $stmt->execute();
    $sizes = $stmt->fetchAll();
    
    // Verileri formatla
    $formattedSizes = [];
    foreach ($sizes as $size) {
        $formattedSizes[] = [
            'id' => $size['id'],
            'category' => $size['category'],
            'size_name' => $size['size_name'] ?: $size['name'],
            'dimensions' => $size['bag_dimensions'] ?: $size['dimensions'],
            'min_quantity' => (int)$size['min_order_quantity'] ?: (int)$size['min_quantity'],
            'base_price' => (float)$size['price'] ?: (float)$size['base_price'],
            'description' => $size['bag_description'] ?: $size['description'],
            'is_active' => (bool)$size['is_active']
        ];
    }
    
    echo json_encode([
        'success' => true,
        'sizes' => $formattedSizes,
        'count' => count($formattedSizes)
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Boyutlar yüklenirken hata oluştu: ' . $e->getMessage()
    ]);
}
?>
