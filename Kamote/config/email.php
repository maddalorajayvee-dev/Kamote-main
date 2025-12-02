<?php
// Email configuration
// For Gmail SMTP

// IMPORTANT: Configure these settings with your Gmail credentials
// For Gmail, you need to:
// 1. Enable 2-Step Verification on your Google Account
// 2. Generate an App Password: https://myaccount.google.com/apppasswords
// 3. Use the App Password (not your regular Gmail password) below

define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USERNAME', 'your-email@gmail.com'); // ⚠️ CHANGE THIS to your Gmail address
define('SMTP_PASSWORD', 'your-app-password'); // ⚠️ CHANGE THIS to your Gmail App Password
define('SMTP_FROM_EMAIL', 'your-email@gmail.com'); // ⚠️ CHANGE THIS to your Gmail address
define('SMTP_FROM_NAME', 'Barangay Sto. Angel Payroll');
define('SMTP_SECURE', 'tls'); // Use 'tls' for port 587, 'ssl' for port 465

// How to get Gmail App Password:
// 1. Go to https://myaccount.google.com/security
// 2. Enable 2-Step Verification if not already enabled
// 3. Go to https://myaccount.google.com/apppasswords
// 4. Generate a new app password for "Mail"
// 5. Copy the 16-character password and paste it above in SMTP_PASSWORD

?>

