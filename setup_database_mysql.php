<?php
require_once 'includes/config.php';

// Create MySQL database and tables
try {
    // Create database if it doesn't exist
    $pdo->exec("CREATE DATABASE IF NOT EXISTS student_complaints");
    $pdo->exec("USE student_complaints");
    
    echo "Database 'student_complaints' created/selected successfully.<br>";
    
    // Create users table (MySQL syntax)
    $sql_users = "CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) NOT NULL,
        email VARCHAR(100) UNIQUE NOT NULL,
        password VARCHAR(255) NOT NULL,
        role ENUM('student', 'admin', 'staff') DEFAULT 'student',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    
    $pdo->exec($sql_users);
    echo "Users table created successfully.<br>";

    // Create complaints table (MySQL syntax)
    $sql_complaints = "CREATE TABLE IF NOT EXISTS complaints (
        id INT AUTO_INCREMENT PRIMARY KEY,
        student_id INT NOT NULL,
        category VARCHAR(100) NOT NULL,
        description TEXT NOT NULL,
        status ENUM('pending', 'in_progress', 'resolved', 'rejected') DEFAULT 'pending',
        assigned_to INT NULL,
        admin_remarks TEXT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE,
        FOREIGN KEY (assigned_to) REFERENCES users(id) ON DELETE SET NULL
    )";
    
    $pdo->exec($sql_complaints);
    echo "Complaints table created successfully.<br>";

    // Create notifications table (MySQL syntax)
    $sql_notifications = "CREATE TABLE IF NOT EXISTS notifications (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        message TEXT NOT NULL,
        is_read BOOLEAN DEFAULT FALSE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    )";
    
    $pdo->exec($sql_notifications);
    echo "Notifications table created successfully.<br>";

    // Insert default admin user if not exists
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE role = 'admin'");
    $stmt->execute();
    $adminCount = $stmt->fetchColumn();

    if ($adminCount == 0) {
        $adminPassword = password_hash('admin123', PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, ?)");
        $stmt->execute(['admin', 'admin@example.com', $adminPassword, 'admin']);
        echo "Default admin user created successfully.<br>";
        echo "Admin Login: admin@example.com / admin123<br>";
    }

    // Insert sample staff user if not exists
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE role = 'staff'");
    $stmt->execute();
    $staffCount = $stmt->fetchColumn();

    if ($staffCount == 0) {
        $staffPassword = password_hash('staff123', PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, ?)");
        $stmt->execute(['staff1', 'staff@example.com', $staffPassword, 'staff']);
        echo "Default staff user created successfully.<br>";
        echo "Staff Login: staff@example.com / staff123<br>";
    }

    echo "<br><strong>MySQL Database setup completed successfully!</strong><br>";
    echo "<a href='index.php'>Go to Login Page</a>";

} catch(PDOException $e) {
    die("Error: " . $e->getMessage());
}
?>