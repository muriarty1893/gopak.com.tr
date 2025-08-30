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
    // Aktif ürünleri getir (stok > 0 olanlar)
    $stmt = $pdo->prepare("
        SELECT 
            id, 
            name, 
            description, 
            price, 
            stock_quantity, 
            image_url, 
            category, 
            is_custom 
        FROM products 
        WHERE stock_quantity > 0 
        ORDER BY 
            CASE 
                WHEN category = 'Standart' THEN 1
                WHEN category = 'Özel' THEN 2 
                WHEN category = 'Premium' THEN 3
                ELSE 4
            END,
            name ASC
    ");
    
    $stmt->execute();
    $products = $stmt->fetchAll();
    
    // Ürün verilerini formatla
    $formattedProducts = [];
    foreach ($products as $product) {
        $features = [];
        
        // Kategoriye göre özellikler belirle
        switch ($product['category']) {
            case 'Standart':
                $features = [
                    '✓ Ayarlanabilir boyut',
                    '✓ Çevre dostu',
                    '✓ Ekonomik'
                ];
                break;
            case 'Özel':
                $features = [
                    '✓ Özel logo baskısı',
                    '✓ Ayarlanabilir boyut',
                    '✓ Marka tanıtımı'
                ];
                break;
            case 'Premium':
                $features = [
                    '✓ Premium kalite',
                    '✓ Kalın malzeme',
                    '✓ Uzun ömürlü',
                    '✓ Özel tasarım'
                ];
                break;
            default:
                $features = [
                    '✓ Kaliteli malzeme',
                    '✓ Çevre dostu'
                ];
        }
        
        $formattedProducts[] = [
            'id' => $product['id'],
            'name' => $product['name'],
            'description' => $product['description'],
            'price' => number_format($product['price'], 2),
            'stock_quantity' => $product['stock_quantity'],
            'image_url' => $product['image_url'] ?: 'img/logo_nobg.png', // Default image
            'category' => $product['category'],
            'is_custom' => (bool)$product['is_custom'],
            'features' => $features
        ];
    }
    
    echo json_encode([
        'success' => true,
        'products' => $formattedProducts,
        'count' => count($formattedProducts)
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'error' => 'Database error',
        'message' => 'Ürünler yüklenirken bir hata oluştu.'
    ]);
}
?>
