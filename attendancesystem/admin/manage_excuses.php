<?php
// Configuration and session setup
session_start();
if (!isset($_SESSION['logged_in']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

// Include dependencies
require_once '../config/database.php';
require_once '../objects/excuse_letter.php';
require_once '../objects/student.php';

// Initialize database connection
$database = new Database();
$db = $database->getConnection();

// Initialize objects
$excuseLetter = new ExcuseLetter($db);
$student = new Student($db);

// Process operations
$result = processExcuseOperations($excuseLetter, $_GET, $_POST, $_SESSION['user_id']);
$status = $result['status'] ?? 'pending';
$program = $result['program'] ?? null;
$message = $result['message'] ?? '';

// Get data
$excuseLetters = $excuseLetter->getByStatusAndProgram($status, $program);
$programs = getPrograms($db);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Excuse Letters | Attendance System</title>
    
    <!-- External CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.3/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../admin/style.css">
</head>
<body>
    <!-- Navigation Bar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="admin_dashboard.php">
                <i class="bi bi-calendar-check me-2"></i>Attendance System
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <span class="nav-link">
                            <i class="bi bi-person-circle me-1"></i>
                            Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?> (Admin)
                        </span>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="../logout.php"> 
                            <i class="bi bi-box-arrow-right me-1"></i>Logout
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="container dashboard-container">
        <a href="admin_dashboard.php" class="btn btn-primary mb-4">
            <i class="bi bi-arrow-left me-2"></i>Back to Dashboard
        </a>
        
        <div class="row">
            <div class="col-12">
                <!-- Success/Error Messages -->
                <?php echo $message; ?>

                <!-- Filter Section -->
                <div class="filter-section mb-4">
                    <?php renderFilterForm($status, $program, $programs); ?>
                </div>

                <!-- Excuse Letters Table -->
                <div class="card">
                    <div class="card-body">
                        <?php renderExcuseLettersTable($excuseLetters); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- View Excuse Modal -->
    <div class="modal fade" id="viewExcuseModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Excuse Letter Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="excuseDetails">
                    <!-- Content loaded via AJAX -->
                </div>
            </div>
        </div>
    </div>

    <!-- Review Excuse Modal -->
    <div class="modal fade" id="reviewExcuseModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Review Excuse Letter</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="excuse_id" id="reviewExcuseId">
                        <div class="mb-3">
                            <label for="status" class="form-label">Status</label>
                            <select class="form-select" id="status" name="status" required>
                                <option value="approved">Approve</option>
                                <option value="rejected">Reject</option>
                            </select>
                        </div>
                        <button type="submit" name="update_status" class="btn btn-primary">Submit Review</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- External JavaScript -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Internal JavaScript -->
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Load excuse details via AJAX
        const viewModal = document.getElementById('viewExcuseModal');
        viewModal.addEventListener('show.bs.modal', function(event) {
            const button = event.relatedTarget;
            const excuseId = button.getAttribute('data-excuse-id');
            fetch('get_excuse_details.php?id=' + excuseId)
                .then(res => res.text())
                .then(data => { document.getElementById('excuseDetails').innerHTML = data; })
                .catch(() => { 
                    document.getElementById('excuseDetails').innerHTML = 
                    '<div class="alert alert-danger">Error loading excuse details.</div>'; 
                });
        });

        // Review modal setup
        const reviewModal = document.getElementById('reviewExcuseModal');
        reviewModal.addEventListener('show.bs.modal', function(event) {
            const button = event.relatedTarget;
            const excuseId = button.getAttribute('data-excuse-id');
            document.getElementById('reviewExcuseId').value = excuseId;
        });
    });
    </script>
</body>
</html>

<?php
/**
 * Process excuse letter operations
 */
function processExcuseOperations($excuseLetter, $get, $post, $userId) {
    $result = [
        'status' => isset($get['status']) ? htmlspecialchars($get['status']) : 'pending',
        'program' => isset($get['program']) && $get['program'] !== '' ? htmlspecialchars($get['program']) : null,
        'message' => ''
    ];
    
    // Handle status update
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($post['update_status'])) {
        $excuse_id = htmlspecialchars(strip_tags($post['excuse_id']));
        $new_status = htmlspecialchars(strip_tags($post['status']));
        
        if ($excuseLetter->updateStatus($excuse_id, $new_status, $userId)) {
            $result['message'] = '<div class="alert alert-success alert-dismissible fade show" role="alert">
                Excuse letter status updated successfully!
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>';
        } else {
            $result['message'] = '<div class="alert alert-danger alert-dismissible fade show" role="alert">
                Unable to update excuse letter status.
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>';
        }
    }
    
    return $result;
}

/**
 * Get unique programs from students table
 */
function getPrograms($db) {
    $programs_query = $db->query("
        SELECT DISTINCT TRIM(course_program) AS course_program 
        FROM students 
        WHERE course_program IS NOT NULL AND course_program != ''
        ORDER BY course_program
    ");
    return $programs_query->fetchAll(PDO::FETCH_COLUMN);
}

/**
 * Render filter form
 */
function renderFilterForm($status, $program, $programs) {
    ?>
    <form method="GET" class="row g-3">
        <div class="col-md-4">
            <label for="status" class="form-label">Status</label>
            <select class="form-select" id="status" name="status">
                <option value="pending" <?php echo $status === 'pending' ? 'selected' : ''; ?>>Pending</option>
                <option value="approved" <?php echo $status === 'approved' ? 'selected' : ''; ?>>Approved</option>
                <option value="rejected" <?php echo $status === 'rejected' ? 'selected' : ''; ?>>Rejected</option>
            </select>
        </div>
        <div class="col-md-4">
            <label for="program" class="form-label">Program</label>
            <select class="form-select" id="program" name="program">
                <option value="">All Programs</option>
                <?php foreach ($programs as $prog): ?>
                    <option value="<?php echo htmlspecialchars($prog); ?>" 
                        <?php echo $program === $prog ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($prog); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-4 d-flex align-items-end">
            <button type="submit" class="btn btn-primary">Apply Filters</button>
        </div>
    </form>
    <?php
}

/**
 * Render excuse letters table
 */
function renderExcuseLettersTable($excuseLetters) {
    if ($excuseLetters->rowCount() > 0) {
        ?>
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Student</th>
                        <th>Program</th>
                        <th>Absence Date</th>
                        <th>Submission Date</th>
                        <th>Reason</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $status_badge = [
                        'pending' => 'warning',
                        'approved' => 'success',
                        'rejected' => 'danger'
                    ];
                    
                    while ($row = $excuseLetters->fetch(PDO::FETCH_ASSOC)): 
                    ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['first_name'] . ' ' . $row['last_name']); ?></td>
                            <td><?php echo htmlspecialchars($row['course_program']); ?></td>
                            <td><?php echo date('M j, Y', strtotime($row['absence_date'])); ?></td>
                            <td><?php echo date('M j, Y g:i A', strtotime($row['submission_date'])); ?></td>
                            <td><?php 
                                $reason = htmlspecialchars($row['reason']);
                                echo substr($reason, 0, 50) . (strlen($reason) > 50 ? '...' : ''); 
                            ?></td>
                            <td>
                                <span class="badge bg-<?php echo $status_badge[$row['status']]; ?> status-badge">
                                    <?php echo ucfirst($row['status']); ?>
                                </span>
                            </td>
                            <td>
                                <button class="btn btn-sm btn-info" data-bs-toggle="modal" 
                                        data-bs-target="#viewExcuseModal" 
                                        data-excuse-id="<?php echo htmlspecialchars($row['id']); ?>">
                                    <i class="bi bi-eye"></i> View
                                </button>
                                <?php if ($row['status'] === 'pending'): ?>
                                    <button class="btn btn-sm btn-success" data-bs-toggle="modal" 
                                            data-bs-target="#reviewExcuseModal" 
                                            data-excuse-id="<?php echo htmlspecialchars($row['id']); ?>">
                                        <i class="bi bi-check-circle"></i> Review
                                    </button>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
        <?php
    } else {
        echo '<p class="text-center text-muted">No excuse letters found with the selected filters.</p>';
    }
}