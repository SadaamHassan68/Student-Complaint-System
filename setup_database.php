<?php
require_once 'includes/config.php';

// Create SQLite database file if it doesn't exist
try {
    // The PDO connection in config.php will create the database file automatically
    echo "SQLite database file created/connected successfully: " . DB_PATH . "<br>";
    
    // Create users table (SQLite syntax)
    $sql_users = "CREATE TABLE IF NOT EXISTS users (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        username TEXT NOT NULL,
        email TEXT UNIQUE NOT NULL,
        password TEXT NOT NULL,
        role TEXT CHECK(role IN ('student','admin','staff')) DEFAULT 'student',
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )";
    
    $pdo->exec($sql_users);
    echo "Users table created successfully.<br>";

    // Create complaints table (SQLite syntax)
    $sql_complaints = "CREATE TABLE IF NOT EXISTS complaints (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        student_id INTEGER NOT NULL,
        category TEXT NOT NULL,
        description TEXT NOT NULL,
        status TEXT CHECK(status IN ('pending','in_progress','resolved','rejected')) DEFAULT 'pending',
        assigned_to INTEGER NULL,
        admin_remarks TEXT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE,
        FOREIGN KEY (assigned_to) REFERENCES users(id) ON DELETE SET NULL
    )";
    
    $pdo->exec($sql_complaints);
    echo "Complaints table created successfully.<br>";

    // Create trigger for updated_at column (SQLite doesn't have ON UPDATE CURRENT_TIMESTAMP)
    $sql_trigger = "CREATE TRIGGER IF NOT EXISTS update_complaints_updated_at 
        AFTER UPDATE ON complaints
        BEGIN
            UPDATE complaints SET updated_at = CURRENT_TIMESTAMP WHERE id = NEW.id;
        END";
    
    $pdo->exec($sql_trigger);
    echo "Complaints update trigger created successfully.<br>";

    // Create notifications table (SQLite syntax)
    $sql_notifications = "CREATE TABLE IF NOT EXISTS notifications (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        user_id INTEGER NOT NULL,
        message TEXT NOT NULL,
        is_read INTEGER DEFAULT 0,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
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

    echo "<br><strong>SQLite Database setup completed successfully!</strong><br>";
    echo "<strong>Database file location:</strong> " . realpath(DB_PATH) . "<br>";
    echo "<a href='index.php'>Go to Login Page</a>";

} catch(PDOException $e) {
    die("Error: " . $e->getMessage());
}
?>