<?php
include_once 'config/database.php';
include_once 'objects/user.php';
include_once 'objects/student.php';

$database = new Database();
$db = $database->getConnection();

$user = new User($db);
$student = new Student($db);

$message = '';

// Handle registration form submission
if ($_POST) {
    $user->username = $_POST['username'];
    $user->password = $_POST['password'];
    $user->role = $_POST['role'];

    // Check if username already exists
    if ($user->usernameExists()) {
        $message = '<div class="alert alert-danger">Username already exists.</div>';
    } else {
        // Register user
        if ($user->register()) {
            if ($user->role == 'student') {
                $student->user_id = $db->lastInsertId();
                $student->first_name = $_POST['first_name'];
                $student->last_name = $_POST['last_name'];
                $student->course_program = $_POST['course_program'];
                $student->course_id = $_POST['course_id'];
                $student->year_level = $_POST['year_level'];

                if ($student->create()) {
                    $message = '<div class="alert alert-success">Student registration successful.</div>';
                } else {
                    $message = '<div class="alert alert-danger">Student registration failed.</div>';
                }
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register | Attendance System</title>
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
            display: flex;
            align-items: center;
            padding: 20px 0;
        }

        .register-container {
            width: 100%;
            max-width: 600px;
            margin: 0 auto;
        }

        .card {
            background-color: var(--card-bg);
            border: none;
            border-radius: 12px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.3);
            overflow: hidden;
        }

        .card-header {
            background: linear-gradient(135deg, var(--primary), #6a11cb);
            border: none;
            padding: 25px 20px;
            text-align: center;
        }

        .card-header h2 {
            margin: 0;
            font-weight: 600;
            letter-spacing: 0.5px;
        }

        .card-body {
            padding: 30px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-control, .form-select {
            background-color: #2a2a2a;
            border: 1px solid #3a3a3a;
            color: var(--text);
            border-radius: 8px;
            padding: 12px 15px;
            transition: all 0.3s;
        }

        .form-control:focus, .form-select:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 0.25rem rgba(138, 43, 226, 0.25);
        }

        .form-label {
            font-weight: 500;
            margin-bottom: 8px;
            color: var(--text);
        }

        .btn-primary {
            background-color: var(--primary);
            border-color: var(--primary);
            border-radius: 8px;
            padding: 12px;
            font-weight: 600;
            letter-spacing: 0.5px;
            transition: all 0.3s;
            width: 100%;
        }

        .btn-primary:hover {
            background-color: var(--primary-hover);
            border-color: var(--primary-hover);
            transform: translateY(-2px);
        }

        .alert {
            border-radius: 8px;
            padding: 12px 15px;
            margin-bottom: 20px;
        }

        .alert-danger {
            background-color: rgba(220, 53, 69, 0.2);
            border-color: rgba(220, 53, 69, 0.3);
            color: #ff6b6b;
        }

        .alert-success {
            background-color: rgba(40, 167, 69, 0.2);
            border-color: rgba(40, 167, 69, 0.3);
            color: #6bff8f;
        }

        .login-link {
            color: var(--primary);
            text-decoration: none;
            transition: color 0.3s;
            font-weight: 500;
        }

        .login-link:hover {
            color: var(--primary-hover);
            text-decoration: underline;
        }

        .brand-text {
            text-align: center;
            margin-top: 30px;
            color: var(--text-muted);
            font-size: 0.9rem;
        }

        .input-icon {
            position: relative;
        }

        .input-icon .form-control {
            padding-left: 40px;
        }

        .input-icon i {
            position: absolute;
            left: 15px;
            top: 12px;
            color: var(--text-muted);
        }

        .student-fields {
            background: rgba(138, 43, 226, 0.1);
            border-radius: 10px;
            padding: 20px;
            margin-top: 15px;
            border-left: 4px solid var(--primary);
        }

        .student-fields h5 {
            color: var(--primary);
            margin-bottom: 20px;
            font-weight: 600;
            border-bottom: 1px solid rgba(138, 43, 226, 0.3);
            padding-bottom: 10px;
        }
    </style>
</head>
<body>
<div class="register-container">
    <div class="card">
        <div class="card-header">
            <h2><i class="bi bi-person-plus me-2"></i>Register</h2>
        </div>
        <div class="card-body">
            <?php echo $message; ?>
            <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="username" class="form-label">Username</label>
                            <div class="input-icon">
                                <i class="bi bi-person"></i>
                                <input type="text" class="form-control" id="username" name="username" placeholder="Choose a username" required>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="password" class="form-label">Password</label>
                            <div class="input-icon">
                                <i class="bi bi-lock"></i>
                                <input type="password" class="form-control" id="password" name="password" placeholder="Create a password" required>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label for="role" class="form-label">Role</label>
                    <select class="form-select" id="role" name="role" onchange="toggleStudentFields()">
                        <option value="student">Student</option>
                        <option value="admin">Admin</option>
                    </select>
                </div>

                <div id="studentFields" class="student-fields">
                    <h5><i class="bi bi-mortarboard me-2"></i>Student Information</h5>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="first_name" class="form-label">First Name</label>
                                <input type="text" class="form-control" id="first_name" name="first_name" placeholder="Enter first name">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="last_name" class="form-label">Last Name</label>
                                <input type="text" class="form-control" id="last_name" name="last_name" placeholder="Enter last name">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-8">
                            <div class="form-group">
                                <label for="course_id" class="form-label">Course/Program</label>
                                <select class="form-select" id="course_id" name="course_id">
                                    <option value="">Select Course</option>
                                    <?php
                                    include_once 'objects/Course.php';
                                    $course = new Course($db);
                                    $courses = $course->read();
                                    while ($course_row = $courses->fetch(PDO::FETCH_ASSOC)) {
                                        echo "<option value='{$course_row['id']}' data-code='{$course_row['course_code']}'>{$course_row['course_code']} - {$course_row['course_name']}</option>";
                                    }
                                    ?>
                                </select>
                                <input type="hidden" id="course_program" name="course_program">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="year_level" class="form-label">Year Level</label>
                                <select class="form-select" id="year_level" name="year_level">
                                    <option value="">Select Year</option>
                                    <option value="1">1st Year</option>
                                    <option value="2">2nd Year</option>
                                    <option value="3">3rd Year</option>
                                    <option value="4">4th Year</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary mt-4">Register</button>
                <p class="text-center mt-4">Already have an account? <a href="login.php" class="login-link">Login here</a></p>
            </form>
        </div>
    </div>

    <div class="brand-text">
        Attendance System &copy; <?php echo date('Y'); ?>
    </div>
</div>

<script>
function toggleStudentFields() {
    var role = document.getElementById('role').value;
    var studentFields = document.getElementById('studentFields');
    
    if(role === 'student') {
        studentFields.style.display = 'block';
        document.getElementById('first_name').required = true;
        document.getElementById('last_name').required = true;
        document.getElementById('course_program').required = true;
        document.getElementById('year_level').required = true;
    } else {
        studentFields.style.display = 'none';
        document.getElementById('first_name').required = false;
        document.getElementById('last_name').required = false;
        document.getElementById('course_program').required = false;
        document.getElementById('year_level').required = false;
    }
}

// Update course_program hidden input when course is selected
document.getElementById('course_id').addEventListener('change', function() {
    var selectedOption = this.options[this.selectedIndex];
    document.getElementById('course_program').value = selectedOption.getAttribute('data-code');
});

document.addEventListener('DOMContentLoaded', function() {
    toggleStudentFields();
});
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
