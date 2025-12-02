<?php
// Authentication functions

function hashPassword($password) {
    return password_hash($password, PASSWORD_DEFAULT);
}

function verifyPassword($password, $hash) {
    return password_verify($password, $hash);
}

function isLoggedIn() {
    return isset($_SESSION['admin_id']) && !empty($_SESSION['admin_id']);
}

function requireLogin() {
    if (!isLoggedIn()) {
        redirect(base_url('login.php'));
    }
}

function getCurrentAdmin() {
    if (!isLoggedIn()) {
        return null;
    }
    
    $pdo = getDB();
    $stmt = $pdo->prepare("SELECT id, username, name, email FROM admins WHERE id = ?");
    $stmt->execute([$_SESSION['admin_id']]);
    return $stmt->fetch();
}

function login($username, $password) {
    $pdo = getDB();
    $stmt = $pdo->prepare("SELECT * FROM admins WHERE username = ?");
    $stmt->execute([$username]);
    $admin = $stmt->fetch();
    
    if ($admin && verifyPassword($password, $admin['password'])) {
        $_SESSION['admin_id'] = $admin['id'];
        $_SESSION['admin_username'] = $admin['username'];
        $_SESSION['admin_name'] = $admin['name'];
        return true;
    }
    
    return false;
}

function logout() {
    session_destroy();
    redirect(base_url('login.php'));
}

function generateResetToken() {
    return bin2hex(random_bytes(32));
}

function createPasswordResetToken($usernameOrEmail, $sendEmail = true) {
    $pdo = getDB();
    
    // Find admin by username or email
    $stmt = $pdo->prepare("SELECT id, username, email, name FROM admins WHERE username = ? OR email = ?");
    $stmt->execute([$usernameOrEmail, $usernameOrEmail]);
    $admin = $stmt->fetch();
    
    if (!$admin) {
        return null;
    }
    
    // Check if admin has email
    if (empty($admin['email'])) {
        return ['error' => 'No email address found for this account. Please contact administrator.'];
    }
    
    // Generate token
    $token = generateResetToken();
    $id = uniqid('reset_', true);
    $expiresAt = date('Y-m-d H:i:s', strtotime('+1 hour')); // Token expires in 1 hour
    
    // Invalidate old tokens for this admin
    $stmt = $pdo->prepare("UPDATE password_reset_tokens SET used = 1 WHERE admin_id = ? AND used = 0");
    $stmt->execute([$admin['id']]);
    
    // Create new token
    $stmt = $pdo->prepare("INSERT INTO password_reset_tokens (id, admin_id, token, expires_at) VALUES (?, ?, ?, ?)");
    $stmt->execute([$id, $admin['id'], $token, $expiresAt]);
    
    // Send email if requested
    if ($sendEmail) {
        $emailSent = sendPasswordResetEmail($admin['email'], $admin['name'], $token);
        if (!$emailSent) {
            return ['error' => 'Failed to send email. Please try again later or contact administrator.'];
        }
    }
    
    return [
        'token' => $token,
        'admin' => $admin,
        'email_sent' => $sendEmail
    ];
}

function validateResetToken($token) {
    $pdo = getDB();
    
    $stmt = $pdo->prepare("SELECT prt.*, a.id as admin_id, a.username, a.email FROM password_reset_tokens prt 
                           JOIN admins a ON prt.admin_id = a.id 
                           WHERE prt.token = ? AND prt.used = 0 AND prt.expires_at > NOW()");
    $stmt->execute([$token]);
    $resetToken = $stmt->fetch();
    
    return $resetToken;
}

function resetPassword($token, $newPassword) {
    $resetToken = validateResetToken($token);
    
    if (!$resetToken) {
        return false;
    }
    
    $pdo = getDB();
    
    // Update password
    $hashedPassword = hashPassword($newPassword);
    $stmt = $pdo->prepare("UPDATE admins SET password = ? WHERE id = ?");
    $stmt->execute([$hashedPassword, $resetToken['admin_id']]);
    
    // Mark token as used
    $stmt = $pdo->prepare("UPDATE password_reset_tokens SET used = 1 WHERE token = ?");
    $stmt->execute([$token]);
    
    return true;
}
?>

