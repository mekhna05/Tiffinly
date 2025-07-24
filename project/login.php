<?php
session_start();

// Limiting login attempts
if (isset($_SESSION['login_attempts']) && $_SESSION['login_attempts'] > 5) {
    die("Too many login attempts. Try again later.");
}

// Redirect if already logged in
if (isset($_SESSION['user_id']) && isset($_SESSION['role'])) {
    switch ($_SESSION['role']) {
        case 'admin':
            header('Location: admin/admin_dashboard.php');
            break;
        case 'delivery':
            header('Location: delivery/partner_dashboard.php');
            break;
        default:
            header('Location: user/user_dashboard.php');
    }
    exit;
}

// DB connection
$host = "127.0.0.1";
$user = "root";
$password = "";
$database = "tiffinly";
$port = 3307;

$conn = new mysqli($host, $user, $password, $database, $port);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Disable caching
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");

$error = $_SESSION['form_error'] ?? '';
$email = $_SESSION['old_email'] ?? '';
$role = $_SESSION['old_role'] ?? 'user';
$success = '';
$redirectTo = '';
$loginSuccess = false;

// Clear flash messages
unset($_SESSION['form_error']);
unset($_SESSION['old_email']);
unset($_SESSION['old_role']);

//PHP Server side validations
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $role = $_POST['userRole'] ?? 'user';
    $errors = [];
    
    // Email format validation
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Valid email is required";
    }
    
    // disposable email format validation
    $disallowedDomains = ['tempmail.com', 'mailinator.com'];
    $emailDomain = explode('@', $email)[1] ?? '';
    if (in_array(strtolower($emailDomain), $disallowedDomains)) {
        $errors[] = "Disposable email addresses are not allowed";
    }
    
    // Password empty check
    if (empty($password)) {
        $errors[] = "Password is required";
    }
    
    // Password length validation
    if (strlen($password) < 8) {
        $errors[] = "Password must be at least 8 characters";
    }
    
    //Password format validation
    if (!preg_match("/[A-Z]/", $password) || !preg_match("/[0-9]/", $password)) {
        $errors[] = "Password must contain at least one uppercase letter and one number";
    }
    
    // Role validation
    if (!in_array($role, ['user', 'admin', 'delivery'])) {
        $errors[] = "Invalid role selected";
    }
    
    // Check if there are any errors
    if (empty($errors)) {
        $stmt = $conn->prepare("SELECT * FROM users WHERE email = ? AND role = ?");
        $stmt->bind_param("ss", $email, $role);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        
        //Check if user exist and password matches
        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['name'] = $user['name'];
            $_SESSION['login_time'] = time();
            // Reset login attempts on successful login
            $_SESSION['login_attempts'] = 0;
            $loginSuccess = true;
            
            // Redirect based on user role
            switch ($user['role']) {
                case 'admin':
                    $redirectTo = 'admin/admin_dashboard.php';
                    break;
                case 'delivery':
                    $redirectTo = 'delivery/partner_dashboard.php';
                    break;
                default:
                    $redirectTo = 'user/user_dashboard.php';
            }
            
            header("Location: $redirectTo");
            exit;
        } else {
            // If user does not exist or password does not match
            $error = "Invalid email or password for the selected role.";
            $_SESSION['form_error'] = $error;
            $_SESSION['old_email'] = $email;
            $_SESSION['old_role'] = $role;
            header("Location: " . $_SERVER['PHP_SELF']);
            exit;
        }
        $stmt->close();
    } else {
        $error = implode('<br>', $errors);
        $_SESSION['form_error'] = $error;
        $_SESSION['old_email'] = $email;
        $_SESSION['old_role'] = $role;
        header("Location: " . $_SERVER['PHP_SELF']);
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Tiffinly</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="css/login.css" rel="stylesheet">
</head>
<body>
    <div class="auth-container">
        <div class="auth-background"></div>

        <div class="container-fluid">
            <div class="row min-vh-100">
                <!-- Left Side - Branding -->
                <div class="col-lg-6 d-none d-lg-flex auth-brand-side">
                    <div class="auth-brand-content">
                        <div class="brand-header">
                            <h1><i class="fas fa-utensils me-3"></i>Tiffinly</h1>
                            <p class="brand-tagline">Authentic Home Cooked Meals</p>
                        </div>

                        <div class="brand-features">
                            <div class="feature-item">
                                <div class="feature-icon"><i class="fas fa-home"></i></div>
                                <div class="feature-content">
                                    <h4>Home-Style Cooking</h4>
                                    <p>Fresh, healthy meals prepared with traditional recipes</p>
                                </div>
                            </div>
                            <div class="feature-item">
                                <div class="feature-icon"><i class="fas fa-clock"></i></div>
                                <div class="feature-content">
                                    <h4>On-Time Delivery</h4>
                                    <p>Reliable delivery service by trusted partners</p>
                                </div>
                            </div>
                            <div class="feature-item">
                                <div class="feature-icon"><i class="fas fa-star"></i></div>
                                <div class="feature-content">
                                    <h4>Quality Assured</h4>
                                    <p>Premium ingredients and authentic Indian flavors</p>
                                </div>
                            </div>
                        </div>

                        <div class="brand-stats">
                            <div class="stat-item"><span class="stat-number">5K+</span><span class="stat-label">Happy Customers</span></div>
                            <div class="stat-item"><span class="stat-number">20+</span><span class="stat-label">Dishes Available</span></div>
                            <div class="stat-item"><span class="stat-number">99%</span><span class="stat-label">Satisfaction</span></div>
                        </div>
                    </div>
                </div>

                <!-- Right Side - Login Form -->
                <div class="col-lg-6 auth-form-side">
                    <div class="auth-form-container">
                        <div class="auth-header">
                            <h2>Welcome Back!</h2>
                            <p>Please sign in to your account</p>
                        </div>
                        
                        <?php if (!empty($error)): ?>
                            <div class="alert alert-danger mb-4"><?php echo $error; ?></div>
                        <?php endif; ?>
                            

    <!-- Login Form  -->
    <form method="POST" class="auth-form" id="loginForm" autocomplete="off" novalidate>
    <!-- Role Selection -->
    <div class="role-selection">
        <h5>Select Your Role</h5>
        <div class="role-options">
            <input type="radio" name="userRole" id="admin" value="admin"
                <?php echo ($role === 'admin') ? 'checked' : ''; ?>>
            <label for="admin" class="role-option">
                <div class="role-icon"><i class="fas fa-user-shield"></i></div>
                <span>Admin</span>
            </label>

            <input type="radio" name="userRole" id="customer" value="user"
                <?php echo ($role === 'user') ? 'checked' : ''; ?>>
            <label for="customer" class="role-option">
                <div class="role-icon"><i class="fas fa-user"></i></div>
                <span>Customer</span>
            </label>

            <input type="radio" name="userRole" id="delivery" value="delivery"
                <?php echo ($role === 'delivery') ? 'checked' : ''; ?>>
            <label for="delivery" class="role-option">
                <div class="role-icon"><i class="fas fa-motorcycle"></i></div>
                <span>Delivery Partner</span>
            </label>
        </div>
    </div>

    <div class="form-group">
        <label for="email">Email Address</label>
        <div class="input-group">
            <span class="input-icon"><i class="fas fa-envelope"></i></span>
            <input type="email" class="form-control" id="email" name="email"
                   placeholder="Enter your email"
                   value="<?php echo htmlspecialchars($email); ?>" required>
        </div>
    </div>

    <div class="form-group">
        <label for="password">Password</label>
        <div class="input-group">
            <span class="input-icon"><i class="fas fa-lock"></i></span>
            <input type="password" class="form-control" id="password" name="password"
                   placeholder="Enter your password" required minlength="6">
            <span class="password-toggle" onclick="togglePassword()">
                <i class="fas fa-eye" id="passwordToggleIcon"></i>
            </span>
        </div>
    </div>
                            
    <div class="form-options">
        <div class="form-check">
            <input type="checkbox" class="form-check-input" id="remember" name="remember">
            <label class="form-check-label" for="remember">Remember me</label>
        </div>
        <a href="forgot_password.php" class="forgot-password">Forgot Password?</a>
    </div>

    <button type="submit" class="btn btn-primary btn-lg w-100 login-btn">
        <span class="btn-text">Sign In</span>
    </button>
</form>

                            <div class="auth-footer">
                            <p>Don't have an account? <a href="register.php">Sign up here</a></p>
                            <p><a href="index.php">← Back to Home</a></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/login.js"></script>
</body>
</html>