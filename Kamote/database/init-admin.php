<?php
// Script to create initial admin user
require_once __DIR__ . '/../config/config.php';

$username = 'admin';
$password = 'admin123';
$name = 'Administrator';
$email = 'admin@barangaystoangel.com';

$isCLI = php_sapi_name() === 'cli';
$success = false;
$message = '';

try {
    $pdo = getDB();

    // Check if admin already exists
    $stmt = $pdo->prepare("SELECT id FROM admins WHERE username = ?");
    $stmt->execute([$username]);
    $existing = $stmt->fetch();

    if ($existing) {
        // Update existing admin
        $hashedPassword = hashPassword($password);
        $id = $existing['id'];
        $stmt = $pdo->prepare("UPDATE admins SET password = ?, name = ?, email = ? WHERE id = ?");
        $stmt->execute([$hashedPassword, $name, $email, $id]);
        $message = "✅ Admin user updated successfully!";
        $success = true;
    } else {
        // Create new admin
        $id = uniqid('admin_', true);
        $hashedPassword = hashPassword($password);
        $stmt = $pdo->prepare("INSERT INTO admins (id, username, password, name, email) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$id, $username, $hashedPassword, $name, $email]);
        $message = "✅ Admin user created successfully!";
        $success = true;
    }
} catch (Exception $e) {
    $message = "❌ Error: " . $e->getMessage();
    $success = false;
}

if ($isCLI) {
    // CLI output
    echo $message . "\n";
    if ($success) {
        echo "Username: $username\n";
        echo "Password: $password (change this after first login!)\n";
        echo "Name: $name\n";
        echo "Email: $email\n";
    }
} else {
    // HTML output for browser
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Admin Account - Barangay Sto. Angel Payroll</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .init-container {
            max-width: 600px;
            margin: 50px auto;
            padding: 2rem;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .info-box {
            background: #f8f9fa;
            padding: 1.5rem;
            border-radius: 6px;
            margin-top: 1.5rem;
            border-left: 4px solid #007bff;
        }
        .info-box h3 {
            margin-top: 0;
            color: #333;
        }
        .info-item {
            margin: 0.75rem 0;
            padding: 0.5rem;
            background: white;
            border-radius: 4px;
        }
        .info-label {
            font-weight: 600;
            color: #666;
        }
        .info-value {
            color: #333;
            margin-top: 0.25rem;
        }
        .btn-login {
            display: inline-block;
            margin-top: 1.5rem;
            padding: 0.75rem 2rem;
            background: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 6px;
            font-weight: 600;
        }
        .btn-login:hover {
            background: #0056b3;
        }
    </style>
</head>
<body class="auth-wrapper">
    <div class="init-container">
        <h1 class="heading">Admin Account Setup</h1>
        
        <?php if ($success): ?>
            <div class="alert success" style="margin: 1.5rem 0;">
                <?php echo htmlspecialchars($message); ?>
            </div>
            
            <div class="info-box">
                <h3>Admin Account Details</h3>
                <div class="info-item">
                    <div class="info-label">Username:</div>
                    <div class="info-value"><?php echo htmlspecialchars($username); ?></div>
                </div>
                <div class="info-item">
                    <div class="info-label">Password:</div>
                    <div class="info-value"><?php echo htmlspecialchars($password); ?></div>
                </div>
                <div class="info-item">
                    <div class="info-label">Name:</div>
                    <div class="info-value"><?php echo htmlspecialchars($name); ?></div>
                </div>
                <div class="info-item">
                    <div class="info-label">Email:</div>
                    <div class="info-value"><?php echo htmlspecialchars($email); ?></div>
                </div>
            </div>
            
            <p style="color: #dc3545; font-weight: 600; margin-top: 1.5rem;">
                ⚠️ Important: Change the default password after first login!
            </p>
            
            <a href="../login.php" class="btn-login">Go to Login Page</a>
        <?php else: ?>
            <div class="alert error" style="margin: 1.5rem 0;">
                <?php echo htmlspecialchars($message); ?>
            </div>
            <p>Please check your database connection and try again.</p>
        <?php endif; ?>
    </div>
</body>
</html>
<?php
}
?>

