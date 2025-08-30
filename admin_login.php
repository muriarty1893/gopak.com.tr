<?php
session_start();

// Eğer zaten giriş yapılmışsa admin paneline yönlendir
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header('Location: admin_panel.php');
    exit();
}

// Giriş formu gönderildiğinde
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once 'config/database.php';
    
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    if (!empty($username) && !empty($password)) {
        try {
            // Admin kullanıcılarını kontrol et
            $stmt = $pdo->prepare("SELECT * FROM admin_users WHERE username = ? AND is_active = 1");
            $stmt->execute([$username]);
            $admin = $stmt->fetch();
            
            if ($admin && password_verify($password, $admin['password_hash'])) {
                // Giriş başarılı
                $_SESSION['admin_logged_in'] = true;
                $_SESSION['admin_id'] = $admin['id'];
                $_SESSION['admin_username'] = $admin['id'];
                $_SESSION['admin_role'] = $admin['role'];
                
                // Son giriş zamanını güncelle
                $updateStmt = $pdo->prepare("UPDATE admin_users SET last_login = NOW() WHERE id = ?");
                $updateStmt->execute([$admin['id']]);
                
                header('Location: admin_panel.php'); 
                exit();
            } else {
                $error = "Kullanıcı adı veya şifre hatalı!";
            }
        } catch (Exception $e) {
            $error = "Giriş sırasında hata oluştu: " . $e->getMessage();
        }
    } else {
        $error = "Lütfen tüm alanları doldurun!";
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Girişi - Gopak</title>
    <link rel="stylesheet" href="style.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .login-container {
            background: white;
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.1);
            width: 100%;
            max-width: 400px;
            text-align: center;
        }
        
        .login-logo {
            margin-bottom: 30px;
        }
        
        .login-logo img {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background: #FF6000;
            padding: 15px;
        }
        
        .login-title {
            color: #333;
            font-size: 1.8em;
            font-weight: 700;
            margin-bottom: 10px;
        }
        
        .login-subtitle {
            color: #666;
            font-size: 1em;
            margin-bottom: 30px;
        }
        
        .form-group {
            margin-bottom: 20px;
            text-align: left;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: #333;
        }
        
        .form-group input {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e1e5e9;
            border-radius: 8px;
            font-size: 1em;
            transition: border-color 0.3s ease;
            box-sizing: border-box;
        }
        
        .form-group input:focus {
            outline: none;
            border-color: #FF6000;
        }
        
        .login-btn {
            width: 100%;
            background: #FF6000;
            color: white;
            border: none;
            padding: 15px;
            border-radius: 8px;
            font-size: 1.1em;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.3s ease;
            margin-bottom: 20px;
        }
        
        .login-btn:hover {
            background: #e55600;
        }
        
        .error-message {
            background: #f8d7da;
            color: #721c24;
            padding: 12px;
            border-radius: 6px;
            margin-bottom: 20px;
            border: 1px solid #f5c6cb;
            font-size: 0.9em;
        }
        
        .back-link {
            color: #FF6000;
            text-decoration: none;
            font-weight: 500;
            font-size: 0.9em;
        }
        
        .back-link:hover {
            text-decoration: underline;
        }
        
        .security-note {
            background: #d1ecf1;
            color: #0c5460;
            padding: 15px;
            border-radius: 6px;
            margin-top: 20px;
            border: 1px solid #bee5eb;
            font-size: 0.8em;
            line-height: 1.4;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-logo">
            <div style="width: 80px; height: 80px; border-radius: 50%; background: #FF6000; padding: 15px; margin: 0 auto; display: flex; align-items: center; justify-content: center;">
                <span style="color: white; font-size: 2em; font-weight: bold;">🔐</span>
            </div>
        </div>
        
        <h1 class="login-title">Admin Girişi</h1>
        <p class="login-subtitle">Gopak Admin Paneli'ne erişim</p>
        
        <?php if (isset($error)): ?>
            <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <form method="POST" action="">
            <div class="form-group">
                <label for="username">Kullanıcı Adı</label>
                <input type="text" id="username" name="username" required autocomplete="username">
            </div>
            
            <div class="form-group">
                <label for="password">Şifre</label>
                <input type="password" id="password" name="password" required autocomplete="current-password">
            </div>
            
            <button type="submit" class="login-btn">Giriş Yap</button>
        </form>
        
        <a href="index.html" class="back-link">← Ana Sayfaya Dön</a>
        
        <div class="security-note">
            <strong>🔒 Güvenlik Notu:</strong><br>
            Bu sayfa sadece yetkili admin kullanıcıları içindir. 
            Yetkisiz erişim girişimleri kayıt altına alınır.
        </div>
    </div>
</body>
</html>
