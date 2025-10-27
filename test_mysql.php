<?php
require_once 'includes/config.php';

try {
    // Test database connection
    echo "Connected successfully to MySQL database!<br>";
    
    // Show database version
    $stmt = $pdo->query("SELECT VERSION() as version");
    $version = $stmt->fetch();
    echo "MySQL Version: " . $version['version'] . "<br>";
    
    // Check if database exists
    $stmt = $pdo->query("SHOW DATABASES LIKE 'student_complaints'");
    if ($stmt->rowCount() > 0) {
        echo "Database 'student_complaints' exists.<br>";
        
        // Select the database
        $pdo->exec("USE student_complaints");
        
        // Show tables
        $stmt = $pdo->query("SHOW TABLES");
        echo "Tables in database:<br>";
        while ($row = $stmt->fetch()) {
            echo "- " . $row[0] . "<br>";
        }
    } else {
        echo "Database 'student_complaints' does not exist yet.<br>";
        echo "Please run <a href='setup_database.php'>setup_database.php</a> to create it.<br>";
    }
    
} catch(PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
}
?>