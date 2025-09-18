<?php require_once 'classloader.php'; 
require_once 'classes/Category.php';
?>
<?php 
if (!$userObj->isLoggedIn()) {
    header("Location: login.php");
    exit();
}

if ($userObj->isAdmin()) {
    header("Location: ../client/index.php");
    exit();
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
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" crossorigin="anonymous">
  <script src="https://code.jquery.com/jquery-3.7.0.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.min.js"></script>
  <link rel="stylesheet" href="css/offers_from_clients.css">
  <style>
    body { font-family: "Arial"; }

    /* Hover effect for dropdown menus */
    .dropdown:hover > .dropdown-menu {
      display: block;
    }
    .dropdown-submenu:hover > .dropdown-menu {
      display: block;
    }
  </style>
</head>
<body>
  <!-- Navbar -->
  <nav class="navbar navbar-expand-lg navbar-light bg-light">
    <a class="navbar-brand" href="index.php">Freelancer Panel</a>
    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" 
            aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="navbarNav">
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

      <!-- Logout button -->
      <ul class="navbar-nav">
        <li class="nav-item">
          <a class="btn btn-outline-danger" href="logout.php">Logout</a>
        </li>
      </ul>
    </div>
  </nav>

  <!-- Main content -->
  <div class="container-fluid mt-4">
    <div class="display-4 text-center">Hello there and welcome!</div>
    <div class="row justify-content-center">
      <div class="col-md-12">
        <?php $getProposalsByUserID = $proposalObj->getProposalsByUserID($_SESSION['user_id']); ?>
        <?php foreach ($getProposalsByUserID as $proposal) { ?>
          <div class="card shadow mt-4 mb-4">
            <div class="card-body">
              <div class="row">
                <div class="col-md-6">
                  <h2><a href="#"><?php echo $proposal['username']; ?></a></h2>
                  <img src="<?php echo '../images/'.$proposal['image']; ?>" class="img-fluid" alt="">
                  <p class="mt-4 mb-4"><?php echo $proposal['description']; ?></p>
                  <h4><i><?php echo number_format($proposal['min_price']) . " - " . number_format($proposal['max_price']);?> PHP</i></h4>
                  <div class="float-right">
                    <a href="#">Check out services</a>
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="card">
                    <div class="card-header"><h2>All Offers</h2></div>
                    <div class="card-body overflow-auto">
                      <?php $getOffersByProposalID = $offerObj->getOffersByProposalID($proposal['proposal_id']); ?>
                      <?php foreach ($getOffersByProposalID as $offer) { ?>
                        <div class="offer">
                          <h4>
                            <?php echo $offer['username']; ?> 
                            <span class="text-primary">( <?php echo $offer['contact_number']; ?> )</span>
                          </h4>
                          <small><i><?php echo $offer['offer_date_added']; ?></i></small>
                          <p><?php echo $offer['description']; ?></p>
                          <hr>
                        </div>
                      <?php } ?>
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
</body>
</html>
