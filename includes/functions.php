<?php
require_once 'config.php';

// Function to hash password
function hashPassword($password) {
    return password_hash($password, PASSWORD_DEFAULT);
}

// Function to verify password
function verifyPassword($password, $hash) {
    return password_verify($password, $hash);
}

// Function to sanitize input
function sanitizeInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Function to generate random string
function generateRandomString($length = 10) {
    return bin2hex(random_bytes($length / 2));
}

// Function to format date
function formatDate($date) {
    return date('M d, Y', strtotime($date));
}

// Function to format datetime
function formatDateTime($datetime) {
    return date('M d, Y g:i A', strtotime($datetime));
}

// Function to get status badge class
function getStatusBadge($status) {
    switch($status) {
        case 'pending':
            return 'badge bg-warning text-dark';
        case 'in_progress':
            return 'badge bg-info';
        case 'resolved':
            return 'badge bg-success';
        case 'rejected':
            return 'badge bg-danger';
        default:
            return 'badge bg-secondary';
    }
}

// Function to get complaint categories
function getComplaintCategories() {
    return [
        'academic' => 'Academic',
        'hostel' => 'Hostel',
        'finance' => 'Finance',
        'library' => 'Library',
        'it' => 'IT Support',
        'general' => 'General'
    ];
}

// Function to send notification (placeholder for future email integration)
function sendNotification($userId, $message) {
    global $pdo;
    // For now, we'll just log to a simple notifications table
    // In future, this can be enhanced with email functionality
    try {
        $stmt = $pdo->prepare("INSERT INTO notifications (user_id, message, created_at) VALUES (?, ?, NOW())");
        $stmt->execute([$userId, $message]);
    } catch(PDOException $e) {
        // Log error or handle gracefully
    }
}

// Function to get user info
function getUserInfo($userId) {
    global $pdo;
    try {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        return $stmt->fetch();
    } catch(PDOException $e) {
        return false;
    }
}

// Function to get complaint stats
function getComplaintStats() {
    global $pdo;
    try {
        $stmt = $pdo->query("
            SELECT 
                COUNT(*) as total,
                SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
                SUM(CASE WHEN status = 'in_progress' THEN 1 ELSE 0 END) as in_progress,
                SUM(CASE WHEN status = 'resolved' THEN 1 ELSE 0 END) as resolved,
                SUM(CASE WHEN status = 'rejected' THEN 1 ELSE 0 END) as rejected
            FROM complaints
        ");
        return $stmt->fetch();
    } catch(PDOException $e) {
        return false;
    }
}

// Function to check if database tables exist (MySQL version)
function checkDatabaseSetup() {
    global $pdo;
    try {
        // Check if tables exist in MySQL
        $stmt = $pdo->query("SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = 'student_complaints' AND table_name = 'users'");
        $usersExists = $stmt->fetchColumn() > 0;
        
        $stmt = $pdo->query("SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = 'student_complaints' AND table_name = 'complaints'");
        $complaintsExists = $stmt->fetchColumn() > 0;
        
        return $usersExists && $complaintsExists;
    } catch(PDOException $e) {
        return false;
    }
}
?>