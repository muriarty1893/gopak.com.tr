<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

// Form verilerini al
$name = trim($_POST['name'] ?? '');
$email = trim($_POST['email'] ?? '');
$phone = trim($_POST['phone'] ?? '');
$message = trim($_POST['message'] ?? '');

// Validasyon
$errors = [];

if (empty($name)) {
    $errors[] = 'İsim gerekli';
}

if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = 'Geçerli bir email gerekli';
}

if (empty($message)) {
    $errors[] = 'Mesaj gerekli';
}

if (!empty($errors)) {
    http_response_code(400);
    echo json_encode(['error' => 'Validation failed', 'details' => $errors]);
    exit;
}

try {
    // Veritabanına kaydet (iletişim tablosu oluşturmanız gerekebilir)
    $stmt = $pdo->prepare("
        INSERT INTO contact_messages (name, email, phone, message, created_at) 
        VALUES (?, ?, ?, ?, NOW())
    ");
    
    $stmt->execute([$name, $email, $phone, $message]);
    
    // Email gönderme (opsiyonel)
    $to = 'info@gopaktr.com'; // Gerçek email adresinizi yazın
    $subject = 'Yeni İletişim Mesajı - ' . $name;
    $email_message = "
        Ad: $name
        Email: $email
        Telefon: $phone
        
        Mesaj:
        $message
    ";
    
    // PHP mail() fonksiyonu (hostinger'da çalışır)
    mail($to, $subject, $email_message, "From: $email");
    
    echo json_encode([
        'success' => true,
        'message' => 'Mesajınız başarıyla gönderildi. En kısa sürede size dönüş yapacağız.'
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'error' => 'Server error',
        'message' => 'Bir hata oluştu. Lütfen daha sonra tekrar deneyiniz.'
    ]);
}
?>
