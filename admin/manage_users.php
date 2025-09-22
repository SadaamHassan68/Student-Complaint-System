<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users - Admin Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <?php
    require_once '../includes/config.php';
    require_once '../includes/functions.php';

    // Check if user is logged in and is an admin
    if (!isLoggedIn() || !hasRole('admin')) {
        redirect('../index.php');
    }

    $success = '';
    $error = '';

    // Handle user actions
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $action = $_POST['action'];
        
        if ($action == 'add_user') {
            $username = sanitizeInput($_POST['username']);
            $email = sanitizeInput($_POST['email']);
            $password = $_POST['password'];
            $role = $_POST['role'];
            
            if (empty($username) || empty($email) || empty($password) || empty($role)) {
                $error = 'Please fill in all fields.';
            } else {
                try {
                    // Check if email already exists
                    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
                    $stmt->execute([$email]);
                    
                    if ($stmt->fetch()) {
                        $error = 'Email already exists.';
                    } else {
                        $hashedPassword = hashPassword($password);
                        $stmt = $pdo->prepare("INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, ?)");
                        $stmt->execute([$username, $email, $hashedPassword, $role]);
                        $success = 'User added successfully!';
                    }
                } catch(PDOException $e) {
                    $error = 'Error adding user.';
                }
            }
        } elseif ($action == 'update_role') {
            $user_id = (int)$_POST['user_id'];
            $new_role = $_POST['role'];
            
            try {
                $stmt = $pdo->prepare("UPDATE users SET role = ? WHERE id = ?");
                $stmt->execute([$new_role, $user_id]);
                $success = 'User role updated successfully!';
            } catch(PDOException $e) {
                $error = 'Error updating user role.';
            }
        } elseif ($action == 'delete_user') {
            $user_id = (int)$_POST['user_id'];
            
            try {
                // Don't allow deleting the current admin
                if ($user_id == $_SESSION['user_id']) {
                    $error = 'You cannot delete your own account.';
                } else {
                    $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
                    $stmt->execute([$user_id]);
                    $success = 'User deleted successfully!';
                }
            } catch(PDOException $e) {
                $error = 'Error deleting user. User may have associated complaints.';
            }
        }
    }

    // Get all users
    $stmt = $pdo->prepare("
        SELECT u.*, 
               COUNT(c.id) as complaint_count,
               COUNT(CASE WHEN c.status = 'resolved' THEN 1 END) as resolved_count
        FROM users u 
        LEFT JOIN complaints c ON u.id = c.student_id 
        GROUP BY u.id 
        ORDER BY u.created_at DESC
    ");
    $stmt->execute();
    $users = $stmt->fetchAll();

    // Get user statistics
    $stmt = $pdo->query("
        SELECT 
            role,
            COUNT(*) as count 
        FROM users 
        GROUP BY role
    ");
    $user_stats = [];
    while ($row = $stmt->fetch()) {
        $user_stats[$row['role']] = $row['count'];
    }
    ?>

    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <nav class="col-md-3 col-lg-2 d-md-block sidebar collapse">
                <div class="position-sticky pt-3">
                    <div class="text-center mb-4">
                        <h5 class="text-white">Admin Panel</h5>
                        <small class="text-light"><?php echo htmlspecialchars($_SESSION['username']); ?></small>
                    </div>
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link" href="dashboard.php">
                                <i class="fas fa-tachometer-alt"></i> Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="complaints.php">
                                <i class="fas fa-exclamation-triangle"></i> Manage Complaints
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link active" href="manage_users.php">
                                <i class="fas fa-users"></i> Manage Users
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="reports.php">
                                <i class="fas fa-chart-bar"></i> Reports
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="../logout.php">
                                <i class="fas fa-sign-out-alt"></i> Logout
                            </a>
                        </li>
                    </ul>
                </div>
            </nav>

            <!-- Main content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 main-content">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Manage Users</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <div class="btn-group me-2">
                            <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addUserModal">
                                <i class="fas fa-plus"></i> Add User
                            </button>
                        </div>
                    </div>
                </div>

                <?php if ($success): ?>
                    <div class="alert alert-success alert-dismissible fade show">
                        <i class="fas fa-check-circle"></i> <?php echo $success; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <?php if ($error): ?>
                    <div class="alert alert-danger alert-dismissible fade show">
                        <i class="fas fa-exclamation-triangle"></i> <?php echo $error; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <!-- User Statistics -->
                <div class="row mb-4">
                    <div class="col-lg-3 col-md-6 mb-3">
                        <div class="card stats-card">
                            <div class="card-body text-center">
                                <h3><?php echo array_sum($user_stats); ?></h3>
                                <p class="mb-0">Total Users</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6 mb-3">
                        <div class="card text-center" style="background: linear-gradient(135deg, #28a745 0%, #1e7e34 100%); color: white;">
                            <div class="card-body">
                                <h3><?php echo isset($user_stats['student']) ? $user_stats['student'] : 0; ?></h3>
                                <p class="mb-0">Students</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6 mb-3">
                        <div class="card text-center" style="background: linear-gradient(135deg, #17a2b8 0%, #117a8b 100%); color: white;">
                            <div class="card-body">
                                <h3><?php echo isset($user_stats['staff']) ? $user_stats['staff'] : 0; ?></h3>
                                <p class="mb-0">Staff</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6 mb-3">
                        <div class="card text-center" style="background: linear-gradient(135deg, #ffc107 0%, #ff8f00 100%); color: white;">
                            <div class="card-body">
                                <h3><?php echo isset($user_stats['admin']) ? $user_stats['admin'] : 0; ?></h3>
                                <p class="mb-0">Admins</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Users List -->
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">All Users</h5>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>ID</th>
                                                <th>Name</th>
                                                <th>Email</th>
                                                <th>Role</th>
                                                <th>Complaints</th>
                                                <th>Resolved</th>
                                                <th>Joined</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($users as $user): ?>
                                                <tr>
                                                    <td><?php echo $user['id']; ?></td>
                                                    <td>
                                                        <strong><?php echo htmlspecialchars($user['username']); ?></strong>
                                                        <?php if ($user['id'] == $_SESSION['user_id']): ?>
                                                            <span class="badge bg-primary">You</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td><?php echo htmlspecialchars($user['email']); ?></td>
                                                    <td>
                                                        <span class="badge <?php 
                                                            echo $user['role'] == 'admin' ? 'bg-danger' : 
                                                                ($user['role'] == 'staff' ? 'bg-info' : 'bg-success'); 
                                                        ?>">
                                                            <?php echo ucfirst($user['role']); ?>
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <?php if ($user['role'] == 'student'): ?>
                                                            <?php echo $user['complaint_count']; ?>
                                                        <?php else: ?>
                                                            <span class="text-muted">N/A</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <?php if ($user['role'] == 'student' && $user['complaint_count'] > 0): ?>
                                                            <?php echo $user['resolved_count']; ?>
                                                        <?php else: ?>
                                                            <span class="text-muted">N/A</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td><?php echo formatDate($user['created_at']); ?></td>
                                                    <td>
                                                        <?php if ($user['id'] != $_SESSION['user_id']): ?>
                                                            <button class="btn btn-sm btn-outline-primary me-1" 
                                                                    onclick="editUser(<?php echo $user['id']; ?>, '<?php echo $user['role']; ?>')">
                                                                <i class="fas fa-edit"></i>
                                                            </button>
                                                            <button class="btn btn-sm btn-outline-danger" 
                                                                    onclick="deleteUser(<?php echo $user['id']; ?>, '<?php echo htmlspecialchars($user['username']); ?>')">
                                                                <i class="fas fa-trash"></i>
                                                            </button>
                                                        <?php else: ?>
                                                            <span class="text-muted">Current User</span>
                                                        <?php endif; ?>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <!-- Add User Modal -->
    <div class="modal fade" id="addUserModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add New User</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="add_user">
                        
                        <div class="mb-3">
                            <label for="username" class="form-label">Full Name *</label>
                            <input type="text" class="form-control" id="username" name="username" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="email" class="form-label">Email Address *</label>
                            <input type="email" class="form-control" id="email" name="email" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="password" class="form-label">Password *</label>
                            <input type="password" class="form-control" id="password" name="password" required>
                            <small class="form-text text-muted">Minimum 6 characters</small>
                        </div>
                        
                        <div class="mb-3">
                            <label for="role" class="form-label">Role *</label>
                            <select class="form-select" id="role" name="role" required>
                                <option value="">Select Role</option>
                                <option value="student">Student</option>
                                <option value="staff">Staff</option>
                                <option value="admin">Admin</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Add User</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit User Modal -->
    <div class="modal fade" id="editUserModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit User Role</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="update_role">
                        <input type="hidden" name="user_id" id="edit_user_id">
                        
                        <div class="mb-3">
                            <label for="edit_role" class="form-label">Role *</label>
                            <select class="form-select" id="edit_role" name="role" required>
                                <option value="student">Student</option>
                                <option value="staff">Staff</option>
                                <option value="admin">Admin</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Update Role</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteUserModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Confirm Delete</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete user <strong id="delete_username"></strong>?</p>
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle"></i> 
                        This action cannot be undone. All associated data will be removed.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <form method="POST" style="display: inline;">
                        <input type="hidden" name="action" value="delete_user">
                        <input type="hidden" name="user_id" id="delete_user_id">
                        <button type="submit" class="btn btn-danger">Delete User</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function editUser(userId, currentRole) {
            document.getElementById('edit_user_id').value = userId;
            document.getElementById('edit_role').value = currentRole;
            new bootstrap.Modal(document.getElementById('editUserModal')).show();
        }

        function deleteUser(userId, username) {
            document.getElementById('delete_user_id').value = userId;
            document.getElementById('delete_username').textContent = username;
            new bootstrap.Modal(document.getElementById('deleteUserModal')).show();
        }
    </script>
</body>
</html>