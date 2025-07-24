<?php
require_once '../config/session.php';
require_once '../config/db_connect.php';

requireAuth(['user']);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../user/cart.php");
    exit();
}

// Validate CSRF token
if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
    $_SESSION['error'] = 'Invalid security token';
    header("Location: ../user/payment.php");
    exit();
}

$subscriptionId = $_POST['subscription_id'] ?? 0;
$amount = $_POST['amount'] ?? 0;
$paymentMethod = $_POST['payment_method'] ?? '';
$userId = getUserId();

try {
    $pdo->beginTransaction();
    
    // Validate subscription belongs to user
    if ($subscriptionId) {
        $stmt = $pdo->prepare("SELECT * FROM subscriptions WHERE subscription_id = ? AND user_id = ?");
        $stmt->execute([$subscriptionId, $userId]);
        $subscription = $stmt->fetch();
        
        if (!$subscription) {
            throw new Exception('Invalid subscription');
        }
    }
    
    // Generate transaction ID
    $transactionId = 'TXN' . time() . rand(1000, 9999);
    
    // Simulate payment processing
    $paymentStatus = 'paid'; // In real implementation, this would come from payment gateway
    
    // Create payment record
    $stmt = $pdo->prepare("
        INSERT INTO payments (subscription_id, amount, method, status, transaction_id, paid_at) 
        VALUES (?, ?, ?, ?, ?, NOW())
    ");
    $stmt->execute([$subscriptionId, $amount, $paymentMethod, $paymentStatus, $transactionId]);
    
    // Update subscription payment status
    if ($subscriptionId) {
        $stmt = $pdo->prepare("
            UPDATE subscriptions 
            SET payment_status = ?, status = 'active' 
            WHERE subscription_id = ?
        ");
        $stmt->execute([$paymentStatus, $subscriptionId]);
    }
    
    // Clear cart
    unset($_SESSION['cart']);
    
    $pdo->commit();
    
    $_SESSION['success'] = 'Payment successful! Your subscription is now active.';
    header("Location: ../user/order_confirmation.php?subscription_id=" . $subscriptionId . "&transaction_id=" . $transactionId);
    
} catch (Exception $e) {
    $pdo->rollback();
    $_SESSION['error'] = 'Payment failed: ' . $e->getMessage();
    header("Location: ../user/payment.php" . ($subscriptionId ? "?subscription_id=" . $subscriptionId : ""));
}
?>