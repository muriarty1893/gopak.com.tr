<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../config/database.php';
require_once '../admin_auth.php';

// Admin kimlik doğrulaması (GET hariç tüm işlemler için)
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    checkAdminAuth();
}

// GET: Tüm ürünleri getir
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    try {
        $stmt = $pdo->prepare("
            SELECT 
                id, 
                name, 
                description, 
                price, 
                stock_quantity, 
                image_url, 
                category, 
                is_custom,
                bag_type,
                bag_dimensions,
                min_order_quantity,
                bag_description,
                has_custom_print,
                created_at,
                updated_at
            FROM products 
            ORDER BY 
                CASE 
                    WHEN bag_type = '3D Çanta (Yan Körüklü)' THEN 1
                    WHEN bag_type = 'Düz Çanta (Yan Körüksüz)' THEN 2
                    WHEN category = 'Standart' THEN 3
                    WHEN category = 'Özel' THEN 4 
                    WHEN category = 'Premium' THEN 5
                    ELSE 6
                END,
                name ASC
        ");
        
        $stmt->execute();
        $products = $stmt->fetchAll();
        
        echo json_encode([
            'success' => true,
            'products' => $products,
            'count' => count($products)
        ]);
        
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Ürünler yüklenirken hata oluştu: ' . $e->getMessage()
        ]);
    }
}

// POST: Yeni ürün ekle
else if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);
    
    if (!$data) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Geçersiz JSON verisi'
        ]);
        exit;
    }
    
    // Gerekli alanları kontrol et
    $required_fields = ['name', 'price', 'stock_quantity'];
    foreach ($required_fields as $field) {
        if (empty($data[$field])) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => "Gerekli alan eksik: $field"
            ]);
            exit;
        }
    }
    
    try {
        // Products tablosunu oluştur (eğer yoksa)
        $createProductsTable = "
            CREATE TABLE IF NOT EXISTS products (
                id INT AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(255) NOT NULL,
                description TEXT,
                price DECIMAL(10,2) NOT NULL,
                stock_quantity INT NOT NULL DEFAULT 0,
                image_url VARCHAR(500),
                category VARCHAR(100) DEFAULT 'Standart',
                is_custom BOOLEAN DEFAULT FALSE,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            )
        ";
        
        $pdo->exec($createProductsTable);
        
        // Yeni ürünü ekle
        $stmt = $pdo->prepare("
            INSERT INTO products (
                name, description, price, stock_quantity, 
                image_url, category, is_custom, bag_type, bag_dimensions, 
                min_order_quantity, bag_description, has_custom_print, created_at
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
        ");
        
        $stmt->execute([
            $data['name'],
            $data['description'] ?? '',
            (float)$data['price'],
            (int)$data['stock_quantity'],
            $data['image_url'] ?? '',
            $data['category'] ?? 'Standart',
            isset($data['is_custom']) ? (bool)$data['is_custom'] : false,
            $data['bag_type'] ?? null,
            $data['bag_dimensions'] ?? null,
            !empty($data['min_order_quantity']) ? (int)$data['min_order_quantity'] : null,
            $data['bag_description'] ?? null,
            isset($data['has_custom_print']) ? (bool)$data['has_custom_print'] : false
        ]);
        
        $productId = $pdo->lastInsertId();
        
        echo json_encode([
            'success' => true,
            'message' => 'Ürün başarıyla eklendi',
            'product_id' => $productId
        ]);
        
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Ürün eklenirken hata oluştu: ' . $e->getMessage()
        ]);
    }
}

// PUT: Ürün güncelle
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
    
    $productId = (int)$_GET['id'];
    
    // Gerekli alanları kontrol et
    $required_fields = ['name', 'price', 'stock_quantity'];
    foreach ($required_fields as $field) {
        if (empty($data[$field])) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => "Gerekli alan eksik: $field"
            ]);
            exit;
        }
    }
    
    try {
        $stmt = $pdo->prepare("
            UPDATE products 
            SET 
                name = ?,
                description = ?,
                price = ?,
                stock_quantity = ?,
                image_url = ?,
                category = ?,
                is_custom = ?,
                bag_type = ?,
                bag_dimensions = ?,
                min_order_quantity = ?,
                bag_description = ?,
                has_custom_print = ?,
                updated_at = NOW()
            WHERE id = ?
        ");
        
        $stmt->execute([
            $data['name'],
            $data['description'] ?? '',
            (float)$data['price'],
            (int)$data['stock_quantity'],
            $data['image_url'] ?? '',
            $data['category'] ?? 'Standart',
            isset($data['is_custom']) ? (bool)$data['is_custom'] : false,
            $data['bag_type'] ?? null,
            $data['bag_dimensions'] ?? null,
            !empty($data['min_order_quantity']) ? (int)$data['min_order_quantity'] : null,
            $data['bag_description'] ?? null,
            isset($data['has_custom_print']) ? (bool)$data['has_custom_print'] : false,
            $productId
        ]);
        
        if ($stmt->rowCount() > 0) {
            echo json_encode([
                'success' => true,
                'message' => 'Ürün başarıyla güncellendi'
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Ürün bulunamadı'
            ]);
        }
        
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Ürün güncellenirken hata oluştu: ' . $e->getMessage()
        ]);
    }
}

// DELETE: Ürün sil
else if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    if (!isset($_GET['id'])) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Ürün ID gerekli'
        ]);
        exit;
    }
    
    $productId = (int)$_GET['id'];
    
    try {
        // Önce bu ürünle ilgili sipariş var mı kontrol et
        $checkOrders = $pdo->prepare("
            SELECT COUNT(*) FROM orders 
            WHERE product_type = (SELECT name FROM products WHERE id = ?)
        ");
        $checkOrders->execute([$productId]);
        $orderCount = $checkOrders->fetchColumn();
        
        if ($orderCount > 0) {
            echo json_encode([
                'success' => false,
                'message' => 'Bu ürünle ilgili siparişler bulunduğu için silinemez'
            ]);
            exit;
        }
        
        // Ürünü sil
        $stmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
        $stmt->execute([$productId]);
        
        if ($stmt->rowCount() > 0) {
            echo json_encode([
                'success' => true,
                'message' => 'Ürün başarıyla silindi'
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Ürün bulunamadı'
            ]);
        }
        
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Ürün silinirken hata oluştu: ' . $e->getMessage()
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
