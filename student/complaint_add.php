<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Submit Complaint - Student Portal</title>
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

    $error = '';
    $success = '';

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $category = sanitizeInput($_POST['category']);
        $description = sanitizeInput($_POST['description']);

        if (empty($category) || empty($description)) {
            $error = 'Please fill in all required fields.';
        } else {
            try {
                $stmt = $pdo->prepare("INSERT INTO complaints (student_id, category, description) VALUES (?, ?, ?)");
                $stmt->execute([$_SESSION['user_id'], $category, $description]);
                
                $success = 'Complaint submitted successfully! You will be notified when it is reviewed.';
                
                // Clear form data
                $_POST = array();
            } catch(PDOException $e) {
                $error = 'An error occurred while submitting your complaint. Please try again.';
            }
        }
    }

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
                            <a class="nav-link active" href="complaint_add.php">
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
                    <h1 class="h2">Submit New Complaint</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <div class="btn-group me-2">
                            <a href="dashboard.php" class="btn btn-sm btn-outline-secondary">
                                <i class="fas fa-arrow-left"></i> Back to Dashboard
                            </a>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-lg-8">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">Complaint Form</h5>
                            </div>
                            <div class="card-body">
                                <?php if ($error): ?>
                                    <div class="alert alert-danger">
                                        <i class="fas fa-exclamation-triangle"></i> <?php echo $error; ?>
                                    </div>
                                <?php endif; ?>

                                <?php if ($success): ?>
                                    <div class="alert alert-success">
                                        <i class="fas fa-check-circle"></i> <?php echo $success; ?>
                                        <div class="mt-2">
                                            <a href="dashboard.php" class="btn btn-success btn-sm">Go to Dashboard</a>
                                            <a href="complaint_view.php" class="btn btn-outline-success btn-sm">View My Complaints</a>
                                        </div>
                                    </div>
                                <?php else: ?>
                                    <form method="POST">
                                        <div class="mb-3">
                                            <label for="category" class="form-label">Complaint Category *</label>
                                            <select class="form-select" id="category" name="category" required>
                                                <option value="">Select a category</option>
                                                <?php foreach ($categories as $key => $value): ?>
                                                    <option value="<?php echo $key; ?>" 
                                                            <?php echo (isset($_POST['category']) && $_POST['category'] == $key) ? 'selected' : ''; ?>>
                                                        <?php echo $value; ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>

                                        <div class="mb-3">
                                            <label for="description" class="form-label">Complaint Description *</label>
                                            <textarea class="form-control" id="description" name="description" rows="6" 
                                                    placeholder="Please provide detailed information about your complaint..." required><?php echo isset($_POST['description']) ? htmlspecialchars($_POST['description']) : ''; ?></textarea>
                                            <small class="form-text text-muted">
                                                Please be as detailed as possible to help us resolve your issue quickly.
                                            </small>
                                        </div>

                                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                            <button type="reset" class="btn btn-outline-secondary me-md-2">
                                                <i class="fas fa-undo"></i> Reset
                                            </button>
                                            <button type="submit" class="btn btn-primary">
                                                <i class="fas fa-paper-plane"></i> Submit Complaint
                                            </button>
                                        </div>
                                    </form>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-4">
                        <div class="card">
                            <div class="card-header">
                                <h6 class="mb-0">Complaint Categories</h6>
                            </div>
                            <div class="card-body">
                                <?php foreach ($categories as $key => $value): ?>
                                    <div class="category-card p-3 mb-2 border rounded">
                                        <h6 class="mb-1"><?php echo $value; ?></h6>
                                        <small class="text-muted">
                                            <?php
                                            switch($key) {
                                                case 'academic':
                                                    echo 'Issues related to courses, exams, grades, faculty';
                                                    break;
                                                case 'hostel':
                                                    echo 'Accommodation, roommates, facilities';
                                                    break;
                                                case 'finance':
                                                    echo 'Fee payments, scholarships, refunds';
                                                    break;
                                                case 'library':
                                                    echo 'Book issues, study spaces, digital resources';
                                                    break;
                                                case 'it':
                                                    echo 'Internet, email, system access, technical support';
                                                    break;
                                                case 'general':
                                                    echo 'Other issues not covered in above categories';
                                                    break;
                                            }
                                            ?>
                                        </small>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <div class="card mt-4">
                            <div class="card-header">
                                <h6 class="mb-0">Tips for Better Resolution</h6>
                            </div>
                            <div class="card-body">
                                <ul class="list-unstyled">
                                    <li class="mb-2"><i class="fas fa-check text-success me-2"></i> Be specific and detailed</li>
                                    <li class="mb-2"><i class="fas fa-check text-success me-2"></i> Include relevant dates and times</li>
                                    <li class="mb-2"><i class="fas fa-check text-success me-2"></i> Mention any previous attempts to resolve</li>
                                    <li class="mb-2"><i class="fas fa-check text-success me-2"></i> Use professional language</li>
                                    <li class="mb-0"><i class="fas fa-check text-success me-2"></i> Check status regularly</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>