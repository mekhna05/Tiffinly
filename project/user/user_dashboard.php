<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tiffinly - User Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #FF6B35;
            --secondary: #F7931E;
            --accent: #4ECDC4;
            --dark: #2C3E50;
            --light: #FFF8E1;
            --gray: #F5F5F5;
            --text-dark: #333;
            --text-light: #777;
            --success: #4CAF50;
            --warning: #FF9800;
            --danger: #F44336;
            --border: #e0e0e0;
            --shadow: 0 4px 12px rgba(0,0,0,0.1);
            --radius: 12px;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            background-color: var(--gray);
            color: var(--text-dark);
        }

        /* Dashboard Layout */
        .dashboard {
            display: flex;
            min-height: 100vh;
        }

        /* Scrollable Sidebar */
        .sidebar {
            width: 260px;
            background: white;
            box-shadow: var(--shadow);
            position: fixed;
            height: 100vh;
            display: flex;
            flex-direction: column;
            z-index: 100;
        }

        .sidebar-header {
            padding: 1.5rem 1rem;
            border-bottom: 1px solid var(--border);
            flex-shrink: 0;
        }

        .brand {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .brand i {
            color: var(--primary);
            font-size: 1.8rem;
        }

        .brand h1 {
            font-size: 1.5rem;
            color: var(--primary);
        }

        .nav-container {
            flex: 1;
            overflow-y: auto;
            padding: 1rem 0.5rem;
            scrollbar-width: thin;
            scrollbar-color: var(--primary) transparent;
        }

        .nav-container::-webkit-scrollbar {
            width: 6px;
        }

        .nav-container::-webkit-scrollbar-thumb {
            background-color: var(--primary);
            border-radius: 3px;
        }

        .menu-header {
            font-size: 0.8rem;
            text-transform: uppercase;
            color: var(--text-light);
            margin: 1.5rem 0 0.5rem 0.5rem;
            letter-spacing: 1px;
        }

        .nav-menu {
            list-style: none;
        }

        .nav-item {
            margin: 0.25rem 0;
        }

        .nav-link {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 0.75rem 1rem;
            border-radius: var(--radius);
            color: var(--text-dark);
            text-decoration: none;
            transition: all 0.2s ease;
        }

        .nav-link i {
            width: 24px;
            text-align: center;
            color: var(--text-light);
        }

        .nav-link:hover, .nav-link.active {
            background-color: rgba(255, 107, 53, 0.1);
            color: var(--primary);
        }

        .nav-link:hover i, .nav-link.active i {
            color: var(--primary);
        }


        .logout-sidebar-btn {
            background: none;
            border: none;
            color: var(--text-light);
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            font-size: 0.9rem;
        }

        .logout-sidebar-btn:hover {
            color: var(--primary);
        }

        /* Main Content */
        .main-content {
            margin-left: 260px;
            flex: 1;
            padding: 2rem;
            transition: margin 0.3s ease;
        }

        .topbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            background: white;
            padding: 1rem 2rem;
            border-radius: var(--radius);
            box-shadow: var(--shadow);
        }

        .page-title {
            font-size: 1.8rem;
            font-weight: 600;
            color: var(--dark);
        }

        .user-display {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .username {
            font-weight: 600;
            color: var(--primary);
        }

        .logout-btn {
            background: none;
            border: none;
            color: var(--text-light);
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 5px;
            font-size: 0.9rem;
        }

        .logout-btn:hover {
            color: var(--primary);
        }

        /* Dashboard Cards */
        .welcome-card {
            background: white;
            border-radius: var(--radius);
            padding: 2rem;
            box-shadow: var(--shadow);
            margin-bottom: 2rem;
            text-align: center;
            border: 2px dashed var(--primary);
            background-color: rgba(255, 107, 53, 0.05);
        }

        .welcome-title {
            font-size: 1.5rem;
            margin-bottom: 0.5rem;
            color: var(--primary);
        }

        .welcome-text {
            color: var(--text-light);
            margin-bottom: 1.5rem;
            max-width: 600px;
            margin-left: auto;
            margin-right: auto;
        }

        .action-btns {
            display: flex;
            gap: 1rem;
            justify-content: center;
            flex-wrap: wrap;
        }

        .btn {
            padding: 0.75rem 1.5rem;
            border-radius: var(--radius);
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s ease;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            text-decoration: none;
            border: none;
        }

        .btn-primary {
            background-color: var(--primary);
            color: white;
        }

        .btn-primary:hover {
            background-color: #e05a2b;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }

        .btn-outline {
            background: transparent;
            border: 1px solid var(--primary);
            color: var(--primary);
        }

        .btn-outline:hover {
            background-color: rgba(255, 107, 53, 0.1);
        }

        /* Dashboard Sections */
        .dashboard-section {
            background: white;
            border-radius: var(--radius);
            padding: 1.5rem;
            box-shadow: var(--shadow);
            margin-bottom: 2rem;
        }

        .section-title {
            font-size: 1.2rem;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 10px;
            color: var(--dark);
        }

        .empty-state {
            text-align: center;
            padding: 3rem 1rem;
            color: var(--text-light);
        }

        .empty-state i {
            font-size: 3rem;
            margin-bottom: 1rem;
            color: #e0e0e0;
        }

        .empty-state p {
            margin-bottom: 1.5rem;
        }

        /* Quick Links */
        .quick-links {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 1rem;
        }

        .quick-link {
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 1.5rem 1rem;
            border-radius: var(--radius);
            background-color: var(--gray);
            text-decoration: none;
            color: var(--text-dark);
            transition: all 0.2s ease;
        }

        .quick-link:hover {
            background-color: rgba(255, 107, 53, 0.1);
            color: var(--primary);
            transform: translateY(-3px);
        }

        .quick-link i {
            font-size: 1.8rem;
            margin-bottom: 1rem;
            color: var(--primary);
        }

        .quick-link span {
            font-weight: 500;
            text-align: center;
        }

        /* Responsive */
        @media (max-width: 992px) {
            .sidebar {
                width: 80px;
            }
            .brand h1, .menu-header, .nav-link span {
                display: none;
            }
            .brand {
                justify-content: center;
            }
            .nav-link {
                justify-content: center;
                padding: 0.75rem 0.25rem;
            }
            .main-content {
                margin-left: 80px;
            }
           
        }

        @media (max-width: 768px) {
            .sidebar {
                width: 100%;
                height: auto;
                position: static;
                margin-bottom: 1rem;
            }
            .main-content {
                margin-left: 0;
            }
            .dashboard {
                flex-direction: column;
            }
            .brand h1, .menu-header, .nav-link span {
                display: block;
            }
            .nav-link {
                justify-content: flex-start;
                padding: 0.75rem 1rem;
            }
            .quick-links {
                grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
            }
            
        }
    </style>
</head>
<body>
    <div class="dashboard">
        <!-- Scrollable Sidebar -->
        <aside class="sidebar">
            <div class="sidebar-header">
                <div class="brand">
                    <i class="fas fa-utensils"></i>
                    <h1>Tiffinly</h1>
                </div>
            </div>
            
            <div class="nav-container">
                <ul class="nav-menu">
                    <li class="nav-item">
                        <a href="profile.php" class="nav-link active">
                            <i class="fas fa-user"></i>
                            <span>My Profile</span>
                        </a>
                    </li>
                    
                    <!-- Meal Planning -->
                    <li class="menu-header">Meal Planning</li>
                    <li class="nav-item">
                        <a href="browse_plans.php" class="nav-link">
                            <i class="fas fa-list"></i>
                            <span>Browse Plans</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="compare_plans.php" class="nav-link">
                            <i class="fas fa-balance-scale"></i>
                            <span>Compare Plans</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="select_plan.php" class="nav-link">
                            <i class="fas fa-check-circle"></i>
                            <span>Select Plan</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="customize_meals.php" class="nav-link">
                            <i class="fas fa-sliders-h"></i>
                            <span>Customize Meals</span>
                        </a>
                    </li>
                    
                    <!-- Order Management -->
                    <li class="menu-header">Order Management</li>
                    <li class="nav-item">
                        <a href="cart.php" class="nav-link">
                            <i class="fas fa-shopping-cart"></i>
                            <span>My Cart</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="delivery_preferences.php" class="nav-link">
                            <i class="fas fa-truck"></i>
                            <span>Delivery Preferences</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="payment.php" class="nav-link">
                            <i class="fas fa-credit-card"></i>
                            <span>Payment</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="order_confirmation.php" class="nav-link">
                            <i class="fas fa-receipt"></i>
                            <span>Order Confirmation</span>
                        </a>
                    </li>
                    
                    <!-- History & Tracking -->
                    <li class="menu-header">History & Tracking</li>
                    <li class="nav-item">
                        <a href="order_history.php" class="nav-link">
                            <i class="fas fa-history"></i>
                            <span>Order History</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="track_order.php" class="nav-link">
                            <i class="fas fa-map-marker-alt"></i>
                            <span>Track Order</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="subscription_history.php" class="nav-link">
                            <i class="fas fa-calendar-alt"></i>
                            <span>Subscription History</span>
                        </a>
                    </li>
                    
                    <!-- Feedback & Support -->
                    <li class="menu-header">Feedback & Support</li>
                    <li class="nav-item">
                        <a href="meal_feedback.php" class="nav-link">
                            <i class="fas fa-comment-alt"></i>
                            <span>Meal Feedback</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="send_inquiry.php" class="nav-link">
                            <i class="fas fa-envelope"></i>
                            <span>Send Inquiry</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="my_inquiries.php" class="nav-link">
                            <i class="fas fa-question-circle"></i>
                            <span>My Inquiries</span>
                        </a>
                    </li>
                </ul>
            </div>
            
        
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <div class="topbar">
                <h1 class="page-title">Dashboard</h1>
                <div class="user-display">
                    <span class="username">Welcome, Sarah Johnson</span>
                    <button class="logout-btn">
                        <i class="fas fa-sign-out-alt"></i>
                        <span>Logout</span>
                    </button>
                </div>
            </div>

            <!-- Welcome Card -->
            <div class="welcome-card">
                <h2 class="welcome-title">Welcome to Tiffinly!</h2>
                <p class="welcome-text">
                    Get started by exploring our meal plans and customizing your weekly menu. 
                    We offer fresh, healthy meals delivered right to your doorstep.
                </p>
                <div class="action-btns">
                    <a href="browse_plans.php" class="btn btn-primary">
                        <i class="fas fa-utensils"></i> Browse Meal Plans
                    </a>
                    <a href="compare_plans.php" class="btn btn-outline">
                        <i class="fas fa-balance-scale"></i> Compare Plans
                    </a>
                </div>
            </div>

            <!-- Quick Access Section -->
            <div class="dashboard-section">
                <h3 class="section-title">
                    <i class="fas fa-bolt"></i>
                    <span>Quick Access</span>
                </h3>
                <div class="quick-links">
                    <a href="select_plan.php" class="quick-link">
                        <i class="fas fa-check-circle"></i>
                        <span>Select Plan</span>
                    </a>
                    <a href="customize_meals.php" class="quick-link">
                        <i class="fas fa-sliders-h"></i>
                        <span>Customize Meals</span>
                    </a>
                    <a href="delivery_preferences.php" class="quick-link">
                        <i class="fas fa-truck"></i>
                        <span>Delivery Setup</span>
                    </a>
                    <a href="meal_feedback.php" class="quick-link">
                        <i class="fas fa-comment-alt"></i>
                        <span>Give Feedback</span>
                    </a>
                </div>
            </div>

            <!-- Order Status Section -->
            <div class="dashboard-section">
                <h3 class="section-title">
                    <i class="fas fa-shopping-bag"></i>
                    <span>Your Orders</span>
                </h3>
                <div class="empty-state">
                    <i class="fas fa-shopping-bag"></i>
                    <h4>No Orders Yet</h4>
                    <p>You haven't placed any orders yet. Get started by selecting a meal plan.</p>
                    <a href="browse_plans.php" class="btn btn-primary">
                        <i class="fas fa-utensils"></i> Browse Plans
                    </a>
                </div>
            </div>

            <!-- Recent Activity Section -->
            <div class="dashboard-section">
                <h3 class="section-title">
                    <i class="fas fa-clock"></i>
                    <span>Recent Activity</span>
                </h3>
                <div class="empty-state">
                    <i class="fas fa-clock"></i>
                    <h4>No Recent Activity</h4>
                    <p>Your activity will appear here once you start using Tiffinly.</p>
                </div>
            </div>
        </main>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Sidebar scroll effect
            const navContainer = document.querySelector('.nav-container');
            navContainer.addEventListener('scroll', function() {
                if(this.scrollTop > 10) {
                    this.style.boxShadow = 'inset 0 5px 5px -5px rgba(0,0,0,0.1)';
                } else {
                    this.style.boxShadow = 'none';
                }
            });

            // Logout confirmation (both buttons)
            const logoutBtns = document.querySelectorAll('.logout-btn, .logout-sidebar-btn');
            logoutBtns.forEach(btn => {
                btn.addEventListener('click', function() {
                    if(confirm('Are you sure you want to logout?')) {
                        window.location.href = 'logout.php';
                    }
                });
            });

            // Quick link animations
            const quickLinks = document.querySelectorAll('.quick-link');
            quickLinks.forEach(link => {
                link.addEventListener('mouseenter', function() {
                    const icon = this.querySelector('i');
                    icon.style.transform = 'scale(1.1)';
                });
                link.addEventListener('mouseleave', function() {
                    const icon = this.querySelector('i');
                    icon.style.transform = 'scale(1)';
                });
            });

            // Mobile menu toggle (for smaller screens)
            const sidebar = document.querySelector('.sidebar');
            function checkScreenSize() {
                if(window.innerWidth <= 768) {
                    sidebar.classList.add('mobile-collapsed');
                } else {
                    sidebar.classList.remove('mobile-collapsed');
                }
            }
            
            window.addEventListener('resize', checkScreenSize);
            checkScreenSize();
        });
    </script>
</body>
</html>