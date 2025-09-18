<?php
require_once 'classloader.php';

// Check if user is logged in and is admin
if (!$userObj->isLoggedIn() || !$userObj->isAdmin()) {
  header("Location: login.php");
  exit();
}

// Initialize Category class
$categoryObj = new Category();
$categories = $categoryObj->getCategoriesWithSubcategories();

// Handle category creation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['createCategoryBtn'])) {
    $result = $categoryObj->createCategory($_POST['name'], $_POST['description']);
    $_SESSION['flash_message'] = $result['message'];
    header("Location: admin_categories.php");
    exit();
}

// Handle subcategory creation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['createSubcategoryBtn'])) {
    $result = $categoryObj->createSubcategory($_POST['category_id'], $_POST['name'], $_POST['description']);
    $_SESSION['flash_message'] = $result['message'];
    header("Location: admin_categories.php");
    exit();
}
?>

<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
  <script src="https://code.jquery.com/jquery-3.7.0.js"></script>
  <title>Admin - Category Management</title>
  <style>
    /* Global Styles */
    body {
      font-family: "Arial", sans-serif;
      background: linear-gradient(135deg, #e8eaf6 0%, #d1c4e9 100%);
      min-height: 100vh;
      line-height: 1.6;
    }

     /* Navigation Styles */
    .navbar {
      background: linear-gradient(90deg, #433878 0%, #6a5acd 100%) !important;
      box-shadow: 0 4px 20px rgba(0,0,0,0.15);
    }

    .navbar-brand {
      font-weight: bold;
      font-size: 1.5rem;
      color: white !important;
      text-shadow: 1px 1px 2px rgba(0,0,0,0.3);
    }

    .nav-link {
      font-weight: 500;
      color: rgba(255,255,255,0.9) !important;
      transition: all 0.3s ease;
      margin: 0 0.5rem;
    }

    .nav-link:hover {
      color: white !important;
      transform: translateY(-2px);
      text-shadow: 0 2px 4px rgba(0,0,0,0.2);
    }

    /* Dropdown Styles */
    .dropdown:hover > .dropdown-menu {
      display: block;
      animation: slideDown 0.3s ease;
    }

    .dropdown-menu .dropdown:hover > .dropdown-menu {
      display: block;
      position: absolute;
      left: 100%;
      top: 0;
      margin-left: 0;
      animation: slideRight 0.3s ease;
    }

    .dropdown-menu {
      z-index: 1000;
      border: none;
      border-radius: 12px;
      box-shadow: 0 8px 30px rgba(0,0,0,0.15);
      background: linear-gradient(135deg, #f3e5f5 0%, #ede7f6 100%);
    }

    .dropdown-item {
      padding: 0.75rem 1.25rem;
      font-weight: 500;
      transition: all 0.3s ease;
      border-radius: 8px;
      margin: 0.25rem;
    }

    .dropdown-item:hover {
      background: linear-gradient(45deg, #433878, #6a5acd);
      color: white !important;
      transform: translateX(5px);
    }

    /* Button Styles */
    .btn-outline-danger {
      border: 2px solid #d32f2f;
      color: #d32f2f;
      font-weight: 600;
      border-radius: 25px;
      padding: 0.75rem 1.5rem;
      transition: all 0.3s ease;
    }

    .btn-outline-danger:hover {
      background: linear-gradient(45deg, #d32f2f, #b71c1c);
      transform: translateY(-2px);
      box-shadow: 0 5px 15px rgba(211, 47, 47, 0.3);
      color: white !important;
    }

    .btn-primary {
      background: linear-gradient(45deg, #433878, #6a5acd);
      border: none;
    }

    .btn-primary:hover {
      transform: translateY(-2px);
      box-shadow: 0 5px 15px rgba(67,56,120,0.3);
      background: linear-gradient(45deg, #6a5acd, #8a79ff);
    }

    /* Hero Section */
    .display-4 {
      font-weight: 300;
      color: #433878;
      text-shadow: 1px 1px 2px rgba(0,0,0,0.1);
      margin-bottom: 2rem;
      background: linear-gradient(45deg, #433878, #6a5acd);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
    }

    .text-success {
      font-weight: 600;
    }

    /* Card Styles */
    .card {
      border: none;
      border-radius: 20px;
      background: linear-gradient(135deg, #f3e5f5 0%, #ede7f6 100%);
      box-shadow: 0 15px 50px rgba(0,0,0,0.1);
      overflow: hidden;
      transition: transform 0.3s ease, box-shadow 0.3s ease;
    }

    .card:hover {
      transform: translateY(-10px);
      box-shadow: 0 25px 60px rgba(0,0,0,0.15);
    }

    .card-body {
      padding: 2rem;
    }

    /* Form Styles */
    .form-group {
      margin-bottom: 1.5rem;
    }

    .form-control {
      border: 2px solid #e9ecef;
      border-radius: 12px;
      padding: 1rem 1.25rem;
      font-size: 1rem;
      transition: all 0.3s ease;
      background: linear-gradient(135deg, #ede7f6, #f3e5f5);
    }

    .form-control:focus {
      border-color: #433878;
      box-shadow: 0 0 0 0.3rem rgba(67,56,120,0.25);
      transform: scale(1.02);
    }

    .form-label {
      font-weight: 600;
      color: #433878;
      margin-bottom: 0.5rem;
      display: block;
    }

    /* Category List */
    h5 {
      font-weight: 600;
      color: #433878;
    }

    ul {
      padding-left: 20px;
    }

    ul li {
      margin-bottom: 5px;
      color: #555;
    }

    hr {
      border-top: 1px solid #ccc;
    }

    /* Flash Message */
    .alert-info {
      background-color: #e1bee7;
      color: #4a148c;
      border-radius: 8px;
    }

    /* Animations */
    @keyframes slideDown {
      from { opacity: 0; transform: translateY(-10px); }
      to { opacity: 1; transform: translateY(0); }
    }

    @keyframes slideRight {
      from { opacity: 0; transform: translateX(-10px); }
      to { opacity: 1; transform: translateX(0); }
    }

    @keyframes fadeInUp {
      from { opacity: 0; transform: translateY(30px); }
      to { opacity: 1; transform: translateY(0); }
    }

    .card {
      animation: fadeInUp 0.6s ease;
    }

    /* Container Styles */
    .container-fluid {
      padding: 2rem;
    }

    /* Responsive Design */
    @media (max-width: 768px) {
      .display-4 { font-size: 2.5rem; }
      .card-body { padding: 1.5rem; }
      .navbar-nav, .dropdown-menu { text-align: center; }
      .dropdown-menu .dropdown:hover > .dropdown-menu { position: static; left: 0; margin-left: 1rem; }
    }

    @media (max-width: 576px) {
      .container-fluid { padding: 1rem; }
      .card-body { padding: 1.25rem; }
      .btn { padding: 0.6rem 1.2rem; font-size: 0.9rem; }
      .form-control { padding: 0.75rem 1rem; }
      .navbar-brand { font-size: 1.2rem; }
    }

    /* Image Styles */
    img {
      max-width: 100%;
      height: auto;
      border-radius: 12px;
    }
    
    /* Navbar */
    .navbar {
      background: linear-gradient(90deg, #433878 0%, #6a5acd 100%) !important;
      box-shadow: 0 4px 20px rgba(0,0,0,0.15);
    }
    .navbar-brand { font-weight: bold; font-size: 1.5rem; color: white !important; }
    .nav-link { font-weight: 500; color: rgba(255,255,255,0.9) !important; margin: 0 0.5rem; }
    .nav-link:hover { color: white !important; }

    /* Dropdown */
    .dropdown:hover > .dropdown-menu { display: block; }
    .dropdown-menu .dropdown:hover > .dropdown-menu { display: block; position: absolute; left: 100%; top: 0; margin-left: 0; }
    .dropdown-menu { z-index: 1000; border: none; border-radius: 12px; box-shadow: 0 8px 30px rgba(0,0,0,0.15); background: #ede7f6; }
    .dropdown-item { padding: 0.75rem 1.25rem; border-radius: 8px; margin: 0.25rem; font-weight: 500; }
    .dropdown-item:hover { background: linear-gradient(45deg, #433878, #6a5acd); color: white !important; }

    /* Card */
    .card { border: none; border-radius: 20px; background: #f3e5f5; box-shadow: 0 15px 50px rgba(0,0,0,0.1); transition: transform 0.3s ease, box-shadow 0.3s ease; }
    .card:hover { transform: translateY(-10px); box-shadow: 0 25px 60px rgba(0,0,0,0.15); }
    .card-body { padding: 2rem; }

    /* Inputs, Textareas, Select */
    .form-control {
      border: 2px solid #e9ecef;
      border-radius: 12px;
      padding: 0.5rem 1rem;
      font-size: 1rem;
      background: #ede7f6;
      color: #433878;
      transition: all 0.3s ease;
    }
    .form-control:focus {
      border-color: #433878;
      box-shadow: 0 0 0 0.3rem rgba(67,56,120,0.25);
      transform: scale(1.02);
    }
    select.form-control { -webkit-appearance: none; -moz-appearance: none; appearance: none; }

    /* Buttons */
    .btn-primary { background: linear-gradient(45deg, #433878, #6a5acd); border: none; }
    .btn-primary:hover { background: linear-gradient(45deg, #6a5acd, #8a79ff); box-shadow: 0 5px 15px rgba(67,56,120,0.3); transform: translateY(-2px); }

    .btn-outline-danger { border: 2px solid #d32f2f; color: #d32f2f; border-radius: 25px; padding: 0.75rem 1.5rem; }
    .btn-outline-danger:hover { background: linear-gradient(45deg, #d32f2f, #b71c1c); color: white !important; }

    /* Category List */
    h5 { font-weight: 600; color: #433878; }
    ul { padding-left: 20px; }
    ul li { margin-bottom: 5px; color: #555; }
    hr { border-top: 1px solid #ccc; }

    /* Flash Message */
    .alert-info { background-color: #e1bee7; color: #4a148c; border-radius: 8px; }

    /* Animations */
    @keyframes fadeInUp { from {opacity:0; transform:translateY(30px);} to {opacity:1; transform:translateY(0);} }
    .card { animation: fadeInUp 0.6s ease; }

    /* Responsive */
    @media (max-width: 768px) { .card-body { padding: 1.5rem; } }
    @media (max-width: 576px) { .card-body { padding: 1.25rem; } }
  </style>
</head>
<body>
  <!-- NAVBAR -->
  <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <a class="navbar-brand" href="#">Admin Panel</a>
    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNavDropdown">
      <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="navbarNavDropdown">
      <ul class="navbar-nav mr-auto">
        <li class="nav-item"><a class="nav-link" href="admin_categories.php">Category Management</a></li>
        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle" href="#" id="categoriesDropdown" role="button" data-toggle="dropdown">
            Categories
          </a>
          <div class="dropdown-menu" aria-labelledby="categoriesDropdown">
            <?php foreach ($categories as $cat): ?>
              <div class="dropdown dropright">
                <a class="dropdown-item dropdown-toggle" href="#"><?php echo htmlspecialchars($cat['name']); ?></a>
                <div class="dropdown-menu">
                  <?php if (!empty($cat['subcategories'])): ?>
                    <?php foreach ($cat['subcategories'] as $sub): ?>
                      <a class="dropdown-item" href="#"><?php echo htmlspecialchars($sub['name']); ?></a>
                    <?php endforeach; ?>
                  <?php else: ?>
                    <span class="dropdown-item disabled">No subcategories</span>
                  <?php endif; ?>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        </li>
      </ul>
      <ul class="navbar-nav">
        <li class="nav-item"><a class="btn btn-outline-danger" href="logout.php">Logout</a></li>
      </ul>
    </div>
  </nav>

  <div class="container-fluid mt-4">
    <?php
      if (isset($_SESSION['flash_message'])) {
        echo "<div class='alert alert-info'>{$_SESSION['flash_message']}</div>";
        unset($_SESSION['flash_message']);
      }
    ?>

    <div class="row">
      <!-- Create Category -->
      <div class="col-md-6">
        <div class="card mb-4">
          <div class="card-header"><h4>Create Category</h4></div>
          <div class="card-body">
            <form method="POST">
              <div class="form-group">
                <label for="categoryName">Category Name</label>
                <input type="text" name="name" id="categoryName" class="form-control" required>
              </div>
              <div class="form-group">
                <label for="categoryDesc">Description</label>
                <textarea name="description" id="categoryDesc" class="form-control" rows="3"></textarea>
              </div>
              <button type="submit" name="createCategoryBtn" class="btn btn-primary">Create Category</button>
            </form>
          </div>
        </div>
      </div>

      <!-- Create Subcategory -->
      <div class="col-md-6">
        <div class="card mb-4">
          <div class="card-header"><h4>Create Subcategory</h4></div>
          <div class="card-body">
            <form method="POST">
              <div class="form-group">
                <label for="parentCategory">Parent Category</label>
                <select name="category_id" id="parentCategory" class="form-control" required>
                  <option value="">Select Category</option>
                  <?php foreach ($categories as $cat): ?>
                    <option value="<?php echo $cat['category_id']; ?>"><?php echo htmlspecialchars($cat['name']); ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
              <div class="form-group">
                <label for="subName">Subcategory Name</label>
                <input type="text" name="name" id="subName" class="form-control" required>
              </div>
              <div class="form-group">
                <label for="subDesc">Description</label>
                <textarea name="description" id="subDesc" class="form-control" rows="3"></textarea>
              </div>
              <button type="submit" name="createSubcategoryBtn" class="btn btn-primary">Create Subcategory</button>
            </form>
          </div>
        </div>
      </div>
    </div>

    <!-- Display Existing Categories -->
    <div class="row">
      <div class="col-12">
        <div class="card mb-4">
          <div class="card-header"><h4>Existing Categories & Subcategories</h4></div>
          <div class="card-body">
            <?php foreach ($categories as $cat): ?>
              <h5><?php echo htmlspecialchars($cat['name']); ?></h5>
              <?php if (!empty($cat['subcategories'])): ?>
                <ul>
                  <?php foreach ($cat['subcategories'] as $sub): ?>
                    <li><?php echo htmlspecialchars($sub['name']); ?></li>
                  <?php endforeach; ?>
                </ul>
              <?php else: ?>
                <p>No subcategories yet.</p>
              <?php endif; ?>
              <hr>
            <?php endforeach; ?>
          </div>
        </div>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
