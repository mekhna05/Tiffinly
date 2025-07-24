<?php
$page_title = 'Admin Dashboard';
require_once '../config/auth_check.php';
require_once '../config/db_connect.php';

requireAdmin();

// Get statistics
$stmt = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'user'");
$totalUsers = $stmt->fetchColumn();

$stmt = $pdo->query("SELECT COUNT(*) FROM subscriptions WHERE status = 'active'");
$activeSubscriptions = $stmt->fetchColumn();

$stmt = $pdo->query("SELECT COUNT(*) FROM deliveries WHERE status = 'available'");
$pendingDeliveries = $stmt->fetchColumn();

$stmt = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'delivery'");
$deliveryPartners = $stmt->fetchColumn();

$stmt = $pdo->query("SELECT SUM(total_price) FROM subscriptions WHERE payment_status = 'paid'");
$totalRevenue = $stmt->fetchColumn() ?: 0;

$stmt = $pdo->query("SELECT COUNT(*) FROM inquiries WHERE status = 'pending'");
$pendingInquiries = $stmt->fetchColumn();

// Get recent subscriptions
$stmt = $pdo->query("
    SELECT s.*, u.name, u.email, mp.plan_name 
    FROM subscriptions s 
    JOIN users u ON s.user_id = u.user_id 
    JOIN meal_plans mp ON s.plan_id = mp.plan_id 
    ORDER BY s.created_at DESC 
    LIMIT 10
");
$recentSubscriptions = $stmt->fetchAll();

// Get pending deliveries
$stmt = $pdo->query("
    SELECT d.*, s.subscription_id, u.name, u.email, mp.plan_name 
    FROM deliveries d 
    JOIN subscriptions s ON d.subscription_id = s.subscription_id 
    JOIN users u ON s.user_id = u.user_id 
    JOIN meal_plans mp ON s.plan_id = mp.plan_id 
    WHERE d.status = 'available' 
    ORDER BY d.delivery_date ASC 
    LIMIT 10
");
$pendingDeliveryList = $stmt->fetchAll();

require_once '../includes/header.php';
?>

<div class="container-fluid px-0">
    <div class="row g-0">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3 mb-0 text-gray-800">Admin Dashboard</h1>
                <div class="d-flex gap-2">
                    <button class="btn btn-primary" onclick="refreshData()">
                        <i class="fas fa-sync-alt me-2"></i>Refresh
                    </button>
                </div>
            </div>

            <!-- Statistics Cards -->
            <div class="row mb-4">
                <div class="col-xl-2 col-md-6 mb-4">
                    <div class="stat-card primary">
                        <div class="icon">
                            <i class="fas fa-users"></i>
                        </div>
                        <h3><?php echo $totalUsers; ?></h3>
                        <p>Total Users</p>
                    </div>
                </div>
                <div class="col-xl-2 col-md-6 mb-4">
                    <div class="stat-card success">
                        <div class="icon">
                            <i class="fas fa-calendar-check"></i>
                        </div>
                        <h3><?php echo $activeSubscriptions; ?></h3>
                        <p>Active Subscriptions</p>
                    </div>
                </div>
                <div class="col-xl-2 col-md-6 mb-4">
                    <div class="stat-card warning">
                        <div class="icon">
                            <i class="fas fa-clock"></i>
                        </div>
                        <h3><?php echo $pendingDeliveries; ?></h3>
                        <p>Pending Deliveries</p>
                    </div>
                </div>
                <div class="col-xl-2 col-md-6 mb-4">
                    <div class="stat-card info">
                        <div class="icon">
                            <i class="fas fa-truck"></i>
                        </div>
                        <h3><?php echo $deliveryPartners; ?></h3>
                        <p>Delivery Partners</p>
                    </div>
                </div>
                <div class="col-xl-2 col-md-6 mb-4">
                    <div class="stat-card primary">
                        <div class="icon">
                            <i class="fas fa-rupee-sign"></i>
                        </div>
                        <h3>₹<?php echo number_format($totalRevenue/1000, 1); ?>k</h3>
                        <p>Total Revenue</p>
                    </div>
                </div>
                <div class="col-xl-2 col-md-6 mb-4">
                    <div class="stat-card warning">
                        <div class="icon">
                            <i class="fas fa-question-circle"></i>
                        </div>
                        <h3><?php echo $pendingInquiries; ?></h3>
                        <p>Pending Inquiries</p>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="quick-actions mb-4">
                <a href="manage_meals.php" class="quick-action">
                    <div class="icon">
                        <i class="fas fa-utensils"></i>
                    </div>
                    <h6>Manage Meals</h6>
                </a>
                <a href="manage_users.php" class="quick-action">
                    <div class="icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <h6>Manage Users</h6>
                </a>
                <a href="manage_deliveries.php" class="quick-action">
                    <div class="icon">
                        <i class="fas fa-truck"></i>
                    </div>
                    <h6>Monitor Deliveries</h6>
                </a>
                <a href="inquiries.php" class="quick-action">
                    <div class="icon">
                        <i class="fas fa-envelope"></i>
                    </div>
                    <h6>View Inquiries</h6>
                </a>
                <a href="reports.php" class="quick-action">
                    <div class="icon">
                        <i class="fas fa-chart-bar"></i>
                    </div>
                    <h6>Reports</h6>
                </a>
            </div>

            <!-- Recent Activity -->
            <div class="row">
                <div class="col-lg-8">
                    <div class="card mb-4">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">Recent Subscriptions</h5>
                            <a href="manage_subscriptions.php" class="btn btn-sm btn-outline-primary">View All</a>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>User</th>
                                            <th>Plan</th>
                                            <th>Status</th>
                                            <th>Amount</th>
                                            <th>Date</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($recentSubscriptions as $subscription): ?>
                                            <tr>
                                                <td>
                                                    <div>
                                                        <strong><?php echo htmlspecialchars($subscription['name']); ?></strong>
                                                        <br>
                                                        <small class="text-muted"><?php echo htmlspecialchars($subscription['email']); ?></small>
                                                    </div>
                                                </td>
                                                <td><?php echo htmlspecialchars($subscription['plan_name']); ?></td>
                                                <td>
                                                    <span class="badge bg-<?php echo $subscription['status'] === 'active' ? 'success' : 'warning'; ?>">
                                                        <?php echo ucfirst($subscription['status']); ?>
                                                    </span>
                                                </td>
                                                <td>₹<?php echo number_format($subscription['total_price']); ?></td>
                                                <td><?php echo date('M j, Y', strtotime($subscription['created_at'])); ?></td>
                                                <td>
                                                    <a href="subscription_details.php?id=<?php echo $subscription['subscription_id']; ?>" 
                                                       class="btn btn-sm btn-outline-primary">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="card mb-4">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">Pending Deliveries</h5>
                            <a href="manage_deliveries.php" class="btn btn-sm btn-outline-primary">View All</a>
                        </div>
                        <div class="card-body">
                            <?php if (empty($pendingDeliveryList)): ?>
                                <p class="text-muted">No pending deliveries.</p>
                            <?php else: ?>
                                <div class="delivery-list">
                                    <?php foreach ($pendingDeliveryList as $delivery): ?>
                                        <div class="delivery-item mb-3 p-3 border rounded">
                                            <div class="d-flex justify-content-between align-items-start">
                                                <div>
                                                    <h6 class="mb-1"><?php echo htmlspecialchars($delivery['name']); ?></h6>
                                                    <p class="text-muted mb-1"><?php echo htmlspecialchars($delivery['plan_name']); ?></p>
                                                    <small class="text-muted">
                                                        <?php echo date('M j, Y', strtotime($delivery['delivery_date'])); ?>
                                                        at <?php echo date('h:i A', strtotime($delivery['delivery_time'])); ?>
                                                    </small>
                                                </div>
                                                <span class="badge bg-warning">Pending</span>
                                            </div>
                                            <div class="mt-2">
                                                <button class="btn btn-sm btn-primary" onclick="assignDelivery(<?php echo $delivery['delivery_id']; ?>)">
                                                    <i class="fas fa-user-plus"></i> Assign
                                                </button>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function refreshData() {
    location.reload();
}

function assignDelivery(deliveryId) {
    // This would open a modal to assign delivery partner
    showToast('Delivery assignment feature will be implemented.', 'info');
}

// Auto-refresh every 30 seconds
function autoRefreshData() {
    $.get('../ajax/get_dashboard_stats.php', function(data) {
        if (data.success) {
            // Update statistics without full page reload
            updateStats(data.stats);
        }
    });
}

function updateStats(stats) {
    // Update stat cards with new data
    // This would be implemented to update specific elements
}

setInterval(autoRefreshData, 30000);
</script>

<?php require_once '../includes/footer.php'; ?>