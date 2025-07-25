<?php
session_start();
ini_set('display_errors', 1);
error_reporting(E_ALL);

// DB connection
require_once 'config/db_connect.php';

$step = 1;
$error = '';
$success = '';
$email = '';
$question = '';
$showResetForm = false;

// Handle Step 1: Submit Email
if (isset($_POST['submit_email'])) {
    $email = trim($_POST['email'] ?? '');
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Please enter a valid email address.";
    } else {
        $stmt = $conn->prepare("SELECT security_question FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->bind_result($question);
        if ($stmt->fetch()) {
            $step = 2;
        } else {
            $error = "No account found with that email.";
        }
        $stmt->close();
    }
}

// Handle Step 2: Submit Answer
if (isset($_POST['submit_answer'])) {
    $email = trim($_POST['email'] ?? '');
    $answer = trim($_POST['security_answer'] ?? '');

    $stmt = $conn->prepare("SELECT security_question, security_answer FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->bind_result($question, $hashedAnswer);
    if ($stmt->fetch()) {
        if (password_verify($answer, $hashedAnswer)) {
            $step = 3;
            $showResetForm = true;
        } else {
            $step = 2;
            $error = "Incorrect answer. Please try again.";
        }
    } else {
        $step = 1;
        $error = "Invalid request.";
    }
    $stmt->close();
}

// Handle Step 3: Reset Password
if (isset($_POST['reset_password'])) {
    $email = trim($_POST['email'] ?? '');
    $newPassword = $_POST['new_password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    if (strlen($newPassword) < 8 ||
        !preg_match('/[A-Z]/', $newPassword) ||
        !preg_match('/[a-z]/', $newPassword) ||
        !preg_match('/\d/', $newPassword) ||
        !preg_match('/[^A-Za-z0-9]/', $newPassword)) {
        $error = "Password must be at least 8 characters and include uppercase, lowercase, number, and special character.";
        $step = 3;
        $showResetForm = true;
    } elseif ($newPassword !== $confirmPassword) {
        $error = "Passwords do not match.";
        $step = 3;
        $showResetForm = true;
    } else {
        $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT);
        $stmt = $conn->prepare("UPDATE users SET password = ? WHERE email = ?");
        $stmt->bind_param("ss", $hashedPassword, $email);
        if ($stmt->execute()) {
            $success = "Password updated successfully. Redirecting to login...";
            echo "<script>
                setTimeout(() => {
                    window.location.href = 'login.php';
                }, 3000);
            </script>";
        } else {
            $error = "Something went wrong. Please try again.";
            $step = 3;
            $showResetForm = true;
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Forgot Password - Tiffinly</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link href="css/login.css" rel="stylesheet">
    <link href="css/forgot_password.css" rel="stylesheet">
    <style>
        body {
            margin: 0;
            min-height: 100vh;
            background: linear-gradient(135deg, var(--secondary-color) 40%);
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }
        .auth-box {
            width: 100%;
            max-width: 500px;
            background: #fff;
            padding: 2.5rem;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.15);
            animation: float 3s ease-in-out infinite;
        }
        @keyframes float {
            0% { transform: translateY(0px); }
            50% { transform: translateY(-10px); }
            100% { transform: translateY(0px); }
        }
    </style>
</head>
<body>

<div class="auth-box">
    <h3 class="mb-3 text-center"><i class="fas fa-key me-2"></i>Forgot Password</h3>
    <?php if ($error): ?>
        <div class="alert alert-danger"><?= $error ?></div>
    <?php elseif ($success): ?>
        <div class="alert alert-success"><?= $success ?></div>
    <?php endif; ?>

    <?php if ($step === 1): ?>
        <form method="POST">
            <div class="mb-3">
                <label for="email" class="form-label">Registered Email</label>
                <input type="email" name="email" class="form-control" required placeholder="Enter your registered email">
            </div>
            <button type="submit" name="submit_email" class="btn btn-primary w-100">Next</button>
        </form>

    <?php elseif ($step === 2): ?>
    <form method="POST">
        <input type="hidden" name="email" value="<?= htmlspecialchars($email) ?>">
        <div class="mb-3">
            <label class="form-label"><?= $question ?></label>
            <div class="input-group">
    <input type="password" id="security_answer" name="security_answer" class="form-control" required placeholder="Enter your answer">
    <button type="button" class="btn btn-outline-secondary toggle-password" data-target="security_answer">
        <i class="bi bi-eye"></i>
    </button>
</div>
        </div>
        <button type="submit" name="submit_answer" class="btn btn-primary w-100">Verify</button>
    </form>

    <?php elseif ($showResetForm): ?>
        <form method="POST">
            <input type="hidden" name="email" value="<?= htmlspecialchars($email) ?>">
            <div class="mb-3 position-relative">
                <label for="new_password" class="form-label">New Password</label>
                <div class="input-group">
                    <input type="password" name="new_password" id="new_password" class="form-control" required placeholder="Enter new password">
                    <button type="button" class="btn btn-outline-secondary toggle-password" data-target="new_password">
                        <i class="bi bi-eye"></i>
                    </button>
                </div>
                <div class="form-text">At least 8 characters with uppercase, lowercase, number, and special character.</div>
            </div>

            <div class="mb-3 position-relative">
                <label for="confirm_password" class="form-label">Confirm Password</label>
                <div class="input-group">
                    <input type="password" name="confirm_password" id="confirm_password" class="form-control" required placeholder="Confirm new password">
                    <button type="button" class="btn btn-outline-secondary toggle-password" data-target="confirm_password">
                        <i class="bi bi-eye"></i>
                    </button>
                </div>
            </div>
            <button type="submit" name="reset_password" class="btn btn-success w-100">Reset Password</button>
        </form>
    <?php endif; ?>

    <div class="text-center mt-3">
        <a href="login.php">← Back to Login</a>
    </div>
</div>

<script src="https://kit.fontawesome.com/a2e0fa2c2e.js" crossorigin="anonymous"></script>
<script src="js/forgot_password.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>