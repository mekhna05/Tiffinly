<?php
$page_title = 'Manage Users';
require_once '../config/auth_check.php';
require_once '../config/db_connect.php';

requireAdmin();

// Handle user operations
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        $_SESSION['error'] = 'Invalid security token';
    } else {
        $action = $_POST['action'] ?? '';
        $userId = $_POST['user_id'] ?? 0;
        
        try {
            switch ($action) {
                case 'delete':
                    // Check if user has active subscriptions
                    $stmt = $pdo->prepare("SELECT COUNT(*) FROM subscriptions WHERE user_id = ? AND status = 'active'");
                    $stmt->execute([$userId]);
                    $activeSubscriptions = $stmt->fetchColumn();
                    
                    if ($activeSubscriptions > 0) {
                        $_SESSION['error'] = 'Cannot delete user with active subscriptions';
                    } else {
                        $stmt = $pdo->prepare("DELETE FROM users WHERE user_id = ? AND role != 'admin'");
                        $stmt->execute([$userId]);
                        $_SESSION['success'] = 'User deleted successfully!';
                    }
                    break;
                    
                case 'toggle_status':
                    // This would be for blocking/unblocking users (if you add a status field)
                    $_SESSION['info'] = 'User status toggle feature will be implemented';
                    break;
            }
        } catch (Exception $e) {
            $_SESSION['error'] = 'Error: ' . $e->getMessage();
        }
        
        header("Location: manage_users.php");
        exit();
    }
}

// Get users with statistics
$stmt = $pdo->query("
    SELECT u.*, 
           COUNT(s.subscription_id) as total_orders,
           SUM(CASE WHEN s.status = 'active' THEN 1 ELSE 0 END) as active_orders,
           SUM(s.total_price) as total_spent
    FROM users u 
    LEFT JOIN subscriptions s ON u.user_id = s.user_id 
    WHERE u.role != 'admin'
    GROUP BY u.user_id 
    ORDER BY u.created_at DESC
");
$users = $stmt->fetchAll();

// Get user statistics
$stmt = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'user'");
$totalUsers = $stmt->fetchColumn();

$stmt = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'delivery'");
$deliveryPartners = $stmt->fetchColumn();

$stmt = $pdo->query("SELECT COUNT(*) FROM users WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)");
$newUsersThisMonth = $stmt->fetchColumn();

require_once '../includes/header.php';
?>

<div class="container-fluid px-0">
    <div class="row g-0">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3 mb-0 text-gray-800">Manage Users</h1>
                <div class="d-flex gap-2">
                    <button class="btn btn-outline-primary" onclick="exportUsers()">
                        <i class="fas fa-download me-2"></i>Export
                    </button>
                </div>
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

            <?php if (isset($_SESSION['info'])): ?>
                <div class="alert alert-info alert-dismissible fade show">
                    <?php echo $_SESSION['info']; unset($_SESSION['info']); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <!-- Statistics Cards -->
            <div class="row mb-4">
                <div class="col-xl-4 col-md-6 mb-4">
                    <div class="stat-card primary">
                        <div class="icon">
                            <i class="fas fa-users"></i>
                        </div>
                        <h3><?php echo $totalUsers; ?></h3>
                        <p>Total Users</p>
                    </div>
                </div>
                <div class="col-xl-4 col-md-6 mb-4">
                    <div class="stat-card success">
                        <div class="icon">
                            <i class="fas fa-truck"></i>
                        </div>
                        <h3><?php echo $deliveryPartners; ?></h3>
                        <p>Delivery Partners</p>
                    </div>
                </div>
                <div class="col-xl-4 col-md-6 mb-4">
                    <div class="stat-card info">
                        <div class="icon">
                            <i class="fas fa-user-plus"></i>
                        </div>
                        <h3><?php echo $newUsersThisMonth; ?></h3>
                        <p>New This Month</p>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <div class="row align-items-center">
                        <div class="col-md-6">
                            <h5 class="mb-0">All Users</h5>
                        </div>
                        <div class="col-md-6">
                            <div class="input-group">
                                <input type="text" class="form-control search-input" 
                                       placeholder="Search users..." data-target="#usersTable">
                                <span class="input-group-text">
                                    <i class="fas fa-search"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover" id="usersTable">
                            <thead>
                                <tr>
                                    <th>User</th>
                                    <th>Role</th>
                                    <th>Contact</th>
                                    <th>Orders</th>
                                    <th>Total Spent</th>
                                    <th>Joined</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($users as $user): ?>
                                    <tr>
                                        <td>
                                            <div>
                                                <strong><?php echo htmlspecialchars($user['name']); ?></strong>
                                                <br>
                                                <small class="text-muted"><?php echo htmlspecialchars($user['email']); ?></small>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge bg-<?php echo getRoleColor($user['role']); ?>">
                                                <?php echo ucfirst($user['role']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php if ($user['phone']): ?>
                                                <a href="tel:<?php echo $user['phone']; ?>" class="text-decoration-none">
                                                    <i class="fas fa-phone me-1"></i><?php echo htmlspecialchars($user['phone']); ?>
                                                </a>
                                            <?php else: ?>
                                                <span class="text-muted">Not provided</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div>
                                                <strong><?php echo $user['total_orders']; ?></strong> total
                                                <br>
                                                <small class="text-success"><?php echo $user['active_orders']; ?> active</small>
                                            </div>
                                        </td>
                                        <td>
                                            <strong>₹<?php echo number_format($user['total_spent'] ?: 0); ?></strong>
                                        </td>
                                        <td>
                                            <?php echo date('M j, Y', strtotime($user['created_at'])); ?>
                                        </td>
                                        <td>
                                            <div class="btn-group">
                                                <a href="user_details.php?id=<?php echo $user['user_id']; ?>" 
                                                   class="btn btn-sm btn-outline-primary">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <?php if ($user['role'] !== 'admin'): ?>
                                                    <button class="btn btn-sm btn-outline-danger btn-delete" 
                                                            onclick="deleteUser(<?php echo $user['user_id']; ?>, '<?php echo htmlspecialchars($user['name']); ?>')">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                <?php endif; ?>
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

<script>
function deleteUser(userId, userName) {
    if (confirm(`Are you sure you want to delete user "${userName}"? This action cannot be undone.`)) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.innerHTML = `
            <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
            <input type="hidden" name="action" value="delete">
            <input type="hidden" name="user_id" value="${userId}">
        `;
        document.body.appendChild(form);
        form.submit();
    }
}

function exportUsers() {
    showToast('Export functionality will be implemented', 'info');
}
</script>

<?php require_once '../includes/footer.php'; ?>

<?php
function getRoleColor($role) {
    switch ($role) {
        case 'user': return 'primary';
        case 'delivery': return 'success';
        case 'admin': return 'danger';
        default: return 'secondary';
    }
}
?>