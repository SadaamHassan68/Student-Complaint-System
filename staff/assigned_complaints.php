<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Assigned Complaints - Staff Portal</title>
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

    // Get filter parameters
    $status_filter = isset($_GET['status']) ? $_GET['status'] : '';
    $priority = isset($_GET['priority']) ? (bool)$_GET['priority'] : false;

    // Build query with filters
    $where_conditions = ["c.assigned_to = ?"];
    $params = [$_SESSION['user_id']];

    if ($status_filter) {
        $where_conditions[] = "c.status = ?";
        $params[] = $status_filter;
    }

    if ($priority) {
        $where_conditions[] = "c.status IN ('pending', 'in_progress')";
    }

    $where_clause = implode(' AND ', $where_conditions);

    // Get staff's assigned complaints with filters
    $stmt = $pdo->prepare("
        SELECT c.*, s.username as student_name, s.email as student_email
        FROM complaints c 
        JOIN users s ON c.student_id = s.id 
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

    $categories = getComplaintCategories();
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
                            <a class="nav-link" href="dashboard.php">
                                <i class="fas fa-tachometer-alt"></i> Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link active" href="assigned_complaints.php">
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
                    <h1 class="h2">Assigned Complaints</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <div class="btn-group me-2">
                            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="location.reload()">
                                <i class="fas fa-sync-alt"></i> Refresh
                            </button>
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
                                        <label class="form-label">Quick Filters</label>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="priority" name="priority" value="1" 
                                                   <?php echo $priority ? 'checked' : ''; ?>>
                                            <label class="form-check-label" for="priority">
                                                Priority Only (Pending & In Progress)
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-md-4 d-flex align-items-end">
                                        <button type="submit" class="btn btn-primary me-2">
                                            <i class="fas fa-filter"></i> Apply Filters
                                        </button>
                                        <a href="assigned_complaints.php" class="btn btn-outline-secondary">
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
                                    My Assigned Complaints 
                                    <span class="badge bg-primary"><?php echo count($complaints); ?></span>
                                </h5>
                            </div>
                            <div class="card-body">
                                <?php if (count($complaints) > 0): ?>
                                    <div class="row">
                                        <?php foreach ($complaints as $complaint): ?>
                                            <div class="col-lg-6 col-xl-4 mb-4">
                                                <div class="card h-100 status-<?php echo $complaint['status']; ?>">
                                                    <div class="card-header d-flex justify-content-between align-items-center">
                                                        <h6 class="mb-0">#<?php echo $complaint['id']; ?></h6>
                                                        <span class="<?php echo getStatusBadge($complaint['status']); ?>">
                                                            <?php echo ucfirst(str_replace('_', ' ', $complaint['status'])); ?>
                                                        </span>
                                                    </div>
                                                    <div class="card-body">
                                                        <div class="mb-2">
                                                            <strong>Student:</strong> <?php echo htmlspecialchars($complaint['student_name']); ?>
                                                        </div>
                                                        <div class="mb-2">
                                                            <strong>Email:</strong> 
                                                            <small><?php echo htmlspecialchars($complaint['student_email']); ?></small>
                                                        </div>
                                                        <div class="mb-2">
                                                            <strong>Category:</strong> 
                                                            <span class="badge bg-secondary">
                                                                <?php echo ucfirst($complaint['category']); ?>
                                                            </span>
                                                        </div>
                                                        <div class="mb-2">
                                                            <strong>Submitted:</strong> <?php echo formatDate($complaint['created_at']); ?>
                                                        </div>
                                                        <div class="mb-3">
                                                            <strong>Description:</strong>
                                                            <p class="small text-muted mb-0">
                                                                <?php echo substr(htmlspecialchars($complaint['description']), 0, 120) . '...'; ?>
                                                            </p>
                                                        </div>
                                                        
                                                        <?php if ($complaint['admin_remarks']): ?>
                                                            <div class="mb-3">
                                                                <strong>Current Remarks:</strong>
                                                                <p class="small text-info mb-0">
                                                                    <?php echo substr(htmlspecialchars($complaint['admin_remarks']), 0, 100) . '...'; ?>
                                                                </p>
                                                            </div>
                                                        <?php endif; ?>
                                                    </div>
                                                    <div class="card-footer">
                                                        <div class="d-flex gap-2">
                                                            <button class="btn btn-sm btn-outline-primary flex-fill" 
                                                                    onclick="viewComplaint(<?php echo $complaint['id']; ?>)">
                                                                <i class="fas fa-eye"></i> View
                                                            </button>
                                                            <button class="btn btn-sm btn-primary flex-fill" 
                                                                    onclick="updateComplaint(<?php echo $complaint['id']; ?>)">
                                                                <i class="fas fa-edit"></i> Update
                                                            </button>
                                                        </div>
                                                        
                                                        <?php if ($complaint['status'] == 'pending'): ?>
                                                            <div class="mt-2">
                                                                <button class="btn btn-sm btn-info w-100" 
                                                                        onclick="quickUpdate(<?php echo $complaint['id']; ?>, 'in_progress')">
                                                                    <i class="fas fa-play"></i> Start Working
                                                                </button>
                                                            </div>
                                                        <?php elseif ($complaint['status'] == 'in_progress'): ?>
                                                            <div class="mt-2">
                                                                <button class="btn btn-sm btn-success w-100" 
                                                                        onclick="quickUpdate(<?php echo $complaint['id']; ?>, 'resolved')">
                                                                    <i class="fas fa-check"></i> Mark Resolved
                                                                </button>
                                                            </div>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php else: ?>
                                    <div class="text-center py-5">
                                        <i class="fas fa-tasks fa-3x text-muted mb-3"></i>
                                        <h5 class="text-muted">
                                            <?php if ($status_filter || $priority): ?>
                                                No complaints match your filters
                                            <?php else: ?>
                                                No complaints assigned yet
                                            <?php endif; ?>
                                        </h5>
                                        <p class="text-muted">
                                            <?php if ($status_filter || $priority): ?>
                                                Try adjusting your filters or <a href="assigned_complaints.php">view all complaints</a>.
                                            <?php else: ?>
                                                Complaints will appear here when they are assigned to you by an administrator.
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

        function quickUpdate(id, status) {
            if (confirm('Are you sure you want to update this complaint status to ' + status.replace('_', ' ') + '?')) {
                const formData = new FormData();
                formData.append('complaint_id', id);
                formData.append('status', status);
                formData.append('admin_remarks', status === 'in_progress' ? 
                    'Complaint is being reviewed and worked on.' : 
                    'Complaint has been resolved. Please contact us if you need further assistance.');

                fetch('complaint_update.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.text())
                .then(data => {
                    location.reload();
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error updating complaint');
                });
            }
        }
    </script>
</body>
</html>