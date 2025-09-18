<?php
include_once '../config/database.php';
include_once '../objects/student.php';
include_once '../objects/course.php';
include_once '../objects/excuse_letter.php';

session_start();

// Redirect if not logged in as student
if (!isset($_SESSION['logged_in']) || $_SESSION['role'] !== 'student') {
    header("Location: login.php");
    exit();
}

$database = new Database();
$db = $database->getConnection();

$student = new Student($db);
$course = new Course($db);
$excuseLetter = new ExcuseLetter($db);

// Get student data with course information
$student->getStudentByUserId($_SESSION['user_id']);

// Get student's course data
$studentCourseData = $student->getStudentCourse();

// For backward compatibility - if course_program is empty but we have course data
if (empty($student->course_program) && $studentCourseData) {
    $student->course_program = $studentCourseData['course_code'];
}

// Get all courses (for fallback)
$allCourses = $course->read();

// Get student's excuse letters
$excuseLetters = $excuseLetter->getByStudent($student->id);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit_excuse'])) {
    $excuseLetter->student_id = $student->id;
    $excuseLetter->course_id = $_POST['course_id'];
    $excuseLetter->absence_date = $_POST['absence_date'];
    $excuseLetter->reason = $_POST['reason'];

    // Handle file upload
    if (isset($_FILES['supporting_document']) && $_FILES['supporting_document']['error'] == 0) {
        $target_dir = "../uploads/excuse_letters/";
        if (!file_exists($target_dir)) mkdir($target_dir, 0777, true);

        $allowed_types = ['pdf','doc','docx','jpg','jpeg','png'];
        $max_size = 5 * 1024 * 1024;
        $file_extension = strtolower(pathinfo($_FILES["supporting_document"]["name"], PATHINFO_EXTENSION));

        if (in_array($file_extension, $allowed_types) && $_FILES["supporting_document"]["size"] <= $max_size) {
            $file_name = "excuse_" . $student->id . "_" . time() . "_" . bin2hex(random_bytes(5)) . "." . $file_extension;
            $target_file = $target_dir . $file_name;

            if (move_uploaded_file($_FILES["supporting_document"]["tmp_name"], $target_file)) {
                $excuseLetter->supporting_document = $file_name;
            }
        }
    }

    if ($excuseLetter->create()) {
        $_SESSION['success_message'] = "Excuse letter submitted successfully!";
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    } else {
        $_SESSION['error_message'] = "Unable to submit excuse letter. Please try again.";
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    }
}

// Display messages and clear session
if (isset($_SESSION['success_message'])) {
    $success_message = $_SESSION['success_message'];
    unset($_SESSION['success_message']);
}

if (isset($_SESSION['error_message'])) {
    $error_message = $_SESSION['error_message'];
    unset($_SESSION['error_message']);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard | Attendance System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.3/font/bootstrap-icons.css">
    <style>
        :root {
            --dark-bg: #121212;
            --darker-bg: #0a0a0a;
            --card-bg: #1e1e1e;
            --primary: #8a2be2;
            --primary-hover: #9b45ed;
            --text: #e0e0e0;
            --text-muted: #a0a0a0;
        }
        
        body {
            background-color: var(--dark-bg);
            color: var(--text);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            min-height: 100vh;
        }
        
        .navbar {
            background-color: var(--darker-bg) !important;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.3);
        }
        
        .navbar-brand {
            color: var(--primary) !important;
            font-weight: 700;
            letter-spacing: 0.5px;
        }
        
        .nav-link {
            color: var(--text) !important;
            transition: color 0.3s;
        }
        
        .nav-link:hover {
            color: var(--primary) !important;
        }
        
        .dashboard-container {
            padding: 30px 0;
        }
        
        .card {
            background-color: var(--card-bg);
            border: none;
            border-radius: 12px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
            margin-bottom: 25px;
            transition: transform 0.3s, box-shadow 0.3s;
        }
        
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(138, 43, 226, 0.2);
        }
        
        .card-header {
            background: linear-gradient(135deg, var(--primary), #6a11cb);
            border: none;
            padding: 20px;
            border-radius: 12px 12px 0 0 !important;
        }
        
        .card-header h4 {
            margin: 0;
            font-weight: 600;
            letter-spacing: 0.5px;
        }
        
        .card-body {
            padding: 25px;
        }
        
        .alert-info {
            background-color: rgba(138, 43, 226, 0.1);
            border: 1px solid rgba(138, 43, 226, 0.3);
            color: var(--text);
            border-radius: 10px;
            padding: 20px;
        }
        
        .btn-outline-primary {
            color: var(--primary);
            border-color: var(--primary);
            border-radius: 8px;
            padding: 20px;
            text-align: center;
            transition: all 0.3s;
            height: 100%;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
        }
        
        .btn-outline-primary:hover {
            background-color: var(--primary);
            border-color: var(--primary);
            color: white;
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(138, 43, 226, 0.3);
        }
        
        .btn-outline-primary i {
            font-size: 2.5rem;
            margin-bottom: 15px;
        }
        
        .btn-outline-primary span {
            font-weight: 600;
            font-size: 1.1rem;
        }
        
        .welcome-text {
            color: var(--primary);
            font-weight: 600;
        }
        
        .footer {
            background-color: var(--darker-bg);
            color: var(--text-muted);
            text-align: center;
            padding: 20px;
            margin-top: auto;
        }

        #excuseLetterModal .modal-content {
            background-color: var(--card-bg);
            border: none;
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.4);
        }

        #excuseLetterModal .modal-header {
            background: linear-gradient(135deg, var(--primary), #6a11cb);
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            padding: 20px;
            border-radius: 12px 12px 0 0;
        }

        #excuseLetterModal .modal-title {
            color: white;
            font-weight: 600;
            letter-spacing: 0.5px;
        }

        #excuseLetterModal .btn-close {
            filter: invert(1) brightness(2);
            opacity: 0.8;
            transition: opacity 0.3s;
        }

        #excuseLetterModal .btn-close:hover {
            opacity: 1;
        }

        #excuseLetterModal .modal-body {
            padding: 25px;
            background-color: var(--card-bg);
        }

        #excuseLetterModal .modal-footer {
            background-color: var(--darker-bg);
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            padding: 20px;
            border-radius: 0 0 12px 12px;
        }

        #excuseLetterModal .form-label {
            color: var(--text);
            font-weight: 500;
            margin-bottom: 8px;
        }

        #excuseLetterModal .form-select,
        #excuseLetterModal .form-control,
        #excuseLetterModal .form-control:focus {
            background-color: var(--darker-bg);
            border: 1px solid rgba(255, 255, 255, 0.15);
            color: var(--text);
            border-radius: 8px;
            padding: 12px 15px;
            transition: all 0.3s;
        }

        #excuseLetterModal .form-select:focus,
        #excuseLetterModal .form-control:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 0.2rem rgba(138, 43, 226, 0.25);
            outline: none;
        }

        #excuseLetterModal .form-select option {
            background-color: var(--darker-bg);
            color: var(--text);
        }

        #excuseLetterModal .form-text {
            color: var(--text-muted);
            font-size: 0.85rem;
            margin-top: 6px;
        }

        #excuseLetterModal textarea.form-control {
            resize: vertical;
            min-height: 120px;
        }

        #excuseLetterModal input[type="file"]::file-selector-button {
            background-color: var(--primary);
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 6px;
            margin-right: 10px;
            transition: background-color 0.3s;
            cursor: pointer;
        }

        #excuseLetterModal input[type="file"]::file-selector-button:hover {
            background-color: var(--primary-hover);
        }

        #excuseLetterModal .btn-secondary {
            background-color: #6c757d;
            border: none;
            border-radius: 8px;
            padding: 10px 20px;
            transition: all 0.3s;
        }

        #excuseLetterModal .btn-secondary:hover {
            background-color: #5a6268;
            transform: translateY(-2px);
        }

        #excuseLetterModal .btn-primary {
            background: linear-gradient(135deg, var(--primary), #6a11cb);
            border: none;
            border-radius: 8px;
            padding: 10px 20px;
            transition: all 0.3s;
        }

        #excuseLetterModal .btn-primary:hover {
            background: linear-gradient(135deg, var(--primary-hover), #7a22db);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(138, 43, 226, 0.3);
        }

        /* Custom placeholder styling */
        #excuseLetterModal .form-control::placeholder {
            color: var(--text-muted);
            opacity: 0.7;
        }

        /* Date picker icon color */
        #excuseLetterModal input[type="date"]::-webkit-calendar-picker-indicator {
            filter: invert(0.8);
            cursor: pointer;
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            #excuseLetterModal .modal-dialog {
                margin: 20px;
            }
            
            #excuseLetterModal .modal-body {
                padding: 20px 15px;
            }
            
            #excuseLetterModal .modal-footer {
                padding: 15px;
            }
        }

        /* Excuse Letters History Card */
        .card-header h5 {
            margin: 0;
            color: white;
            font-weight: 600;
            letter-spacing: 0.5px;
        }

        .card-header {
            background: linear-gradient(135deg, var(--primary), #6a11cb);
            border-radius: 12px 12px 0 0 !important;
            padding: 20px;
        }

        .card-body {
            padding: 25px;
          
        }

        /* Table styling */
        .table-responsive {
            overflow-x: auto;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
            color: var(--text);
            
        }

        .table th, 
        .table td {
            color: var(--text) !important; 
            border-color: rgba(255, 255, 255, 0.1) !important; 
        }

        .table thead {
            background-color: rgba(138, 43, 226, 0.3) !important; 
            color: var(--text) !important;
        }

        .table tbody tr {
            background-color: rgba(30, 30, 30, 0.7) !important; 
        }

        .table-striped tbody tr:nth-of-type(odd) {
            background-color: rgba(138, 43, 226, 0.1) !important; 
        }

        .table tbody tr:hover {
            background-color: rgba(138, 43, 226, 0.2) !important;
        }
        .badge {
            font-weight: 500;
            padding: 5px 10px;
            border-radius: 8px;
        }

        /* No data message */
        .text-muted {
            color: var(--text-muted) !important;
            margin-top: 15px;
            font-style: italic;
            text-align: center;
        }

    </style>
</head>
<body>

<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-dark">
    <div class="container">
        <a class="navbar-brand" href="../student/student_dashboard.php">
            <i class="bi bi-calendar-check me-2"></i>Attendance System
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item">
                    <span class="nav-link welcome-text">
                        <i class="bi bi-person-circle me-1"></i>Welcome, <?php echo $_SESSION['username']; ?>
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

<!-- Dashboard Container -->
<div class="container dashboard-container">

    <!-- Success/Error Messages -->
    <?php if (isset($success_message)): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?php echo $success_message; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if (isset($error_message)): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?php echo $error_message; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="row">
        <div class="col-md-12">
            <div class="card">

                <!-- Card Header -->
                <div class="card-header">
                    <h4><i class="bi bi-speedometer2 me-2"></i>Student Dashboard</h4>
                </div>

                <!-- Card Body -->
                <div class="card-body">

                    <!-- Student Information -->
                    <div class="alert alert-info">
                        <h5><i class="bi bi-info-circle me-2"></i>Student Information</h5>
                        <div class="row mt-3">
                            <div class="col-md-3">
                                <strong>Name:</strong><br>
                                <?php echo $student->first_name . ' ' . $student->last_name; ?>
                            </div>
                            <div class="col-md-3">
                                <strong>Program:</strong><br>
                                <?php
                                if (!empty($studentCourseData['course_code']) && !empty($studentCourseData['course_name'])) {
                                    echo $studentCourseData['course_code'] . ' - ' . $studentCourseData['course_name'];
                                } else {
                                    echo $student->course_program ?? 'Not assigned';
                                }
                                ?>
                            </div>
                            <div class="col-md-3">
                                <strong>Year Level:</strong><br>
                                <?php echo $student->year_level; ?>
                            </div>
                            <div class="col-md-3">
                                <strong>Status:</strong><br>
                                <span class="badge bg-success">Active</span>
                            </div>
                        </div>
                    </div>

                    <!-- Quick Actions -->
                    <div class="row mt-4">
                        <div class="col-md-4 mb-4">
                            <a href="file_attendance.php" class="btn btn-outline-primary">
                                <i class="bi bi-journal-plus"></i>
                                <span>File Attendance</span>
                                <small class="d-block mt-2">Record your daily attendance</small>
                            </a>
                        </div>
                        <div class="col-md-4 mb-4">
                            <a href="attendance_history.php" class="btn btn-outline-primary">
                                <i class="bi bi-list-check"></i>
                                <span>View History</span>
                                <small class="d-block mt-2">Check your attendance records</small>
                            </a>
                        </div>
                        <div class="col-md-4 mb-4">
                            <a href="#" class="btn btn-outline-primary d-flex flex-column align-items-center justify-content-center p-3 h-100 text-decoration-none" data-bs-toggle="modal" data-bs-target="#excuseLetterModal">
                                <i class="bi bi-envelope-paper fs-1 mb-2"></i>
                                <span class="fw-bold">Submit Excuse</span>
                                <small class="d-block mt-2">Submit excuse letter for absence</small>
                            </a>
                        </div>
                    </div>

                    <!-- Excuse Letters History -->
                    <div class="row mt-4">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    <h5><i class="bi bi-clock-history me-2"></i>Excuse Letter History</h5>
                                </div>
                                <div class="card-body">
                                    <?php if ($excuseLetters->rowCount() > 0): ?>
                                        <div class="table-responsive">
                                            <table class="table table-striped bg">
                                                <thead>
                                                    <tr>
                                                        <th>Course</th>
                                                        <th>Absence Date</th>
                                                        <th>Submission Date</th>
                                                        <th>Reason</th>
                                                        <th>Status</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php while ($row = $excuseLetters->fetch(PDO::FETCH_ASSOC)): ?>
                                                        <tr>
                                                            <td><?php echo $row['course_code']; ?></td>
                                                            <td><?php echo date('M j, Y', strtotime($row['absence_date'])); ?></td>
                                                            <td><?php echo date('M j, Y g:i A', strtotime($row['submission_date'])); ?></td>
                                                            <td><?php echo substr($row['reason'], 0, 50) . (strlen($row['reason']) > 50 ? '...' : ''); ?></td>
                                                            <td>
                                                                <?php
                                                                $status_badge = [
                                                                    'pending' => 'warning',
                                                                    'approved' => 'success',
                                                                    'rejected' => 'danger'
                                                                ];
                                                                ?>
                                                                <span class="badge bg-<?php echo $status_badge[$row['status']]; ?>">
                                                                    <?php echo ucfirst($row['status']); ?>
                                                                </span>
                                                            </td>
                                                        </tr>
                                                    <?php endwhile; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    <?php else: ?>
                                        <p class="text-center text-muted">No excuse letters submitted yet.</p>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>

                </div> <!-- End Card Body -->
            </div>
        </div>
    </div>
</div>

<!-- Excuse Letter Modal -->
<div class="modal fade" id="excuseLetterModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Submit Excuse Letter</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" enctype="multipart/form-data">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Program</label>
                        <div class="form-control bg">
                            <?php
                            if (!empty($student->course_program)) {
                                echo htmlspecialchars($student->course_program);
                                if (!empty($studentCourseData['course_name'])) {
                                    echo " - " . htmlspecialchars($studentCourseData['course_name']);
                                }
                            } else {
                                echo "No course registered. Please select a course below.";
                            }
                            ?>
                        </div>
                        <input type="hidden" name="course_id" value="<?php echo $studentCourseData['id'] ?? ''; ?>">
                    </div>

                    <div class="mb-3">
                        <label for="absence_date" class="form-label">Absence Date</label>
                        <input type="date" class="form-control" id="absence_date" name="absence_date" required
                               max="<?php echo date('Y-m-d'); ?>">
                    </div>

                    <div class="mb-3">
                        <label for="reason" class="form-label">Reason for Absence</label>
                        <textarea class="form-control" id="reason" name="reason" rows="4" required
                                  placeholder="Please provide a detailed reason for your absence"></textarea>
                    </div>

                    <div class="mb-3">
                        <label for="supporting_document" class="form-label">Supporting Document (Optional)</label>
                        <input type="file" class="form-control" id="supporting_document" name="supporting_document"
                               accept=".pdf,.doc,.docx,.jpg,.jpeg,.png">
                        <div class="form-text">Upload any supporting documents (medical certificate, etc.)</div>
                    </div>

                    <button type="submit" name="submit_excuse" class="btn btn-primary">Submit Excuse</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
