<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../config/database.php';
require_once '../admin_auth.php';

// Admin kimlik doğrulaması
checkAdminAuth();

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

try {
    // Fiyat geçmişi tablosunu oluştur (eğer yoksa)
    $createTable = "
        CREATE TABLE IF NOT EXISTS price_history (
            id INT AUTO_INCREMENT PRIMARY KEY,
            change_type ENUM('increase', 'decrease') NOT NULL,
            percentage_change DECIMAL(5,2) NOT NULL,
            reason TEXT,
            affected_products INT DEFAULT 1,
            product_id INT NULL,
            product_name VARCHAR(255) NULL,
            old_price DECIMAL(10,2) NULL,
            new_price DECIMAL(10,2) NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_created_at (created_at),
            INDEX idx_change_type (change_type),
            INDEX idx_product_id (product_id)
        )
    ";
    
    $pdo->exec($createTable);
    
    // Fiyat geçmişini getir
    $stmt = $pdo->prepare("
        SELECT 
            id,
            change_type,
            percentage_change,
            reason,
            affected_products,
            product_id,
            product_name,
            old_price,
            new_price,
            created_at
        FROM price_history 
        ORDER BY created_at DESC 
        LIMIT 100
    ");
    
    $stmt->execute();
    $history = $stmt->fetchAll();
    
    // İstatistikleri hesapla
    $stats = [
        'total_changes' => count($history),
        'total_increases' => 0,
        'total_decreases' => 0,
        'avg_increase' => 0,
        'avg_decrease' => 0,
        'last_change' => null
    ];
    
    $increaseSum = 0;
    $decreaseSum = 0;
    $increaseCount = 0;
    $decreaseCount = 0;
    
    foreach ($history as $record) {
        if ($record['change_type'] === 'increase') {
            $stats['total_increases']++;
            $increaseSum += $record['percentage_change'];
            $increaseCount++;
        } else {
            $stats['total_decreases']++;
            $decreaseSum += abs($record['percentage_change']);
            $decreaseCount++;
        }
    }
    
    if ($increaseCount > 0) {
        $stats['avg_increase'] = round($increaseSum / $increaseCount, 2);
    }
    
    if ($decreaseCount > 0) {
        $stats['avg_decrease'] = round($decreaseSum / $decreaseCount, 2);
    }
    
    if (count($history) > 0) {
        $stats['last_change'] = $history[0]['created_at'];
    }
    
    echo json_encode([
        'success' => true,
        'history' => $history,
        'stats' => $stats,
        'count' => count($history)
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Fiyat geçmişi yüklenirken hata oluştu: ' . $e->getMessage()
    ]);
}
?>
