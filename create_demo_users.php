<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

echo "Checking database for demo users...\n";

try {
    // Check if any users exist
    $stmt = $pdo->query('SELECT COUNT(*) as count FROM users');
    $result = $stmt->fetch();
    
    if ($result['count'] == 0) {
        echo "No users found. Creating demo users...\n";
        
        // Create demo users
        $demoUsers = [
            [
                'username' => 'Administrator',
                'email' => 'admin@scms.com',
                'password' => hashPassword('password123'),
                'role' => 'admin'
            ],
            [
                'username' => 'Staff Member',
                'email' => 'staff@scms.com',
                'password' => hashPassword('password123'),
                'role' => 'staff'
            ],
            [
                'username' => 'John Student',
                'email' => 'student@scms.com',
                'password' => hashPassword('password123'),
                'role' => 'student'
            ]
        ];
        
        foreach ($demoUsers as $user) {
            $stmt = $pdo->prepare("INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, ?)");
            $stmt->execute([$user['username'], $user['email'], $user['password'], $user['role']]);
            echo "Created: " . $user['username'] . " (" . $user['email'] . ") - " . $user['role'] . "\n";
        }
        
        echo "\nDemo users created successfully!\n";
    } else {
        echo "Found " . $result['count'] . " users in database.\n";
        
        // Display existing users
        $stmt = $pdo->query('SELECT username, email, role FROM users');
        $users = $stmt->fetchAll();
        
        echo "\nExisting users:\n";
        foreach ($users as $user) {
            echo "- " . $user['username'] . " (" . $user['email'] . ") - " . $user['role'] . "\n";
        }
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

echo "\nDemo Login Credentials:\n";
echo "Admin: admin@scms.com / password123\n";
echo "Staff: staff@scms.com / password123\n";
echo "Student: student@scms.com / password123\n";
?>