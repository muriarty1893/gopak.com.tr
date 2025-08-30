<?php
// GitHub Webhook Handler for Auto-Deploy
// Bu dosya GitHub'dan gelen push event'lerini handle eder ve otomatik deploy yapar

// Webhook secret key (GitHub'da ayarladığınız secret ile aynı olmalı)
$secret = 'gopak_webhook_secret_2024'; // Bu değeri GitHub'da ayarladığınız secret ile değiştirin

// GitHub'dan gelen payload'ı al
$payload = file_get_contents('php://input');
$signature = $_SERVER['HTTP_X_HUB_SIGNATURE_256'] ?? '';

// Webhook signature'ını doğrula
if (!verifySignature($payload, $signature, $secret)) {
    http_response_code(403);
    die('Invalid signature');
}

// JSON payload'ı decode et
$data = json_decode($payload, true);

// Sadece push event'lerini handle et
if ($_SERVER['HTTP_X_GITHUB_EVENT'] === 'push') {
    // Main branch'e push yapıldıysa deploy et
    if ($data['ref'] === 'refs/heads/main') {
        deploySite();
        echo "Deployment triggered successfully!";
    } else {
        echo "Push detected but not on main branch. No deployment needed.";
    }
} else {
    echo "Event received: " . $_SERVER['HTTP_X_GITHUB_EVENT'];
}

/**
 * GitHub webhook signature'ını doğrula
 */
function verifySignature($payload, $signature, $secret) {
    if (empty($signature)) {
        return false;
    }
    
    $expectedSignature = 'sha256=' . hash_hmac('sha256', $payload, $secret);
    return hash_equals($expectedSignature, $signature);
}

/**
 * Siteyi deploy et
 */
function deploySite() {
    // Log dosyasına deploy bilgisini yaz
    $logMessage = date('Y-m-d H:i:s') . " - Deployment triggered by GitHub webhook\n";
    file_put_contents('deploy.log', $logMessage, FILE_APPEND);
    
    // Git pull komutunu çalıştır (Hostinger'da git repository kurulu olmalı)
    $output = [];
    $returnCode = 0;
    
    // Git pull komutu
    exec('cd /home/user/public_html && git pull origin main 2>&1', $output, $returnCode);
    
    // Sonucu log'a yaz
    $logMessage = date('Y-m-d H:i:s') . " - Git pull result: " . implode("\n", $output) . "\n";
    file_put_contents('deploy.log', $logMessage, FILE_APPEND);
    
    // Cache temizleme (gerekirse)
    if (function_exists('opcache_reset')) {
        opcache_reset();
    }
    
    // Deploy sonrası işlemler (gerekirse)
    // - Composer install
    // - NPM build
    // - Database migration
    // - Cache clear
    
    return $returnCode === 0;
}
?>
