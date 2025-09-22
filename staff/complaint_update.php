<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

// Check if user is logged in and is a staff member
if (!isLoggedIn() || !hasRole('staff')) {
    echo '<div class="alert alert-danger">Access denied.</div>';
    exit;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $complaint_id = (int)$_POST['complaint_id'];
    $new_status = $_POST['status'];
    $admin_remarks = sanitizeInput($_POST['admin_remarks']);
    
    try {
        $stmt = $pdo->prepare("
            UPDATE complaints 
            SET status = ?, admin_remarks = ? 
            WHERE id = ? AND assigned_to = ?
        ");
        $stmt->execute([$new_status, $admin_remarks, $complaint_id, $_SESSION['user_id']]);
        
        // Send notification to student
        $stmt = $pdo->prepare("SELECT student_id FROM complaints WHERE id = ?");
        $stmt->execute([$complaint_id]);
        $complaint = $stmt->fetch();
        
        if ($complaint) {
            sendNotification($complaint['student_id'], "Your complaint #$complaint_id has been updated by staff. Status: " . ucfirst(str_replace('_', ' ', $new_status)));
        }
        
        echo '<div class="alert alert-success">Complaint updated successfully!</div>';
        echo '<script>setTimeout(function(){ location.reload(); }, 2000);</script>';
        exit;
    } catch(PDOException $e) {
        echo '<div class="alert alert-danger">Error updating complaint. Please try again.</div>';
        exit;
    }
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

<div class="complaint-update">
    <!-- Complaint Summary -->
    <div class="card mb-4">
        <div class="card-body">
            <div class="row">
                <div class="col-md-8">
                    <h6>Complaint #<?php echo $complaint['id']; ?> - <?php echo htmlspecialchars($complaint['student_name']); ?></h6>
                    <p class="mb-0">
                        <span class="badge bg-secondary me-2"><?php echo ucfirst($complaint['category']); ?></span>
                        <span class="<?php echo getStatusBadge($complaint['status']); ?>">
                            <?php echo ucfirst(str_replace('_', ' ', $complaint['status'])); ?>
                        </span>
                    </p>
                </div>
                <div class="col-md-4 text-end">
                    <small class="text-muted">
                        Submitted: <?php echo formatDate($complaint['created_at']); ?>
                    </small>
                </div>
            </div>
        </div>
    </div>

    <!-- Description -->
    <div class="mb-4">
        <h6>Description</h6>
        <div class="p-3 bg-light rounded">
            <?php echo nl2br(htmlspecialchars($complaint['description'])); ?>
        </div>
    </div>

    <!-- Current Admin Remarks -->
    <?php if ($complaint['admin_remarks']): ?>
        <div class="mb-4">
            <h6>Current Remarks</h6>
            <div class="p-3 bg-info bg-opacity-10 rounded border-start border-info border-3">
                <?php echo nl2br(htmlspecialchars($complaint['admin_remarks'])); ?>
            </div>
        </div>
    <?php endif; ?>

    <!-- Update Form -->
    <form method="POST">
        <input type="hidden" name="complaint_id" value="<?php echo $complaint['id']; ?>">
        
        <div class="mb-3">
            <label for="status" class="form-label">Update Status *</label>
            <select class="form-select" id="status" name="status" required>
                <option value="pending" <?php echo $complaint['status'] == 'pending' ? 'selected' : ''; ?>>Pending</option>
                <option value="in_progress" <?php echo $complaint['status'] == 'in_progress' ? 'selected' : ''; ?>>In Progress</option>
                <option value="resolved" <?php echo $complaint['status'] == 'resolved' ? 'selected' : ''; ?>>Resolved</option>
                <option value="rejected" <?php echo $complaint['status'] == 'rejected' ? 'selected' : ''; ?>>Rejected</option>
            </select>
            <small class="form-text text-muted">
                Select the current status of this complaint.
            </small>
        </div>

        <div class="mb-4">
            <label for="admin_remarks" class="form-label">Resolution Notes / Remarks</label>
            <textarea class="form-control" id="admin_remarks" name="admin_remarks" rows="5" 
                    placeholder="Add your resolution notes, actions taken, or feedback for the student..."><?php echo htmlspecialchars($complaint['admin_remarks']); ?></textarea>
            <small class="form-text text-muted">
                These notes will be visible to the student and can help them understand the resolution process.
            </small>
        </div>

        <!-- Status-specific guidance -->
        <div class="mb-4">
            <h6>Status Guidelines</h6>
            <div class="row">
                <div class="col-md-6">
                    <div class="card border-warning">
                        <div class="card-body">
                            <h6 class="card-title text-warning">
                                <i class="fas fa-clock"></i> In Progress
                            </h6>
                            <p class="card-text small">
                                Use when you are actively working on the complaint. 
                                Provide updates on what actions you are taking.
                            </p>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card border-success">
                        <div class="card-body">
                            <h6 class="card-title text-success">
                                <i class="fas fa-check-circle"></i> Resolved
                            </h6>
                            <p class="card-text small">
                                Use when the issue has been completely addressed. 
                                Explain what was done to resolve the complaint.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="d-flex justify-content-end">
            <button type="button" class="btn btn-secondary me-2" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save"></i> Update Complaint
            </button>
        </div>
    </form>

    <!-- Quick Actions -->
    <div class="mt-4 p-3 bg-light rounded">
        <h6>Quick Actions</h6>
        <div class="btn-group" role="group">
            <button type="button" class="btn btn-outline-info btn-sm" onclick="setStatus('in_progress')">
                <i class="fas fa-play"></i> Mark In Progress
            </button>
            <button type="button" class="btn btn-outline-success btn-sm" onclick="setStatus('resolved')">
                <i class="fas fa-check"></i> Mark Resolved
            </button>
            <button type="button" class="btn btn-outline-warning btn-sm" onclick="addCommonRemarks()">
                <i class="fas fa-comment"></i> Add Common Remarks
            </button>
        </div>
    </div>
</div>

<script>
function setStatus(status) {
    document.getElementById('status').value = status;
    
    // Set appropriate remarks based on status
    const remarksField = document.getElementById('admin_remarks');
    if (status === 'in_progress' && !remarksField.value) {
        remarksField.value = 'Your complaint is being reviewed and we are working on a resolution. We will update you soon with our progress.';
    } else if (status === 'resolved' && !remarksField.value) {
        remarksField.value = 'Your complaint has been resolved. Please contact us if you need any further assistance.';
    }
}

function addCommonRemarks() {
    const remarksField = document.getElementById('admin_remarks');
    const commonRemarks = [
        'Thank you for bringing this to our attention. We are investigating the matter.',
        'We have forwarded your complaint to the relevant department for action.',
        'Your complaint has been resolved. Please let us know if you need any further assistance.',
        'We apologize for any inconvenience caused. The issue has been addressed.',
        'Additional information may be required to process your complaint further.'
    ];
    
    let selectedRemark = prompt('Select a common remark:\n' + 
        commonRemarks.map((remark, index) => `${index + 1}. ${remark}`).join('\n') + 
        '\n\nEnter the number (1-5):');
    
    if (selectedRemark && selectedRemark >= 1 && selectedRemark <= 5) {
        remarksField.value = commonRemarks[selectedRemark - 1];
    }
}
</script>