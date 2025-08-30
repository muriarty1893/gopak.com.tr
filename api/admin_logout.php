<?php
header('Content-Type: application/json');
require_once '../admin_auth.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $result = adminLogout();
    echo json_encode($result);
} else {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'message' => 'Sadece POST metodu desteklenir.'
    ]);
}
?>
