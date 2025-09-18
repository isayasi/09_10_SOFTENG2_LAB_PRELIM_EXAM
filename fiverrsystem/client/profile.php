<?php 
require_once 'classloader.php'; 
require_once 'classes/Category.php';
?>
<?php 
if (!$userObj->isLoggedIn()) {
    header("Location: login.php");
    exit();
}

if (!$userObj->isAdmin()) {
    header("Location: index.php");
    exit();
} 

$categoryObj = new Category();
$categories = $categoryObj->getCategoriesWithSubcategories();
$userInfo = $userObj->getUsers($_SESSION['user_id']);
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

  <!-- Bootstrap CSS -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
  
  <!-- jQuery and Bootstrap JS -->
  <script src="https://code.jquery.com/jquery-3.7.0.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.min.js"></script>
  <link rel="stylesheet" href="css/profile.css">

  <style>
    body {
      font-family: Arial, sans-serif;
    }
    /* Optional: adjust dropdown submenu width */
    .dropright .dropdown-menu {
      min-width: 180px;
    }
  </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-light bg-light">
  <a class="navbar-brand" href="#">Client Panel</a>
  <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" 
          aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
    <span class="navbar-toggler-icon"></span>
  </button>

  <div class="collapse navbar-collapse" id="navbarSupportedContent">
    <!-- Left Links -->
    <ul class="navbar-nav mr-auto">
      <li class="nav-item"><a class="nav-link" href="index.php">Home</a></li>
      <li class="nav-item"><a class="nav-link" href="other_profile_view.php">Other Profile View</a></li>
      <li class="nav-item"><a class="nav-link" href="profile.php">Profile</a></li>
  
      <!-- Categories Dropdown -->
      <li class="nav-item dropdown">
        <a class="nav-link dropdown-toggle" href="#" id="categoriesDropdown" role="button" 
           data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
          Categories
        </a>
        <div class="dropdown-menu" aria-labelledby="categoriesDropdown">
          <?php if (!empty($categories)) : ?>
            <?php foreach ($categories as $cat) : ?>
              <div class="dropdown dropright">
                <a class="dropdown-item dropdown-toggle" href="#" id="cat<?php echo $cat['category_id']; ?>">
                  <?php echo htmlspecialchars($cat['name']); ?>
                </a>
                <div class="dropdown-menu">
                  <?php if (!empty($cat['subcategories'])) : ?>
                    <?php foreach ($cat['subcategories'] as $sub) : ?>
                      <a class="dropdown-item" href="#"><?php echo htmlspecialchars($sub['name']); ?></a>
                    <?php endforeach; ?>
                  <?php else: ?>
                    <span class="dropdown-item disabled">No subcategories</span>
                  <?php endif; ?>
                </div>
              </div>
            <?php endforeach; ?>
          <?php endif; ?>
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

<div class="container-fluid mt-4">
  <div class="display-4 text-center mb-4">Hello there and welcome!</div>

  <!-- Messages -->
  <div class="text-center mb-4">
    <?php  
      if (isset($_SESSION['message']) && isset($_SESSION['status'])) {
        $color = ($_SESSION['status'] == "200") ? "green" : "red";
        echo "<h1 style='color: {$color};'>{$_SESSION['message']}</h1>";
      }
      unset($_SESSION['message']);
      unset($_SESSION['status']);
    ?>
  </div>

  <!-- Profile Card -->
  <div class="row justify-content-center">
    <div class="col-md-10">
      <div class="card shadow mb-4">
        <div class="card-body">
          <div class="row">
            <div class="col-md-6 text-center">
              <h1>Your Profile</h1>
              <img src="https://cdn.pixabay.com/photo/2015/10/05/22/37/blank-profile-picture-973460_1280.png" class="img-fluid mt-4 mb-4" alt="Profile Picture">
              <h3>Username: <?php echo $userInfo['username']; ?></h3>
              <h3>Email: <?php echo $userInfo['email']; ?></h3>
              <h3>Phone Number: <?php echo $userInfo['contact_number']; ?></h3>
            </div>

            <div class="col-md-6">
              <form action="core/handleForms.php" method="POST" enctype="multipart/form-data">
                <div class="form-group">
                  <label>Username</label>
                  <input type="text" class="form-control" value="<?php echo $userInfo['username']; ?>" disabled>
                </div>
                <div class="form-group">
                  <label>Email</label>
                  <input type="email" class="form-control" value="<?php echo $userInfo['email']; ?>" disabled>
                </div>
                <div class="form-group">
                  <label>Contact Number</label>
                  <input type="text" class="form-control" name="contact_number" value="<?php echo $userInfo['contact_number']; ?>" required>
                </div>
                <div class="form-group">
                  <label>Bio</label>
                  <textarea name="bio_description" class="form-control"><?php echo $userInfo['bio_description']; ?></textarea>
                </div>
                <div class="form-group">
                  <label>Display Picture</label>
                  <input type="file" class="form-control" name="display_picture">
                </div>
                <button type="submit" class="btn btn-primary float-right mt-2" name="updateUserBtn">Update Profile</button>
              </form>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Hover script for multi-level dropdown -->
<script>
$(document).ready(function(){
    // Main dropdown hover
    $('ul.navbar-nav .dropdown').hover(
        function() { $(this).find('.dropdown-menu').first().stop(true, true).slideDown(150); },
        function() { $(this).find('.dropdown-menu').first().stop(true, true).slideUp(150); }
    );

    // Submenu hover (dropright)
    $('.dropright').hover(
        function() { $(this).children('.dropdown-menu').stop(true, true).slideDown(150); },
        function() { $(this).children('.dropdown-menu').stop(true, true).slideUp(150); }
    );
});
</script>

</body>
</html>
