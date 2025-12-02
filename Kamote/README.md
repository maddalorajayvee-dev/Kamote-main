# Barangay Sto. Angel Payroll System

A comprehensive payroll management system for Barangay Sto. Angel built with PHP, MySQL, and Tailwind CSS.

## Features

- 🔐 **Admin Authentication** - Secure login system with PHP sessions
- 👥 **Employee Management** - Complete CRUD operations for employee records
- 💰 **Payroll Management** - Create, view, edit, and manage payroll records
- 📊 **Dashboard** - Overview of key statistics and quick actions
- 🎨 **Modern UI** - Beautiful and responsive interface built with Tailwind CSS

## Tech Stack

- **Backend**: PHP 7.4+
- **Database**: MySQL/MariaDB
- **Styling**: Tailwind CSS (CDN)
- **Authentication**: PHP Sessions
- **Database Access**: PDO

## Prerequisites

- PHP 7.4 or higher
- MySQL 5.7+ or MariaDB 10.2+
- Apache/Nginx web server
- PHP extensions: PDO, PDO_MySQL

## Installation

### Step 1: Database Setup

1. Create a MySQL database:
```sql
CREATE DATABASE barangay_sto_angel_payroll;
```

2. Import the database schema:
```bash
mysql -u root -p barangay_sto_angel_payroll < database/schema.sql
```

Or run the SQL file directly in phpMyAdmin or MySQL Workbench.

### Step 2: Configure Database Connection

Edit `config/database.php` and update the database credentials:

```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'barangay_sto_angel_payroll');
define('DB_USER', 'your_username');
define('DB_PASS', 'your_password');
```

### Step 3: Configure Base URL

Edit `config/config.php` and update the BASE_URL:

```php
define('BASE_URL', 'http://localhost/kamote');
```

### Step 4: Create Admin User

Run the initialization script:

```bash
php database/init-admin.php
```

Or access it via browser:
```
http://localhost/kamote/database/init-admin.php
```

Default admin credentials:
- Username: `admin`
- Password: `admin123`

**⚠️ Important**: Change the default password after first login!

### Step 5: Set Up Web Server

#### Using Apache
1. Place the project in your web root (e.g., `htdocs` or `www`)
2. Ensure mod_rewrite is enabled
3. Access via: `http://localhost/kamote`

#### Using PHP Built-in Server (Development)
```bash
php -S localhost:8000 -t .
```

Then access: `http://localhost:8000`

## Project Structure

```
├── admin/
│   ├── dashboard.php          # Admin dashboard
│   ├── employees.php          # Employee list
│   ├── employee-form.php       # Add/Edit employee form
│   ├── payroll.php            # Payroll list
│   ├── payroll-form.php       # Add/Edit payroll form
│   └── includes/
│       └── header.php         # Navigation header
├── config/
│   ├── config.php             # Main configuration
│   └── database.php           # Database connection
├── database/
│   ├── schema.sql             # Database schema
│   └── init-admin.php         # Admin user initialization
├── includes/
│   ├── auth.php               # Authentication functions
│   └── functions.php          # Helper functions
├── index.php                  # Home page (redirects)
├── login.php                  # Login page
└── logout.php                 # Logout handler
```

## Usage

1. **Login**: Access `http://localhost/kamote/login.php`
2. **Dashboard**: View statistics and quick actions
3. **Employees**: Manage employee records (Add, Edit, Delete, Search)
4. **Payroll**: Create and manage payroll records

## Features Overview

### Admin Dashboard
- View statistics (total employees, active employees, payroll records)
- Quick actions (add employee, create payroll)
- Navigation to all sections

### Employee Management
- Add new employees with complete information
- Edit existing employee records
- Delete employees
- Search and filter employees
- View employee status and details

### Payroll Management
- Create payroll records for employees
- Automatic calculation of gross pay and net pay
- Support for overtime, allowances, bonuses, and deductions
- Track payroll status (Pending, Approved, Paid)
- Edit and delete payroll records
- Search functionality

## Security Notes

- Passwords are hashed using PHP's `password_hash()` function
- PHP sessions are used for authentication
- All user input is sanitized
- SQL injection protection via PDO prepared statements
- Change default admin credentials in production
- Use HTTPS in production

## Troubleshooting

### Database Connection Error
- Check database credentials in `config/database.php`
- Ensure MySQL service is running
- Verify database exists

### Session Issues
- Check PHP session configuration
- Ensure `session_start()` is called
- Check file permissions

### 404 Errors
- Verify web server configuration
- Check file paths and URLs
- Ensure mod_rewrite is enabled (Apache)

## Production Deployment

1. Update database credentials
2. Change the default admin password
3. Set `display_errors = 0` in `php.ini`
4. Use HTTPS
5. Set proper file permissions
6. Update `BASE_URL` in `config/config.php`
7. Consider using environment variables for sensitive data

## License

This project is created for Barangay Sto. Angel.

## Support

For issues or questions, please contact the system administrator.

