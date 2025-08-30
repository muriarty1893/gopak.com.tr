<?php
// Admin Kullanıcı Kurulum Scripti
// Bu dosyayı bir kez çalıştırarak admin kullanıcı tablosunu oluşturun

require_once 'config/database.php';

echo "<h2>Gopak Admin Kullanıcı Kurulum</h2>";

try {
    // Admin users tablosunu oluştur
    $createAdminUsersTable = "
        CREATE TABLE IF NOT EXISTS admin_users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(50) UNIQUE NOT NULL,
            password_hash VARCHAR(255) NOT NULL,
            email VARCHAR(100),
            full_name VARCHAR(100),
            role ENUM('admin', 'super_admin') DEFAULT 'admin',
            is_active BOOLEAN DEFAULT TRUE,
            last_login TIMESTAMP NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )
    ";
    
    $pdo->exec($createAdminUsersTable);
    echo "<p>✅ Admin users tablosu oluşturuldu/mevcut</p>";
    
    // Varsayılan admin kullanıcısını kontrol et
    $checkAdmin = $pdo->query("SELECT COUNT(*) FROM admin_users WHERE username = 'admin'");
    $adminCount = $checkAdmin->fetchColumn();
    
    if ($adminCount == 0) {
        // Varsayılan admin kullanıcısı oluştur
        $defaultPassword = 'admin123'; // Bu şifreyi değiştirin!
        $passwordHash = password_hash($defaultPassword, PASSWORD_DEFAULT);
        
        $insertAdmin = $pdo->prepare("
            INSERT INTO admin_users (username, password_hash, email, full_name, role)
            VALUES (?, ?, ?, ?, ?)
        ");
        
        $insertAdmin->execute([
            'admin',
            $passwordHash,
            'admin@gopak.com',
            'Sistem Yöneticisi',
            'super_admin'
        ]);
        
        echo "<p>✅ Varsayılan admin kullanıcısı oluşturuldu</p>";
        echo "<p><strong>Kullanıcı Adı:</strong> admin</p>";
        echo "<p><strong>Şifre:</strong> {$defaultPassword}</p>";
        echo "<p><strong>⚠️ ÖNEMLİ:</strong> Bu şifreyi değiştirmeyi unutmayın!</p>";
    } else {
        echo "<p>✅ Admin kullanıcısı zaten mevcut</p>";
    }
    
    // Mevcut admin kullanıcılarını listele
    $admins = $pdo->query("SELECT id, username, email, full_name, role, is_active, last_login FROM admin_users")->fetchAll();
    
    if (count($admins) > 0) {
        echo "<hr>";
        echo "<h3>Mevcut Admin Kullanıcıları</h3>";
        echo "<table border='1' style='border-collapse: collapse; width: 100%; margin-top: 20px;'>";
        echo "<tr style='background: #f0f0f0;'>";
        echo "<th style='padding: 10px;'>ID</th>";
        echo "<th style='padding: 10px;'>Kullanıcı Adı</th>";
        echo "<th style='padding: 10px;'>E-posta</th>";
        echo "<th style='padding: 10px;'>Ad Soyad</th>";
        echo "<th style='padding: 10px;'>Rol</th>";
        echo "<th style='padding: 10px;'>Durum</th>";
        echo "<th style='padding: 10px;'>Son Giriş</th>";
        echo "</tr>";
        
        foreach ($admins as $admin) {
            echo "<tr>";
            echo "<td style='padding: 10px;'>{$admin['id']}</td>";
            echo "<td style='padding: 10px;'>{$admin['username']}</td>";
            echo "<td style='padding: 10px;'>{$admin['email']}</td>";
            echo "<td style='padding: 10px;'>{$admin['full_name']}</td>";
            echo "<td style='padding: 10px;'>{$admin['role']}</td>";
            echo "<td style='padding: 10px;'>" . ($admin['is_active'] ? 'Aktif' : 'Pasif') . "</td>";
            echo "<td style='padding: 10px;'>" . ($admin['last_login'] ? $admin['last_login'] : 'Hiç giriş yapılmamış') . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
    echo "<hr>";
    echo "<h3>Kurulum Tamamlandı!</h3>";
    echo "<p>Admin kullanıcı sistemi kullanıma hazır.</p>";
    echo "<p><a href='admin_login.php'>Admin giriş sayfasına git</a></p>";
    echo "<p><strong>Güvenlik Notu:</strong> Bu dosyayı güvenlik için sunucudan silmeyi unutmayın.</p>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Hata: " . $e->getMessage() . "</p>";
    echo "<p>Veritabanı bağlantısını ve ayarlarını kontrol edin.</p>";
}
?>
