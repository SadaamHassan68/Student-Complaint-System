<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard - Complaint System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <?php
    require_once '../includes/config.php';
    require_once '../includes/functions.php';

    // Check if user is logged in and is a student
    if (!isLoggedIn() || !hasRole('student')) {
        redirect('../index.php');
    }

    // Get student's complaints
    $stmt = $pdo->prepare("
        SELECT c.*, u.username as assigned_to_name 
        FROM complaints c 
        LEFT JOIN users u ON c.assigned_to = u.id 
        WHERE c.student_id = ? 
        ORDER BY c.created_at DESC
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $complaints = $stmt->fetchAll();

    // Get complaint statistics for this student
    $stmt = $pdo->prepare("
        SELECT 
            COUNT(*) as total,
            SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
            SUM(CASE WHEN status = 'in_progress' THEN 1 ELSE 0 END) as in_progress,
            SUM(CASE WHEN status = 'resolved' THEN 1 ELSE 0 END) as resolved,
            SUM(CASE WHEN status = 'rejected' THEN 1 ELSE 0 END) as rejected
        FROM complaints 
        WHERE student_id = ?
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
                        <h5 class="text-white">Student Portal</h5>
                        <small class="text-light"><?php echo htmlspecialchars($_SESSION['username']); ?></small>
                    </div>
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link active" href="dashboard.php">
                                <i class="fas fa-tachometer-alt"></i> Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="complaint_add.php">
                                <i class="fas fa-plus-circle"></i> New Complaint
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="complaint_view.php">
                                <i class="fas fa-list"></i> My Complaints
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
                    <h1 class="h2">Dashboard</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <div class="btn-group me-2">
                            <a href="complaint_add.php" class="btn btn-sm btn-outline-secondary">
                                <i class="fas fa-plus"></i> New Complaint
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Welcome Card -->
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="card welcome-card">
                            <div class="card-body">
                                <h4 class="card-title">Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>!</h4>
                                <p class="card-text">Manage your complaints and track their progress from here.</p>
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
                                <p class="mb-0">Total Complaints</p>
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

                <!-- Recent Complaints -->
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">Recent Complaints</h5>
                            </div>
                            <div class="card-body">
                                <?php if (count($complaints) > 0): ?>
                                    <div class="table-responsive">
                                        <table class="table table-hover">
                                            <thead>
                                                <tr>
                                                    <th>ID</th>
                                                    <th>Category</th>
                                                    <th>Description</th>
                                                    <th>Status</th>
                                                    <th>Assigned To</th>
                                                    <th>Date</th>
                                                    <th>Action</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach (array_slice($complaints, 0, 5) as $complaint): ?>
                                                    <tr>
                                                        <td>#<?php echo $complaint['id']; ?></td>
                                                        <td>
                                                            <span class="badge bg-secondary">
                                                                <?php echo ucfirst($complaint['category']); ?>
                                                            </span>
                                                        </td>
                                                        <td><?php echo substr(htmlspecialchars($complaint['description']), 0, 50) . '...'; ?></td>
                                                        <td>
                                                            <span class="<?php echo getStatusBadge($complaint['status']); ?>">
                                                                <?php echo ucfirst(str_replace('_', ' ', $complaint['status'])); ?>
                                                            </span>
                                                        </td>
                                                        <td>
                                                            <?php echo $complaint['assigned_to_name'] ? htmlspecialchars($complaint['assigned_to_name']) : 'Not Assigned'; ?>
                                                        </td>
                                                        <td><?php echo formatDate($complaint['created_at']); ?></td>
                                                        <td>
                                                            <button class="btn btn-sm btn-outline-primary" 
                                                                    onclick="viewComplaint(<?php echo $complaint['id']; ?>)">
                                                                View
                                                            </button>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                    <?php if (count($complaints) > 5): ?>
                                        <div class="text-center mt-3">
                                            <a href="complaint_view.php" class="btn btn-primary">View All Complaints</a>
                                        </div>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <div class="text-center py-4">
                                        <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                                        <h5 class="text-muted">No complaints submitted yet</h5>
                                        <p class="text-muted">Click the button below to submit your first complaint.</p>
                                        <a href="complaint_add.php" class="btn btn-primary">Submit Complaint</a>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <!-- Complaint View Modal -->
    <div class="modal fade" id="complaintModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Complaint Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="complaintDetails">
                    <!-- Complaint details will be loaded here -->
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
    </script>
</body>
</html>