<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Complaints - Admin Panel</title>
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

    // Handle complaint status update
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
        $complaint_id = (int)$_POST['complaint_id'];
        $action = $_POST['action'];
        
        switch($action) {
            case 'update_status':
                $new_status = $_POST['status'];
                $assigned_to = !empty($_POST['assigned_to']) ? (int)$_POST['assigned_to'] : null;
                $admin_remarks = sanitizeInput($_POST['admin_remarks']);
                
                $stmt = $pdo->prepare("
                    UPDATE complaints 
                    SET status = ?, assigned_to = ?, admin_remarks = ? 
                    WHERE id = ?
                ");
                $stmt->execute([$new_status, $assigned_to, $admin_remarks, $complaint_id]);
                
                // Send notification to student
                $stmt = $pdo->prepare("SELECT student_id FROM complaints WHERE id = ?");
                $stmt->execute([$complaint_id]);
                $complaint = $stmt->fetch();
                
                if ($complaint) {
                    sendNotification($complaint['student_id'], "Your complaint #$complaint_id status has been updated to: " . ucfirst(str_replace('_', ' ', $new_status)));
                }
                
                $_SESSION['success'] = 'Complaint updated successfully!';
                break;
        }
        
        redirect('complaints.php');
    }

    // Get filter parameters
    $status_filter = isset($_GET['status']) ? $_GET['status'] : '';
    $category_filter = isset($_GET['category']) ? $_GET['category'] : '';
    $search = isset($_GET['search']) ? sanitizeInput($_GET['search']) : '';

    // Build query with filters
    $where_conditions = ["1=1"];
    $params = [];

    if ($status_filter) {
        $where_conditions[] = "c.status = ?";
        $params[] = $status_filter;
    }

    if ($category_filter) {
        $where_conditions[] = "c.category = ?";
        $params[] = $category_filter;
    }

    if ($search) {
        $where_conditions[] = "(s.username LIKE ? OR c.description LIKE ? OR c.id = ?)";
        $params[] = "%$search%";
        $params[] = "%$search%";
        $params[] = $search;
    }

    $where_clause = implode(' AND ', $where_conditions);

    // Get complaints with filters
    $stmt = $pdo->prepare("
        SELECT c.*, s.username as student_name, s.email as student_email, u.username as assigned_to_name 
        FROM complaints c 
        JOIN users s ON c.student_id = s.id 
        LEFT JOIN users u ON c.assigned_to = u.id 
        WHERE $where_clause
        ORDER BY 
            CASE WHEN c.status = 'pending' THEN 1 
                 WHEN c.status = 'in_progress' THEN 2 
                 WHEN c.status = 'resolved' THEN 3 
                 ELSE 4 END,
            c.created_at DESC
    ");
    $stmt->execute($params);
    $complaints = $stmt->fetchAll();

    // Get staff members for assignment
    $stmt = $pdo->prepare("SELECT id, username FROM users WHERE role = 'staff' ORDER BY username");
    $stmt->execute();
    $staff_members = $stmt->fetchAll();

    $categories = getComplaintCategories();

    $success = isset($_SESSION['success']) ? $_SESSION['success'] : '';
    if ($success) {
        unset($_SESSION['success']);
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
                            <a class="nav-link active" href="complaints.php">
                                <i class="fas fa-exclamation-triangle"></i> Manage Complaints
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="manage_users.php">
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
                    <h1 class="h2">Manage Complaints</h1>
                </div>

                <?php if ($success): ?>
                    <div class="alert alert-success alert-dismissible fade show">
                        <i class="fas fa-check-circle"></i> <?php echo $success; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <!-- Filters -->
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
                                <form method="GET" class="row g-3">
                                    <div class="col-md-3">
                                        <label for="status" class="form-label">Status</label>
                                        <select class="form-select" id="status" name="status">
                                            <option value="">All Statuses</option>
                                            <option value="pending" <?php echo $status_filter == 'pending' ? 'selected' : ''; ?>>Pending</option>
                                            <option value="in_progress" <?php echo $status_filter == 'in_progress' ? 'selected' : ''; ?>>In Progress</option>
                                            <option value="resolved" <?php echo $status_filter == 'resolved' ? 'selected' : ''; ?>>Resolved</option>
                                            <option value="rejected" <?php echo $status_filter == 'rejected' ? 'selected' : ''; ?>>Rejected</option>
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <label for="category" class="form-label">Category</label>
                                        <select class="form-select" id="category" name="category">
                                            <option value="">All Categories</option>
                                            <?php foreach ($categories as $key => $value): ?>
                                                <option value="<?php echo $key; ?>" <?php echo $category_filter == $key ? 'selected' : ''; ?>>
                                                    <?php echo $value; ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label for="search" class="form-label">Search</label>
                                        <input type="text" class="form-control" id="search" name="search" 
                                               value="<?php echo htmlspecialchars($search); ?>" 
                                               placeholder="Search by student name, complaint ID, or description...">
                                    </div>
                                    <div class="col-md-2 d-flex align-items-end">
                                        <button type="submit" class="btn btn-primary me-2">
                                            <i class="fas fa-search"></i> Search
                                        </button>
                                        <a href="complaints.php" class="btn btn-outline-secondary">
                                            <i class="fas fa-times"></i> Clear
                                        </a>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Complaints List -->
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h5 class="mb-0">
                                    Complaints List 
                                    <span class="badge bg-primary"><?php echo count($complaints); ?></span>
                                </h5>
                            </div>
                            <div class="card-body">
                                <?php if (count($complaints) > 0): ?>
                                    <div class="table-responsive">
                                        <table class="table table-hover">
                                            <thead>
                                                <tr>
                                                    <th>ID</th>
                                                    <th>Student</th>
                                                    <th>Category</th>
                                                    <th>Description</th>
                                                    <th>Status</th>
                                                    <th>Assigned To</th>
                                                    <th>Created</th>
                                                    <th>Action</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($complaints as $complaint): ?>
                                                    <tr class="status-<?php echo $complaint['status']; ?>">
                                                        <td><strong>#<?php echo $complaint['id']; ?></strong></td>
                                                        <td>
                                                            <div>
                                                                <strong><?php echo htmlspecialchars($complaint['student_name']); ?></strong><br>
                                                                <small class="text-muted"><?php echo htmlspecialchars($complaint['student_email']); ?></small>
                                                            </div>
                                                        </td>
                                                        <td>
                                                            <span class="badge bg-secondary">
                                                                <?php echo ucfirst($complaint['category']); ?>
                                                            </span>
                                                        </td>
                                                        <td>
                                                            <div style="max-width: 200px;">
                                                                <?php echo substr(htmlspecialchars($complaint['description']), 0, 100) . '...'; ?>
                                                            </div>
                                                        </td>
                                                        <td>
                                                            <span class="<?php echo getStatusBadge($complaint['status']); ?>">
                                                                <?php echo ucfirst(str_replace('_', ' ', $complaint['status'])); ?>
                                                            </span>
                                                        </td>
                                                        <td>
                                                            <?php echo $complaint['assigned_to_name'] ? htmlspecialchars($complaint['assigned_to_name']) : '<span class="text-muted">Not Assigned</span>'; ?>
                                                        </td>
                                                        <td><?php echo formatDate($complaint['created_at']); ?></td>
                                                        <td>
                                                            <button class="btn btn-sm btn-outline-primary" 
                                                                    onclick="manageComplaint(<?php echo $complaint['id']; ?>)">
                                                                <i class="fas fa-edit"></i> Manage
                                                            </button>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                <?php else: ?>
                                    <div class="text-center py-5">
                                        <i class="fas fa-search fa-3x text-muted mb-3"></i>
                                        <h5 class="text-muted">No complaints found</h5>
                                        <p class="text-muted">Try adjusting your search criteria.</p>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <!-- Manage Complaint Modal -->
    <div class="modal fade" id="manageComplaintModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Manage Complaint</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="complaintManageContent">
                    <!-- Content will be loaded here -->
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function manageComplaint(id) {
            fetch('complaint_manage.php?id=' + id)
                .then(response => response.text())
                .then(data => {
                    document.getElementById('complaintManageContent').innerHTML = data;
                    new bootstrap.Modal(document.getElementById('manageComplaintModal')).show();
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error loading complaint details');
                });
        }

        // Auto-refresh every 30 seconds
        setInterval(function() {
            if (!document.querySelector('.modal.show')) {
                location.reload();
            }
        }, 30000);
    </script>
</body>
</html>