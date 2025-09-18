<?php
// Configuration and session setup
session_start();
if (!isset($_SESSION['logged_in']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Include dependencies
require_once '../config/Database.php';
require_once '../objects/Course.php';

// Initialize database connection
$database = new Database();
$db = $database->getConnection();

// Initialize course object
$course = new Course($db);

// Process form submissions
$message = processCourseOperations($course, $_POST, $_GET, $_SESSION['user_id']);

// Get all courses
$courses = $course->read();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Courses | Attendance System</title>
    
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
        
        .alert-success {
            background-color: rgba(0, 204, 102, 0.1);
            border-color: rgba(0, 204, 102, 0.3);
            color: #6bff8f;
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
        
        .btn-secondary {
            background-color: #6c757d;
            border-color: #6c757d;
            border-radius: 8px;
            padding: 12px 25px;
            font-weight: 600;
            letter-spacing: 0.5px;
            transition: all 0.3s;
        }
        
        .btn-secondary:hover {
            background-color: #5a6268;
            border-color: #545b62;
            transform: translateY(-2px);
        }
        
        .btn-sm {
            padding: 6px 12px;
            font-size: 0.875rem;
        }
        
        .btn-danger {
            margin-top: 15px;
            background-color: #dc3545;
            border-color: #dc3545;
            border-radius: 6px;
            transition: all 0.3s;
        }
        
        .btn-danger:hover {
            background-color: #bb2d3b;
            border-color: #b02a37;
            transform: translateY(-2px);
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
        <a href="admin_dashboard.php" class="btn btn-primary mb-4">
            <i class="bi bi-arrow-left me-2"></i>Back to Dashboard
        </a>
        
        <div class="row">
            <!-- Course Form -->
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <h4>
                            <i class="bi bi-<?php echo isset($_GET['edit']) ? 'pencil' : 'plus'; ?> me-2"></i>
                            <?php echo isset($_GET['edit']) ? 'Edit Course' : 'Add New Course'; ?>
                        </h4>
                    </div>
                    <div class="card-body">
                        <?php renderCourseForm($course, $db, $_GET); ?>
                    </div>
                </div>
            </div>
            
            <!-- Course List -->
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h4><i class="bi bi-list-check me-2"></i>Course List</h4>
                    </div>
                    <div class="card-body">
                        <?php echo $message; ?>
                        <div class="table-responsive">
                            <?php renderCourseTable($courses); ?>
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

<?php
/**
 * Process course operations (create, update, delete)
 */
function processCourseOperations($course, $post, $get, $userId) {
    $message = '';
    
    // Create course
    if (isset($post['create'])) {
        $course->course_code = htmlspecialchars(strip_tags($post['course_code']));
        $course->course_name = htmlspecialchars(strip_tags($post['course_name']));
        $course->created_by = $userId;
        
        if ($course->create()) {
            $message = '<div class="alert alert-success">Course created successfully.</div>';
        } else {
            $message = '<div class="alert alert-danger">Unable to create course.</div>';
        }
    }
    
    // Update course
    if (isset($post['update'])) {
        $course->id = htmlspecialchars(strip_tags($post['id']));
        $course->course_code = htmlspecialchars(strip_tags($post['course_code']));
        $course->course_name = htmlspecialchars(strip_tags($post['course_name']));
        
        if ($course->update()) {
            $message = '<div class="alert alert-success">Course updated successfully.</div>';
        } else {
            $message = '<div class="alert alert-danger">Unable to update course.</div>';
        }
    }
    
    // Delete course
    if (isset($get['delete'])) {
        $course->id = htmlspecialchars(strip_tags($get['delete']));
        
        if ($course->delete()) {
            $message = '<div class="alert alert-success">Course deleted successfully.</div>';
        } else {
            $message = '<div class="alert alert-danger">Unable to delete course.</div>';
        }
    }
    
    return $message;
}

/**
 * Render course form (create or edit)
 */
function renderCourseForm($course, $db, $get) {
    if (isset($get['edit'])) {
        $edit_course = new Course($db);
        if ($edit_course->getCourseById($get['edit'])) {
            ?>
            <form method="post" action="">
                <input type="hidden" name="id" value="<?php echo htmlspecialchars($edit_course->id); ?>">
                <div class="form-group mb-3">
                    <label for="course_code" class="form-label">Course Code</label>
                    <input type="text" class="form-control" id="course_code" name="course_code" 
                           value="<?php echo htmlspecialchars($edit_course->course_code); ?>" required>
                </div>
                <div class="form-group mb-3">
                    <label for="course_name" class="form-label">Course Name</label>
                    <input type="text" class="form-control" id="course_name" name="course_name" 
                           value="<?php echo htmlspecialchars($edit_course->course_name); ?>" required>
                </div>
                <button type="submit" name="update" class="btn btn-primary">Update Course</button>
                <a href="manage_courses.php" class="btn btn-secondary">Cancel</a>
            </form>
            <?php
        }
    } else {
        ?>
        <form method="post" action="">
            <div class="form-group mb-3">
                <label for="course_code" class="form-label">Course Code</label>
                <input type="text" class="form-control" id="course_code" name="course_code" required>
            </div>
            <div class="form-group mb-3">
                <label for="course_name" class="form-label">Course Name</label>
                <input type="text" class="form-control" id="course_name" name="course_name" required>
            </div>
            <button type="submit" name="create" class="btn btn-primary">Add Course</button>
        </form>
        <?php
    }
}

/**
 * Render course table
 */
function renderCourseTable($courses) {
    ?>
    <table class="table">
        <thead>
            <tr>
                <th>Course Code</th>
                <th>Course Name</th>
                <th>Created By</th>
                <th>Date Created</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php
            if ($courses->rowCount() > 0) {
                while ($row = $courses->fetch(PDO::FETCH_ASSOC)) {
                    echo "<tr>";
                    echo "<td>" . htmlspecialchars($row['course_code']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['course_name']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['created_by_name']) . "</td>";
                    echo "<td>" . date('M j, Y', strtotime($row['created_at'])) . "</td>";
                    echo "<td>";
                    echo "<a href='manage_courses.php?edit=" . htmlspecialchars($row['id']) . "' class='btn btn-sm btn-primary me-1'><i class='bi bi-pencil me-1'></i>Edit</a> ";
                    echo "<a href='manage_courses.php?delete=" . htmlspecialchars($row['id']) . "' class='btn btn-sm btn-danger' onclick='return confirm(\"Are you sure you want to delete this course?\")'><i class='bi bi-trash me-1'></i>Delete</a>";
                    echo "</td>";
                    echo "</tr>";
                }
            } else {
                echo "<tr><td colspan='5' class='text-center py-4'>No courses found.</td></tr>";
            }
            ?>
        </tbody>
    </table>
    <?php
}