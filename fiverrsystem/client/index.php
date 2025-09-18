<?php 
require_once 'classloader.php'; 
require_once 'classes/Category.php'; 
?>
<?php 
if (!$userObj->isLoggedIn()) {
  header("Location: login.php");
  exit;
}

if (!$userObj->isAdmin()) {
  header("Location: ../freelancer/index.php");
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

  <!-- Bootstrap CSS -->
  <!-- Bootstrap CSS -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
  
  <!-- jQuery and Bootstrap JS -->
  <script src="https://code.jquery.com/jquery-3.7.0.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.min.js"></script>
  <link rel="stylesheet" href="css/index.css">
  <style>
    body {
      font-family: "Arial";
    }
    img {
      max-width: 100%;
      height: auto;
    }

    /* Hoverable nested dropdowns */
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
    .dropdown-menu {
      z-index: 1000;
    }
  </style>
</head>
<body>
  <!-- NAVBAR -->
  <nav class="navbar navbar-expand-lg navbar-light bg-light">
  <a class="navbar-brand" href="#">Client Panel</a>
  <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" 
          aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
    <span class="navbar-toggler-icon"></span>
  </button>

  <div class="collapse navbar-collapse" id="navbarSupportedContent">
    <ul class="navbar-nav mr-auto">
      <!-- Home -->
      <li class="nav-item active">
        <a class="nav-link" href="#">Home <span class="sr-only">(current)</span></a>
      </li>

      <!-- Other Profile View -->
      <li class="nav-item">
        <a class="nav-link" href="other_profile_view.php">Other Profile View</a>
      </li>

      <!-- Profile -->
      <li class="nav-item">
        <a class="nav-link" href="profile.php">Profile</a>
      </li>

      
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


  <div class="container-fluid">
    <div class="display-4 text-center mt-4">
      Hello there and welcome! <span class="text-success"><?php echo $_SESSION['username']; ?>.</span> Double click to edit your offers and then press enter to save!
    </div>
    <div class="text-center">
      <?php  
        if (isset($_SESSION['message']) && isset($_SESSION['status'])) {
          if ($_SESSION['status'] == "200") {
            echo "<h1 style='color: green;'>{$_SESSION['message']}</h1>";
          } else {
            echo "<h1 style='color: red;'>{$_SESSION['message']}</h1>"; 
          }
        }
        unset($_SESSION['message']);
        unset($_SESSION['status']);
      ?>
    </div>

    <div class="row justify-content-center">
      <div class="col-md-12">
        <?php $getProposals = $proposalObj->getProposals(); ?>
        <?php foreach ($getProposals as $proposal) { ?>
          <div class="card shadow mt-4 mb-4">
            <div class="card-body">
              <div class="row">
                <div class="col-md-6">
                  <h2><a href="other_profile_view.php?user_id=<?php echo $proposal['user_id'] ?>"><?php echo $proposal['username']; ?></a></h2>
                  <h5 class="text-muted">
                    Category: <?php echo htmlspecialchars($proposal['category_name'] ?? 'Uncategorized'); ?>
                    <?php if (!empty($proposal['subcategory_name'])): ?>
                      → <?php echo htmlspecialchars($proposal['subcategory_name']); ?>
                    <?php endif; ?>
                  </h5>
                  <img src="<?php echo '../images/'.$proposal['image']; ?>" class="img-fluid" alt="">
                  <p class="mt-4 mb-4"><?php echo $proposal['description']; ?></p>
                  <h4><i><?php echo number_format($proposal['min_price']) . " - " . number_format($proposal['max_price']);?> PHP</i></h4>
                </div>

                <div class="col-md-6">
                  <div class="card" style="height: 600px;">
                    <div class="card-header"><h2>All Offers</h2></div>
                    <div class="card-body overflow-auto">
                      <?php $getOffersByProposalID = $offerObj->getOffersByProposalID($proposal['proposal_id']); ?>
                      <?php foreach ($getOffersByProposalID as $offer) { ?>
                        <div class="offer">
                          <h4><?php echo $offer['username']; ?> <span class="text-primary">( <?php echo $offer['contact_number']; ?> )</span></h4>
                          <small><i><?php echo $offer['offer_date_added']; ?></i></small>
                          <p><?php echo $offer['description']; ?></p>

                          <?php if ($offer['user_id'] == $_SESSION['user_id']) { ?>
                            <form action="core/handleForms.php" method="POST">
                              <div class="form-group">
                                <input type="hidden" class="form-control" value="<?php echo $offer['offer_id']; ?>" name="offer_id" >
                                <input type="submit" class="btn btn-danger" value="Delete" name="deleteOfferBtn">
                              </div>
                            </form>

                            <form action="core/handleForms.php" method="POST" class="updateOfferForm d-none">
                              <div class="form-group">
                                <label for="#">Description</label>
                                <input type="text" class="form-control" value="<?php echo $offer['description']; ?>" name="description">
                                <input type="hidden" class="form-control" value="<?php echo $offer['offer_id']; ?>" name="offer_id" >
                                <input type="submit" class="btn btn-primary form-control" name="updateOfferBtn">
                              </div>
                            </form>
                          <?php } ?>
                          <hr>
                        </div>
                      <?php } ?>
                    </div>
                    <div class="card-footer">
                      <form action="core/handleForms.php" method="POST">
                        <div class="form-group">
                          <label for="#">Description</label>
                          <input type="text" class="form-control" name="description">
                          <input type="hidden" class="form-control" name="proposal_id" value="<?php echo $proposal['proposal_id']; ?>">
                          <input type="submit" class="btn btn-primary float-right mt-4" name="insertOfferBtn"> 
                        </div>
                      </form>
                    </div>
                  </div>
                </div>

              </div>
            </div>
          </div>
        <?php } ?>
      </div>
    </div>
  </div>

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
