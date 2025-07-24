<?php
$page_title = 'Manage Meals';
require_once '../config/auth_check.php';
require_once '../config/db_connect.php';

requireAdmin();

// Handle meal operations
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        $_SESSION['error'] = 'Invalid security token';
    } else {
        $action = $_POST['action'] ?? '';
        
        try {
            switch ($action) {
                case 'add':
                    $planId = $_POST['plan_id'] ?? 0;
                    $day = $_POST['day'] ?? '';
                    $mealType = $_POST['meal_type'] ?? '';
                    $optionType = $_POST['option_type'] ?? '';
                    $mealName = $_POST['meal_name'] ?? '';
                    
                    $stmt = $pdo->prepare("
                        INSERT INTO meals (plan_id, day, meal_type, option_type, meal_name) 
                        VALUES (?, ?, ?, ?, ?)
                    ");
                    $stmt->execute([$planId, $day, $mealType, $optionType, $mealName]);
                    
                    $_SESSION['success'] = 'Meal added successfully!';
                    break;
                    
                case 'edit':
                    $mealId = $_POST['meal_id'] ?? 0;
                    $planId = $_POST['plan_id'] ?? 0;
                    $day = $_POST['day'] ?? '';
                    $mealType = $_POST['meal_type'] ?? '';
                    $optionType = $_POST['option_type'] ?? '';
                    $mealName = $_POST['meal_name'] ?? '';
                    
                    $stmt = $pdo->prepare("
                        UPDATE meals 
                        SET plan_id = ?, day = ?, meal_type = ?, option_type = ?, meal_name = ? 
                        WHERE meal_id = ?
                    ");
                    $stmt->execute([$planId, $day, $mealType, $optionType, $mealName, $mealId]);
                    
                    $_SESSION['success'] = 'Meal updated successfully!';
                    break;
                    
                case 'delete':
                    $mealId = $_POST['meal_id'] ?? 0;
                    
                    $stmt = $pdo->prepare("DELETE FROM meals WHERE meal_id = ?");
                    $stmt->execute([$mealId]);
                    
                    $_SESSION['success'] = 'Meal deleted successfully!';
                    break;
            }
        } catch (Exception $e) {
            $_SESSION['error'] = 'Error: ' . $e->getMessage();
        }
        
        header("Location: manage_meals.php");
        exit();
    }
}

// Get meal plans
$stmt = $pdo->query("SELECT * FROM meal_plans ORDER BY plan_id");
$mealPlans = $stmt->fetchAll();

// Get all meals
$stmt = $pdo->query("
    SELECT m.*, mp.plan_name 
    FROM meals m 
    JOIN meal_plans mp ON m.plan_id = mp.plan_id 
    ORDER BY mp.plan_name, 
    FIELD(m.day, 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'),
    FIELD(m.meal_type, 'Breakfast', 'Lunch', 'Dinner')
");
$meals = $stmt->fetchAll();

require_once '../includes/header.php';
?>

<div class="container-fluid px-0">
    <div class="row g-0">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3 mb-0 text-gray-800">Manage Meals</h1>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addMealModal">
                    <i class="fas fa-plus me-2"></i>Add New Meal
                </button>
            </div>

            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success alert-dismissible fade show">
                    <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-danger alert-dismissible fade show">
                    <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <div class="card">
                <div class="card-header">
                    <div class="row align-items-center">
                        <div class="col-md-6">
                            <h5 class="mb-0">All Meals</h5>
                        </div>
                        <div class="col-md-6">
                            <div class="input-group">
                                <input type="text" class="form-control search-input" 
                                       placeholder="Search meals..." data-target="#mealsTable">
                                <span class="input-group-text">
                                    <i class="fas fa-search"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover" id="mealsTable">
                            <thead>
                                <tr>
                                    <th>Plan</th>
                                    <th>Day</th>
                                    <th>Meal Type</th>
                                    <th>Option</th>
                                    <th>Meal Name</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($meals as $meal): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($meal['plan_name']); ?></td>
                                        <td><?php echo htmlspecialchars($meal['day']); ?></td>
                                        <td><?php echo htmlspecialchars($meal['meal_type']); ?></td>
                                        <td>
                                            <span class="badge bg-<?php echo $meal['option_type'] === 'veg' ? 'success' : 'danger'; ?>">
                                                <?php echo ucfirst($meal['option_type']); ?>
                                            </span>
                                        </td>
                                        <td><?php echo htmlspecialchars($meal['meal_name']); ?></td>
                                        <td>
                                            <div class="btn-group">
                                                <button class="btn btn-sm btn-outline-primary" 
                                                        onclick="editMeal(<?php echo htmlspecialchars(json_encode($meal)); ?>)">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button class="btn btn-sm btn-outline-danger btn-delete" 
                                                        onclick="deleteMeal(<?php echo $meal['meal_id']; ?>)">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Meal Modal -->
<div class="modal fade" id="addMealModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add New Meal</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                    <input type="hidden" name="action" value="add">
                    
                    <div class="mb-3">
                        <label for="plan_id" class="form-label">Meal Plan</label>
                        <select name="plan_id" id="plan_id" class="form-select" required>
                            <option value="">Select Plan</option>
                            <?php foreach ($mealPlans as $plan): ?>
                                <option value="<?php echo $plan['plan_id']; ?>">
                                    <?php echo htmlspecialchars($plan['plan_name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="day" class="form-label">Day</label>
                        <select name="day" id="day" class="form-select" required>
                            <option value="">Select Day</option>
                            <option value="Monday">Monday</option>
                            <option value="Tuesday">Tuesday</option>
                            <option value="Wednesday">Wednesday</option>
                            <option value="Thursday">Thursday</option>
                            <option value="Friday">Friday</option>
                            <option value="Saturday">Saturday</option>
                            <option value="Sunday">Sunday</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="meal_type" class="form-label">Meal Type</label>
                        <select name="meal_type" id="meal_type" class="form-select" required>
                            <option value="">Select Type</option>
                            <option value="Breakfast">Breakfast</option>
                            <option value="Lunch">Lunch</option>
                            <option value="Dinner">Dinner</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="option_type" class="form-label">Option Type</label>
                        <select name="option_type" id="option_type" class="form-select" required>
                            <option value="">Select Option</option>
                            <option value="veg">Vegetarian</option>
                            <option value="non_veg">Non-Vegetarian</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="meal_name" class="form-label">Meal Name</label>
                        <input type="text" name="meal_name" id="meal_name" class="form-control" 
                               placeholder="Enter meal name" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add Meal</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Meal Modal -->
<div class="modal fade" id="editMealModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Meal</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" id="editMealForm">
                <div class="modal-body">
                    <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                    <input type="hidden" name="action" value="edit">
                    <input type="hidden" name="meal_id" id="edit_meal_id">
                    
                    <div class="mb-3">
                        <label for="edit_plan_id" class="form-label">Meal Plan</label>
                        <select name="plan_id" id="edit_plan_id" class="form-select" required>
                            <option value="">Select Plan</option>
                            <?php foreach ($mealPlans as $plan): ?>
                                <option value="<?php echo $plan['plan_id']; ?>">
                                    <?php echo htmlspecialchars($plan['plan_name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="edit_day" class="form-label">Day</label>
                        <select name="day" id="edit_day" class="form-select" required>
                            <option value="">Select Day</option>
                            <option value="Monday">Monday</option>
                            <option value="Tuesday">Tuesday</option>
                            <option value="Wednesday">Wednesday</option>
                            <option value="Thursday">Thursday</option>
                            <option value="Friday">Friday</option>
                            <option value="Saturday">Saturday</option>
                            <option value="Sunday">Sunday</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="edit_meal_type" class="form-label">Meal Type</label>
                        <select name="meal_type" id="edit_meal_type" class="form-select" required>
                            <option value="">Select Type</option>
                            <option value="Breakfast">Breakfast</option>
                            <option value="Lunch">Lunch</option>
                            <option value="Dinner">Dinner</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="edit_option_type" class="form-label">Option Type</label>
                        <select name="option_type" id="edit_option_type" class="form-select" required>
                            <option value="">Select Option</option>
                            <option value="veg">Vegetarian</option>
                            <option value="non_veg">Non-Vegetarian</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="edit_meal_name" class="form-label">Meal Name</label>
                        <input type="text" name="meal_name" id="edit_meal_name" class="form-control" 
                               placeholder="Enter meal name" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Meal</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function editMeal(meal) {
    $('#edit_meal_id').val(meal.meal_id);
    $('#edit_plan_id').val(meal.plan_id);
    $('#edit_day').val(meal.day);
    $('#edit_meal_type').val(meal.meal_type);
    $('#edit_option_type').val(meal.option_type);
    $('#edit_meal_name').val(meal.meal_name);
    
    $('#editMealModal').modal('show');
}

function deleteMeal(mealId) {
    if (confirm('Are you sure you want to delete this meal?')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.innerHTML = `
            <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
            <input type="hidden" name="action" value="delete">
            <input type="hidden" name="meal_id" value="${mealId}">
        `;
        document.body.appendChild(form);
        form.submit();
    }
}
</script>

<?php require_once '../includes/footer.php'; ?>