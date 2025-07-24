<?php
require_once '../config/session.php';
require_once '../config/db_connect.php';

requireAuth(['user']);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../user/browse_plans.php");
    exit();
}

// Validate CSRF token
if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
    $_SESSION['error'] = 'Invalid security token';
    header("Location: ../user/customize_meals.php");
    exit();
}

$planId = $_POST['plan_id'] ?? 0;
$schedule = $_POST['schedule'] ?? '';
$deliveryTime = $_POST['delivery_time'] ?? '';
$startDate = $_POST['start_date'] ?? '';
$meals = $_POST['meals'] ?? [];
$userId = getUserId();

try {
    $pdo->beginTransaction();
    
    // Get plan details
    $stmt = $pdo->prepare("SELECT * FROM meal_plans WHERE plan_id = ?");
    $stmt->execute([$planId]);
    $plan = $stmt->fetch();
    
    if (!$plan) {
        throw new Exception('Invalid plan selected');
    }
    
    // Calculate price based on schedule
    $basePrice = $plan['price'];
    $multiplier = 1;
    
    switch ($schedule) {
        case 'weekdays':
            $multiplier = 5/7;
            break;
        case 'extended':
            $multiplier = 6/7;
            break;
        case 'full-week':
            $multiplier = 1;
            break;
        default:
            throw new Exception('Invalid schedule selected');
    }
    
    $totalPrice = round($basePrice * $multiplier);
    
    // Calculate end date (1 month from start)
    $endDate = date('Y-m-d', strtotime($startDate . ' +1 month'));
    
    // Create subscription
    $stmt = $pdo->prepare("
        INSERT INTO subscriptions (user_id, plan_id, start_date, end_date, schedule, delivery_time, total_price, status, created_at) 
        VALUES (?, ?, ?, ?, ?, ?, ?, 'pending', NOW())
    ");
    $stmt->execute([$userId, $planId, $startDate, $endDate, $schedule, $deliveryTime, $totalPrice]);
    
    $subscriptionId = $pdo->lastInsertId();
    
    // Save meal customizations
    foreach ($meals as $day => $dayMeals) {
        foreach ($dayMeals as $mealType => $mealId) {
            if (!empty($mealId)) {
                $stmt = $pdo->prepare("
                    INSERT INTO custom_meal_selection (subscription_id, meal_id, day, meal_type) 
                    VALUES (?, ?, ?, ?)
                ");
                $stmt->execute([$subscriptionId, $mealId, $day, $mealType]);
            }
        }
    }
    
    // Generate delivery entries for the subscription period
    $currentDate = new DateTime($startDate);
    $endDateTime = new DateTime($endDate);
    
    $activeDays = [];
    switch ($schedule) {
        case 'weekdays':
            $activeDays = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];
            break;
        case 'extended':
            $activeDays = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
            break;
        case 'full-week':
            $activeDays = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
            break;
    }
    
    while ($currentDate <= $endDateTime) {
        $dayName = $currentDate->format('l');
        
        if (in_array($dayName, $activeDays)) {
            $stmt = $pdo->prepare("
                INSERT INTO deliveries (subscription_id, delivery_date, delivery_time, status) 
                VALUES (?, ?, ?, 'available')
            ");
            $stmt->execute([$subscriptionId, $currentDate->format('Y-m-d'), $deliveryTime]);
        }
        
        $currentDate->add(new DateInterval('P1D'));
    }
    
    $pdo->commit();
    
    $_SESSION['success'] = 'Meal plan customized successfully! Proceed to payment.';
    header("Location: ../user/payment.php?subscription_id=" . $subscriptionId);
    
} catch (Exception $e) {
    $pdo->rollback();
    $_SESSION['error'] = 'Error saving customization: ' . $e->getMessage();
    header("Location: ../user/customize_meals.php");
}
?>