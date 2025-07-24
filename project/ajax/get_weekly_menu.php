<?php
require_once '../config/session.php';
require_once '../config/db_connect.php';

$planId = $_GET['plan_id'] ?? 0;

if (!$planId) {
    echo '<p class="text-danger">Invalid plan ID</p>';
    exit();
}

try {
    // Get plan details
    $stmt = $pdo->prepare("SELECT * FROM meal_plans WHERE plan_id = ?");
    $stmt->execute([$planId]);
    $plan = $stmt->fetch();
    
    if (!$plan) {
        echo '<p class="text-danger">Plan not found</p>';
        exit();
    }
    
    // Get meals for this plan
    $stmt = $pdo->prepare("SELECT * FROM meals WHERE plan_id = ? ORDER BY FIELD(day, 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'), FIELD(meal_type, 'Breakfast', 'Lunch', 'Dinner')");
    $stmt->execute([$planId]);
    $meals = $stmt->fetchAll();
    
    // Organize meals by day and type
    $mealsByDay = [];
    foreach ($meals as $meal) {
        $mealsByDay[$meal['day']][$meal['meal_type']][] = $meal;
    }
    
    $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
    $mealTypes = ['Breakfast', 'Lunch', 'Dinner'];
    
    echo '<h4>' . htmlspecialchars($plan['plan_name']) . ' Plan Weekly Menu</h4>';
    echo '<div class="table-responsive">';
    echo '<table class="table table-bordered">';
    echo '<thead><tr><th>Day</th><th>Breakfast</th><th>Lunch</th><th>Dinner</th></tr></thead>';
    echo '<tbody>';
    
    foreach ($days as $day) {
        echo '<tr>';
        echo '<td class="fw-bold">' . $day . '</td>';
        
        foreach ($mealTypes as $mealType) {
            echo '<td>';
            if (isset($mealsByDay[$day][$mealType])) {
                foreach ($mealsByDay[$day][$mealType] as $meal) {
                    echo '<div class="meal-item mb-1">';
                    echo '<span class="badge bg-' . ($meal['option_type'] === 'veg' ? 'success' : 'danger') . ' me-1">';
                    echo ucfirst($meal['option_type']);
                    echo '</span>';
                    echo htmlspecialchars($meal['meal_name']);
                    echo '</div>';
                }
            } else {
                echo '<span class="text-muted">No meals</span>';
            }
            echo '</td>';
        }
        
        echo '</tr>';
    }
    
    echo '</tbody>';
    echo '</table>';
    echo '</div>';
    
} catch (Exception $e) {
    echo '<p class="text-danger">Error loading menu: ' . $e->getMessage() . '</p>';
}
?>