<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Complaint Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <?php
    require_once '../includes/config.php';
    require_once '../includes/functions.php';

    // Check if user is logged in and is an admin
    if (!isLoggedIn() || !hasRole('admin')) {
        redirect('../index.php');
    }

    // Get overall statistics
    $stats = getComplaintStats();

    // Get recent complaints
    $stmt = $pdo->prepare("
        SELECT c.*, s.username as student_name, u.username as assigned_to_name 
        FROM complaints c 
        JOIN users s ON c.student_id = s.id 
        LEFT JOIN users u ON c.assigned_to = u.id 
        ORDER BY c.created_at DESC 
        LIMIT 10
    ");
    $stmt->execute();
    $recent_complaints = $stmt->fetchAll();

    // Get category-wise statistics
    $stmt = $pdo->query("
        SELECT category, COUNT(*) as count 
        FROM complaints 
        GROUP BY category 
        ORDER BY count DESC
    ");
    $category_stats = $stmt->fetchAll();

    // Get monthly statistics for the current year (SQLite version)
    $stmt = $pdo->query("
        SELECT 
            CAST(strftime('%m', created_at) AS INTEGER) as month,
            COUNT(*) as count 
        FROM complaints 
        WHERE strftime('%Y', created_at) = strftime('%Y', 'now')
        GROUP BY CAST(strftime('%m', created_at) AS INTEGER)
        ORDER BY month
    ");
    $monthly_stats = $stmt->fetchAll();

    // Get total users count
    $stmt = $pdo->query("SELECT COUNT(*) as total_users FROM users WHERE role = 'student'");
    $user_stats = $stmt->fetch();
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
                            <a class="nav-link active" href="dashboard.php">
                                <i class="fas fa-tachometer-alt"></i> Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="complaints.php">
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
                    <h1 class="h2">Admin Dashboard</h1>
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
                                <h4 class="card-title">Welcome, Administrator!</h4>
                                <p class="card-text">Monitor and manage student complaints from the central dashboard.</p>
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

                <!-- Charts Row -->
                <div class="row mb-4">
                    <div class="col-lg-6">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">Complaints by Status</h5>
                            </div>
                            <div class="card-body">
                                <canvas id="statusChart" width="400" height="300"></canvas>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">Complaints by Category</h5>
                            </div>
                            <div class="card-body">
                                <canvas id="categoryChart" width="400" height="300"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Complaints and Quick Stats -->
                <div class="row">
                    <div class="col-lg-8">
                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h5 class="mb-0">Recent Complaints</h5>
                                <a href="complaints.php" class="btn btn-sm btn-primary">View All</a>
                            </div>
                            <div class="card-body">
                                <?php if (count($recent_complaints) > 0): ?>
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
                                                <?php foreach ($recent_complaints as $complaint): ?>
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
                                                            <a href="complaints.php?id=<?php echo $complaint['id']; ?>" 
                                                               class="btn btn-sm btn-outline-primary">
                                                                View
                                                            </a>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                <?php else: ?>
                                    <div class="text-center py-3">
                                        <i class="fas fa-inbox fa-2x text-muted mb-2"></i>
                                        <p class="text-muted mb-0">No complaints yet</p>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-4">
                        <div class="card mb-3">
                            <div class="card-header">
                                <h6 class="mb-0">System Overview</h6>
                            </div>
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span>Total Students</span>
                                    <strong><?php echo $user_stats['total_users']; ?></strong>
                                </div>
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span>Resolution Rate</span>
                                    <strong>
                                        <?php 
                                        $resolution_rate = $stats['total'] > 0 ? round(($stats['resolved'] / $stats['total']) * 100, 1) : 0;
                                        echo $resolution_rate . '%';
                                        ?>
                                    </strong>
                                </div>
                                <div class="d-flex justify-content-between align-items-center">
                                    <span>Avg. Processing Time</span>
                                    <strong>2.5 days</strong>
                                </div>
                            </div>
                        </div>

                        <div class="card">
                            <div class="card-header">
                                <h6 class="mb-0">Quick Actions</h6>
                            </div>
                            <div class="card-body">
                                <div class="d-grid gap-2">
                                    <a href="complaints.php?status=pending" class="btn btn-warning btn-sm">
                                        <i class="fas fa-clock"></i> View Pending Complaints
                                    </a>
                                    <a href="manage_users.php" class="btn btn-info btn-sm">
                                        <i class="fas fa-users"></i> Manage Users
                                    </a>
                                    <a href="reports.php" class="btn btn-success btn-sm">
                                        <i class="fas fa-download"></i> Generate Report
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Status Chart
        const statusCtx = document.getElementById('statusChart').getContext('2d');
        new Chart(statusCtx, {
            type: 'doughnut',
            data: {
                labels: ['Pending', 'In Progress', 'Resolved', 'Rejected'],
                datasets: [{
                    data: [
                        <?php echo $stats['pending']; ?>,
                        <?php echo $stats['in_progress']; ?>,
                        <?php echo $stats['resolved']; ?>,
                        <?php echo $stats['rejected']; ?>
                    ],
                    backgroundColor: [
                        '#ffc107',
                        '#17a2b8',
                        '#28a745',
                        '#dc3545'
                    ],
                    borderWidth: 2,
                    borderColor: '#fff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });

        // Category Chart
        const categoryCtx = document.getElementById('categoryChart').getContext('2d');
        new Chart(categoryCtx, {
            type: 'bar',
            data: {
                labels: [
                    <?php foreach ($category_stats as $cat): ?>
                        '<?php echo ucfirst($cat['category']); ?>',
                    <?php endforeach; ?>
                ],
                datasets: [{
                    label: 'Complaints',
                    data: [
                        <?php foreach ($category_stats as $cat): ?>
                            <?php echo $cat['count']; ?>,
                        <?php endforeach; ?>
                    ],
                    backgroundColor: 'rgba(102, 126, 234, 0.8)',
                    borderColor: 'rgba(102, 126, 234, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                },
                plugins: {
                    legend: {
                        display: false
                    }
                }
            }
        });
    </script>
</body>
</html>