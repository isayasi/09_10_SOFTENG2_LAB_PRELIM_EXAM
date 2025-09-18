<?php
require_once 'classloader.php';
require_once 'classes/Category.php';
require_once 'classes/Proposal.php';
require_once 'classes/Offer.php';

// Redirect if not logged in
if (!$userObj->isLoggedIn()) {
    header("Location: login.php");
    exit();
}

// Redirect admin to client page
if ($userObj->isAdmin()) {
    header("Location:index.php");
    exit();
}

// Initialize objects
$categoryObj = new Category();
$categories = $categoryObj->getCategoriesWithSubcategories();

$proposalObj = new Proposal();
$offerObj = new Offer();

// Handle new proposal creation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['createProposalBtn'])) {
    $result = $proposalObj->createProposal(
        $_SESSION['user_id'],
        $_POST['description'],
        $_FILES['image'],
        $_POST['min_price'],
        $_POST['max_price'],
        $_POST['category_id'],
        $_POST['subcategory_id']
    );

    if (!$result['success']) {
        echo "<script>alert('{$result['message']}');</script>";
    } else {
        echo "<script>alert('{$result['message']}'); window.location.reload();</script>";
    }
}

// Handle sending offer
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['createOfferBtn'])) {
    $result = $offerObj->createOffer($_SESSION['user_id'], $_POST['description'], $_POST['proposal_id']);
    if (!$result['success']) {
        echo "<script>alert('{$result['message']}');</script>";
    } else {
        echo "<script>alert('{$result['message']}'); window.location.href='your_proposals.php';</script>";
    }
}

// Fetch user proposals (with category/subcategory)
$proposals = $proposalObj->getProposalsByUserID($_SESSION['user_id']);

?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <!-- Bootstrap CSS & JS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <script src="https://code.jquery.com/jquery-3.7.0.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.min.js"></script>
    <link rel="stylesheet" href="css/your_proposals.css">
    <style>
        body { font-family: Arial; }
        .dropdown:hover > .dropdown-menu { display: block; }
        .dropright:hover > .dropdown-menu { display: block; }
    </style>
</head>
<body>

<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-light bg-light">
    <a class="navbar-brand" href="index.php">Freelancer Panel</a>
    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent">
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
    <h2 class="text-center">Your Proposals</h2>

    <!-- Flash messages -->
    <div class="text-center">
        <?php
        if (isset($_SESSION['message']) && isset($_SESSION['status'])) {
            $color = $_SESSION['status'] == '200' ? 'green' : 'red';
            echo "<h5 style='color: $color;'>{$_SESSION['message']}</h5>";
            unset($_SESSION['message'], $_SESSION['status']);
        }
        ?>
    </div>

    <!-- Create Proposal Form -->
    <div class="row justify-content-center mt-4">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header"><h4>Create New Proposal</h4></div>
                <div class="card-body">
                    <form method="POST" enctype="multipart/form-data">
                        <div class="form-group">
                            <label>Description</label>
                            <textarea class="form-control" name="description" rows="3" required></textarea>
                        </div>
                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label>Min Price</label>
                                <input type="number" class="form-control" name="min_price" required>
                            </div>
                            <div class="form-group col-md-6">
                                <label>Max Price</label>
                                <input type="number" class="form-control" name="max_price" required>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label>Category</label>
                                <select class="form-control" id="category_id" name="category_id" required>
                                    <option value="">Select a category</option>
                                    <?php
                                    foreach ($categories as $cat) {
                                        echo "<option value='{$cat['category_id']}'>{$cat['name']}</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                            <div class="form-group col-md-6">
                                <label>Subcategory</label>
                                <select class="form-control" id="subcategory_id" name="subcategory_id" required>
                                    <option value="">Select a category first</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Upload Image</label>
                            <input type="file" class="form-control-file" name="image">
                        </div>
                        <button type="submit" name="createProposalBtn" class="btn btn-primary btn-block">Create Proposal</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- User Proposals List -->
    <div class="row justify-content-center mt-4">
        <div class="col-md-8">
            <?php foreach ($proposals as $proposal): ?>
                <div class="card shadow mb-4 proposalCard">
                    <div class="card-body">
                        <h5><?php echo htmlspecialchars($proposal['username']); ?></h5>
                        <?php if(!empty($proposal['image'])): ?>
                            <img src="<?php echo "../images/".$proposal['image']; ?>" class="img-fluid mb-2">
                        <?php endif; ?>
                        <p><strong>Description:</strong> <?php echo htmlspecialchars($proposal['description']); ?></p>
                        <p><strong>Price:</strong> <?php echo number_format($proposal['min_price']).' - '.number_format($proposal['max_price']); ?></p>
                        <p><strong>Category:</strong> <?php echo htmlspecialchars($proposal['category_name'] ?? '') . " > " . htmlspecialchars($proposal['subcategory_name'] ?? ''); ?></p>

                        <!-- Send Offer -->
                        <form method="POST">
                            <input type="hidden" name="proposal_id" value="<?php echo $proposal['proposal_id']; ?>">
                            <div class="form-group">
                                <label>Offer Description</label>
                                <textarea class="form-control" name="description" placeholder="Write your offer..." required></textarea>
                            </div>
                            <button type="submit" name="createOfferBtn" class="btn btn-success">Send Offer</button>
                        </form>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<script>
    // Load subcategories dynamically
    $('#category_id').change(function() {
        var category_id = $(this).val();
        if(category_id) {
            $.ajax({
                type: 'POST',
                url: 'core/getSubcategories.php',
                data: {category_id: category_id},
                success: function(data) {
                    $('#subcategory_id').html(data);
                }
            });
        } else {
            $('#subcategory_id').html('<option value="">Select a category first</option>');
        }
    });

    // Optional: double click to show/hide update forms if needed
    $('.proposalCard').on('dblclick', function () {
        var updateForm = $(this).find('.updateProposalForm');
        if(updateForm.length) updateForm.toggleClass('d-none');
    });
</script>

</body>
</html>
