<?php
require_once __DIR__ . '/config/config.php';

$error = '';
$success = '';
$token = $_GET['token'] ?? '';
$isValidToken = false;
$adminInfo = null;

// Validate token
if ($token) {
    $resetToken = validateResetToken($token);
    if ($resetToken) {
        $isValidToken = true;
        $adminInfo = [
            'username' => $resetToken['username'],
            'email' => $resetToken['email']
        ];
    } else {
        $error = 'Invalid or expired reset token. Please request a new one.';
    }
} else {
    $error = 'No reset token provided.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $isValidToken) {
    $newPassword = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    
    if (empty($newPassword) || empty($confirmPassword)) {
        $error = 'All fields are required';
    } elseif (strlen($newPassword) < 6) {
        $error = 'Password must be at least 6 characters';
    } elseif ($newPassword !== $confirmPassword) {
        $error = 'Passwords do not match';
    } else {
        if (resetPassword($token, $newPassword)) {
            $success = 'Password reset successfully! You can now login with your new password.';
            $isValidToken = false; // Hide form after success
        } else {
            $error = 'Failed to reset password. The token may have expired or been used.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - Barangay Sto. Angel Payroll</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        body {
            margin: 0;
            padding: 0;
            min-height: 100vh;
            display: flex;
            font-family: Arial, sans-serif;
            background-color: #f0fff4;
        }
        .login-container {
            display: flex;
            width: 100%;
            min-height: 100vh;
        }
        .left-side {
            flex: 1;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding: 2rem;
            min-height: 100vh;
        }
        .logo-container {
            margin-bottom: 2rem;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        .logo-container img {
            max-width: 100%;
            height: auto;
            max-height: 300px;
            object-fit: contain;
        }
        .right-side {
            flex: 1;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 2rem;
        }
        .welcome-text {
            max-width: 500px;
            text-align: center;
        }
        .welcome-text h1 {
            font-size: 2.5rem;
            color: #2d3748;
            margin-bottom: 1rem;
        }
        .welcome-text p {
            font-size: 1.1rem;
            color: #4a5568;
            line-height: 1.6;
        }
        .auth-card {
            width: 100%;
            max-width: 400px;
            padding: 2.5rem;
            background: white;
            border-radius: 1rem;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        }
        .heading {
            font-size: 1.5rem;
            font-weight: 700;
            color: #2d3748;
            text-align: center;
            margin-bottom: 0.5rem;
        }
        .subtext {
            color: #4a5568;
            text-align: center;
            margin-bottom: 2rem;
        }
        .form-label {
            display: block;
            margin-bottom: 0.5rem;
            color: #4a5568;
            font-weight: 500;
        }
        .form-input {
            width: 100%;
            padding: 0.75rem 1rem;
            border: 1px solid #e2e8f0;
            border-radius: 0.5rem;
            font-size: 1rem;
            transition: border-color 0.2s;
            box-sizing: border-box;
        }
        .form-input:focus {
            outline: none;
            border-color: #4299e1;
            box-shadow: 0 0 0 3px rgba(66, 153, 225, 0.2);
        }
        .primary-btn {
            background-color: #4299e1;
            color: white;
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 0.5rem;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            width: 100%;
            transition: background-color 0.2s;
        }
        .primary-btn:hover {
            background-color: #3182ce;
        }
        .secondary-btn {
            display: block;
            text-align: center;
            color: #4299e1;
            padding: 0.75rem 1.5rem;
            border: 1px solid #4299e1;
            border-radius: 0.5rem;
            font-size: 1rem;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.2s;
            margin-top: 0.75rem;
        }
        .secondary-btn:hover {
            background-color: #ebf8ff;
        }
        .alert {
            padding: 0.75rem 1rem;
            border-radius: 0.5rem;
            margin-bottom: 1.5rem;
            font-size: 0.875rem;
        }
        .alert.error {
            background-color: #fff5f5;
            color: #e53e3e;
            border: 1px solid #fed7d7;
        }
        .alert.success {
            background-color: #f0fff4;
            color: #22543d;
            border: 1px solid #c6f6d5;
        }
        .account-info {
            background-color: #f7fafc;
            border-radius: 0.5rem;
            padding: 1rem;
            margin-bottom: 1.5rem;
            font-size: 0.875rem;
            color: #4a5568;
        }
        .account-info strong {
            color: #2d3748;
        }
        .link-text {
            text-align: center;
            margin-top: 1rem;
            color: #4a5568;
            font-size: 0.875rem;
        }
        .link-text a {
            color: #4299e1;
            text-decoration: none;
        }
        .link-text a:hover {
            text-decoration: underline;
        }
        @media (max-width: 768px) {
            .login-container {
                flex-direction: column;
            }
            .left-side, .right-side {
                padding: 2rem 1rem;
            }
            .welcome-text {
                text-align: center;
                margin-bottom: 2rem;
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="left-side">
            <div class="logo-container">
                <img src="img/logonatics.png" alt="Barangay Sto. Angel Logo">
            </div>
            <div class="welcome-text">
                <h1>Barangay Sto. Angel</h1>
                <p>Set a new password for your admin account.</p>
            </div>
        </div>
        <div class="right-side">
            <div class="auth-card">
                <h1 class="heading">Reset Password</h1>
                <p class="subtext">Barangay Sto. Angel Payroll System</p>

                <?php if ($error): ?>
                    <div class="alert error">
                        <?php echo htmlspecialchars($error); ?>
                    </div>
                <?php endif; ?>

                <?php if ($success): ?>
                    <div class="alert success">
                        <?php echo htmlspecialchars($success); ?>
                    </div>
                    <a href="login.php" class="primary-btn" style="text-decoration: none; display: block; text-align: center;">
                        Go to Login
                    </a>
                <?php elseif ($isValidToken): ?>
                    <?php if ($adminInfo): ?>
                        <div class="account-info">
                            <strong>Account:</strong> <?php echo htmlspecialchars($adminInfo['username']); ?>
                            <?php if ($adminInfo['email']): ?>
                                <br><strong>Email:</strong> <?php echo htmlspecialchars($adminInfo['email']); ?>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>

                    <form method="POST" action="" style="display: flex; flex-direction: column; gap: 1.5rem;">
                        <div>
                            <label for="password" class="form-label">
                                New Password
                            </label>
                            <input
                                id="password"
                                type="password"
                                name="password"
                                required
                                class="form-input"
                                placeholder="Enter new password (min. 6 characters)"
                                minlength="6"
                            />
                        </div>

                        <div>
                            <label for="confirm_password" class="form-label">
                                Confirm New Password
                            </label>
                            <input
                                id="confirm_password"
                                type="password"
                                name="confirm_password"
                                required
                                class="form-input"
                                placeholder="Re-enter new password"
                                minlength="6"
                            />
                        </div>

                        <div style="display: flex; flex-direction: column; gap: 0.75rem;">
                            <button
                                type="submit"
                                class="primary-btn"
                            >
                                Reset Password
                            </button>
                            <a
                                href="login.php"
                                class="secondary-btn"
                            >
                                Back to Login
                            </a>
                        </div>
                    </form>
                <?php else: ?>
                    <div class="link-text">
                        <a href="forgot-password.php">Request a new reset token</a>
                    </div>
                <?php endif; ?>

                <div class="link-text" style="margin-top: 1.5rem;">
                    Remember your password? <a href="login.php">Sign in</a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>

