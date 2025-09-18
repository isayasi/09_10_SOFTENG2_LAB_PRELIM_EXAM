<?php
require_once 'classloader.php';
require_once 'classes/Category.php'; // include Category class

// Instantiate objects
$userObj = new User();
$categoryObj = new Category();

if (!$userObj->isLoggedIn()) {
    header("Location: login.php");
    exit;
}

if ($userObj->isAdmin()) { // admin users should go to client index
    header("Location: index.php"); // or wherever you want admins
    exit;
}

// Determine which user to display
$user_id = $_GET['user_id'] ?? $_SESSION['user_id'];
$userInfo = $userObj->getUsers($user_id);

if (!$userInfo) {
    echo "<div class='text-center mt-5'><h3>Invalid User ID or user not found.</h3></div>";
    exit;
}

// Load categories for navbar
$categories = $categoryObj->getCategoriesWithSubcategories();
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
  <script src="https://code.jquery.com/jquery-3.7.0.js"></script>
  <link rel="stylesheet" href="css/other_profile_view.css">
  <style>
    body { font-family: "Arial"; }
    .dropdown:hover > .dropdown-menu { display: block; }
    .dropright:hover > .dropdown-menu { display: block; }
  </style>
</head>
<body>
  <!-- Navbar -->
  <nav class="navbar navbar-expand-lg navbar-light bg-light">
    <a class="navbar-brand" href="index.php">Freelancer Panel</a>
    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" 
            aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="navbarSupportedContent">
      <ul class="navbar-nav mr-auto">
        <li class="nav-item"><a class="nav-link" href="index.php">Home</a></li>
        <li class="nav-item"><a class="nav-link" href="other_profile_view.php">Other Profile View</a></li>
        <li class="nav-item"><a class="nav-link" href="profile.php">Profile</a></li>
        <li class="nav-item"><a class="nav-link" href="your_proposals.php">Your Proposal</a></li>
        <li class="nav-item"><a class="nav-link" href="offers_from_clients.php">Offers From Clients</a></li>

        <!-- Categories Dropdown -->
        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle" href="#" id="categoriesDropdown" role="button" 
             data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
            Categories
          </a>
          <div class="dropdown-menu" aria-labelledby="categoriesDropdown">
            <?php foreach ($categories as $cat): ?>
              <div class="dropdown dropright">
                <a class="dropdown-item dropdown-toggle" href="#">
                  <?php echo htmlspecialchars($cat['name']); ?>
                </a>
                <div class="dropdown-menu">
                  <?php if (!empty($cat['subcategories'])): ?>
                    <?php foreach ($cat['subcategories'] as $sub): ?>
                      <a class="dropdown-item" href="#">
                        <?php echo htmlspecialchars($sub['name']); ?>
                      </a>
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

      <!-- Logout Button -->
      <ul class="navbar-nav">
        <li class="nav-item">
          <a class="btn btn-outline-danger" href="logout.php">Logout</a>
        </li>
      </ul>
    </div>
  </nav>

  <!-- Profile Content -->
  <div class="container-fluid">
    <div class="display-4 text-center mt-4">Hello there and welcome!</div>
    <div class="row justify-content-center">
      <div class="col-md-12">
        <div class="card shadow mt-4 mb-4">
          <div class="card-body">
            <div class="row">
              <div class="col-md-6 text-center">
                <img src="<?php echo htmlspecialchars($userInfo['display_picture'] ?? 'https://cdn.pixabay.com/photo/2015/10/05/22/37/blank-profile-picture-973460_1280.png'); ?>" 
                     class="img-fluid mt-4 mb-4" alt="Profile Picture">
                <h3>Username: <?php echo htmlspecialchars($userInfo['username'] ?? 'N/A'); ?></h3>
                <h3>Email: <?php echo htmlspecialchars($userInfo['email'] ?? 'N/A'); ?></h3>
                <h3>Phone Number: <?php echo htmlspecialchars($userInfo['contact_number'] ?? 'N/A'); ?></h3>
              </div>

              <div class="col-md-6">
                <form>
                  <div class="card-body">
                    <div class="form-group">
                      <label>Username</label>
                      <input type="text" class="form-control" 
                             value="<?php echo htmlspecialchars($userInfo['username'] ?? ''); ?>" disabled>
                    </div>
                    <div class="form-group">
                      <label>Email</label>
                      <input type="email" class="form-control" 
                             value="<?php echo htmlspecialchars($userInfo['email'] ?? ''); ?>" disabled>
                    </div>
                    <div class="form-group">
                      <label>Contact Number</label>
                      <input type="text" class="form-control" 
                             value="<?php echo htmlspecialchars($userInfo['contact_number'] ?? ''); ?>" disabled>
                    </div>
                    <div class="form-group">
                      <label>Bio</label>
                      <textarea class="form-control" disabled>
                        <?php echo htmlspecialchars($userInfo['bio_description'] ?? ''); ?>
                      </textarea>
                    </div>  
                  </div>
                </form>
              </div>

            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

</body>
</html>
