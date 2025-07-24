<?php
$userRole = getUserRole();
?>
<div class="sidebar-content">
    <div class="sidebar-header">
        <h5><i class="fas fa-tachometer-alt me-2"></i>Dashboard</h5>
    </div>
    
    <ul class="sidebar-nav">
        <?php if ($userRole === 'user'): ?>
            <li class="nav-item">
                <a class="nav-link <?php echo ($current_page == 'dashboard.php') ? 'active' : ''; ?>" href="dashboard.php">
                    <i class="fas fa-home me-2"></i>Dashboard
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo ($current_page == 'browse_plans.php') ? 'active' : ''; ?>" href="browse_plans.php">
                    <i class="fas fa-utensils me-2"></i>Browse Plans
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo ($current_page == 'customize_meals.php') ? 'active' : ''; ?>" href="customize_meals.php">
                    <i class="fas fa-edit me-2"></i>Customize Meals
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo ($current_page == 'cart.php') ? 'active' : ''; ?>" href="cart.php">
                    <i class="fas fa-shopping-cart me-2"></i>My Cart
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo ($current_page == 'my_orders.php') ? 'active' : ''; ?>" href="my_orders.php">
                    <i class="fas fa-list me-2"></i>My Orders
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo ($current_page == 'track_order.php') ? 'active' : ''; ?>" href="track_order.php">
                    <i class="fas fa-truck me-2"></i>Track Order
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo ($current_page == 'feedback.php') ? 'active' : ''; ?>" href="feedback.php">
                    <i class="fas fa-star me-2"></i>Feedback
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo ($current_page == 'support.php') ? 'active' : ''; ?>" href="support.php">
                    <i class="fas fa-question-circle me-2"></i>Support
                </a>
            </li>
        <?php elseif ($userRole === 'admin'): ?>
            <li class="nav-item">
                <a class="nav-link <?php echo ($current_page == 'dashboard.php') ? 'active' : ''; ?>" href="dashboard.php">
                    <i class="fas fa-home me-2"></i>Dashboard
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo ($current_page == 'manage_meals.php') ? 'active' : ''; ?>" href="manage_meals.php">
                    <i class="fas fa-utensils me-2"></i>Manage Meals
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo ($current_page == 'manage_users.php') ? 'active' : ''; ?>" href="manage_users.php">
                    <i class="fas fa-users me-2"></i>Manage Users
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo ($current_page == 'manage_subscriptions.php') ? 'active' : ''; ?>" href="manage_subscriptions.php">
                    <i class="fas fa-calendar-alt me-2"></i>Subscriptions
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo ($current_page == 'manage_deliveries.php') ? 'active' : ''; ?>" href="manage_deliveries.php">
                    <i class="fas fa-truck me-2"></i>Deliveries
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo ($current_page == 'manage_partners.php') ? 'active' : ''; ?>" href="manage_partners.php">
                    <i class="fas fa-handshake me-2"></i>Partners
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo ($current_page == 'inquiries.php') ? 'active' : ''; ?>" href="inquiries.php">
                    <i class="fas fa-envelope me-2"></i>Inquiries
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo ($current_page == 'reports.php') ? 'active' : ''; ?>" href="reports.php">
                    <i class="fas fa-chart-bar me-2"></i>Reports
                </a>
            </li>
        <?php elseif ($userRole === 'delivery'): ?>
            <li class="nav-item">
                <a class="nav-link <?php echo ($current_page == 'dashboard.php') ? 'active' : ''; ?>" href="dashboard.php">
                    <i class="fas fa-home me-2"></i>Dashboard
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo ($current_page == 'available_orders.php') ? 'active' : ''; ?>" href="available_orders.php">
                    <i class="fas fa-list me-2"></i>Available Orders
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo ($current_page == 'my_deliveries.php') ? 'active' : ''; ?>" href="my_deliveries.php">
                    <i class="fas fa-truck me-2"></i>My Deliveries
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo ($current_page == 'delivery_history.php') ? 'active' : ''; ?>" href="delivery_history.php">
                    <i class="fas fa-history me-2"></i>History
                </a>
            </li>
        <?php endif; ?>
    </ul>
</div>