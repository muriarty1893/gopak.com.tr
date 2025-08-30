<?php
// Admin Güvenlik Sistemi Test Scripti
// Bu dosya güvenlik sisteminin düzgün çalışıp çalışmadığını test eder

echo "<h2>🔐 Admin Güvenlik Sistemi Test Sonuçları</h2>";
echo "<style>
    .test-result { padding: 10px; margin: 5px 0; border-radius: 5px; }
    .success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
    .error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
    .warning { background: #fff3cd; color: #856404; border: 1px solid #ffeaa7; }
    .info { background: #d1ecf1; color: #0c5460; border: 1px solid #bee5eb; }
</style>";

$tests = [];
$overall_score = 0;

// Test 1: Session başlatma
echo "<h3>📋 Test 1: Session Yönetimi</h3>";
session_start();
if (session_status() === PHP_SESSION_ACTIVE) {
    echo "<div class='test-result success'>✅ Session başarıyla başlatıldı</div>";
    $tests['session'] = true;
    $overall_score += 20;
} else {
    echo "<div class='test-result error'>❌ Session başlatılamadı</div>";
    $tests['session'] = false;
}

// Test 2: Admin auth dosyası kontrolü
echo "<h3>📋 Test 2: Admin Auth Dosyası</h3>";
if (file_exists('admin_auth.php')) {
    echo "<div class='test-result success'>✅ admin_auth.php dosyası mevcut</div>";
    $tests['admin_auth_file'] = true;
    $overall_score += 20;
} else {
    echo "<div class='test-result error'>❌ admin_auth.php dosyası bulunamadı</div>";
    $tests['admin_auth_file'] = false;
}

// Test 3: Admin login dosyası kontrolü
echo "<h3>📋 Test 3: Admin Login Dosyası</h3>";
if (file_exists('admin_login.php')) {
    echo "<div class='test-result success'>✅ admin_login.php dosyası mevcut</div>";
    $tests['admin_login_file'] = true;
    $overall_score += 20;
} else {
    echo "<div class='test-result error'>❌ admin_login.php dosyası bulunamadı</div>";
    $tests['admin_login_file'] = false;
}

// Test 4: Admin panel dosyası kontrolü
echo "<h3>📋 Test 4: Admin Panel Dosyası</h3>";
if (file_exists('admin_panel.php')) {
    echo "<div class='test-result success'>✅ admin_panel.php dosyası mevcut</div>";
    $tests['admin_panel_file'] = true;
    $overall_score += 20;
} else {
    echo "<div class='test-result error'>❌ admin_panel.php dosyası bulunamadı</div>";
    $tests['admin_panel_file'] = false;
}

// Test 5: .htaccess dosyası kontrolü
echo "<h3>📋 Test 5: .htaccess Dosyası</h3>";
if (file_exists('.htaccess')) {
    echo "<div class='test-result success'>✅ .htaccess dosyası mevcut</div>";
    $tests['htaccess_file'] = true;
    $overall_score += 20;
} else {
    echo "<div class='test-result error'>❌ .htaccess dosyası bulunamadı</div>";
    $tests['htaccess_file'] = false;
}

// Test 6: Veritabanı bağlantısı
echo "<h3>📋 Test 6: Veritabanı Bağlantısı</h3>";
if (file_exists('config/database.php')) {
    try {
        require_once 'config/database.php';
        if (isset($pdo)) {
            echo "<div class='test-result success'>✅ Veritabanı bağlantısı başarılı</div>";
            $tests['database_connection'] = true;
            $overall_score += 20;
        } else {
            echo "<div class='test-result error'>❌ Veritabanı bağlantısı başarısız</div>";
            $tests['database_connection'] = false;
        }
    } catch (Exception $e) {
        echo "<div class='test-result error'>❌ Veritabanı hatası: " . $e->getMessage() . "</div>";
        $tests['database_connection'] = false;
    }
} else {
    echo "<div class='test-result error'>❌ database.php dosyası bulunamadı</div>";
    $tests['database_connection'] = false;
}

// Test 7: Admin users tablosu kontrolü
echo "<h3>📋 Test 7: Admin Users Tablosu</h3>";
if (isset($pdo)) {
    try {
        $stmt = $pdo->query("SHOW TABLES LIKE 'admin_users'");
        if ($stmt->rowCount() > 0) {
            echo "<div class='test-result success'>✅ admin_users tablosu mevcut</div>";
            $tests['admin_users_table'] = true;
            $overall_score += 20;
            
            // Admin kullanıcı sayısını kontrol et
            $userCount = $pdo->query("SELECT COUNT(*) FROM admin_users")->fetchColumn();
            echo "<div class='test-result info'>ℹ️ Toplam admin kullanıcı sayısı: $userCount</div>";
        } else {
            echo "<div class='test-result warning'>⚠️ admin_users tablosu bulunamadı. admin_user_setup.php çalıştırın.</div>";
            $tests['admin_users_table'] = false;
        }
    } catch (Exception $e) {
        echo "<div class='test-result error'>❌ Tablo kontrol hatası: " . $e->getMessage() . "</div>";
        $tests['admin_users_table'] = false;
    }
} else {
    echo "<div class='test-result error'>❌ Veritabanı bağlantısı olmadığı için tablo kontrol edilemiyor</div>";
    $tests['admin_users_table'] = false;
}

// Test 8: Güvenlik dosyaları kontrolü
echo "<h3>📋 Test 8: Güvenlik Dosyaları</h3>";
$security_files = [
    'admin_auth.php' => 'Admin kimlik doğrulama',
    'admin_login.php' => 'Admin giriş sayfası',
    'admin_panel.php' => 'Güvenli admin paneli',
    '.htaccess' => 'Apache güvenlik kuralları',
    'api/admin_logout.php' => 'Çıkış API\'si'
];

$security_score = 0;
foreach ($security_files as $file => $description) {
    if (file_exists($file)) {
        echo "<div class='test-result success'>✅ $description: $file</div>";
        $security_score += 20;
    } else {
        echo "<div class='test-result error'>❌ $description: $file bulunamadı</div>";
    }
}

$tests['security_files'] = ($security_score >= 80);
if ($tests['security_files']) {
    $overall_score += 20;
}

// Genel sonuç
echo "<hr>";
echo "<h3>📊 Test Sonuçları Özeti</h3>";

$passed_tests = array_sum($tests);
$total_tests = count($tests);

echo "<div class='test-result info'>";
echo "<strong>Toplam Test:</strong> $total_tests<br>";
echo "<strong>Başarılı Test:</strong> $passed_tests<br>";
echo "<strong>Başarı Oranı:</strong> " . round(($passed_tests / $total_tests) * 100, 1) . "%<br>";
echo "<strong>Genel Puan:</strong> $overall_score/200";
echo "</div>";

if ($overall_score >= 160) {
    echo "<div class='test-result success'>🎉 Tebrikler! Admin güvenlik sistemi başarıyla kuruldu.</div>";
} elseif ($overall_score >= 120) {
    echo "<div class='test-result warning'>⚠️ Admin güvenlik sistemi kısmen kuruldu. Eksik olan kısımları tamamlayın.</div>";
} else {
    echo "<div class='test-result error'>❌ Admin güvenlik sistemi kurulumunda sorunlar var. Lütfen eksik olan kısımları tamamlayın.</div>";
}

// Öneriler
echo "<hr>";
echo "<h3>💡 Öneriler</h3>";

if (!$tests['admin_users_table']) {
    echo "<div class='test-result warning'>⚠️ admin_user_setup.php dosyasını çalıştırarak admin kullanıcı tablosunu oluşturun.</div>";
}

if (!$tests['database_connection']) {
    echo "<div class='test-result warning'>⚠️ config/database.php dosyasındaki veritabanı bilgilerini kontrol edin.</div>";
}

if (!$tests['security_files']) {
    echo "<div class='test-result warning'>⚠️ Eksik güvenlik dosyalarını oluşturun.</div>";
}

echo "<div class='test-result info'>";
echo "<strong>Sonraki Adımlar:</strong><br>";
echo "1. admin_user_setup.php ile admin kullanıcı oluşturun<br>";
echo "2. admin_login.php ile giriş yapın<br>";
echo "3. admin_panel.php ile admin paneline erişin<br>";
echo "4. Kurulum dosyalarını güvenlik için silin";
echo "</div>";

echo "<hr>";
echo "<p><small>Bu test dosyası güvenlik için silinebilir.</small></p>";
?>
