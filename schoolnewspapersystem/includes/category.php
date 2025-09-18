<?php
class Category {
    private $conn;
    private $table_name = "categories";

    public $id;
    public $name;
    public $description;
    public $created_by;
    public $created_at;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Create a new category
    public function create() {
        $query = "INSERT INTO " . $this->table_name . " 
                 SET name=:name, description=:description, created_by=:created_by";
        
        $stmt = $this->conn->prepare($query);
        
        $this->name = htmlspecialchars(strip_tags($this->name));
        $this->description = htmlspecialchars(strip_tags($this->description));
        $this->created_by = htmlspecialchars(strip_tags($this->created_by));
        
        $stmt->bindParam(":name", $this->name);
        $stmt->bindParam(":description", $this->description);
        $stmt->bindParam(":created_by", $this->created_by);
        
        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    // Retrieve all categories
    public function read() {
        $query = "SELECT c.*, u.username as created_by_name 
                 FROM " . $this->table_name . " c 
                 LEFT JOIN users u ON c.created_by = u.id 
                 ORDER BY c.name ASC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        
        return $stmt;
    }

    // Retrieve a single category by ID
    public function readOne() {
        $query = "SELECT c.*, u.username as created_by_name 
                 FROM " . $this->table_name . " c 
                 LEFT JOIN users u ON c.created_by = u.id 
                 WHERE c.id = ? LIMIT 0,1";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id);
        $stmt->execute();
        
        if($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $this->name = $row['name'];
            $this->description = $row['description'];
            $this->created_by = $row['created_by'];
            $this->created_at = $row['created_at'];
            return true;
        }
        return false;
    }

    // Update an existing category
    public function update() {
        $query = "UPDATE " . $this->table_name . " 
                 SET name=:name, description=:description 
                 WHERE id=:id";
        
        $stmt = $this->conn->prepare($query);
        
        $this->name = htmlspecialchars(strip_tags($this->name));
        $this->description = htmlspecialchars(strip_tags($this->description));
        $this->id = htmlspecialchars(strip_tags($this->id));
        
        $stmt->bindParam(":name", $this->name);
        $stmt->bindParam(":description", $this->description);
        $stmt->bindParam(":id", $this->id);
        
        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    // Delete a category
    public function delete() {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $this->id = htmlspecialchars(strip_tags($this->id));
        $stmt->bindParam(1, $this->id);
        
        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    // Retrieve categories linked to an article
    public function getArticleCategories($article_id) {
        $query = "SELECT c.* FROM categories c 
                 INNER JOIN article_categories ac ON c.id = ac.category_id 
                 WHERE ac.article_id = :article_id 
                 ORDER BY c.name ASC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":article_id", $article_id);
        $stmt->execute();
        
        return $stmt;
    }

    // Link a category to an article
    public function addToArticle($article_id, $category_id) {
        $query = "INSERT INTO article_categories (article_id, category_id) 
                 VALUES (:article_id, :category_id)";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":article_id", $article_id);
        $stmt->bindParam(":category_id", $category_id);
        
        try {
            return $stmt->execute();
        } catch (PDOException $e) {
            // Prevent duplicate entries (category already assigned)
            return false;
        }
    }

    // Unlink a category from an article
    public function removeFromArticle($article_id, $category_id) {
        $query = "DELETE FROM article_categories 
                 WHERE article_id = :article_id AND category_id = :category_id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":article_id", $article_id);
        $stmt->bindParam(":category_id", $category_id);
        
        return $stmt->execute();
    }
}
?>
