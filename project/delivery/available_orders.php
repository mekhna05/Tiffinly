<?php
$page_title = 'Available Orders';
require_once '../config/auth_check.php';
require_once '../config/db_connect.php';

requireDelivery();

$partnerId = getUserId();

// Get available deliveries
$stmt = $pdo->query("
    SELECT d.*, s.subscription_id, u.name, u.email, u.phone, mp.plan_name,
           a.line1, a.line2, a.city, a.state, a.pincode
    FROM deliveries d 
    JOIN subscriptions s ON d.subscription_id = s.subscription_id 
    JOIN users u ON s.user_id = u.user_id 
    JOIN meal_plans mp ON s.plan_id = mp.plan_id 
    LEFT JOIN addresses a ON u.user_id = a.user_id AND a.is_default = 1
    WHERE d.status = 'available' 
    ORDER BY d.delivery_date ASC, d.delivery_time ASC
");
$availableOrders = $stmt->fetchAll();

require_once '../includes/header.php';
?>

<div class="container-fluid px-0">
    <div class="row g-0">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3 mb-0 text-gray-800">Available Orders</h1>
                <div class="d-flex gap-2">
                    <button class="btn btn-outline-primary" onclick="refreshOrders()">
                        <i class="fas fa-sync-alt me-2"></i>Refresh
                    </button>
                    <a href="dashboard.php" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-2"></i>Back to Dashboard
                    </a>
                </div>
            </div>

            <?php if (empty($availableOrders)): ?>
                <div class="card">
                    <div class="card-body text-center py-5">
                        <i class="fas fa-truck fa-3x text-muted mb-3"></i>
                        <h4>No Available Orders</h4>
                        <p class="text-muted">All orders have been assigned. Check back later for new deliveries!</p>
                        <button class="btn btn-primary" onclick="refreshOrders()">
                            <i class="fas fa-sync-alt me-2"></i>Refresh Orders
                        </button>
                    </div>
                </div>
            <?php else: ?>
                <div class="row">
                    <?php foreach ($availableOrders as $order): ?>
                        <div class="col-lg-6 mb-4">
                            <div class="card order-card">
                                <div class="card-header d-flex justify-content-between align-items-center">
                                    <h6 class="mb-0">Delivery #<?php echo $order['delivery_id']; ?></h6>
                                    <span class="badge bg-warning">Available</span>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-8">
                                            <h5 class="card-title"><?php echo htmlspecialchars($order['name']); ?></h5>
                                            <p class="card-text">
                                                <i class="fas fa-utensils me-2"></i>
                                                <?php echo htmlspecialchars($order['plan_name']); ?> Plan
                                            </p>
                                            <p class="card-text">
                                                <i class="fas fa-calendar me-2"></i>
                                                <?php echo date('M j, Y', strtotime($order['delivery_date'])); ?>
                                            </p>
                                            <p class="card-text">
                                                <i class="fas fa-clock me-2"></i>
                                                <?php echo date('h:i A', strtotime($order['delivery_time'])); ?>
                                            </p>
                                            <p class="card-text">
                                                <i class="fas fa-phone me-2"></i>
                                                <a href="tel:<?php echo $order['phone']; ?>" class="text-decoration-none">
                                                    <?php echo htmlspecialchars($order['phone']); ?>
                                                </a>
                                            </p>
                                        </div>
                                        <div class="col-md-4 text-end">
                                            <div class="delivery-time">
                                                <?php
                                                $deliveryDateTime = strtotime($order['delivery_date'] . ' ' . $order['delivery_time']);
                                                $now = time();
                                                $timeDiff = $deliveryDateTime - $now;
                                                
                                                if ($timeDiff > 0) {
                                                    $hours = floor($timeDiff / 3600);
                                                    $minutes = floor(($timeDiff % 3600) / 60);
                                                    echo "<small class='text-muted'>In {$hours}h {$minutes}m</small>";
                                                } else {
                                                    echo "<small class='text-danger'>Overdue</small>";
                                                }
                                                ?>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <?php if ($order['line1']): ?>
                                        <div class="delivery-address mt-3">
                                            <h6><i class="fas fa-map-marker-alt me-2"></i>Delivery Address:</h6>
                                            <p class="mb-0">
                                                <?php echo htmlspecialchars($order['line1']); ?>
                                                <?php if ($order['line2']): ?>, <?php echo htmlspecialchars($order['line2']); ?><?php endif; ?>
                                                <br>
                                                <?php echo htmlspecialchars($order['city']); ?>, <?php echo htmlspecialchars($order['state']); ?> - <?php echo htmlspecialchars($order['pincode']); ?>
                                            </p>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <div class="mt-3">
                                        <button class="btn btn-success w-100" 
                                                onclick="acceptOrder(<?php echo $order['delivery_id']; ?>)">
                                            <i class="fas fa-check me-2"></i>Accept This Order
                                        </button>
                                    </div>
                                </div>
                                <div class="card-footer">
                                    <small class="text-muted">
                                        Order placed on <?php echo date('M j, Y \a\t h:i A', strtotime($order['delivery_date'])); ?>
                                    </small>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
.order-card {
    transition: all 0.3s ease;
    border-left: 4px solid var(--warning-color);
}

.order-card:hover {
    transform: translateY(-5px);
    box-shadow: var(--shadow-hover);
}

.delivery-time {
    font-size: 0.9rem;
}

.delivery-address {
    background: var(--bg-light);
    padding: 1rem;
    border-radius: 8px;
    border-left: 3px solid var(--primary-color);
}
</style>

<script>
function acceptOrder(deliveryId) {
    if (confirm('Are you sure you want to accept this delivery order?')) {
        showLoading();
        
        $.post('../ajax/delivery_actions.php', {
            action: 'accept',
            delivery_id: deliveryId
        }, function(response) {
            hideLoading();
            
            if (response.success) {
                showToast('Order accepted successfully!', 'success');
                setTimeout(() => {
                    window.location.href = 'my_deliveries.php';
                }, 1500);
            } else {
                showToast(response.message || 'Error accepting order', 'danger');
            }
        }).fail(function() {
            hideLoading();
            showToast('Network error. Please try again.', 'danger');
        });
    }
}

function refreshOrders() {
    location.reload();
}

// Auto-refresh every 30 seconds
setInterval(refreshOrders, 30000);

// Add notification sound for new orders (optional)
let lastOrderCount = <?php echo count($availableOrders); ?>;

function checkForNewOrders() {
    $.get('../ajax/get_available_orders_count.php', function(response) {
        if (response.success && response.count > lastOrderCount) {
            showToast('New orders available!', 'info');
            // Optional: Play notification sound
            // new Audio('../assets/sounds/notification.mp3').play();
        }
        lastOrderCount = response.count;
    });
}

// Check for new orders every 15 seconds
setInterval(checkForNewOrders, 15000);
</script>

<?php require_once '../includes/footer.php'; ?>