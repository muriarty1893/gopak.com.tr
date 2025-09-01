<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

// Get JSON input
$input = file_get_contents('php://input');
$data = json_decode($input, true);

if (!$data) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid JSON data']);
    exit;
}

// Check if it's a single item or multiple items order
$isSingleItem = isset($data['cartItem']);
$isMultipleItems = isset($data['cartItems']);

if (!$isSingleItem && !$isMultipleItems) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'No cart items provided']);
    exit;
}

// Validate required fields
$required_fields = ['firstName', 'lastName', 'phone', 'email', 'address'];
foreach ($required_fields as $field) {
    if (empty($data[$field])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => "Missing required field: $field"]);
        exit;
    }
}

// Validate email
if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid email format']);
    exit;
}

try {
    // Include database connection
    require_once '../config/database.php';
    
    // Create tables if they don't exist
    $createCustomersTable = "
        CREATE TABLE IF NOT EXISTS customers (
            id INT AUTO_INCREMENT PRIMARY KEY,
            first_name VARCHAR(100) NOT NULL,
            last_name VARCHAR(100) NOT NULL,
            email VARCHAR(255) UNIQUE NOT NULL,
            phone VARCHAR(20) NOT NULL,
            address TEXT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )
    ";
    
    $createOrdersTable = "
        CREATE TABLE IF NOT EXISTS orders (
            id INT AUTO_INCREMENT PRIMARY KEY,
            order_number VARCHAR(50) UNIQUE NOT NULL,
            customer_id INT NOT NULL,
            product_type VARCHAR(100) NOT NULL,
            product_color VARCHAR(100) NOT NULL,
            product_size VARCHAR(50) NOT NULL,
            quantity INT NOT NULL,
            unit_price DECIMAL(10,2) NOT NULL,
            size_multiplier DECIMAL(3,1) NOT NULL,
            subtotal DECIMAL(10,2) NOT NULL,
            discount DECIMAL(3,2) DEFAULT 0,
            total DECIMAL(10,2) NOT NULL,
            status ENUM('pending', 'confirmed', 'production', 'shipped', 'delivered', 'cancelled') DEFAULT 'pending',
            notes TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (customer_id) REFERENCES customers(id)
        )
    ";
    
    $pdo->exec($createCustomersTable);
    $pdo->exec($createOrdersTable);
    
    // Prepare order data
    $firstName = trim($data['firstName']);
    $lastName = trim($data['lastName']);
    $phone = trim($data['phone']);
    $email = trim($data['email']);
    $address = trim($data['address']);
    
    // Prepare cart items
    $cartItems = [];
    if ($isSingleItem) {
        $cartItems = [$data['cartItem']];
    } else {
        $cartItems = $data['cartItems'];
    }
    
    // Start transaction
    $pdo->beginTransaction();
    
    // Insert customer data
    $customerStmt = $pdo->prepare("
        INSERT INTO customers (first_name, last_name, phone, email, address, created_at) 
        VALUES (?, ?, ?, ?, ?, NOW())
        ON DUPLICATE KEY UPDATE 
        first_name = VALUES(first_name),
        last_name = VALUES(last_name),
        phone = VALUES(phone),
        address = VALUES(address),
        updated_at = NOW()
    ");
    
    $customerStmt->execute([$firstName, $lastName, $phone, $email, $address]);
    $customerId = $pdo->lastInsertId();
    
    // If customer already exists, get the ID
    if ($customerId == 0) {
        $existingCustomer = $pdo->prepare("SELECT id FROM customers WHERE email = ?");
        $existingCustomer->execute([$email]);
        $customerId = $existingCustomer->fetchColumn();
    }
    
    // Prepare order statement
    $orderStmt = $pdo->prepare("
        INSERT INTO orders (
            order_number, customer_id, product_type, product_color, product_size, 
            quantity, unit_price, size_multiplier, subtotal, discount, total, 
            bag_type, bag_dimensions, min_quantity_met, status, created_at
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending', NOW())
    ");
    
    $orderIds = [];
    
    // Insert each cart item as separate order
    foreach ($cartItems as $index => $cartItem) {
        // Generate unique order number for each item
        $orderNumber = 'GOP-' . date('Ymd') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT) . '-' . ($index + 1);
        
        // Çanta ürünü kontrolü
        $isBagProduct = false;
        $bagType = null;
        $bagDimensions = null;
        $minQuantityMet = false;
        
        if (isset($cartItem['bagType']) && $cartItem['bagType']) {
            $isBagProduct = true;
            $bagType = $cartItem['bagType'];
            $bagDimensions = $cartItem['bagDimensions'] ?? null;
            $minQuantityMet = isset($cartItem['minQuantity']) && $cartItem['quantity'] >= $cartItem['minQuantity'];
        }
        
        // Subtotal hesaplama
        $subtotal = $cartItem['unitPrice'] * $cartItem['quantity'];
        
        $orderStmt->execute([
            $orderNumber,
            $customerId,
            $cartItem['typeName'],
            $cartItem['colorName'] ?? 'Standart',
            $cartItem['size'],
            $cartItem['quantity'],
            $cartItem['unitPrice'],
            $cartItem['sizeMultiplier'] ?? 1.0,
            $subtotal,
            $cartItem['discount'] ?? 0,
            $cartItem['total'],
            $bagType,
            $bagDimensions,
            $minQuantityMet
        ]);
        
        $orderIds[] = [
            'order_id' => $pdo->lastInsertId(),
            'order_number' => $orderNumber
        ];
    }
    
    // Commit transaction
    $pdo->commit();
    
    // Calculate total for response
    $totalAmount = array_sum(array_column($cartItems, 'total'));
    
    // Send success response
    echo json_encode([
        'success' => true,
        'message' => 'Siparişiniz başarıyla alındı!',
        'orders' => $orderIds,
        'total_amount' => $totalAmount,
        'customer_id' => $customerId
    ]);
    
} catch (PDOException $e) {
    // Rollback transaction on error
    if ($pdo->inTransaction()) {
        $pdo->rollback();
    }
    
    error_log("Order submission PDO error: " . $e->getMessage());
    
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Veritabanı hatası: ' . $e->getMessage(),
        'error_type' => 'database',
        'debug_info' => [
            'file' => $e->getFile(),
            'line' => $e->getLine()
        ]
    ]);
    
} catch (Exception $e) {
    error_log("General order error: " . $e->getMessage());
    
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Genel hata: ' . $e->getMessage(),
        'error_type' => 'general',
        'debug_info' => [
            'file' => $e->getFile(),
            'line' => $e->getLine()
        ]
    ]);
}
?>
