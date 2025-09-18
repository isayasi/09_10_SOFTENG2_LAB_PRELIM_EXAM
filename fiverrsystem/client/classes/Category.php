<?php
require_once __DIR__ . '/Database.php';

class Category extends Database {

    public function createCategory($name, $description) {
        $query = "INSERT INTO categories (name, description) VALUES (?, ?)";
        $result = $this->executeNonQuery($query, [$name, $description]);
        return $result 
            ? ['success' => true, 'message' => 'Category created successfully']
            : ['success' => false, 'message' => 'Failed to create category'];
    }

    public function createSubcategory($category_id, $name, $description) {
        $query = "INSERT INTO subcategories (category_id, name, description) VALUES (?, ?, ?)";
        $result = $this->executeNonQuery($query, [$category_id, $name, $description]);
        return $result 
            ? ['success' => true, 'message' => 'Subcategory created successfully']
            : ['success' => false, 'message' => 'Failed to create subcategory'];
    }

    public function getAllCategories() {
        $query = "SELECT * FROM categories ORDER BY name";
        return $this->executeQuery($query);
    }

    public function getSubcategoriesByCategory($category_id) {
        $query = "SELECT * FROM subcategories WHERE category_id = ? ORDER BY name";
        return $this->executeQuery($query, [$category_id]);
    }

    public function getCategoriesWithSubcategories() {
        $categories = $this->getAllCategories();
        foreach ($categories as &$category) {
            $category['subcategories'] = $this->getSubcategoriesByCategory($category['category_id']);
        }
        return $categories;
    }

    public function getCategoryName($category_id) {
        $query = "SELECT name FROM categories WHERE category_id = ?";
        $result = $this->executeQuerySingle($query, [$category_id]);
        return $result ? $result['name'] : 'Unknown';
    }

    public function getSubcategoryName($subcategory_id) {
        $query = "SELECT name FROM subcategories WHERE subcategory_id = ?";
        $result = $this->executeQuerySingle($query, [$subcategory_id]);
        return $result ? $result['name'] : 'Unknown';
    }

    // ✅ New method: fetch category_id from subcategory_id
    public function getCategoryIdBySubcategory($subcategory_id) {
        $query = "SELECT category_id FROM subcategories WHERE subcategory_id = ?";
        $result = $this->executeQuerySingle($query, [$subcategory_id]);
        return $result ? $result['category_id'] : null;
    }
}
?>
