<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

// Check if user is logged in and is a student
if (!isLoggedIn() || !hasRole('student')) {
    echo '<div class="alert alert-danger">Access denied.</div>';
    exit;
}

$complaint_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($complaint_id <= 0) {
    echo '<div class="alert alert-danger">Invalid complaint ID.</div>';
    exit;
}

// Get complaint details - ensure it belongs to the logged-in student
$stmt = $pdo->prepare("
    SELECT c.*, u.username as assigned_to_name, s.username as student_name
    FROM complaints c 
    LEFT JOIN users u ON c.assigned_to = u.id 
    LEFT JOIN users s ON c.student_id = s.id
    WHERE c.id = ? AND c.student_id = ?
");
$stmt->execute([$complaint_id, $_SESSION['user_id']]);
$complaint = $stmt->fetch();

if (!$complaint) {
    echo '<div class="alert alert-danger">Complaint not found or access denied.</div>';
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
            <h6>Status</h6>
            <span class="<?php echo getStatusBadge($complaint['status']); ?>">
                <?php echo ucfirst(str_replace('_', ' ', $complaint['status'])); ?>
            </span>
        </div>
    </div>

    <div class="row mb-3">
        <div class="col-md-6">
            <h6>Category</h6>
            <p class="mb-0">
                <span class="badge bg-secondary">
                    <?php echo isset($categories[$complaint['category']]) ? $categories[$complaint['category']] : ucfirst($complaint['category']); ?>
                </span>
            </p>
        </div>
        <div class="col-md-6">
            <h6>Assigned To</h6>
            <p class="mb-0">
                <?php echo $complaint['assigned_to_name'] ? htmlspecialchars($complaint['assigned_to_name']) : '<span class="text-muted">Not assigned yet</span>'; ?>
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
                        <p class="timeline-description">Your complaint was successfully submitted and is awaiting review.</p>
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
                                    Complaint assigned to: <strong><?php echo htmlspecialchars($complaint['assigned_to_name']); ?></strong>
                                </p>
                            <?php endif; ?>
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