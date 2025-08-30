<?php
// Admin Panel Setup Script
// Bu dosyayı bir kez çalıştırarak gerekli tabloları oluşturun

require_once 'config/database.php';

echo "<h2>Gopak Admin Panel Kurulum</h2>";

try {
    // Products tablosunu oluştur
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
    echo "<p>✅ Products tablosu oluşturuldu/mevcut</p>";
    
    // Örnek ürünler ekle (eğer tablo boşsa)
    $checkProducts = $pdo->query("SELECT COUNT(*) FROM products");
    $productCount = $checkProducts->fetchColumn();
    
    if ($productCount == 0) {
        $sampleProducts = [
            [
                'name' => 'Standart Bez Çanta',
                'description' => 'Çevre dostu, dayanıklı bez çanta',
                'price' => 15.00,
                'stock_quantity' => 100,
                'category' => 'Standart',
                'is_custom' => false
            ],
            [
                'name' => 'Özel Logo Bez Çanta',
                'description' => 'Markanızın logosu ile özelleştirilmiş bez çanta',
                'price' => 25.00,
                'stock_quantity' => 50,
                'category' => 'Özel',
                'is_custom' => true
            ],
            [
                'name' => 'Premium Kalite Bez Çanta',
                'description' => 'Yüksek kalite malzeme ile üretilmiş premium bez çanta',
                'price' => 35.00,
                'stock_quantity' => 75,
                'category' => 'Premium',
                'is_custom' => false
            ]
        ];
        
        $insertProduct = $pdo->prepare("
            INSERT INTO products (name, description, price, stock_quantity, category, is_custom)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        
        foreach ($sampleProducts as $product) {
            $insertProduct->execute([
                $product['name'],
                $product['description'],
                $product['price'],
                $product['stock_quantity'],
                $product['category'],
                $product['is_custom']
            ]);
        }
        
        echo "<p>✅ Örnek ürünler eklendi</p>";
    }
    
    // Customers tablosunu kontrol et
    $checkCustomers = $pdo->query("SHOW TABLES LIKE 'customers'");
    if ($checkCustomers->rowCount() > 0) {
        echo "<p>✅ Customers tablosu mevcut</p>";
    } else {
        echo "<p>⚠️ Customers tablosu bulunamadı. Sipariş verildiğinde otomatik oluşturulacak.</p>";
    }
    
    // Orders tablosunu kontrol et
    $checkOrders = $pdo->query("SHOW TABLES LIKE 'orders'");
    if ($checkOrders->rowCount() > 0) {
        echo "<p>✅ Orders tablosu mevcut</p>";
    } else {
        echo "<p>⚠️ Orders tablosu bulunamadı. Sipariş verildiğinde otomatik oluşturulacak.</p>";
    }
    
    // Contact messages tablosunu kontrol et
    $checkContact = $pdo->query("SHOW TABLES LIKE 'contact_messages'");
    if ($checkContact->rowCount() > 0) {
        echo "<p>✅ Contact messages tablosu mevcut</p>";
    } else {
        echo "<p>⚠️ Contact messages tablosu bulunamadı.</p>";
    }
    
    echo "<hr>";
    echo "<h3>Kurulum Tamamlandı!</h3>";
    echo "<p>Admin paneli kullanıma hazır. <a href='admin.html'>Admin paneline git</a></p>";
    echo "<p><strong>Not:</strong> Bu dosyayı güvenlik için sunucudan silmeyi unutmayın.</p>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Hata: " . $e->getMessage() . "</p>";
    echo "<p>Veritabanı bağlantısını ve ayarlarını kontrol edin.</p>";
}
?>
