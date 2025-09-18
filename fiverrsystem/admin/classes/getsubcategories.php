<?php
require_once '../classloader.php';

if (isset($_POST['category_id'])) {
    $category = new Category();
    $subcategories = $category->getSubcategoriesByCategory($_POST['category_id']);
    
    echo '<option value="">Select a subcategory</option>';
    foreach ($subcategories as $subcat) {
        echo "<option value='{$subcat['subcategory_id']}'>{$subcat['name']}</option>";
    }
}
?>