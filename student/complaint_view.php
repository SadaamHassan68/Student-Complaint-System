<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Complaints - Student Portal</title>
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

    // Get filter parameters
    $status_filter = isset($_GET['status']) ? $_GET['status'] : '';
    $category_filter = isset($_GET['category']) ? $_GET['category'] : '';

    // Build query with filters
    $where_conditions = ["c.student_id = ?"];
    $params = [$_SESSION['user_id']];

    if ($status_filter) {
        $where_conditions[] = "c.status = ?";
        $params[] = $status_filter;
    }

    if ($category_filter) {
        $where_conditions[] = "c.category = ?";
        $params[] = $category_filter;
    }

    $where_clause = implode(' AND ', $where_conditions);

    // Get student's complaints with filters
    $stmt = $pdo->prepare("
        SELECT c.*, u.username as assigned_to_name 
        FROM complaints c 
        LEFT JOIN users u ON c.assigned_to = u.id 
        WHERE $where_clause
        ORDER BY c.created_at DESC
    ");
    $stmt->execute($params);
    $complaints = $stmt->fetchAll();

    $categories = getComplaintCategories();
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
                            <a class="nav-link" href="dashboard.php">
                                <i class="fas fa-tachometer-alt"></i> Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="complaint_add.php">
                                <i class="fas fa-plus-circle"></i> New Complaint
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link active" href="complaint_view.php">
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
                    <h1 class="h2">My Complaints</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <div class="btn-group me-2">
                            <a href="complaint_add.php" class="btn btn-sm btn-primary">
                                <i class="fas fa-plus"></i> New Complaint
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Filters -->
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
                                <form method="GET" class="row g-3">
                                    <div class="col-md-4">
                                        <label for="status" class="form-label">Filter by Status</label>
                                        <select class="form-select" id="status" name="status">
                                            <option value="">All Statuses</option>
                                            <option value="pending" <?php echo $status_filter == 'pending' ? 'selected' : ''; ?>>Pending</option>
                                            <option value="in_progress" <?php echo $status_filter == 'in_progress' ? 'selected' : ''; ?>>In Progress</option>
                                            <option value="resolved" <?php echo $status_filter == 'resolved' ? 'selected' : ''; ?>>Resolved</option>
                                            <option value="rejected" <?php echo $status_filter == 'rejected' ? 'selected' : ''; ?>>Rejected</option>
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label for="category" class="form-label">Filter by Category</label>
                                        <select class="form-select" id="category" name="category">
                                            <option value="">All Categories</option>
                                            <?php foreach ($categories as $key => $value): ?>
                                                <option value="<?php echo $key; ?>" <?php echo $category_filter == $key ? 'selected' : ''; ?>>
                                                    <?php echo $value; ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="col-md-4 d-flex align-items-end">
                                        <button type="submit" class="btn btn-primary me-2">
                                            <i class="fas fa-filter"></i> Filter
                                        </button>
                                        <a href="complaint_view.php" class="btn btn-outline-secondary">
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
                                                    <th>Category</th>
                                                    <th>Description</th>
                                                    <th>Status</th>
                                                    <th>Assigned To</th>
                                                    <th>Created</th>
                                                    <th>Updated</th>
                                                    <th>Action</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($complaints as $complaint): ?>
                                                    <tr class="status-<?php echo $complaint['status']; ?>">
                                                        <td><strong>#<?php echo $complaint['id']; ?></strong></td>
                                                        <td>
                                                            <span class="badge bg-secondary">
                                                                <?php echo ucfirst($complaint['category']); ?>
                                                            </span>
                                                        </td>
                                                        <td>
                                                            <div style="max-width: 200px;">
                                                                <?php echo substr(htmlspecialchars($complaint['description']), 0, 80) . '...'; ?>
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
                                                        <td><?php echo formatDate($complaint['updated_at']); ?></td>
                                                        <td>
                                                            <button class="btn btn-sm btn-outline-primary" 
                                                                    onclick="viewComplaint(<?php echo $complaint['id']; ?>)">
                                                                <i class="fas fa-eye"></i> View
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
                                        <p class="text-muted">
                                            <?php if ($status_filter || $category_filter): ?>
                                                Try adjusting your filters or <a href="complaint_view.php">view all complaints</a>.
                                            <?php else: ?>
                                                You haven't submitted any complaints yet. <a href="complaint_add.php">Submit your first complaint</a>.
                                            <?php endif; ?>
                                        </p>
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