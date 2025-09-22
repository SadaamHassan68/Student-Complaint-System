<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Staff Dashboard - Complaint Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <?php
    require_once '../includes/config.php';
    require_once '../includes/functions.php';

    // Check if user is logged in and is a staff member
    if (!isLoggedIn() || !hasRole('staff')) {
        redirect('../index.php');
    }

    // Get staff's assigned complaints
    $stmt = $pdo->prepare("
        SELECT c.*, s.username as student_name, s.email as student_email
        FROM complaints c 
        JOIN users s ON c.student_id = s.id 
        WHERE c.assigned_to = ?
        ORDER BY 
            CASE WHEN c.status = 'in_progress' THEN 1 
                 WHEN c.status = 'pending' THEN 2 
                 WHEN c.status = 'resolved' THEN 3 
                 ELSE 4 END,
            c.created_at DESC
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $assigned_complaints = $stmt->fetchAll();

    // Get statistics for this staff member
    $stmt = $pdo->prepare("
        SELECT 
            COUNT(*) as total,
            SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
            SUM(CASE WHEN status = 'in_progress' THEN 1 ELSE 0 END) as in_progress,
            SUM(CASE WHEN status = 'resolved' THEN 1 ELSE 0 END) as resolved,
            SUM(CASE WHEN status = 'rejected' THEN 1 ELSE 0 END) as rejected
        FROM complaints 
        WHERE assigned_to = ?
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $stats = $stmt->fetch();
    ?>

    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <nav class="col-md-3 col-lg-2 d-md-block sidebar collapse">
                <div class="position-sticky pt-3">
                    <div class="text-center mb-4">
                        <h5 class="text-white">Staff Portal</h5>
                        <small class="text-light"><?php echo htmlspecialchars($_SESSION['username']); ?></small>
                    </div>
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link active" href="dashboard.php">
                                <i class="fas fa-tachometer-alt"></i> Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="assigned_complaints.php">
                                <i class="fas fa-list-alt"></i> Assigned Complaints
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
                    <h1 class="h2">Staff Dashboard</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <div class="btn-group me-2">
                            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="location.reload()">
                                <i class="fas fa-sync-alt"></i> Refresh
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Welcome Card -->
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="card welcome-card">
                            <div class="card-body">
                                <h4 class="card-title">Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>!</h4>
                                <p class="card-text">Manage and resolve complaints assigned to you.</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Statistics Cards -->
                <div class="row mb-4">
                    <div class="col-lg-3 col-md-6 mb-3">
                        <div class="card stats-card">
                            <div class="card-body text-center">
                                <h3><?php echo $stats['total']; ?></h3>
                                <p class="mb-0">Total Assigned</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6 mb-3">
                        <div class="card text-center" style="background: linear-gradient(135deg, #ffc107 0%, #ff8f00 100%); color: white;">
                            <div class="card-body">
                                <h3><?php echo $stats['pending']; ?></h3>
                                <p class="mb-0">Pending</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6 mb-3">
                        <div class="card text-center" style="background: linear-gradient(135deg, #17a2b8 0%, #117a8b 100%); color: white;">
                            <div class="card-body">
                                <h3><?php echo $stats['in_progress']; ?></h3>
                                <p class="mb-0">In Progress</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6 mb-3">
                        <div class="card text-center" style="background: linear-gradient(135deg, #28a745 0%, #1e7e34 100%); color: white;">
                            <div class="card-body">
                                <h3><?php echo $stats['resolved']; ?></h3>
                                <p class="mb-0">Resolved</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Priority Complaints -->
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h5 class="mb-0">
                                    <i class="fas fa-exclamation-circle text-warning"></i> 
                                    Priority Complaints (Pending & In Progress)
                                </h5>
                                <a href="assigned_complaints.php" class="btn btn-sm btn-primary">View All</a>
                            </div>
                            <div class="card-body">
                                <?php 
                                $priority_complaints = array_filter($assigned_complaints, function($complaint) {
                                    return in_array($complaint['status'], ['pending', 'in_progress']);
                                });
                                ?>
                                
                                <?php if (count($priority_complaints) > 0): ?>
                                    <div class="row">
                                        <?php foreach (array_slice($priority_complaints, 0, 6) as $complaint): ?>
                                            <div class="col-lg-4 col-md-6 mb-3">
                                                <div class="card h-100 status-<?php echo $complaint['status']; ?>">
                                                    <div class="card-body">
                                                        <div class="d-flex justify-content-between align-items-start mb-2">
                                                            <h6 class="card-title mb-0">#<?php echo $complaint['id']; ?></h6>
                                                            <span class="<?php echo getStatusBadge($complaint['status']); ?>">
                                                                <?php echo ucfirst(str_replace('_', ' ', $complaint['status'])); ?>
                                                            </span>
                                                        </div>
                                                        <p class="card-text">
                                                            <strong>Student:</strong> <?php echo htmlspecialchars($complaint['student_name']); ?><br>
                                                            <strong>Category:</strong> <?php echo ucfirst($complaint['category']); ?><br>
                                                            <strong>Date:</strong> <?php echo formatDate($complaint['created_at']); ?>
                                                        </p>
                                                        <p class="card-text">
                                                            <?php echo substr(htmlspecialchars($complaint['description']), 0, 100) . '...'; ?>
                                                        </p>
                                                        <div class="d-flex gap-2">
                                                            <button class="btn btn-sm btn-outline-primary" 
                                                                    onclick="viewComplaint(<?php echo $complaint['id']; ?>)">
                                                                <i class="fas fa-eye"></i> View
                                                            </button>
                                                            <button class="btn btn-sm btn-primary" 
                                                                    onclick="updateComplaint(<?php echo $complaint['id']; ?>)">
                                                                <i class="fas fa-edit"></i> Update
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                    
                                    <?php if (count($priority_complaints) > 6): ?>
                                        <div class="text-center mt-3">
                                            <a href="assigned_complaints.php?priority=1" class="btn btn-outline-primary">
                                                View All Priority Complaints
                                            </a>
                                        </div>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <div class="text-center py-4">
                                        <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
                                        <h5 class="text-success">Great job!</h5>
                                        <p class="text-muted">No pending complaints to handle right now.</p>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Activity -->
                <div class="row">
                    <div class="col-lg-8">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">Recent Assignments</h5>
                            </div>
                            <div class="card-body">
                                <?php if (count($assigned_complaints) > 0): ?>
                                    <div class="table-responsive">
                                        <table class="table table-sm table-hover">
                                            <thead>
                                                <tr>
                                                    <th>ID</th>
                                                    <th>Student</th>
                                                    <th>Category</th>
                                                    <th>Status</th>
                                                    <th>Date</th>
                                                    <th>Action</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach (array_slice($assigned_complaints, 0, 8) as $complaint): ?>
                                                    <tr>
                                                        <td>#<?php echo $complaint['id']; ?></td>
                                                        <td><?php echo htmlspecialchars($complaint['student_name']); ?></td>
                                                        <td>
                                                            <span class="badge bg-secondary">
                                                                <?php echo ucfirst($complaint['category']); ?>
                                                            </span>
                                                        </td>
                                                        <td>
                                                            <span class="<?php echo getStatusBadge($complaint['status']); ?>">
                                                                <?php echo ucfirst(str_replace('_', ' ', $complaint['status'])); ?>
                                                            </span>
                                                        </td>
                                                        <td><?php echo formatDate($complaint['created_at']); ?></td>
                                                        <td>
                                                            <button class="btn btn-sm btn-outline-primary" 
                                                                    onclick="updateComplaint(<?php echo $complaint['id']; ?>)">
                                                                Update
                                                            </button>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                <?php else: ?>
                                    <div class="text-center py-3">
                                        <i class="fas fa-clipboard-list fa-2x text-muted mb-2"></i>
                                        <p class="text-muted mb-0">No complaints assigned yet</p>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-4">
                        <div class="card mb-3">
                            <div class="card-header">
                                <h6 class="mb-0">Performance Overview</h6>
                            </div>
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span>Resolution Rate</span>
                                    <strong>
                                        <?php 
                                        $resolution_rate = $stats['total'] > 0 ? round(($stats['resolved'] / $stats['total']) * 100, 1) : 0;
                                        echo $resolution_rate . '%';
                                        ?>
                                    </strong>
                                </div>
                                <div class="progress mb-3">
                                    <div class="progress-bar bg-success" style="width: <?php echo $resolution_rate; ?>%"></div>
                                </div>
                                
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span>Pending Items</span>
                                    <strong><?php echo $stats['pending']; ?></strong>
                                </div>
                                <div class="d-flex justify-content-between align-items-center">
                                    <span>In Progress</span>
                                    <strong><?php echo $stats['in_progress']; ?></strong>
                                </div>
                            </div>
                        </div>

                        <div class="card">
                            <div class="card-header">
                                <h6 class="mb-0">Quick Actions</h6>
                            </div>
                            <div class="card-body">
                                <div class="d-grid gap-2">
                                    <a href="assigned_complaints.php?status=pending" class="btn btn-warning btn-sm">
                                        <i class="fas fa-clock"></i> View Pending
                                    </a>
                                    <a href="assigned_complaints.php?status=in_progress" class="btn btn-info btn-sm">
                                        <i class="fas fa-spinner"></i> View In Progress
                                    </a>
                                    <a href="assigned_complaints.php" class="btn btn-primary btn-sm">
                                        <i class="fas fa-list"></i> View All Assignments
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <!-- View Complaint Modal -->
    <div class="modal fade" id="complaintModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Complaint Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="complaintDetails">
                    <!-- Content will be loaded here -->
                </div>
            </div>
        </div>
    </div>

    <!-- Update Complaint Modal -->
    <div class="modal fade" id="updateComplaintModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Update Complaint</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="updateComplaintContent">
                    <!-- Content will be loaded here -->
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function viewComplaint(id) {
            fetch('complaint_details.php?id=' + id)
                .then(response => response.text())
                .then(data => {
                    document.getElementById('complaintDetails').innerHTML = data;
                    new bootstrap.Modal(document.getElementById('complaintModal')).show();
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error loading complaint details');
                });
        }

        function updateComplaint(id) {
            fetch('complaint_update.php?id=' + id)
                .then(response => response.text())
                .then(data => {
                    document.getElementById('updateComplaintContent').innerHTML = data;
                    new bootstrap.Modal(document.getElementById('updateComplaintModal')).show();
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error loading complaint update form');
                });
        }

        // Auto-refresh every 60 seconds
        setInterval(function() {
            if (!document.querySelector('.modal.show')) {
                location.reload();
            }
        }, 60000);
    </script>
</body>
</html>