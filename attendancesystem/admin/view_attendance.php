<?php
// Configuration and session setup
session_start();
if (!isset($_SESSION['logged_in']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Include dependencies
require_once '../config/Database.php';
require_once '../objects/Attendance.php';
require_once '../objects/Student.php';
require_once '../objects/Course.php';

// Initialize database connection
$database = new Database();
$db = $database->getConnection();

// Initialize objects
$attendance = new Attendance($db);
$student = new Student($db);
$course = new Course($db);

// Get attendance records with optional filtering
$attendance_records = getFilteredAttendance($attendance, $_GET);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Attendance | Attendance System</title>
    
    <!-- External CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.3/font/bootstrap-icons.css">
    
    <!-- Internal CSS -->
    <style>
        :root {
            --dark-bg: #121212;
            --darker-bg: #0a0a0a;
            --card-bg: #1e1e1e;
            --primary: #00cc66;
            --primary-hover: #00dd77;
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
            background: linear-gradient(135deg, var(--primary), #008844);
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
            border-radius: 8px;
            padding: 12px 15px;
            margin-bottom: 20px;
            border: 1px solid transparent;
        }
        
        .alert-info {
            background-color: rgba(0, 204, 102, 0.1);
            border-color: rgba(0, 204, 102, 0.3);
            color: var(--text);
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-label {
            font-weight: 600;
            margin-bottom: 10px;
            color: var(--text);
        }
        
        .form-control {
            background-color: #2a2a2a;
            border: 1px solid #3a3a3a;
            color: var(--text);
            border-radius: 8px;
            padding: 12px 15px;
            transition: all 0.3s;
        }
        
        .form-control:focus {
            background-color: #2a2a2a;
            border-color: var(--primary);
            color: var(--text);
            box-shadow: 0 0 0 0.25rem rgba(0, 204, 102, 0.25);
        }
        
        .form-select {
            background-color: #2a2a2a;
            border: 1px solid #3a3a3a;
            color: var(--text);
            border-radius: 8px;
            padding: 12px 15px;
        }
        
        .form-select:focus {
            background-color: #2a2a2a;
            border-color: var(--primary);
            color: var(--text);
            box-shadow: 0 0 0 0.25rem rgba(0, 204, 102, 0.25);
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
            box-shadow: 0 5px 15px rgba(0, 204, 102, 0.3);
        }
        
        .table {
            color: var(--text);
            border-collapse: separate;
            border-spacing: 0;
            width: 100%;
        }
        
        .table th {
            background-color: rgba(0, 204, 102, 0.2);
            border-bottom: 2px solid rgba(0, 204, 102, 0.3);
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
            background-color: rgba(0, 204, 102, 0.05);
        }
        
        .badge {
            padding: 8px 12px;
            border-radius: 20px;
            font-weight: 600;
            font-size: 0.8rem;
        }
        
        .bg-success {
            background-color: rgba(0, 204, 102, 0.2) !important;
            color: #6bff8f;
            border: 1px solid rgba(0, 204, 102, 0.3);
        }
        
        .bg-warning {
            background-color: rgba(255, 193, 7, 0.2) !important;
            color: #ffd54f;
            border: 1px solid rgba(255, 193, 7, 0.3);
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
            background-color: rgba(0, 204, 102, 0.1);
            color: var(--primary);
            border: 1px solid rgba(0, 204, 102, 0.3);
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
        
        .filter-section {
            background: rgba(0, 204, 102, 0.05);
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 25px;
            border-left: 4px solid var(--primary);
        }
    </style>
</head>
<body>
    <!-- Navigation Bar -->
    <nav class="navbar navbar-expand-lg navbar-dark">
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
                        <span class="nav-link welcome-text">
                            <i class="bi bi-person-circle me-1"></i>
                            Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?> (Admin)
                        </span>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="logout.php">
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
        
        <div class="card">
            <div class="card-header">
                <h4><i class="bi bi-list-check me-2"></i>View Attendance Records</h4>
            </div>
            <div class="card-body">
                <!-- Filter Section -->
                <div class="filter-section">
                    <h5><i class="bi bi-funnel me-2"></i>Filter Options</h5>
                    <?php renderFilterForm($course, $_GET); ?>
                </div>
                
                <!-- Attendance Records Table -->
                <?php renderAttendanceTable($attendance_records); ?>
            </div>
        </div>
    </div>

    <!-- External JavaScript -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php
/**
 * Get filtered attendance records
 */
function getFilteredAttendance($attendance, $getParams) {
    // Apply filters if provided
    $course_filter = isset($getParams['course_filter']) ? htmlspecialchars($getParams['course_filter']) : '';
    $date_filter = isset($getParams['date_filter']) ? htmlspecialchars($getParams['date_filter']) : '';
    
    // You would need to modify your Attendance class to accept filters
    // For now, we'll use getAllAttendance() as in the original code
    return $attendance->getAllAttendance();
}

/**
 * Render filter form
 */
function renderFilterForm($course, $getParams) {
    $course_filter = isset($getParams['course_filter']) ? htmlspecialchars($getParams['course_filter']) : '';
    $date_filter = isset($getParams['date_filter']) ? htmlspecialchars($getParams['date_filter']) : '';
    ?>
    <form method="get" action="" class="row g-3 mt-3">
        <div class="col-md-5">
            <label for="course_filter" class="form-label">Course/Program</label>
            <select class="form-select" id="course_filter" name="course_filter">
                <option value="">All Courses</option>
                <?php
                $courses_list = $course->read();
                while ($course_row = $courses_list->fetch(PDO::FETCH_ASSOC)) {
                    $selected = ($course_filter == $course_row['id']) ? 'selected' : '';
                    echo "<option value='" . htmlspecialchars($course_row['id']) . "' $selected>" . 
                         htmlspecialchars($course_row['course_name']) . "</option>";
                }
                ?>
            </select>
        </div>
        <div class="col-md-5">
            <label for="date_filter" class="form-label">Date</label>
            <input type="date" class="form-control" id="date_filter" name="date_filter" 
                   value="<?php echo $date_filter; ?>">
        </div>
        <div class="col-md-2 d-flex align-items-end">
            <button type="submit" class="btn btn-primary w-100">Apply Filter</button>
        </div>
    </form>
    <?php
}

/**
 * Render attendance table
 */
function renderAttendanceTable($attendance_records) {
    if ($attendance_records && $attendance_records->rowCount() > 0) {
        ?>
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Student Name</th>
                        <th>Course</th>
                        <th>Date</th>
                        <th>Time In</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    while ($row = $attendance_records->fetch(PDO::FETCH_ASSOC)) {
                        echo "<tr>";
                        echo "<td>" . htmlspecialchars($row['first_name'] . ' ' . $row['last_name']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['course_name']) . "</td>";
                        echo "<td>" . date('M j, Y', strtotime($row['date'])) . "</td>";
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
                    ?>
                </tbody>
            </table>
        </div>
        <?php
    } else {
        echo '<div class="alert alert-info">
            <i class="bi bi-info-circle me-2"></i>No attendance records found.
        </div>';
    }
}