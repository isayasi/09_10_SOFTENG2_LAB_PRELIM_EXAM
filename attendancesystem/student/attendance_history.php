<?php
// Configuration and session setup
session_start();
if (!isset($_SESSION['logged_in']) || $_SESSION['role'] !== 'student') {
    header("Location: ../login.php");
    exit();
}

// Include dependencies
require_once '../config/database.php';
require_once '../objects/student.php';
require_once '../objects/attendance.php';

// Initialize database connection
$database = new Database();
$db = $database->getConnection();

// Initialize objects
$student = new Student($db);
$attendance = new Attendance($db);

// Get student data
$student->getStudentByUserId($_SESSION['user_id']);
$studentCourseData = $student->getStudentCourse();

// Get attendance records
$attendance_records = $attendance->getAttendanceByStudentId($student->id);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Attendance History | Attendance System</title>
    
    <!-- External CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.3/font/bootstrap-icons.css">
    
    <!-- Internal CSS -->
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
        
        .alert-info {
            background-color: rgba(138, 43, 226, 0.1);
            border: 1px solid rgba(138, 43, 226, 0.3);
            color: var(--text);
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 25px;
        }
        
        .table {
            color: var(--text);
            border-collapse: separate;
            border-spacing: 0;
            width: 100%;
        }
        
        .table th {
            background-color: rgba(138, 43, 226, 0.2);
            border-bottom: 2px solid rgba(138, 43, 226, 0.3);
            padding: 15px;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.85rem;
            letter-spacing: 0.5px;
        }
        
        .table td {
            padding: 15px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            vertical-align: middle;
        }
        
        .table tbody tr {
            transition: background-color 0.3s;
        }
        
        .table tbody tr:hover {
            background-color: rgba(138, 43, 226, 0.05);
        }
        
        .badge {
            padding: 8px 12px;
            border-radius: 20px;
            font-weight: 600;
            font-size: 0.8rem;
        }
        
        .bg-success {
            background-color: rgba(40, 167, 69, 0.2) !important;
            color: #6bff8f;
            border: 1px solid rgba(40, 167, 69, 0.3);
        }
        
        .bg-warning {
            background-color: rgba(255, 193, 7, 0.2) !important;
            color: #ffd54f;
            border: 1px solid rgba(255, 193, 7, 0.3);
        }
        
        .text-center {
            color: var(--text-muted);
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
    </style>
</head>
<body>
    <!-- Navigation Bar -->
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
                            <i class="bi bi-person-circle me-1"></i>
                            Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>
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
        <a href="student_dashboard.php" class="back-button">
            <i class="bi bi-arrow-left me-2"></i>Back to Dashboard
        </a>
        
        <div class="card">
            <div class="card-header">
                <h4><i class="bi bi-list-check me-2"></i>Attendance History</h4>
            </div>
            <div class="card-body">
                <!-- Student Information -->
                <div class="alert alert-info">
                    <h5><i class="bi bi-info-circle me-2"></i>Student Information</h5>
                    <div class="row mt-3">
                        <div class="col-md-4">
                            <strong>Name:</strong><br>
                            <?php echo htmlspecialchars($student->first_name . ' ' . $student->last_name); ?>
                        </div>
                        <div class="col-md-4">
                            <strong>Program:</strong><br>
                            <?php 
                            if (!empty($studentCourseData['course_code']) && !empty($studentCourseData['course_name'])) {
                                echo htmlspecialchars($studentCourseData['course_code'] . ' - ' . $studentCourseData['course_name']);
                            } else {
                                echo htmlspecialchars($student->course_program ?? 'Not assigned');
                            }
                            ?>
                        </div>
                        <div class="col-md-4">
                            <strong>Year Level:</strong><br>
                            <?php echo htmlspecialchars($student->year_level); ?>
                        </div>
                    </div>
                </div>
                
                <!-- Attendance Records Table -->
                <div class="table-responsive">
                    <?php renderAttendanceTable($attendance_records); ?>
                </div>
            </div>
        </div>
    </div>

    <!-- External JavaScript -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php
/**
 * Render attendance table
 */
function renderAttendanceTable($attendance_records) {
    ?>
    <table class="table">
        <thead>
            <tr>
                <th>Date</th>
                <th>Course</th>
                <th>Time In</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            <?php
            if ($attendance_records->rowCount() > 0) {
                while ($row = $attendance_records->fetch(PDO::FETCH_ASSOC)) {
                    echo "<tr>";
                    echo "<td>" . date('M j, Y', strtotime($row['date'])) . "</td>";
                    echo "<td><strong>" . htmlspecialchars($row['course_code']) . "</strong> - " . 
                         htmlspecialchars($row['course_name']) . "</td>";
                    echo "<td>" . date('h:i A', strtotime($row['time_in'])) . "</td>";
                    echo "<td>";
                    if ($row['status'] == 'late') {
                        echo "<span class='badge bg-warning'><i class='bi bi-clock-history me-1'></i>Late</span>";
                    } else {
                        echo "<span class='badge bg-success'><i class='bi bi-check-circle me-1'></i>Present</span>";
                    }
                    echo "</td>";
                    echo "</tr>";
                }
            } else {
                echo "<tr><td colspan='4' class='text-center py-4'>No attendance records found.</td></tr>";
            }
            ?>
        </tbody>
    </table>
    <?php
}