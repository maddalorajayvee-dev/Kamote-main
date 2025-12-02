<?php
require_once __DIR__ . '/config/config.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitize($_POST['name'] ?? '');
    $username = sanitize($_POST['username'] ?? '');
    $emailRaw = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    $email = filter_var($emailRaw, FILTER_VALIDATE_EMAIL) ? $emailRaw : '';

    if (!$name || !$username || !$email || !$password || !$confirmPassword) {
        $error = 'All fields are required and email must be valid';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters';
    } elseif ($password !== $confirmPassword) {
        $error = 'Passwords do not match';
    } else {
        $pdo = getDB();

        // Check if username or email already exists
        $stmt = $pdo->prepare("SELECT id FROM admins WHERE username = ? OR email = ?");
        $stmt->execute([$username, $email]);
        $existing = $stmt->fetch();

        if ($existing) {
            $error = 'Username or email already exists';
        } else {
            $id = uniqid('admin_', true);
            $hashedPassword = hashPassword($password);

            $stmt = $pdo->prepare("INSERT INTO admins (id, username, password, name, email) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$id, $username, $hashedPassword, $name, $email]);

            $success = 'Account created successfully. You can now sign in.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register Admin - Barangay Sto. Angel Payroll</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="auth-wrapper">
    <div class="auth-card">
        <div class="text-center" style="margin-bottom: 2rem;">
            <h1 class="heading">Create Admin Account</h1>
            <p class="subtext">Barangay Sto. Angel Payroll System</p>
            <p class="helper-text">Register to manage employees, run payroll, and access the admin dashboard in one secure workspace.</p>
        </div>

        <?php if ($error): ?>
            <div class="alert error">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="alert success">
                <?php echo htmlspecialchars($success); ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="" style="display: flex; flex-direction: column; gap: 1.5rem;">
            <div>
                <label for="name" class="form-label">
                    Full Name
                </label>
                <input
                    id="name"
                    type="text"
                    name="name"
                    required
                    value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>"
                    class="form-input"
                    placeholder="Enter full name"
                />
            </div>

            <div>
                <label for="username" class="form-label">
                    Username
                </label>
                <input
                    id="username"
                    type="text"
                    name="username"
                    required
                    value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>"
                    class="form-input"
                    placeholder="Choose a username"
                />
            </div>

            <div>
                <label for="email" class="form-label">
                    Email
                </label>
                <input
                    id="email"
                    type="email"
                    name="email"
                    required
                    value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>"
                    class="form-input"
                    placeholder="Enter a valid email"
                />
            </div>

            <div>
                <label for="password" class="form-label">
                    Password
                </label>
                <input
                    id="password"
                    type="password"
                    name="password"
                    required
                    class="form-input"
                    placeholder="Enter a password"
                />
            </div>

            <div>
                <label for="confirm_password" class="form-label">
                    Confirm Password
                </label>
                <input
                    id="confirm_password"
                    type="password"
                    name="confirm_password"
                    required
                    class="form-input"
                    placeholder="Re-enter the password"
                />
            </div>

            <div style="display: flex; flex-direction: column; gap: 0.75rem;">
                <button
                    type="submit"
                    class="primary-btn"
                >
                    Register
                </button>
                <a
                    href="login.php"
                    class="secondary-btn"
                >
                    Back to Sign In
                </a>
            </div>
        </form>
    </div>
</body>
</html>

