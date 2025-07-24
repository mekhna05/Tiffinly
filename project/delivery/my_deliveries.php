<?php
$page_title = 'My Deliveries';
require_once '../config/auth_check.php';
require_once '../config/db_connect.php';

requireDelivery();

$partnerId = getUserId();

// Get partner's accepted deliveries
$stmt = $pdo->prepare("
    SELECT d.*, s.subscription_id, u.name, u.email, u.phone, mp.plan_name,
           a.line1, a.line2, a.city, a.state, a.pincode
    FROM deliveries d 
    JOIN subscriptions s ON d.subscription_id = s.subscription_id 
    JOIN users u ON s.user_id = u.user_id 
    JOIN meal_plans mp ON s.plan_id = mp.plan_id 
    LEFT JOIN addresses a ON u.user_id = a.user_id AND a.is_default = 1
    WHERE d.partner_id = ? AND d.status IN ('accepted', 'out_for_delivery')
    ORDER BY d.delivery_date ASC, d.delivery_time ASC
");
$stmt->execute([$partnerId]);
$myDeliveries = $stmt->fetchAll();

require_once '../includes/header.php';
?>

<div class="container-fluid px-0">
    <div class="row g-0">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3 mb-0 text-gray-800">My Deliveries</h1>
                <div class="d-flex gap-2">
                    <a href="available_orders.php" class="btn btn-primary">
                        <i class="fas fa-search me-2"></i>Find More Orders
                    </a>
                    <a href="dashboard.php" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-2"></i>Dashboard
                    </a>
                </div>
            </div>

            <?php if (empty($myDeliveries)): ?>
                <div class="card">
                    <div class="card-body text-center py-5">
                        <i class="fas fa-clipboard-list fa-3x text-muted mb-3"></i>
                        <h4>No Active Deliveries</h4>
                        <p class="text-muted">You haven't accepted any deliveries yet. Check available orders to get started!</p>
                        <a href="available_orders.php" class="btn btn-primary">
                            <i class="fas fa-search me-2"></i>Browse Available Orders
                        </a>
                    </div>
                </div>
            <?php else: ?>
                <div class="row">
                    <?php foreach ($myDeliveries as $delivery): ?>
                        <div class="col-lg-6 mb-4">
                            <div class="card delivery-card status-<?php echo $delivery['status']; ?>">
                                <div class="card-header d-flex justify-content-between align-items-center">
                                    <h6 class="mb-0">Delivery #<?php echo $delivery['delivery_id']; ?></h6>
                                    <span class="badge bg-<?php echo getStatusColor($delivery['status']); ?>">
                                        <?php echo getStatusText($delivery['status']); ?>
                                    </span>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-8">
                                            <h5 class="card-title"><?php echo htmlspecialchars($delivery['name']); ?></h5>
                                            <p class="card-text">
                                                <i class="fas fa-utensils me-2"></i>
                                                <?php echo htmlspecialchars($delivery['plan_name']); ?> Plan
                                            </p>
                                            <p class="card-text">
                                                <i class="fas fa-calendar me-2"></i>
                                                <?php echo date('M j, Y', strtotime($delivery['delivery_date'])); ?>
                                            </p>
                                            <p class="card-text">
                                                <i class="fas fa-clock me-2"></i>
                                                <?php echo date('h:i A', strtotime($delivery['delivery_time'])); ?>
                                            </p>
                                            <p class="card-text">
                                                <i class="fas fa-phone me-2"></i>
                                                <a href="tel:<?php echo $delivery['phone']; ?>" class="text-decoration-none">
                                                    <?php echo htmlspecialchars($delivery['phone']); ?>
                                                </a>
                                            </p>
                                        </div>
                                        <div class="col-md-4 text-end">
                                            <div class="delivery-actions">
                                                <?php if ($delivery['status'] === 'accepted'): ?>
                                                    <button class="btn btn-warning btn-sm mb-2" 
                                                            onclick="updateStatus(<?php echo $delivery['delivery_id']; ?>, 'out_for_delivery')">
                                                        <i class="fas fa-truck me-1"></i>Start Delivery
                                                    </button>
                                                <?php elseif ($delivery['status'] === 'out_for_delivery'): ?>
                                                    <button class="btn btn-success btn-sm mb-2" 
                                                            onclick="updateStatus(<?php echo $delivery['delivery_id']; ?>, 'delivered')">
                                                        <i class="fas fa-check me-1"></i>Mark Delivered
                                                    </button>
                                                <?php endif; ?>
                                                
                                                <button class="btn btn-outline-secondary btn-sm" 
                                                        onclick="addNote(<?php echo $delivery['delivery_id']; ?>)">
                                                    <i class="fas fa-comment me-1"></i>Add Note
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <?php if ($delivery['line1']): ?>
                                        <div class="delivery-address mt-3">
                                            <h6><i class="fas fa-map-marker-alt me-2"></i>Delivery Address:</h6>
                                            <p class="mb-0">
                                                <?php echo htmlspecialchars($delivery['line1']); ?>
                                                <?php if ($delivery['line2']): ?>, <?php echo htmlspecialchars($delivery['line2']); ?><?php endif; ?>
                                                <br>
                                                <?php echo htmlspecialchars($delivery['city']); ?>, <?php echo htmlspecialchars($delivery['state']); ?> - <?php echo htmlspecialchars($delivery['pincode']); ?>
                                            </p>
                                            <button class="btn btn-outline-primary btn-sm mt-2" 
                                                    onclick="openMaps('<?php echo urlencode($delivery['line1'] . ', ' . $delivery['city'] . ', ' . $delivery['state'] . ' ' . $delivery['pincode']); ?>')">
                                                <i class="fas fa-map me-1"></i>Open in Maps
                                            </button>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <?php if ($delivery['notes']): ?>
                                        <div class="delivery-notes mt-3">
                                            <h6><i class="fas fa-sticky-note me-2"></i>Notes:</h6>
                                            <p class="mb-0 text-muted"><?php echo nl2br(htmlspecialchars($delivery['notes'])); ?></p>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <div class="card-footer">
                                    <small class="text-muted">
                                        Accepted on <?php echo date('M j, Y \a\t h:i A', strtotime($delivery['accepted_at'])); ?>
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
.delivery-card {
    transition: all 0.3s ease;
}

.delivery-card.status-accepted {
    border-left: 4px solid var(--info-color);
}

.delivery-card.status-out_for_delivery {
    border-left: 4px solid var(--warning-color);
}

.delivery-card:hover {
    transform: translateY(-5px);
    box-shadow: var(--shadow-hover);
}

.delivery-address {
    background: var(--bg-light);
    padding: 1rem;
    border-radius: 8px;
    border-left: 3px solid var(--primary-color);
}

.delivery-notes {
    background: #fff3cd;
    padding: 1rem;
    border-radius: 8px;
    border-left: 3px solid #ffc107;
}

.delivery-actions {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}
</style>

<script>
function updateStatus(deliveryId, status) {
    const statusText = {
        'out_for_delivery': 'start delivery',
        'delivered': 'mark as delivered'
    };
    
    if (confirm(`Are you sure you want to ${statusText[status]}?`)) {
        showLoading();
        
        $.post('../ajax/delivery_actions.php', {
            action: 'update_status',
            delivery_id: deliveryId,
            status: status
        }, function(response) {
            hideLoading();
            
            if (response.success) {
                showToast('Status updated successfully!', 'success');
                setTimeout(() => {
                    location.reload();
                }, 1500);
            } else {
                showToast(response.message || 'Error updating status', 'danger');
            }
        }).fail(function() {
            hideLoading();
            showToast('Network error. Please try again.', 'danger');
        });
    }
}

function addNote(deliveryId) {
    const note = prompt('Enter delivery note:');
    if (note && note.trim()) {
        showLoading();
        
        $.post('../ajax/delivery_actions.php', {
            action: 'add_note',
            delivery_id: deliveryId,
            note: note.trim()
        }, function(response) {
            hideLoading();
            
            if (response.success) {
                showToast('Note added successfully!', 'success');
                setTimeout(() => {
                    location.reload();
                }, 1500);
            } else {
                showToast(response.message || 'Error adding note', 'danger');
            }
        }).fail(function() {
            hideLoading();
            showToast('Network error. Please try again.', 'danger');
        });
    }
}

function openMaps(address) {
    const encodedAddress = encodeURIComponent(address);
    const mapsUrl = `https://www.google.com/maps/search/?api=1&query=${encodedAddress}`;
    window.open(mapsUrl, '_blank');
}

// Auto-refresh every 30 seconds
setInterval(function() {
    location.reload();
}, 30000);
</script>

<?php require_once '../includes/footer.php'; ?>

<?php
function getStatusColor($status) {
    switch ($status) {
        case 'accepted': return 'info';
        case 'out_for_delivery': return 'warning';
        case 'delivered': return 'success';
        case 'failed': return 'danger';
        default: return 'secondary';
    }
}

function getStatusText($status) {
    switch ($status) {
        case 'accepted': return 'Accepted';
        case 'out_for_delivery': return 'Out for Delivery';
        case 'delivered': return 'Delivered';
        case 'failed': return 'Failed';
        default: return ucfirst($status);
    }
}
?>