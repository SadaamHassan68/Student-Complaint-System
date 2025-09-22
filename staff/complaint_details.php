<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

// Check if user is logged in and is a staff member
if (!isLoggedIn() || !hasRole('staff')) {
    echo '<div class="alert alert-danger">Access denied.</div>';
    exit;
}

$complaint_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($complaint_id <= 0) {
    echo '<div class="alert alert-danger">Invalid complaint ID.</div>';
    exit;
}

// Get complaint details - ensure it's assigned to this staff member
$stmt = $pdo->prepare("
    SELECT c.*, s.username as student_name, s.email as student_email
    FROM complaints c 
    JOIN users s ON c.student_id = s.id 
    WHERE c.id = ? AND c.assigned_to = ?
");
$stmt->execute([$complaint_id, $_SESSION['user_id']]);
$complaint = $stmt->fetch();

if (!$complaint) {
    echo '<div class="alert alert-danger">Complaint not found or not assigned to you.</div>';
    exit;
}

$categories = getComplaintCategories();
?>

<div class="complaint-details">
    <div class="row mb-3">
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

    <div class="row mb-3">
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

    <div class="row mb-3">
        <div class="col-md-6">
            <h6>Submitted On</h6>
            <p class="mb-0"><?php echo formatDateTime($complaint['created_at']); ?></p>
        </div>
        <div class="col-md-6">
            <h6>Last Updated</h6>
            <p class="mb-0"><?php echo formatDateTime($complaint['updated_at']); ?></p>
        </div>
    </div>

    <div class="mb-3">
        <h6>Description</h6>
        <div class="p-3 bg-light rounded">
            <?php echo nl2br(htmlspecialchars($complaint['description'])); ?>
        </div>
    </div>

    <?php if ($complaint['admin_remarks']): ?>
        <div class="mb-3">
            <h6>Admin Remarks</h6>
            <div class="p-3 bg-info bg-opacity-10 rounded border-start border-info border-3">
                <?php echo nl2br(htmlspecialchars($complaint['admin_remarks'])); ?>
            </div>
        </div>
    <?php endif; ?>

    <div class="row">
        <div class="col-12">
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

                <div class="timeline-item">
                    <div class="timeline-marker bg-info"></div>
                    <div class="timeline-content">
                        <h6 class="timeline-title">Assigned to You</h6>
                        <p class="timeline-time"><?php echo formatDateTime($complaint['updated_at']); ?></p>
                        <p class="timeline-description">This complaint has been assigned to you for resolution.</p>
                    </div>
                </div>

                <?php if ($complaint['status'] == 'resolved'): ?>
                    <div class="timeline-item">
                        <div class="timeline-marker bg-success"></div>
                        <div class="timeline-content">
                            <h6 class="timeline-title">Complaint Resolved</h6>
                            <p class="timeline-time"><?php echo formatDateTime($complaint['updated_at']); ?></p>
                            <p class="timeline-description">Complaint has been marked as resolved.</p>
                        </div>
                    </div>
                <?php elseif ($complaint['status'] == 'rejected'): ?>
                    <div class="timeline-item">
                        <div class="timeline-marker bg-danger"></div>
                        <div class="timeline-content">
                            <h6 class="timeline-title">Complaint Rejected</h6>
                            <p class="timeline-time"><?php echo formatDateTime($complaint['updated_at']); ?></p>
                            <p class="timeline-description">Complaint has been rejected.</p>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
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