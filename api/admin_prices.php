<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../config/database.php';
require_once '../admin_auth.php';

// Admin kimlik doğrulaması (GET hariç tüm işlemler için)
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    checkAdminAuth();
}

// GET: Tüm ürün fiyatlarını getir
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    try {
        $stmt = $pdo->prepare("
            SELECT 
                id, 
                name, 
                price, 
                category, 
                bag_type,
                bag_dimensions,
                min_order_quantity,
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
            'message' => 'Ürün fiyatları yüklenirken hata oluştu: ' . $e->getMessage()
        ]);
    }
}

// POST: Toplu fiyat güncelleme (yüzde artış/azalış)
else if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);
    
    if (!$data || !isset($data['percentage_change'])) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Yüzde değişim bilgisi gerekli'
        ]);
        exit;
    }
    
    $percentageChange = (float)$data['percentage_change'];
    $reason = $data['reason'] ?? 'Genel fiyat güncellemesi';
    
    try {
        // Tüm ürünlerin fiyatlarını güncelle
        $stmt = $pdo->prepare("
            UPDATE products 
            SET 
                price = ROUND(price * (1 + ? / 100), 2),
                updated_at = NOW()
        ");
        
        $stmt->execute([$percentageChange]);
        $affectedRows = $stmt->rowCount();
        
        // Fiyat güncelleme geçmişini kaydet
        $historyStmt = $pdo->prepare("
            INSERT INTO price_history (
                change_type, 
                percentage_change, 
                reason, 
                affected_products, 
                created_at
            ) VALUES (?, ?, ?, ?, NOW())
        ");
        
        $historyStmt->execute([
            $percentageChange > 0 ? 'increase' : 'decrease',
            $percentageChange,
            $reason,
            $affectedRows
        ]);
        
        echo json_encode([
            'success' => true,
            'message' => "Tüm ürün fiyatları %{$percentageChange} " . ($percentageChange > 0 ? 'artırıldı' : 'azaltıldı'),
            'affected_products' => $affectedRows,
            'percentage_change' => $percentageChange
        ]);
        
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Toplu fiyat güncellemesi sırasında hata oluştu: ' . $e->getMessage()
        ]);
    }
}

// PUT: Tek ürün fiyat güncelleme
else if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);
    
    if (!$data || !isset($_GET['id']) || !isset($data['price'])) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Ürün ID ve yeni fiyat gerekli'
        ]);
        exit;
    }
    
    $productId = (int)$_GET['id'];
    $newPrice = (float)$data['price'];
    $reason = $data['reason'] ?? 'Tekil fiyat güncellemesi';
    
    if ($newPrice < 0) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Fiyat negatif olamaz'
        ]);
        exit;
    }
    
    try {
        // Eski fiyatı al
        $oldPriceStmt = $pdo->prepare("SELECT price, name FROM products WHERE id = ?");
        $oldPriceStmt->execute([$productId]);
        $oldPriceData = $oldPriceStmt->fetch();
        
        if (!$oldPriceData) {
            http_response_code(404);
            echo json_encode([
                'success' => false,
                'message' => 'Ürün bulunamadı'
            ]);
            exit;
        }
        
        $oldPrice = (float)$oldPriceData['price'];
        $productName = $oldPriceData['name'];
        
        // Fiyatı güncelle
        $stmt = $pdo->prepare("
            UPDATE products 
            SET 
                price = ?,
                updated_at = NOW()
            WHERE id = ?
        ");
        
        $stmt->execute([$newPrice, $productId]);
        
        if ($stmt->rowCount() > 0) {
            // Fiyat değişim geçmişini kaydet
            $percentageChange = (($newPrice - $oldPrice) / $oldPrice) * 100;
            
            $historyStmt = $pdo->prepare("
                INSERT INTO price_history (
                    change_type, 
                    percentage_change, 
                    reason, 
                    affected_products, 
                    product_id,
                    product_name,
                    old_price,
                    new_price,
                    created_at
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())
            ");
            
            $historyStmt->execute([
                $percentageChange > 0 ? 'increase' : 'decrease',
                round($percentageChange, 2),
                $reason,
                1,
                $productId,
                $productName,
                $oldPrice,
                $newPrice
            ]);
            
            echo json_encode([
                'success' => true,
                'message' => "Ürün fiyatı güncellendi: {$oldPrice}₺ → {$newPrice}₺",
                'old_price' => $oldPrice,
                'new_price' => $newPrice,
                'percentage_change' => round($percentageChange, 2)
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Fiyat güncellenemedi'
            ]);
        }
        
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Fiyat güncellenirken hata oluştu: ' . $e->getMessage()
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
