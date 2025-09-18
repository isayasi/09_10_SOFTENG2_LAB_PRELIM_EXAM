<?php
session_start();
require_once __DIR__ . "/../config/database.php";
require_once __DIR__ . "/../includes/category.php";

// Redirect non-admin users
if ($_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit();
}

$category = new Category($pdo);
$message = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Add new category
    if (isset($_POST['add_category'])) {
        $category->name = $_POST['name'];
        $category->description = $_POST['description'];
        $category->created_by = $_SESSION['user_id'];

        if ($category->create()) {
            $message = '<div class="notification success">Category added successfully!</div>';
        } else {
            $message = '<div class="notification danger">Error adding category.</div>';
        }
    }

    // Delete category
    if (isset($_POST['delete_category'])) {
        $category->id = $_POST['category_id'];
        if ($category->delete()) {
            $message = '<div class="notification success">Category deleted successfully!</div>';
        } else {
            $message = '<div class="notification danger">Error deleting category.</div>';
        }
    }
}

// Get all categories
$categories = $category->read();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Categories</title>
    <link rel="stylesheet" href="../assets/css/dashboard.css">
    <style>
        /* =======================
           Styles for Manage Categories
           ======================= */
        .card {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            position: relative;
            overflow: hidden;
        }

        .card::before {
            content: "";
            position: absolute;
            top: 0; left: 0; right: 0;
            height: 8px;
            background: linear-gradient(90deg, var(--primary), var(--secondary), var(--accent));
            border-radius: 15px 15px 0 0;
        }

        .card h3 {
            color: var(--primary);
            margin-bottom: 1.5rem;
            padding-bottom: 10px;
            border-bottom: 2px dashed var(--secondary);
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .card h3::before { content: "📁"; }

        .form-group { margin-bottom: 1.5rem; }

        .form-group label {
            display: block;
            font-weight: bold;
            margin-bottom: 0.5rem;
            color: var(--dark);
        }

        .form-control {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            font-size: 1rem;
            transition: all 0.3s ease;
            background: #f8f9fa;
        }

        .form-control:focus {
            border-color: var(--primary);
            outline: none;
            box-shadow: 0 0 0 3px rgba(74, 111, 165, 0.2);
            background: white;
        }

        textarea.form-control { min-height: 100px; resize: vertical; }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary), #6a8dc8);
            color: white;
            border: none;
            padding: 12px 25px;
            border-radius: 50px;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .btn-primary::before { content: "+"; font-size: 1.2rem; }

        .btn-primary:hover {
            background: linear-gradient(135deg, #3a5a84, #5a7ab8);
            transform: translateY(-2px);
            box-shadow: 0 6px 12px rgba(74, 111, 165, 0.4);
        }

        .btn-danger {
            background: linear-gradient(135deg, var(--danger), #e57373);
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 50px;
            font-size: 0.9rem;
            cursor: pointer;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }

        .btn-danger::before { content: "🗑️"; }

        .btn-danger:hover {
            background: linear-gradient(135deg, #c62828, #d32f2f);
            transform: translateY(-2px);
        }

        .table-responsive { overflow-x: auto; border-radius: 10px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }

        .table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            border-radius: 10px;
            overflow: hidden;
        }

        .table th {
            background: linear-gradient(135deg, var(--primary), #6a8dc8);
            color: white;
            padding: 15px;
            text-align: left;
            font-weight: bold;
        }

        .table td { padding: 15px; border-bottom: 1px solid #f0f0f0; }
        .table tr:last-child td { border-bottom: none; }
        .table tr:hover { background: #f8f9fa; }

        .actions form { display: inline; }

        .notification {
            padding: 15px 20px;
            margin-bottom: 20px;
            border-radius: 10px;
            border-left: 4px solid;
            background: white;
            box-shadow: 0 3px 10px rgba(0,0,0,0.1);
        }

        .notification.success { border-left-color: var(--success); background: #f0fff0; }
        .notification.danger { border-left-color: var(--danger); background: #fff0f0; }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .table { font-size: 0.9rem; }
            .table th, .table td { padding: 10px; }
            .btn-danger { padding: 6px 12px; font-size: 0.8rem; }
        }

        @media (max-width: 480px) {
            .card { padding: 1.5rem; }
            .table-responsive { margin: 0 -1rem; }
            .table { font-size: 0.8rem; }
        }
    </style>
</head>
<body>
    <div class="dashboard">
        <div class="sidebar">
            <h3>Admin Panel</h3>
            <div class="sidebar-nav">
                <ul>
                    <li><a href="dashboard.php">All Articles</a></li>
                    <li><a href="notifications.php">Notifications</a></li>
                    <li><a href="manage_categories.php" class="active">Manage Categories</a></li>
                    <li><a href="../logout.php">Logout</a></li>
                </ul>
            </div>
        </div>

        <div class="dashboard-content">
            <h2>Manage Categories</h2>

            <!-- Display messages -->
            <?php echo $message; ?>

            <!-- Add Category Form -->
            <div class="card">
                <h3>Add New Category</h3>
                <form method="POST">
                    <div class="form-group">
                        <label>Category Name</label>
                        <input type="text" name="name" class="form-control" required placeholder="Enter category name (e.g., News, Sports, Opinion)">
                    </div>
                    <div class="form-group">
                        <label>Description</label>
                        <textarea name="description" class="form-control" rows="3" placeholder="Brief description of this category"></textarea>
                    </div>
                    <button type="submit" name="add_category" class="btn btn-primary">Add Category</button>
                </form>
            </div>

            <!-- Existing Categories Table -->
            <div class="card">
                <h3>Existing Categories</h3>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Description</th>
                                <th>Created By</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($cat = $categories->fetch(PDO::FETCH_ASSOC)): ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($cat['name']); ?></strong></td>
                                <td><?php echo htmlspecialchars($cat['description']); ?></td>
                                <td><?php echo htmlspecialchars($cat['created_by_name']); ?></td>
                                <td class="actions">
                                    <form method="POST" style="display:inline;">
                                        <input type="hidden" name="category_id" value="<?php echo $cat['id']; ?>">
                                        <button type="submit" name="delete_category" class="btn btn-danger" 
                                                onclick="return confirm('Are you sure you want to delete this category? All articles in this category will keep their category assignment until updated.')">
                                            Delete
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>
</body>
</html>