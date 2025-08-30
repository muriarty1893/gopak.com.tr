<?php
header('Content-Type: application/json');

try {
    require_once '../config/database.php';
    
    // Test database connection
    $stmt = $pdo->query("SELECT 1");
    
    // Check if tables exist
    $tables = [];
    
    // Check customers table
    try {
        $pdo->query("SELECT 1 FROM customers LIMIT 1");
        $tables['customers'] = 'exists';
    } catch (PDOException $e) {
        $tables['customers'] = 'missing - ' . $e->getMessage();
    }
    
    // Check orders table
    try {
        $pdo->query("SELECT 1 FROM orders LIMIT 1");
        $tables['orders'] = 'exists';
    } catch (PDOException $e) {
        $tables['orders'] = 'missing - ' . $e->getMessage();
    }
    
    echo json_encode([
        'success' => true,
        'message' => 'Database connection successful',
        'tables' => $tables,
        'server_info' => $pdo->getAttribute(PDO::ATTR_SERVER_VERSION)
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database connection failed: ' . $e->getMessage(),
        'error_type' => get_class($e)
    ]);
}
?>
