<?php
$page_title = 'Delivery Dashboard';
require_once '../config/auth_check.php';
require_once '../config/db_connect.php';

requireDelivery();

$partnerId = getUserId();

// Get delivery statistics
$stmt = $pdo->prepare("SELECT COUNT(*) FROM deliveries WHERE partner_id = ?");
$stmt->execute([$partnerId]);
$totalDeliveries = $stmt->fetchColumn();

$stmt = $pdo->prepare("SELECT COUNT(*) FROM deliveries WHERE partner_id = ? AND status = 'delivered'");
$stmt->execute([$partnerId]);
$completedDeliveries = $stmt->fetchColumn();

$stmt = $pdo->prepare("SELECT COUNT(*) FROM deliveries WHERE partner_id = ? AND status IN ('accepted', 'out_for_delivery')");
$stmt->execute([$partnerId]);
$activeDeliveries = $stmt->fetchColumn();

$stmt = $pdo->query("SELECT COUNT(*) FROM deliveries WHERE status = 'available'");
$availableDeliveries = $stmt->fetchColumn();

$stmt = $pdo->prepare("SELECT COUNT(*) FROM deliveries WHERE partner_id = ? AND delivery_date = CURDATE()");
$stmt->execute([$partnerId]);
$todayDeliveries = $stmt->fetchColumn();

// Get recent deliveries
$stmt = $pdo->prepare("
    SELECT d.*, s.subscription_id, u.name, u.email, mp.plan_name 
    FROM deliveries d 
    JOIN subscriptions s ON d.subscription_id = s.subscription_id 
    JOIN users u ON s.user_id = u.user_id 
    JOIN meal_plans mp ON s.plan_id = mp.plan_id 
    WHERE d.partner_id = ? 
    ORDER BY d.delivery_date DESC 
    LIMIT 10
");
$stmt->execute([$partnerId]);
$recentDeliveries = $stmt->fetchAll();

// Get today's deliveries
$stmt = $pdo->prepare("
    SELECT d.*, s.subscription_id, u.name, u.email, u.phone, mp.plan_name 
    FROM deliveries d 
    JOIN subscriptions s ON d.subscription_id = s.subscription_id 
    JOIN users u ON s.user_id = u.user_id 
    JOIN meal_plans mp ON s.plan_id = mp.plan_id 
    WHERE d.partner_id = ? AND d.delivery_date = CURDATE()
    ORDER BY d.delivery_time ASC
");
$stmt->execute([$partnerId]);
$todayDeliveryList = $stmt->fetchAll();

require_once '../includes/header.php';
?>

<div class="container-fluid px-0">
    <div class="row g-0">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3 mb-0 text-gray-800">Delivery Dashboard</h1>
                <div class="d-flex gap-2">
                    <a href="available_orders.php" class="btn btn-primary">
                        <i class="fas fa-search me-2"></i>Find Orders
                    </a>
                    <button class="btn btn-outline-primary" onclick="refreshData()">
                        <i class="fas fa-sync-alt me-2"></i>Refresh
                    </button>
                </div>
            </div>

            <!-- Statistics Cards -->
            <div class="row mb-4">
                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="stat-card primary">
                        <div class="icon">
                            <i class="fas fa-box"></i>
                        </div>
                        <h3><?php echo $totalDeliveries; ?></h3>
                        <p>Total Deliveries</p>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="stat-card success">
                        <div class="icon">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <h3><?php echo $completedDeliveries; ?></h3>
                        <p>Completed</p>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="stat-card warning">
                        <div class="icon">
                            <i class="fas fa-truck"></i>
                        </div>
                        <h3><?php echo $activeDeliveries; ?></h3>
                        <p>Active Deliveries</p>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="stat-card info">
                        <div class="icon">
                            <i class="fas fa-list"></i>
                        </div>
                        <h3><?php echo $availableDeliveries; ?></h3>
                        <p>Available Orders</p>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="quick-actions mb-4">
                <a href="available_orders.php" class="quick-action">
                    <div class="icon">
                        <i class="fas fa-search"></i>
                    </div>
                    <h6>Available Orders</h6>
                </a>
                <a href="my_deliveries.php" class="quick-action">
                    <div class="icon">
                        <i class="fas fa-truck"></i>
                    </div>
                    <h6>My Deliveries</h6>
                </a>
                <a href="delivery_history.php" class="quick-action">
                    <div class="icon">
                        <i class="fas fa-history"></i>
                    </div>
                    <h6>Delivery History</h6>
                </a>
            </div>

            <!-- Today's Deliveries -->
            <div class="row">
                <div class="col-lg-8">
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="mb-0">Today's Deliveries (<?php echo date('M j, Y'); ?>)</h5>
                        </div>
                        <div class="card-body">
                            <?php if (empty($todayDeliveryList)): ?>
                                <p class="text-muted">No deliveries scheduled for today.</p>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>Time</th>
                                                <th>Customer</th>
                                                <th>Plan</th>
                                                <th>Status</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($todayDeliveryList as $delivery): ?>
                                                <tr>
                                                    <td><?php echo date('h:i A', strtotime($delivery['delivery_time'])); ?></td>
                                                    <td>
                                                        <div>
                                                            <strong><?php echo htmlspecialchars($delivery['name']); ?></strong>
                                                            <br>
                                                            <small class="text-muted"><?php echo htmlspecialchars($delivery['phone']); ?></small>
                                                        </div>
                                                    </td>
                                                    <td><?php echo htmlspecialchars($delivery['plan_name']); ?></td>
                                                    <td>
                                                        <span class="status-indicator <?php echo $delivery['status']; ?>"></span>
                                                        <?php echo ucfirst(str_replace('_', ' ', $delivery['status'])); ?>
                                                    </td>
                                                    <td>
                                                        <div class="btn-group">
                                                            <?php if ($delivery['status'] === 'accepted'): ?>
                                                                <button class="btn btn-sm btn-warning" 
                                                                        onclick="updateDeliveryStatus(<?php echo $delivery['delivery_id']; ?>, 'out_for_delivery')">
                                                                    <i class="fas fa-truck"></i> Start Delivery
                                                                </button>
                                                            <?php elseif ($delivery['status'] === 'out_for_delivery'): ?>
                                                                <button class="btn btn-sm btn-success" 
                                                                        onclick="updateDeliveryStatus(<?php echo $delivery['delivery_id']; ?>, 'delivered')">
                                                                    <i class="fas fa-check"></i> Mark Delivered
                                                                </button>
                                                            <?php endif; ?>
                                                            <button class="btn btn-sm btn-outline-secondary" 
                                                                    onclick="addDeliveryNote(<?php echo $delivery['delivery_id']; ?>)">
                                                                <i class="fas fa-comment"></i> Note
                                                            </button>
                                                        </div>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="mb-0">Recent Activity</h5>
                        </div>
                        <div class="card-body">
                            <?php if (empty($recentDeliveries)): ?>
                                <p class="text-muted">No recent deliveries.</p>
                            <?php else: ?>
                                <div class="delivery-timeline">
                                    <?php foreach (array_slice($recentDeliveries, 0, 5) as $delivery): ?>
                                        <div class="delivery-item mb-3 p-3 border rounded">
                                            <div class="d-flex justify-content-between align-items-start">
                                                <div>
                                                    <h6 class="mb-1"><?php echo htmlspecialchars($delivery['name']); ?></h6>
                                                    <p class="text-muted mb-1"><?php echo htmlspecialchars($delivery['plan_name']); ?></p>
                                                    <small class="text-muted">
                                                        <?php echo date('M j, Y', strtotime($delivery['delivery_date'])); ?>
                                                    </small>
                                                </div>
                                                <span class="badge bg-<?php echo $delivery['status'] === 'delivered' ? 'success' : 'warning'; ?>">
                                                    <?php echo ucfirst(str_replace('_', ' ', $delivery['status'])); ?>
                                                </span>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                                <div class="text-center">
                                    <a href="delivery_history.php" class="btn btn-outline-primary">View All History</a>
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

// Auto-refresh every 30 seconds
function autoRefreshData() {
    $.get('../ajax/get_delivery_stats.php', function(data) {
        if (data.success) {
            // Update statistics without full page reload
            updateDeliveryStats(data.stats);
        }
    });
}

function updateDeliveryStats(stats) {
    // Update stat cards with new data
    // This would be implemented to update specific elements
}

setInterval(autoRefreshData, 30000);
</script>

<?php require_once '../includes/footer.php'; ?>