<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports - Admin Panel</title>
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

    // Handle report generation
    if (isset($_GET['export'])) {
        $export_type = $_GET['export'];
        $date_from = isset($_GET['date_from']) ? $_GET['date_from'] : '';
        $date_to = isset($_GET['date_to']) ? $_GET['date_to'] : '';
        
        // Build query based on date range
        $where_clause = "1=1";
        $params = [];
        
        if ($date_from) {
            $where_clause .= " AND DATE(c.created_at) >= ?";
            $params[] = $date_from;
        }
        
        if ($date_to) {
            $where_clause .= " AND DATE(c.created_at) <= ?";
            $params[] = $date_to;
        }
        
        $stmt = $pdo->prepare("
            SELECT c.*, s.username as student_name, s.email as student_email, u.username as assigned_to_name
            FROM complaints c 
            JOIN users s ON c.student_id = s.id 
            LEFT JOIN users u ON c.assigned_to = u.id 
            WHERE $where_clause
            ORDER BY c.created_at DESC
        ");
        $stmt->execute($params);
        $export_data = $stmt->fetchAll();
        
        if ($export_type === 'csv') {
            header('Content-Type: text/csv');
            header('Content-Disposition: attachment; filename="complaints_report_' . date('Y-m-d') . '.csv"');
            
            $output = fopen('php://output', 'w');
            fputcsv($output, [
                'ID', 'Student Name', 'Student Email', 'Category', 'Description', 
                'Status', 'Assigned To', 'Admin Remarks', 'Created At', 'Updated At'
            ]);
            
            foreach ($export_data as $row) {
                fputcsv($output, [
                    $row['id'],
                    $row['student_name'],
                    $row['student_email'],
                    $row['category'],
                    $row['description'],
                    $row['status'],
                    $row['assigned_to_name'] ?: 'Not Assigned',
                    $row['admin_remarks'],
                    $row['created_at'],
                    $row['updated_at']
                ]);
            }
            
            fclose($output);
            exit;
        }
    }

    // Get statistics for different time periods
    $stmt = $pdo->query("
        SELECT 
            COUNT(*) as total,
            SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
            SUM(CASE WHEN status = 'in_progress' THEN 1 ELSE 0 END) as in_progress,
            SUM(CASE WHEN status = 'resolved' THEN 1 ELSE 0 END) as resolved,
            SUM(CASE WHEN status = 'rejected' THEN 1 ELSE 0 END) as rejected
        FROM complaints
    ");
    $overall_stats = $stmt->fetch();

    // Monthly statistics for current year (MySQL version)
    $stmt = $pdo->query("
        SELECT 
            MONTH(created_at) as month,
            MONTHNAME(created_at) as month_name,
            COUNT(*) as count,
            SUM(CASE WHEN status = 'resolved' THEN 1 ELSE 0 END) as resolved
        FROM complaints 
        WHERE YEAR(created_at) = YEAR(NOW())
        GROUP BY MONTH(created_at)
        ORDER BY month
    ");
    $monthly_stats = $stmt->fetchAll();

    // Category-wise statistics
    $stmt = $pdo->query("
        SELECT 
            category,
            COUNT(*) as total,
            SUM(CASE WHEN status = 'resolved' THEN 1 ELSE 0 END) as resolved,
            ROUND((SUM(CASE WHEN status = 'resolved' THEN 1 ELSE 0 END) / COUNT(*)) * 100, 1) as resolution_rate
        FROM complaints 
        GROUP BY category 
        ORDER BY total DESC
    ");
    $category_stats = $stmt->fetchAll();

    // Daily stats for last 30 days (MySQL version)
    $stmt = $pdo->query("
        SELECT 
            DATE(created_at) as date,
            COUNT(*) as count
        FROM complaints 
        WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
        GROUP BY DATE(created_at)
        ORDER BY date
    ");
    $daily_stats = $stmt->fetchAll();
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
                            <a class="nav-link" href="manage_users.php">
                                <i class="fas fa-users"></i> Manage Users
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link active" href="reports.php">
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
                    <h1 class="h2">Reports & Analytics</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <div class="btn-group me-2">
                            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="window.print()">
                                <i class="fas fa-print"></i> Print
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Export Section -->
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">Export Reports</h5>
                            </div>
                            <div class="card-body">
                                <form method="GET" class="row g-3">
                                    <div class="col-md-3">
                                        <label for="date_from" class="form-label">From Date</label>
                                        <input type="date" class="form-control" id="date_from" name="date_from" 
                                               value="<?php echo isset($_GET['date_from']) ? $_GET['date_from'] : ''; ?>">
                                    </div>
                                    <div class="col-md-3">
                                        <label for="date_to" class="form-label">To Date</label>
                                        <input type="date" class="form-control" id="date_to" name="date_to" 
                                               value="<?php echo isset($_GET['date_to']) ? $_GET['date_to'] : ''; ?>">
                                    </div>
                                    <div class="col-md-6 d-flex align-items-end">
                                        <button type="submit" name="export" value="csv" class="btn btn-success me-2">
                                            <i class="fas fa-file-csv"></i> Export CSV
                                        </button>
                                        <a href="reports.php" class="btn btn-outline-secondary">
                                            <i class="fas fa-times"></i> Clear
                                        </a>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Overview Statistics -->
                <div class="row mb-4">
                    <div class="col-lg-3 col-md-6 mb-3">
                        <div class="card stats-card">
                            <div class="card-body text-center">
                                <h3><?php echo $overall_stats['total']; ?></h3>
                                <p class="mb-0">Total Complaints</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6 mb-3">
                        <div class="card text-center" style="background: linear-gradient(135deg, #28a745 0%, #1e7e34 100%); color: white;">
                            <div class="card-body">
                                <h3><?php echo $overall_stats['resolved']; ?></h3>
                                <p class="mb-0">Resolved</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6 mb-3">
                        <div class="card text-center" style="background: linear-gradient(135deg, #ffc107 0%, #ff8f00 100%); color: white;">
                            <div class="card-body">
                                <h3><?php echo $overall_stats['pending'] + $overall_stats['in_progress']; ?></h3>
                                <p class="mb-0">Active</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6 mb-3">
                        <div class="card text-center" style="background: linear-gradient(135deg, #17a2b8 0%, #117a8b 100%); color: white;">
                            <div class="card-body">
                                <h3>
                                    <?php 
                                    $resolution_rate = $overall_stats['total'] > 0 ? 
                                        round(($overall_stats['resolved'] / $overall_stats['total']) * 100, 1) : 0;
                                    echo $resolution_rate . '%';
                                    ?>
                                </h3>
                                <p class="mb-0">Resolution Rate</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Charts Row -->
                <div class="row mb-4">
                    <div class="col-lg-6">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">Monthly Trends (<?php echo date('Y'); ?>)</h5>
                            </div>
                            <div class="card-body">
                                <canvas id="monthlyChart" height="300"></canvas>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">Category Distribution</h5>
                            </div>
                            <div class="card-body">
                                <canvas id="categoryChart" height="300"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Category Statistics Table -->
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">Category Performance</h5>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-striped">
                                        <thead>
                                            <tr>
                                                <th>Category</th>
                                                <th>Total Complaints</th>
                                                <th>Resolved</th>
                                                <th>Resolution Rate</th>
                                                <th>Performance</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($category_stats as $cat): ?>
                                                <tr>
                                                    <td>
                                                        <span class="badge bg-secondary">
                                                            <?php echo ucfirst($cat['category']); ?>
                                                        </span>
                                                    </td>
                                                    <td><?php echo $cat['total']; ?></td>
                                                    <td><?php echo $cat['resolved']; ?></td>
                                                    <td><?php echo $cat['resolution_rate']; ?>%</td>
                                                    <td>
                                                        <div class="progress">
                                                            <div class="progress-bar <?php echo $cat['resolution_rate'] >= 80 ? 'bg-success' : ($cat['resolution_rate'] >= 60 ? 'bg-warning' : 'bg-danger'); ?>" 
                                                                 style="width: <?php echo $cat['resolution_rate']; ?>%">
                                                            </div>
                                                        </div>
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

                <!-- Daily Activity Chart -->
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">Daily Activity (Last 30 Days)</h5>
                            </div>
                            <div class="card-body">
                                <canvas id="dailyChart" height="150"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Monthly Trends Chart
        const monthlyCtx = document.getElementById('monthlyChart').getContext('2d');
        new Chart(monthlyCtx, {
            type: 'line',
            data: {
                labels: [
                    <?php foreach ($monthly_stats as $stat): ?>
                        '<?php echo $stat['month_name']; ?>',
                    <?php endforeach; ?>
                ],
                datasets: [{
                    label: 'Total Complaints',
                    data: [
                        <?php foreach ($monthly_stats as $stat): ?>
                            <?php echo $stat['count']; ?>,
                        <?php endforeach; ?>
                    ],
                    borderColor: 'rgba(102, 126, 234, 1)',
                    backgroundColor: 'rgba(102, 126, 234, 0.1)',
                    tension: 0.4
                }, {
                    label: 'Resolved',
                    data: [
                        <?php foreach ($monthly_stats as $stat): ?>
                            <?php echo $stat['resolved']; ?>,
                        <?php endforeach; ?>
                    ],
                    borderColor: 'rgba(40, 167, 69, 1)',
                    backgroundColor: 'rgba(40, 167, 69, 0.1)',
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });

        // Category Distribution Chart
        const categoryCtx = document.getElementById('categoryChart').getContext('2d');
        new Chart(categoryCtx, {
            type: 'doughnut',
            data: {
                labels: [
                    <?php foreach ($category_stats as $cat): ?>
                        '<?php echo ucfirst($cat['category']); ?>',
                    <?php endforeach; ?>
                ],
                datasets: [{
                    data: [
                        <?php foreach ($category_stats as $cat): ?>
                            <?php echo $cat['total']; ?>,
                        <?php endforeach; ?>
                    ],
                    backgroundColor: [
                        '#667eea', '#764ba2', '#f093fb', '#f5576c', 
                        '#4facfe', '#00f2fe', '#43e97b', '#38f9d7'
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

        // Daily Activity Chart
        const dailyCtx = document.getElementById('dailyChart').getContext('2d');
        new Chart(dailyCtx, {
            type: 'bar',
            data: {
                labels: [
                    <?php foreach ($daily_stats as $stat): ?>
                        '<?php echo date('M d', strtotime($stat['date'])); ?>',
                    <?php endforeach; ?>
                ],
                datasets: [{
                    label: 'Daily Complaints',
                    data: [
                        <?php foreach ($daily_stats as $stat): ?>
                            <?php echo $stat['count']; ?>,
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