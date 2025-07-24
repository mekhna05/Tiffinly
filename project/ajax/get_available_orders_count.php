<?php
require_once '../config/session.php';
require_once '../config/db_connect.php';

header('Content-Type: application/json');

try {
    $stmt = $pdo->query("SELECT COUNT(*) FROM deliveries WHERE status = 'available'");
    $count = $stmt->fetchColumn();
    
    echo json_encode([
        'success' => true,
        'count' => (int)$count
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error fetching order count'
    ]);
}
?>