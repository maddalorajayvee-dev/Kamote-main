<?php
// Email helper functions using SMTP
require_once __DIR__ . '/phpmailer.php';

function sendPasswordResetEmail($email, $name, $resetToken) {
    $resetUrl = base_url('reset-password.php?token=' . urlencode($resetToken));
    
    $subject = "Password Reset Request - Barangay Sto. Angel Payroll System";
    
    $message = "
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset='UTF-8'>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { background-color: #4299e1; color: white; padding: 20px; text-align: center; border-radius: 5px 5px 0 0; }
            .content { background-color: #f7fafc; padding: 30px; border: 1px solid #e2e8f0; }
            .button { display: inline-block; padding: 12px 30px; background-color: #4299e1; color: white; text-decoration: none; border-radius: 5px; margin: 20px 0; }
            .footer { text-align: center; padding: 20px; color: #666; font-size: 12px; }
            .warning { background-color: #fff5f5; border-left: 4px solid #e53e3e; padding: 15px; margin: 20px 0; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h2>Password Reset Request</h2>
            </div>
            <div class='content'>
                <p>Hello " . htmlspecialchars($name) . ",</p>
                <p>We received a request to reset your password for your Barangay Sto. Angel Payroll System admin account.</p>
                <p>Click the button below to reset your password:</p>
                <p style='text-align: center;'>
                    <a href='" . htmlspecialchars($resetUrl) . "' class='button'>Reset Password</a>
                </p>
                <p>Or copy and paste this link into your browser:</p>
                <p style='word-break: break-all; color: #4299e1;'>" . htmlspecialchars($resetUrl) . "</p>
                <div class='warning'>
                    <strong>⚠️ Important:</strong> This link will expire in 1 hour. If you didn't request this password reset, please ignore this email.
                </div>
            </div>
            <div class='footer'>
                <p>This is an automated email from Barangay Sto. Angel Payroll System.</p>
                <p>Please do not reply to this email.</p>
            </div>
        </div>
    </body>
    </html>
    ";
    
    // Use SMTP to send email
    return sendSMTPEmail($email, $subject, $message);
}

function sendSMTPEmail($to, $subject, $htmlMessage) {
    // Check if SMTP is configured
    if (!defined('SMTP_HOST') || (SMTP_HOST === 'smtp.gmail.com' && SMTP_USERNAME === 'your-email@gmail.com')) {
        // Configuration not set, return false
        error_log("SMTP not configured. Please configure config/email.php");
        return false;
    }
    
    // SMTP Configuration
    $smtpHost = SMTP_HOST;
    $smtpPort = defined('SMTP_PORT') ? SMTP_PORT : 587;
    $smtpUser = SMTP_USERNAME;
    $smtpPass = SMTP_PASSWORD;
    $smtpSecure = defined('SMTP_SECURE') ? SMTP_SECURE : 'tls';
    $fromEmail = defined('SMTP_FROM_EMAIL') ? SMTP_FROM_EMAIL : SMTP_USERNAME;
    $fromName = defined('SMTP_FROM_NAME') ? SMTP_FROM_NAME : 'Barangay Sto. Angel Payroll';
    
    // Use SimpleSMTP class
    $smtp = new SimpleSMTP($smtpHost, $smtpPort, $smtpUser, $smtpPass, $smtpSecure);
    
    return $smtp->send($fromEmail, $fromName, $to, $subject, $htmlMessage);
}
?>

