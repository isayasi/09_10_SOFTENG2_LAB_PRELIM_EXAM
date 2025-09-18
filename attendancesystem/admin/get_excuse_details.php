<?php
// Configuration and session setup
session_start();
if (!isset($_SESSION['logged_in']) || $_SESSION['role'] !== 'admin') {
    http_response_code(403);
    exit('Access denied');
}

// Validate request
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    http_response_code(400);
    exit('Invalid request');
}

// Include dependencies
require_once '../config/database.php';
require_once '../objects/excuse_letter.php';

// Initialize database connection
$database = new Database();
$db = $database->getConnection();

// Get excuse letter details
$excuseLetter = new ExcuseLetter($db);
$excuse = $excuseLetter->getById($_GET['id']);

if ($excuse->rowCount() == 0) {
    echo '<div class="alert alert-danger">Excuse letter not found.</div>';
    exit();
}

$row = $excuse->fetch(PDO::FETCH_ASSOC);

// Status badge styling
$status_badge = [
    'pending' => 'warning',
    'approved' => 'success',
    'rejected' => 'danger'
];
?>

<!-- Student Information -->
<div class="row">
    <div class="col-md-6">
        <p><strong>Student:</strong> <?php echo htmlspecialchars($row['first_name'] . ' ' . $row['last_name']); ?></p>
        <p><strong>Program:</strong> <?php echo htmlspecialchars($row['course_program']); ?></p>
        <p><strong>Course:</strong> <?php echo htmlspecialchars($row['course_code'] . ' - ' . $row['course_name']); ?></p>
    </div>
    <div class="col-md-6">
        <p><strong>Absence Date:</strong> <?php echo date('M j, Y', strtotime($row['absence_date'])); ?></p>
        <p><strong>Submission Date:</strong> <?php echo date('M j, Y g:i A', strtotime($row['submission_date'])); ?></p>
        <p><strong>Status:</strong> 
            <span class="badge bg-<?php echo $status_badge[$row['status']]; ?>">
                <?php echo ucfirst($row['status']); ?>
            </span>
        </p>
    </div>
</div>

<hr>

<!-- Reason for Absence -->
<div class="mb-3">
    <strong>Reason for Absence:</strong>
    <p class="mt-2"><?php echo nl2br(htmlspecialchars($row['reason'])); ?></p>
</div>

<!-- Supporting Document -->
<?php if (!empty($row['supporting_document'])): ?>
<div class="mb-3">
    <strong>Supporting Document:</strong>
    <p>
        <a href="../uploads/excuse_letters/<?php echo htmlspecialchars($row['supporting_document']); ?>" 
           target="_blank" class="btn btn-sm btn-outline-primary">
            <i class="bi bi-download"></i> Download Document
        </a>
    </p>
</div>
<?php endif; ?>

<!-- Admin Notes -->
<?php if (!empty($row['admin_notes'])): ?>
<div class="mb-3">
    <strong>Admin Notes:</strong>
    <p class="mt-2"><?php echo nl2br(htmlspecialchars($row['admin_notes'])); ?></p>
</div>
<?php endif; ?>

<!-- Review Information -->
<?php if ($row['reviewed_by']): ?>
<div class="mb-3">
    <strong>Reviewed By:</strong> <?php echo htmlspecialchars($row['admin_reviewer']); ?><br>
    <strong>Reviewed At:</strong> <?php echo date('M j, Y g:i A', strtotime($row['reviewed_at'])); ?>
</div>
<?php endif; ?>