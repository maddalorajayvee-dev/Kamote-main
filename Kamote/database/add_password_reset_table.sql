-- Add password reset tokens table
USE barangay_sto_angel_payroll;

CREATE TABLE IF NOT EXISTS password_reset_tokens (
    id VARCHAR(255) PRIMARY KEY,
    admin_id VARCHAR(255) NOT NULL,
    token VARCHAR(255) UNIQUE NOT NULL,
    expires_at DATETIME NOT NULL,
    used TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (admin_id) REFERENCES admins(id) ON DELETE CASCADE,
    INDEX idx_token (token),
    INDEX idx_admin_id (admin_id)
);

