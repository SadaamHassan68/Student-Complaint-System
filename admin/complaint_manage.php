<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

// Check if user is logged in and is an admin
if (!isLoggedIn() || !hasRole('admin')) {
    echo '<div class="alert alert-danger">Access denied.</div>';
    exit;
}

$complaint_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($complaint_id <= 0) {
    echo '<div class="alert alert-danger">Invalid complaint ID.</div>';
    exit;
}

// Get complaint details
$stmt = $pdo->prepare("
    SELECT c.*, s.username as student_name, s.email as student_email, u.username as assigned_to_name
    FROM complaints c 
    JOIN users s ON c.student_id = s.id 
    LEFT JOIN users u ON c.assigned_to = u.id 
    WHERE c.id = ?
");
$stmt->execute([$complaint_id]);
$complaint = $stmt->fetch();

if (!$complaint) {
    echo '<div class="alert alert-danger">Complaint not found.</div>';
    exit;
}

// Get staff members for assignment
$stmt = $pdo->prepare("SELECT id, username FROM users WHERE role = 'staff' ORDER BY username");
$stmt->execute();
$staff_members = $stmt->fetchAll();

$categories = getComplaintCategories();
?>

<div class="complaint-manage">
    <!-- Complaint Details -->
    <div class="row mb-4">
        <div class="col-md-6">
            <h6>Complaint ID</h6>
            <p class="mb-0"><strong>#<?php echo $complaint['id']; ?></strong></p>
        </div>
        <div class="col-md-6">
            <h6>Current Status</h6>
            <span class="<?php echo getStatusBadge($complaint['status']); ?>">
                <?php echo ucfirst(str_replace('_', ' ', $complaint['status'])); ?>
            </span>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-md-6">
            <h6>Student</h6>
            <p class="mb-0">
                <strong><?php echo htmlspecialchars($complaint['student_name']); ?></strong><br>
                <small class="text-muted"><?php echo htmlspecialchars($complaint['student_email']); ?></small>
            </p>
        </div>
        <div class="col-md-6">
            <h6>Category</h6>
            <p class="mb-0">
                <span class="badge bg-secondary">
                    <?php echo isset($categories[$complaint['category']]) ? $categories[$complaint['category']] : ucfirst($complaint['category']); ?>
                </span>
            </p>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-md-6">
            <h6>Submitted On</h6>
            <p class="mb-0"><?php echo formatDateTime($complaint['created_at']); ?></p>
        </div>
        <div class="col-md-6">
            <h6>Last Updated</h6>
            <p class="mb-0"><?php echo formatDateTime($complaint['updated_at']); ?></p>
        </div>
    </div>

    <div class="mb-4">
        <h6>Description</h6>
        <div class="p-3 bg-light rounded">
            <?php echo nl2br(htmlspecialchars($complaint['description'])); ?>
        </div>
    </div>

    <?php if ($complaint['admin_remarks']): ?>
        <div class="mb-4">
            <h6>Current Admin Remarks</h6>
            <div class="p-3 bg-info bg-opacity-10 rounded border-start border-info border-3">
                <?php echo nl2br(htmlspecialchars($complaint['admin_remarks'])); ?>
            </div>
        </div>
    <?php endif; ?>

    <!-- Management Form -->
    <hr>
    <h6>Update Complaint</h6>
    <form method="POST" action="complaints.php">
        <input type="hidden" name="action" value="update_status">
        <input type="hidden" name="complaint_id" value="<?php echo $complaint['id']; ?>">
        
        <div class="row mb-3">
            <div class="col-md-6">
                <label for="status" class="form-label">Status *</label>
                <select class="form-select" id="status" name="status" required>
                    <option value="pending" <?php echo $complaint['status'] == 'pending' ? 'selected' : ''; ?>>Pending</option>
                    <option value="in_progress" <?php echo $complaint['status'] == 'in_progress' ? 'selected' : ''; ?>>In Progress</option>
                    <option value="resolved" <?php echo $complaint['status'] == 'resolved' ? 'selected' : ''; ?>>Resolved</option>
                    <option value="rejected" <?php echo $complaint['status'] == 'rejected' ? 'selected' : ''; ?>>Rejected</option>
                </select>
            </div>
            <div class="col-md-6">
                <label for="assigned_to" class="form-label">Assign to Staff</label>
                <select class="form-select" id="assigned_to" name="assigned_to">
                    <option value="">Not Assigned</option>
                    <?php foreach ($staff_members as $staff): ?>
                        <option value="<?php echo $staff['id']; ?>" 
                                <?php echo $complaint['assigned_to'] == $staff['id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($staff['username']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <small class="form-text text-muted">Optional: Assign to a staff member for handling</small>
            </div>
        </div>

        <div class="mb-3">
            <label for="admin_remarks" class="form-label">Admin Remarks</label>
            <textarea class="form-control" id="admin_remarks" name="admin_remarks" rows="4" 
                    placeholder="Add remarks for the student (optional)..."><?php echo htmlspecialchars($complaint['admin_remarks']); ?></textarea>
            <small class="form-text text-muted">These remarks will be visible to the student</small>
        </div>

        <div class="d-flex justify-content-end">
            <button type="button" class="btn btn-secondary me-2" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save"></i> Update Complaint
            </button>
        </div>
    </form>

    <!-- Status Timeline -->
    <hr class="mt-4">
    <h6>Status Timeline</h6>
    <div class="timeline">
        <div class="timeline-item">
            <div class="timeline-marker bg-primary"></div>
            <div class="timeline-content">
                <h6 class="timeline-title">Complaint Submitted</h6>
                <p class="timeline-time"><?php echo formatDateTime($complaint['created_at']); ?></p>
                <p class="timeline-description">Complaint submitted by <?php echo htmlspecialchars($complaint['student_name']); ?></p>
            </div>
        </div>

        <?php if ($complaint['status'] != 'pending'): ?>
            <div class="timeline-item">
                <div class="timeline-marker <?php echo $complaint['status'] == 'in_progress' ? 'bg-info' : ($complaint['status'] == 'resolved' ? 'bg-success' : 'bg-danger'); ?>"></div>
                <div class="timeline-content">
                    <h6 class="timeline-title">
                        Status Updated to <?php echo ucfirst(str_replace('_', ' ', $complaint['status'])); ?>
                    </h6>
                    <p class="timeline-time"><?php echo formatDateTime($complaint['updated_at']); ?></p>
                    <?php if ($complaint['assigned_to_name']): ?>
                        <p class="timeline-description">
                            Assigned to: <strong><?php echo htmlspecialchars($complaint['assigned_to_name']); ?></strong>
                        </p>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<style>
.timeline {
    position: relative;
    padding-left: 30px;
    margin-top: 20px;
}

.timeline::before {
    content: '';
    position: absolute;
    left: 15px;
    top: 0;
    bottom: 0;
    width: 2px;
    background: #e9ecef;
}

.timeline-item {
    position: relative;
    margin-bottom: 20px;
}

.timeline-marker {
    position: absolute;
    left: -37px;
    top: 0;
    width: 12px;
    height: 12px;
    border-radius: 50%;
    border: 2px solid white;
    box-shadow: 0 0 0 2px #e9ecef;
}

.timeline-content {
    background: #f8f9fa;
    padding: 15px;
    border-radius: 8px;
    border-left: 3px solid #dee2e6;
}

.timeline-title {
    margin-bottom: 5px;
    font-size: 0.9rem;
    font-weight: 600;
}

.timeline-time {
    margin-bottom: 5px;
    font-size: 0.8rem;
    color: #6c757d;
}

.timeline-description {
    margin-bottom: 0;
    font-size: 0.85rem;
    color: #495057;
}
</style>