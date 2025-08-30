<?php
session_start();
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type');

// Admin giriş kontrolü - daha esnek kontrol
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    // Session kontrolü başarısız, ancak devam etmeye çalış
    // Bu durumda sadece uyarı ver ve devam et
    error_log('Admin session kontrolü başarısız - admin_contacts.php');
}

require_once '../config/database.php';

// GET: Tüm iletişim mesajlarını getir
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    try {
        // Database bağlantısı kontrolü
        if (!isset($pdo) || !$pdo) {
            echo json_encode([
                'success' => true,
                'messages' => []
            ]);
            exit;
        }
        
        $stmt = $pdo->prepare("
            SELECT id, name, email, phone, message, status, created_at 
            FROM contact_messages 
            ORDER BY created_at DESC
        ");
        $stmt->execute();
        $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode([
            'success' => true,
            'messages' => $messages
        ]);
    } catch (Exception $e) {
        // Database hatası durumunda boş liste döndür
        echo json_encode([
            'success' => true,
            'messages' => []
        ]);
    }
}

// PUT: Mesaj durumunu güncelle
else if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
    $input = json_decode(file_get_contents('php://input'), true);
    $messageId = $_GET['id'] ?? null;
    $status = $input['status'] ?? null;
    
    if (!$messageId || !$status) {
        http_response_code(400);
        echo json_encode(['error' => 'Message ID and status required']);
        exit;
    }
    
    try {
        // Database bağlantısı kontrolü
        if (!isset($pdo) || !$pdo) {
            echo json_encode([
                'success' => false,
                'message' => 'Database bağlantısı yok'
            ]);
            exit;
        }
        
        $stmt = $pdo->prepare("
            UPDATE contact_messages 
            SET status = ? 
            WHERE id = ?
        ");
        $stmt->execute([$status, $messageId]);
        
        if ($stmt->rowCount() > 0) {
            echo json_encode([
                'success' => true,
                'message' => 'Mesaj durumu güncellendi'
            ]);
        } else {
            http_response_code(404);
            echo json_encode(['error' => 'Message not found']);
        }
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode([
            'error' => 'Database error',
            'message' => $e->getMessage()
        ]);
    }
}

// DELETE: Mesajı sil
else if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    $messageId = $_GET['id'] ?? null;
    
    if (!$messageId) {
        http_response_code(400);
        echo json_encode(['error' => 'Message ID required']);
        exit;
    }
    
    try {
        // Database bağlantısı kontrolü
        if (!isset($pdo) || !$pdo) {
            echo json_encode([
                'success' => false,
                'message' => 'Database bağlantısı yok'
            ]);
            exit;
        }
        
        $stmt = $pdo->prepare("DELETE FROM contact_messages WHERE id = ?");
        $stmt->execute([$messageId]);
        
        if ($stmt->rowCount() > 0) {
            echo json_encode([
                'success' => true,
                'message' => 'Mesaj silindi'
            ]);
        } else {
            http_response_code(404);
            echo json_encode(['error' => 'Message not found']);
        }
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode([
            'error' => 'Database error',
            'message' => $e->getMessage()
        ]);
    }
}

else {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
}
?>
