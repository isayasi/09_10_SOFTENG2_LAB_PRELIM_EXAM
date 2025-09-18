<?php
include_once '../config/Database.php';
include_once '../objects/Student.php';
include_once '../objects/Course.php';
include_once '../objects/Attendance.php';

session_start();
if(!isset($_SESSION['logged_in']) || $_SESSION['role'] !== 'student'){
    header("Location: ../login.php");
    exit();
}

$database = new Database();
$db = $database->getConnection();

$student = new Student($db);
$course = new Course($db);
$attendance = new Attendance($db);

$student->getStudentByUserId($_SESSION['user_id']);

$message = '';

$course_program = $student->getCourseProgram();

// Always try to find the course by program code first
$course_stmt = $db->prepare("SELECT id, course_code, course_name FROM courses WHERE course_code = ?");
$course_stmt->execute([$course_program]);
$course_data = $course_stmt->fetch(PDO::FETCH_ASSOC);

if ($course_data) {
    // Found course by program code
    $course_name = $course_data['course_name'];
    $course_id = $course_data['id'];
} else {
    // If not found by code, try to find by name match
    $course_stmt = $db->prepare("SELECT id, course_code, course_name FROM courses WHERE course_name LIKE ?");
    $searchTerm = '%' . $course_program . '%';
    $course_stmt->execute([$searchTerm]);
    $course_data = $course_stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($course_data) {
        $course_name = $course_data['course_name'];
        $course_id = $course_data['id'];
    } else {
        // Still not found, use fallback
        $course_name = $course_program;
        $course_id = null;
        
        // Log the error for debugging
        error_log("Course not found for program: " . $course_program);
    }
}

error_log("Course Program: " . $course_program);
error_log("Course Name: " . $course_name);
error_log("Course ID: " . $course_id);

if($_POST && $course_id){
    $current_time = date('H:i:s');
    $status = ($current_time > '08:30:00') ? 'late' : 'present';
    
    $attendance->student_id = $student->id;
    $attendance->course_id = $course_id;
    $attendance->date = date('Y-m-d');
    $attendance->time_in = $current_time;
    $attendance->status = $status;
    
    if($attendance->checkIfAlreadyAttended($student->id, $course_id, date('Y-m-d'))){
        $message = '<div class="alert alert-warning">You have already filed your attendance for this course today.</div>';
    } else {
        if($attendance->create()){
            $message = '<div class="alert alert-success">Attendance filed successfully. Status: ' . $status . '</div>';
        } else {
            $message = '<div class="alert alert-danger">Unable to file attendance.</div>';
        }
    }
} elseif ($_POST && !$course_id) {
    $message = '<div class="alert alert-danger">Cannot file attendance: Invalid course information.</div>';
}

// Get student data with course information
$student->getStudentByUserId($_SESSION['user_id']);

// Get student's course data
$studentCourseData = $student->getStudentCourse();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>File Attendance | Attendance System</title>
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
        
        .alert {
            border-radius: 10px;
            padding: 15px 20px;
            margin-bottom: 20px;
            border: 1px solid transparent;
        }
        
        .alert-info {
            background-color: rgba(138, 43, 226, 0.1);
            border-color: rgba(138, 43, 226, 0.3);
            color: var(--text);
        }
        
        .alert-success {
            background-color: rgba(40, 167, 69, 0.1);
            border-color: rgba(40, 167, 69, 0.3);
            color: #6bff8f;
        }
        
        .alert-warning {
            background-color: rgba(255, 193, 7, 0.1);
            border-color: rgba(255, 193, 7, 0.3);
            color: #ffd54f;
        }
        
        .alert-danger {
            background-color: rgba(220, 53, 69, 0.1);
            border-color: rgba(220, 53, 69, 0.3);
            color: #ff6b6b;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-label {
            font-weight: 600;
            margin-bottom: 10px;
            color: var(--text);
        }
        
        .course-display {
            background-color: #2a2a2a;
            border: 1px solid #3a3a3a;
            color: var(--text);
            border-radius: 8px;
            padding: 12px 15px;
            font-weight: 500;
        }
        
        .btn-primary {
            background-color: var(--primary);
            border-color: var(--primary);
            border-radius: 8px;
            padding: 12px 25px;
            font-weight: 600;
            letter-spacing: 0.5px;
            transition: all 0.3s;
        }
        
        .btn-primary:hover {
            background-color: var(--primary-hover);
            border-color: var(--primary-hover);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(138, 43, 226, 0.3);
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
        
        .back-button {
            background-color: rgba(138, 43, 226, 0.1);
            color: var(--primary);
            border: 1px solid rgba(138, 43, 226, 0.3);
            padding: 10px 20px;
            border-radius: 8px;
            text-decoration: none;
            transition: all 0.3s;
            display: inline-flex;
            align-items: center;
            margin-bottom: 20px;
        }
        
        .back-button:hover {
            background-color: var(--primary);
            color: white;
            transform: translateY(-2px);
        }
        
        .student-info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-top: 10px;
        }
        
        .info-item {
            background: rgba(138, 43, 226, 0.05);
            padding: 10px;
            border-radius: 8px;
            border-left: 3px solid var(--primary);
        }
        
        .info-label {
            font-size: 0.85rem;
            color: var(--text-muted);
            margin-bottom: 5px;
        }
        
        .info-value {
            font-weight: 600;
            color: var(--text);
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container">
            <a class="navbar-brand" href="student_dashboard.php">
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

    <div class="container dashboard-container">
        <a href="student_dashboard.php" class="btn btn-primary mb-4">
            <i class="bi bi-arrow-left me-2"></i>Back to Dashboard
        </a>
        
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h4><i class="bi bi-journal-plus me-2"></i>File Attendance</h4>
                    </div>
                    <div class="card-body">
                        <?php echo $message; ?>
                        
                        <div class="alert alert-info">
                            <h5><i class="bi bi-info-circle me-2"></i>Student Information</h5>
                            <div class="student-info-grid">
                                <div class="info-item">
                                    <div class="info-label">Name</div>
                                    <div class="info-value"><?php echo $student->first_name . ' ' . $student->last_name; ?></div>
                                </div>
                                <div class="info-item">
                                    <div class="info-label">Program</div>
                                    <div class="info-value">
                                        <?php 
                                                if (!empty($studentCourseData['course_code']) && !empty($studentCourseData['course_name'])) {
                                                    echo $studentCourseData['course_code'] . ' - ' . $studentCourseData['course_name'];
                                                } else {
                                                    echo $student->course_program ?? 'Not assigned';
                                                }
                                        ?>
                                    </div>
                                </div>
                                <div class="info-item">
                                    <div class="info-label">Year Level</div>
                                    <div class="info-value"><?php echo $student->year_level; ?></div>
                                </div>
                                <div class="info-item">
                                    <div class="info-label">Date</div>
                                    <div class="info-value"><?php echo date('F j, Y'); ?></div>
                                </div>
                                <div class="info-item">
                                    <div class="info-label">Time</div>
                                    <div class="info-value"><?php echo date('h:i A'); ?></div>
                                </div>
                            </div>
                        </div>
                        
                        <?php if ($course_program): ?>
                        <form method="post" action="">
                            <div class="form-group">
                                <label class="form-label">
                                    <i class="bi bi-journal-text me-1"></i>Course
                                </label>
                                <div class="course-display">
                                    <?php 
                                    if (!empty($studentCourseData['course_code']) && !empty($studentCourseData['course_name'])) {
                                        echo $studentCourseData['course_code'] . ' - ' . $studentCourseData['course_name'];
                                    } else {
                                        echo $student->course_program ?? 'Not assigned';
                                    }
                                    ?>
                                </div>
                                <input type="hidden" name="course_id" value="<?php echo $course_id; ?>">
                            </div>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-circle me-1"></i>File Attendance
                            </button>
                        </form>
                        <?php else: ?>
                        <div class="alert alert-danger">
                            <i class="bi bi-exclamation-triangle me-2"></i>
                            Cannot file attendance: Course information is missing or invalid.
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>