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
                name,
                bag_type,
                bag_dimensions,
                min_order_quantity,
                price,
                bag_description,
                description
        FROM products 
        WHERE bag_type IS NOT NULL 
        ORDER BY 
            CASE 
                WHEN bag_type = '3D Çanta (Yan Körüklü)' THEN 1
                WHEN bag_type = 'Düz Çanta (Yan Körüksüz)' THEN 2
                ELSE 3
            END,
            min_order_quantity ASC
        ");
        
        $stmt->execute();
        $sizes = $stmt->fetchAll();
        
        // Verileri formatla
        $formattedSizes = [];
        foreach ($sizes as $size) {
            $formattedSizes[] = [
                'id' => $size['id'],
                'category' => $size['bag_type'],
                'size_name' => $size['name'],
                'dimensions' => $size['bag_dimensions'],
                'min_quantity' => (int)$size['min_order_quantity'],
                'base_price' => (float)$size['price'],
                'description' => $size['bag_description'] ?: $size['description'],
                'is_active' => true
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
