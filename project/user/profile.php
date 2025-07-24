<?php
session_start();
require_once '../config/db_connect.php'; // Database connection file

// Redirect to login if not authenticated
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Initialize variables
$error = '';
$success = '';
$name = $email = $phone = '';

// Fetch user data
$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT name, email, phone FROM users WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if ($user) {
    $name = htmlspecialchars($user['name']);
    $email = htmlspecialchars($user['email']);
    $phone = htmlspecialchars($user['phone']);
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate inputs
    $name = trim($_POST['name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    // Basic validation
    if (empty($name)) {
        $error = "Name is required";
    } elseif (!preg_match("/^[a-zA-Z ]*$/", $name)) {
        $error = "Only letters and spaces allowed in name";
    } elseif (!empty($phone) && !preg_match("/^[0-9]{10,15}$/", $phone)) {
        $error = "Invalid phone number format";
    } else {
        try {
            // Start transaction
            $conn->begin_transaction();

            // Update basic info
            $stmt = $conn->prepare("UPDATE users SET name = ?, phone = ? WHERE user_id = ?");
            $stmt->bind_param("ssi", $name, $phone, $user_id);
            $stmt->execute();

            // Handle password change if provided
            if (!empty($current_password) || !empty($new_password) || !empty($confirm_password)) {
                if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
                    throw new Exception("All password fields are required for password change");
                }

                if ($new_password !== $confirm_password) {
                    throw new Exception("New passwords don't match");
                }

                if (strlen($new_password) < 8) {
                    throw new Exception("Password must be at least 8 characters");
                }

                // Verify current password
                $stmt = $conn->prepare("SELECT password FROM users WHERE user_id = ?");
                $stmt->bind_param("i", $user_id);
                $stmt->execute();
                $result = $stmt->get_result();
                $user = $result->fetch_assoc();

                if (!password_verify($current_password, $user['password'])) {
                    throw new Exception("Current password is incorrect");
                }

                // Update password
                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                $stmt = $conn->prepare("UPDATE users SET password = ? WHERE user_id = ?");
                $stmt->bind_param("si", $hashed_password, $user_id);
                $stmt->execute();
            }

            // Commit transaction
            $conn->commit();
            $success = "Profile updated successfully!";
            
            // Update session with new name
            $_SESSION['username'] = $name;

        } catch (Exception $e) {
            $conn->rollback();
            $error = $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile | Tiffinly</title>
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

        .profile-container {
            max-width: 800px;
            margin: 2rem auto;
            padding: 2rem;
            background: white;
            border-radius: var(--radius);
            box-shadow: var(--shadow);
        }

        .profile-header {
            text-align: center;
            margin-bottom: 2rem;
        }

        .profile-header h1 {
            color: var(--primary);
            margin-bottom: 0.5rem;
        }

        .profile-header p {
            color: var(--text-light);
        }

        .profile-form {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1.5rem;
        }

        .form-group {
            margin-bottom: 1rem;
        }

        .form-group.full-width {
            grid-column: span 2;
        }

        label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: var(--text-dark);
        }

        input, select {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid var(--border);
            border-radius: var(--radius);
            font-size: 1rem;
        }

        input:focus, select:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 2px rgba(255, 107, 53, 0.2);
        }

        .btn {
            padding: 0.75rem 1.5rem;
            border-radius: var(--radius);
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s ease;
            border: none;
            font-size: 1rem;
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

        .alert {
            padding: 1rem;
            border-radius: var(--radius);
            margin-bottom: 1.5rem;
            grid-column: span 2;
        }

        .alert-success {
            background-color: rgba(76, 175, 80, 0.1);
            color: var(--success);
            border: 1px solid var(--success);
        }

        .alert-error {
            background-color: rgba(244, 67, 54, 0.1);
            color: var(--danger);
            border: 1px solid var(--danger);
        }

        .password-toggle {
            position: relative;
        }

        .password-toggle i {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: var(--text-light);
        }

        @media (max-width: 768px) {
            .profile-form {
                grid-template-columns: 1fr;
            }
            
            .form-group.full-width {
                grid-column: span 1;
            }
        }
    </style>
</head>
<body>
    <?php include '../includes/header.php'; // Include your header/navigation ?>

    <div class="profile-container">
        <div class="profile-header">
            <h1>My Profile</h1>
            <p>Manage your account information and settings</p>
        </div>

        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>

        <form class="profile-form" method="POST" action="profile.php">
            <div class="form-group">
                <label for="name">Full Name</label>
                <input type="text" id="name" name="name" value="<?php echo $name; ?>" required>
            </div>

            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" value="<?php echo $email; ?>" disabled>
            </div>

            <div class="form-group">
                <label for="phone">Phone Number</label>
                <input type="tel" id="phone" name="phone" value="<?php echo $phone; ?>">
            </div>

            <div class="form-group full-width">
                <h3>Change Password</h3>
                <p>Leave blank to keep current password</p>
            </div>

            <div class="form-group password-toggle">
                <label for="current_password">Current Password</label>
                <input type="password" id="current_password" name="current_password">
                <i class="fas fa-eye" onclick="togglePassword('current_password')"></i>
            </div>

            <div class="form-group password-toggle">
                <label for="new_password">New Password</label>
                <input type="password" id="new_password" name="new_password">
                <i class="fas fa-eye" onclick="togglePassword('new_password')"></i>
            </div>

            <div class="form-group password-toggle">
                <label for="confirm_password">Confirm New Password</label>
                <input type="password" id="confirm_password" name="confirm_password">
                <i class="fas fa-eye" onclick="togglePassword('confirm_password')"></i>
            </div>

            <div class="form-group full-width" style="text-align: right;">
                <button type="submit" class="btn btn-primary">Update Profile</button>
            </div>
        </form>
    </div>

    <script>
        // Toggle password visibility
        function togglePassword(id) {
            const input = document.getElementById(id);
            const icon = input.nextElementSibling;
            
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.replace('fa-eye', 'fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.replace('fa-eye-slash', 'fa-eye');
            }
        }

        // Form validation
        document.querySelector('form').addEventListener('submit', function(e) {
            const newPassword = document.getElementById('new_password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            
            // Only validate passwords if at least one is filled
            if (newPassword || confirmPassword) {
                if (newPassword !== confirmPassword) {
                    e.preventDefault();
                    alert('New passwords do not match');
                    return false;
                }
                
                if (newPassword.length < 8) {
                    e.preventDefault();
                    alert('Password must be at least 8 characters');
                    return false;
                }
                
                if (!document.getElementById('current_password').value) {
                    e.preventDefault();
                    alert('Please enter your current password to change it');
                    return false;
                }
            }
            return true;
        });
    </script>
</body>
</html>