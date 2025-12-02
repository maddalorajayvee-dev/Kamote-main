<?php
require_once __DIR__ . '/config/config.php';

// Redirect to login if not logged in, otherwise to dashboard
if (isLoggedIn()) {
    redirect(base_url('admin/dashboard.php'));
} else {
    redirect(base_url('login.php'));
}
?>

