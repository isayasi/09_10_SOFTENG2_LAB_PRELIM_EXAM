<?php require_once 'classloader.php'; ?>
<?php 
if (!$userObj->isLoggedIn()) {
    header("Location: login.php");
    exit;
}

if ($userObj->isAdmin()) {
    header("Location: ../client/index.php");
    exit;
} 

$categoryObj = new Category();
$categories = $categoryObj->getCategoriesWithSubcategories();
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
  <script src="https://code.jquery.com/jquery-3.7.0.js"></script>
  <link rel="stylesheet" href="css/index.css">
  <style>
    body {
      font-family: "Arial";
    }
    img {
      max-width: 100%;
      height: auto;
    }

    /* Enable hoverable nested dropdowns */
    .dropdown:hover>.dropdown-menu {
      display: block;
    }
    .dropdown-menu .dropdown:hover>.dropdown-menu {
      display: block;
      position: absolute;
      left: 100%;
      top: 0;
      margin-left: 0;
    }

    /* Ensure dropdowns appear above content */
    .dropdown-menu {
      z-index: 1000;
    }
  </style>
</head>
<body>
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
        
        <!-- Categories dropdown -->
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

      <!-- Logout button -->
      <ul class="navbar-nav">
        <li class="nav-item">
          <a class="btn btn-outline-danger" href="logout.php">Logout</a>
        </li>
      </ul>
    </div>
  </nav>

  <div class="container-fluid">
    <div class="display-4 text-center mt-4">
      Hello there and welcome! 
      <span class="text-success"><?php echo $_SESSION['username']; ?></span>. Add Proposal Here!
    </div>

    <div class="row">
      <div class="col-md-5">
        <div class="card mt-4 mb-4">
          <div class="card-body">
            <form action="core/handleForms.php" method="POST" enctype="multipart/form-data">
              <?php  
              if (isset($_SESSION['message']) && isset($_SESSION['status'])) {
                  if ($_SESSION['status'] == "200") {
                      echo "<h1 style='color: green;'>{$_SESSION['message']}</h1>";
                  } else {
                      echo "<h1 style='color: red;'>{$_SESSION['message']}</h1>"; 
                  }
                  unset($_SESSION['message']);
                  unset($_SESSION['status']);
              }
              ?>

              <h1 class="mb-4 mt-4">Add Proposal Here!</h1>

              <!-- Classification dropdown -->
              <div class="form-group">
                <label for="classification">Classification</label>
                <select class="form-control" name="classification" id="classification" required>
                  <option value="">-- Select Category --</option>
                  <?php
                  if (!empty($categories) && is_array($categories)) {
                      foreach ($categories as $cat) {
                          echo "<optgroup label='" . htmlspecialchars($cat['name']) . "'>";
                          if (!empty($cat['subcategories'])) {
                              foreach ($cat['subcategories'] as $sub) {
                                  echo "<option value='" . htmlspecialchars($sub['subcategory_id']) . "'>" 
                                      . htmlspecialchars($sub['name']) . "</option>";
                              }
                          } else {
                              echo "<option disabled>No subcategories</option>";
                          }
                          echo "</optgroup>";
                      }
                  }
                  ?>
                </select>
              </div>

              <div class="form-group">
                <label for="desc">Description</label>
                <input type="text" class="form-control" name="description" id="desc" required>
              </div>
              <div class="form-group">
                <label for="minPrice">Minimum Price</label>
                <input type="number" class="form-control" name="min_price" id="minPrice" required>
              </div>
              <div class="form-group">
                <label for="maxPrice">Max Price</label>
                <input type="number" class="form-control" name="max_price" id="maxPrice" required>
              </div>
              <div class="form-group">
                <label for="image">Image</label>
                <input type="file" class="form-control" name="image" id="image" required>
              </div>

              <input type="submit" class="btn btn-primary float-right mt-4" name="insertNewProposalBtn" value="Submit Proposal">
            </form>
          </div>
        </div>
      </div>

      <div class="col-md-7">
        <?php 
        $getProposals = $proposalObj->getProposals(true); 

        if (!empty($getProposals) && is_array($getProposals)) {
            foreach ($getProposals as $proposal) { ?>
              <div class="card shadow mt-4 mb-4">
                <div class="card-body">
                  <h2>
                    <a href="other_profile_view.php?user_id=<?php echo $proposal['user_id']; ?>">
                      <?php echo htmlspecialchars($proposal['username']); ?>
                    </a>
                  </h2>
                  <img src="<?php echo '../images/' . htmlspecialchars($proposal['image']); ?>" alt="">
                  <p class="mt-4"><i><?php echo $proposal['proposals_date_added']; ?></i></p>
                  <p class="mt-2"><?php echo htmlspecialchars($proposal['description']); ?></p>
                  <h5>Classification: 
                    <i>
                      <?php echo htmlspecialchars($categoryObj->getSubcategoryName($proposal['subcategory_id'])); ?>
                    </i>
                  </h5>
                  <h4>
                    <i>
                      <?php 
                      echo (isset($proposal['min_price']) ? number_format($proposal['min_price']) : 0) 
                          . " - " . 
                          (isset($proposal['max_price']) ? number_format($proposal['max_price']) : 0); 
                      ?> PHP
                    </i>
                  </h4>
                  <div class="float-right">
                    <a href="#">Check out services</a>
                  </div>
                </div>
              </div>
            <?php }
        } else {
            echo "<p class='text-muted'>No proposals available yet.</p>";
        }
        ?>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
