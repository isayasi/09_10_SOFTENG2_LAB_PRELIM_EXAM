<?php
// Load categories with subcategories for navbar
$category = new Category();
$categories = $category->getCategoriesWithSubcategories();
?>

<style>
    .dropdown-submenu {
      position: relative;
    }

    .dropdown-submenu .dropdown-menu {
      top: 0;
      left: 100%;
      margin-top: -1px;
    }
</style>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
  <a class="navbar-brand" href="index.php">Fiverr Clone</a>
    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNavDropdown" aria-controls="navbarNavDropdown" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>
  <div class="collapse navbar-collapse" id="navbarNavDropdown">
    <ul class="navbar-nav mr-auto">
      <li class="nav-item active">
        <a class="nav-link" href="index.php">Home</a>
      </li>
      
      <!-- Categories Dropdown -->
      <li class="nav-item dropdown">
        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdownMenuLink" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
          Categories
        </a>
        <div class="dropdown-menu" aria-labelledby="navbarDropdownMenuLink">
          <?php foreach ($categories as $category): ?>
            <?php if (!empty($category['subcategories'])): ?>
              <div class="dropdown-submenu">
                <a class="dropdown-item dropdown-toggle" href="category.php?id=<?php echo $category['category_id']; ?>">
                  <?php echo $category['name']; ?>
                </a>
                <div class="dropdown-menu">
                  <?php foreach ($category['subcategories'] as $subcategory): ?>
                    <a class="dropdown-item" href="subcategory.php?id=<?php echo $subcategory['subcategory_id']; ?>">
                      <?php echo $subcategory['name']; ?>
                    </a>
                  <?php endforeach; ?>
                </div>
              </div>
            <?php else: ?>
              <a class="dropdown-item" href="category.php?id=<?php echo $category['category_id']; ?>">
                <?php echo $category['name']; ?>
              </a>
            <?php endif; ?>
          <?php endforeach; ?>
        </div>
      </li>
      
      <?php if ($userObj->isLoggedIn()): ?>
        <?php if ($userObj->isAdmin()): ?>
          <li class="nav-item">
            <a class="nav-link" href="admin_categories.php">Manage Categories</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="../client/index.php">Client Dashboard</a>
          </li>
        <?php else: ?>
          <li class="nav-item">
            <a class="nav-link" href="yourproposal.php">Your Proposals</a>
          </li>
        <?php endif; ?>
      <?php endif; ?>
    </ul>
    
    <ul class="navbar-nav ml-auto">
      <?php if ($userObj->isLoggedIn()): ?>
        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
            <?php echo $_SESSION['username']; ?>
          </a>
          <div class="dropdown-menu" aria-labelledby="navbarDropdown">
            <a class="dropdown-item" href="profile.php">Profile</a>
            <div class="dropdown-divider"></div>
            <a class="dropdown-item" href="logout.php">Logout</a>
          </div>
        </li>
      <?php else: ?>
        <li class="nav-item">
          <a class="nav-link" href="login.php">Login</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="register.php">Register</a>
        </li>
      <?php endif; ?>
    </ul>
  </div>
</nav>

<script>
      // Enable Bootstrap dropdowns
      $(document).ready(function(){
        $('.dropdown-submenu a.dropdown-toggle').on("click", function(e){
          $(this).next('div.dropdown-menu').toggle();
          e.stopPropagation();
          e.preventDefault();
        });
      });
</script>