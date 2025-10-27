# MySQL Setup Instructions for Student Complaint Management System

This document provides instructions for setting up the Student Complaint Management System with MySQL using XAMPP.

## Prerequisites

1. XAMPP installed on your system
2. Apache and MySQL services running in XAMPP Control Panel

## Database Setup

### Step 1: Start XAMPP Services
1. Open XAMPP Control Panel
2. Start Apache service
3. Start MySQL service

### Step 2: Create Database
The system will automatically create the database and tables when you run the setup script. However, if you want to create the database manually:

1. Open phpMyAdmin (http://localhost/phpmyadmin)
2. Create a new database named `student_complaints`

### Step 3: Configure Database Connection
The database connection is configured in `includes/config.php`:

```php
// Database Configuration (MySQL)
define('DB_HOST', 'localhost');
define('DB_NAME', 'student_complaints');
define('DB_USER', 'root');
define('DB_PASS', '');
```

By default, XAMPP uses `root` as the MySQL username with no password. If you have a different configuration, update these values accordingly.

### Step 4: Run Database Setup
1. Open your browser
2. Navigate to `http://localhost/student_complaient/setup_database.php`
3. The script will create all necessary tables and insert default users

### Step 5: Login Credentials
After setup, you can log in with these default credentials:

- Admin: admin@example.com / admin123
- Staff: staff@example.com / staff123

## Troubleshooting

### If you get a "Connection failed" error:
1. Make sure MySQL service is running in XAMPP
2. Check that the database credentials in `config.php` are correct
3. Verify that the `student_complaints` database exists

### If you get a "Database not found" error:
1. Create the database manually in phpMyAdmin
2. Make sure the database name matches `DB_NAME` in `config.php`

## File Structure
The system has been updated to work with MySQL:
- `includes/config.php` - MySQL database configuration
- `setup_database.php` - MySQL table creation script
- All query files updated to use MySQL syntax instead of SQLite

## Key Differences from SQLite Version
1. Uses MySQL PDO connection instead of SQLite
2. Changed `AUTOINCREMENT` to `AUTO_INCREMENT`
3. Replaced `strftime()` functions with MySQL date functions
4. Changed `ENUM` constraints to MySQL syntax
5. Updated foreign key constraint handling
6. Replaced SQLite triggers with MySQL's `ON UPDATE CURRENT_TIMESTAMP`

For any issues, please check the Apache error logs in XAMPP or the PHP error messages displayed in your browser.