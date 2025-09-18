<?php
// Configuration and session setup
session_start();
if (!isset($_SESSION['logged_in']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Include dependencies
require_once '../config/database.php';
require_once '../objects/user.php';
require_once '../objects/student.php';
require_once '../objects/course.php';
require_once '../objects/attendance.php';
require_once '../objects/excuse_letter.php';

// Initialize database connection
$database = new Database();
$db = $database->getConnection();

// Initialize objects
$student = new Student($db);
$course = new Course($db);
$excuseLetter = new ExcuseLetter($db);

// Get statistics
$totalStudents = $student->getAllStudents()->rowCount();
$totalCourses = $course->read()->rowCount();

// Get pending excuse letters count
$pendingExcuses = $excuseLetter->getByStatusAndProgram('pending');
$pendingCount = $pendingExcuses->rowCount();

// Get unique programs for filter
$programs_query = $db->query("SELECT DISTINCT course_program FROM students");
$programs = $programs_query->fetchAll(PDO::FETCH_COLUMN);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard | Attendance System</title>
    
    <!-- External CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.3/font/bootstrap-icons.css">
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
        
        .welcome-text {
            color: var(--primary);
            font-weight: 600;
        }
        
        /* Statistics Cards */
        .stat-card {
            background: linear-gradient(135deg, rgba(0, 204, 102, 0.1), rgba(0, 136, 68, 0.1));
            border: 1px solid rgba(0, 204, 102, 0.2);
            border-radius: 12px;
            transition: all 0.3s ease;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0, 204, 102, 0.2);
        }
        
        .stat-icon {
            font-size: 2.5rem;
            color: #00cc66;
            margin-bottom: 15px;
        }
        
        .stat-number {
            font-size: 2.5rem;
            font-weight: 700;
            color: #00cc66;
            margin: 10px 0;
        }
        
        .stat-label {
            color: var(--text-muted);
            margin-bottom: 15px;
        }
        
        /* Quick Actions */
        .quick-actions-container {
            display: flex;
            justify-content: center;
            flex-wrap: wrap;
            gap: 20px;
            margin-top: 30px;
        }
        
        .action-card {
            background: var(--card-bg);
            border: 1px solid rgba(0, 204, 102, 0.2);
            border-radius: 12px;
            padding: 25px;
            text-align: center;
            width: 250px;
            transition: all 0.3s ease;
            display: flex;
            flex-direction: column;
            align-items: center;
            text-decoration: none;
        }
        
        .action-card:hover {
            background: linear-gradient(135deg, rgba(0, 204, 102, 0.1), rgba(0, 136, 68, 0.1));
            border-color: #00cc66;
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0, 204, 102, 0.2);
            text-decoration: none; 
        }
        
        .action-icon {
            font-size: 2.5rem;
            color: #00cc66;
            margin-bottom: 15px;
        }
        
        .action-title {
            color: var(--text);
            font-weight: 600;
            margin-bottom: 8px;
            font-size: 1.1rem;
        }
        
        .action-description {
            color: var(--text-muted);
            font-size: 0.9rem;
            margin: 0;
        }
        
        .section-title {
            color: var(--text);
            font-weight: 600;
            margin-bottom: 25px;
            text-align: center;
            font-size: 1.3rem;
        }
        
        .stat-card .btn {
            text-decoration: none;
        }

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

    .welcome-text {
        color: var(--primary);
        font-weight: 600;
    }

    /* Statistics Cards */
    .stat-card {
        background: linear-gradient(135deg, rgba(0, 204, 102, 0.1), rgba(0, 136, 68, 0.1));
        border: 1px solid rgba(0, 204, 102, 0.2);
        border-radius: 12px;
        transition: all 0.3s ease;
    }

    .stat-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 25px rgba(0, 204, 102, 0.2);
    }

    .stat-icon {
        font-size: 2.5rem;
        color: #00cc66;
        margin-bottom: 15px;
    }

    .stat-number {
        font-size: 2.5rem;
        font-weight: 700;
        color: #00cc66;
        margin: 10px 0;
    }

    .stat-label {
        color: var(--text-muted);
        margin-bottom: 15px;
    }

    /* Quick Actions */
    .quick-actions-container {
        display: flex;
        justify-content: center;
        flex-wrap: wrap;
        gap: 20px;
        margin-top: 30px;
    }

    .action-card {
        background: var(--card-bg);
        border: 1px solid rgba(0, 204, 102, 0.2);
        border-radius: 12px;
        padding: 25px;
        text-align: center;
        width: 250px;
        transition: all 0.3s ease;
        display: flex;
        flex-direction: column;
        align-items: center;
        text-decoration: none;
    }

    .action-card:hover {
        background: linear-gradient(135deg, rgba(0, 204, 102, 0.1), rgba(0, 136, 68, 0.1));
        border-color: #00cc66;
        transform: translateY(-5px);
        box-shadow: 0 10px 25px rgba(0, 204, 102, 0.2);
        text-decoration: none; 
    }

    .action-icon {
        font-size: 2.5rem;
        color: #00cc66;
        margin-bottom: 15px;
    }

    .action-title {
        color: var(--text);
        font-weight: 600;
        margin-bottom: 8px;
        font-size: 1.1rem;
    }

    .action-description {
        color: var(--text-muted);
        font-size: 0.9rem;
        margin: 0;
    }

    .section-title {
        color: var(--text);
        font-weight: 600;
        margin-bottom: 25px;
        text-align: center;
        font-size: 1.3rem;
    }

    .stat-card .btn {
        text-decoration: none;
    }

    /* Table Styling for Admin Dashboard */
    .table-responsive {
        overflow-x: auto;
        border-radius: 8px;
    }

    .table {
        width: 100%;
        border-collapse: collapse;
        color: var(--text) !important;
        background-color: var(--card-bg);
    }

    .table th, 
    .table td {
        padding: 12px 15px;
        text-align: left;
        vertical-align: middle;
        border-color: rgba(255, 255, 255, 0.1) !important;
        color: var(--text) !important;
    }

    .table thead {
        background: linear-gradient(135deg, rgba(0, 204, 102, 0.3), rgba(0, 136, 68, 0.3)) !important;
        color: var(--text) !important;
    }

    .table thead th {
        border-bottom: 2px solid rgba(0, 204, 102, 0.5);
        font-weight: 600;
        letter-spacing: 0.5px;
    }

    .table tbody tr {
        background-color: rgba(30, 30, 30, 0.7);
        transition: background 0.3s;
    }

    .table tbody tr:hover {
        background-color: rgba(0, 204, 102, 0.1) !important;
    }

    .table-striped tbody tr:nth-of-type(odd) {
        background-color: rgba(255, 255, 255, 0.03);
    }

    .table-striped tbody tr:nth-of-type(odd):hover {
        background-color: rgba(0, 204, 102, 0.15) !important;
    }

    /* Badge Styling */
    .badge {
        font-weight: 500;
        padding: 6px 12px;
        border-radius: 20px;
        font-size: 0.85rem;
    }

    .status-badge {
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    /* Button Styling in Table */
    .table .btn {
        margin: 2px;
        font-size: 0.8rem;
        padding: 5px 10px;
    }

    /* Filter Section Styling */
    .filter-section {
        background-color: var(--card-bg);
        padding: 20px;
        border-radius: 12px;
        margin-bottom: 20px;
        box-shadow: 0 3px 10px rgba(0, 0, 0, 0.1);
    }

    .filter-section .form-label {
        color: var(--text);
        font-weight: 500;
    }

    .filter-section .form-select,
    .filter-section .form-control {
        background-color: var(--darker-bg);
        border: 1px solid rgba(255, 255, 255, 0.15);
        color: var(--text);
        border-radius: 8px;
    }

    .filter-section .form-select:focus,
    .filter-section .form-control:focus {
        border-color: var(--primary);
        box-shadow: 0 0 0 0.2rem rgba(0, 204, 102, 0.25);
    }

    /* Modal Styling */
    .modal-content {
        background-color: var(--card-bg);
        border: none;
        border-radius: 12px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.4);
    }

    .modal-header {
        background: linear-gradient(135deg, var(--primary), #008844);
        border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        padding: 20px;
        border-radius: 12px 12px 0 0;
    }

    .modal-title {
        color: white;
        font-weight: 600;
        letter-spacing: 0.5px;
    }

    .modal-body {
        padding: 25px;
        background-color: var(--card-bg);
    }

    .modal-footer {
        background-color: var(--darker-bg);
        border-top: 1px solid rgba(255, 255, 255, 0.1);
        padding: 20px;
        border-radius: 0 0 12px 12px;
    }

    /* No data message */
    .text-muted {
        color: var(--text-muted) !important;
        margin-top: 15px;
        font-style: italic;
        text-align: center;
    }

    /* Responsive adjustments */
    @media (max-width: 768px) {
        .table-responsive {
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .table thead {
            display: none;
        }
        
        .table, .table tbody, .table tr, .table td {
            display: block;
            width: 100%;
        }
        
        .table tr {
            margin-bottom: 15px;
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 8px;
            padding: 10px;
        }
        
        .table td {
            text-align: right;
            padding-left: 50%;
            position: relative;
            border: none;
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
        }
        
        .table td:before {
            content: attr(data-label);
            position: absolute;
            left: 15px;
            width: 45%;
            padding-right: 10px;
            text-align: left;
            font-weight: bold;
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
        <div class="row justify-content-center">
            <div class="col-md-10">
                <div class="card">
                    <div class="card-header">
                        <h4><i class="bi bi-speedometer2 me-2"></i>Admin Dashboard</h4>
                    </div>
                    
                    <div class="card-body">
                        <!-- Statistics Cards -->
                        <div class="row justify-content-center mb-5">
                            <!-- Students Card -->
                            <div class="col-md-4 mb-4">
                                <div class="card stat-card">
                                    <div class="card-body text-center">
                                        <div class="stat-icon">
                                            <i class="bi bi-people"></i>
                                        </div>
                                        <h3 class="stat-number"><?php echo $totalStudents; ?></h3>
                                        <p class="stat-label">Total Students</p>
                                        <a href="#" class="btn btn-sm btn-primary">View Students</a>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Courses Card -->
                            <div class="col-md-4 mb-4">
                                <div class="card stat-card">
                                    <div class="card-body text-center">
                                        <div class="stat-icon">
                                            <i class="bi bi-journal-bookmark"></i>
                                        </div>
                                        <h3 class="stat-number"><?php echo $totalCourses; ?></h3>
                                        <p class="stat-label">Total Courses</p>
                                        <a href="manage_courses.php" class="btn btn-sm btn-primary">Manage Courses</a>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Pending Excuses Card -->
                            <div class="col-md-4 mb-4">
                                <div class="card stat-card">
                                    <div class="card-body text-center">
                                        <div class="stat-icon">
                                            <i class="bi bi-envelope-exclamation"></i>
                                        </div>
                                        <h3 class="stat-number"><?php echo $pendingCount; ?></h3>
                                        <p class="stat-label">Pending Excuses</p>
                                        <a href="manage_excuses.php" class="btn btn-sm btn-primary">Review Excuses</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Quick Actions -->
                        <div class="row">
                            <div class="col-12">
                                <h5 class="section-title"><i class="bi bi-lightning me-2"></i>Quick Actions</h5>
                                <div class="quick-actions-container">
                                    <!-- Manage Courses -->
                                    <a href="manage_courses.php" class="action-card">
                                        <div class="action-icon">
                                            <i class="bi bi-journal-plus"></i>
                                        </div>
                                        <div class="action-text">
                                            <div class="action-title">Manage Courses</div>
                                            <p class="action-description">Add, edit, or remove courses</p>
                                        </div>
                                    </a>
                                    
                                    <!-- View Attendance -->
                                    <a href="view_attendance.php" class="action-card">
                                        <div class="action-icon">
                                            <i class="bi bi-list-check"></i>
                                        </div>
                                        <div class="action-text">
                                            <div class="action-title">View Attendance</div>
                                            <p class="action-description">Check attendance records</p>
                                        </div>
                                    </a>
                                    
                                    <!-- Manage Excuses -->
                                    <a href="manage_excuses.php" class="action-card">
                                        <div class="action-icon">
                                            <i class="bi bi-envelope-check"></i>
                                        </div>
                                        <div class="action-text">
                                            <div class="action-title">Manage Excuses</div>
                                            <p class="action-description">Review excuse letters</p>
                                        </div>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- External JavaScript -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>