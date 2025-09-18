<?php  
/**
 * Class for handling Proposal-related operations.
 * Inherits CRUD methods from the Database class.
 */
class Proposal extends Database {
    private $db;
    
    public function __construct() {
        // Initialize database connection if needed
        $this->db = new Database();
    }
    
    /**
     * Creates a new Proposal.
     * @param int $user_id The ID of the user creating the proposal
     * @param string $description The proposal description
     * @param string $image The image file path
     * @param int $min_price The minimum price
     * @param int $max_price The maximum price
     * @param int $category_id The category ID
     * @param int $subcategory_id The subcategory ID
     * @return array Result with success status and message
     */
    public function createProposal($user_id, $description, $image, $min_price, $max_price, $category_id, $subcategory_id) {
        // Handle file upload
        $imagePath = $this->uploadImage($image);
        
        if (!$imagePath) {
            return ['success' => false, 'message' => 'Failed to upload image'];
        }
        
        $query = "INSERT INTO proposals (user_id, description, image, min_price, max_price, category_id, subcategory_id) 
                VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param("issiiii", $user_id, $description, $imagePath, $min_price, $max_price, $category_id, $subcategory_id);
        
        if ($stmt->execute()) {
            return ['success' => true, 'message' => 'Proposal created successfully'];
        } else {
            return ['success' => false, 'message' => 'Failed to create proposal'];
        }
    }

    /**
     * Retrieves Proposals by User ID from the database.
     * @param int $user_id The User ID to retrieve proposals for
     * @return array Array of proposals with user, category, and subcategory info
     */
    public function getProposalsByUserID($user_id) {
        $query = "SELECT p.*, u.username, c.name as category_name, s.name as subcategory_name 
                FROM proposals p 
                JOIN fiverr_clone_users u ON p.user_id = u.user_id 
                LEFT JOIN categories c ON p.category_id = c.category_id 
                LEFT JOIN subcategories s ON p.subcategory_id = s.subcategory_id 
                WHERE p.user_id = ? 
                ORDER BY p.date_added DESC";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Updates a Proposal.
     * @param string $description The new description
     * @param int $min_price The new minimum price
     * @param int $max_price The new maximum price
     * @param int $proposal_id The Proposal ID to update
     * @param string $image The new image file path (optional)
     * @return int The number of affected rows
     */
    public function updateProposal($description, $min_price, $max_price, $proposal_id, $image = "") {
        if (!empty($image)) {
            $sql = "UPDATE proposals SET description = ?, image = ?, min_price = ?, max_price = ? WHERE proposal_id = ?";
            return $this->executeNonQuery($sql, [$description, $image, $min_price, $max_price, $proposal_id]);
        } else {
            $sql = "UPDATE proposals SET description = ?, min_price = ?, max_price = ? WHERE proposal_id = ?";
            return $this->executeNonQuery($sql, [$description, $min_price, $max_price, $proposal_id]);  
        }
    }

    /**
     * Increments the view count for a proposal.
     * @param int $proposal_id The Proposal ID to update
     * @return int The number of affected rows
     */
    public function addViewCount($proposal_id) {
        $sql = "UPDATE proposals SET view_count = view_count + 1 WHERE proposal_id = ?";
        return $this->executeNonQuery($sql, [$proposal_id]);
    }

    /**
     * Deletes a Proposal.
     * @param int $id The Proposal ID to delete
     * @return int The number of affected rows
     */
    public function deleteProposal($id) {
        $sql = "DELETE FROM proposals WHERE proposal_id = ?";
        return $this->executeNonQuery($sql, [$id]);
    }

    /**
     * Handles image upload for proposals
     * @param array $image The image file from $_FILES
     * @return string|bool The file path on success, false on failure
     */
    private function uploadImage($image) {
        // Implementation for image upload
        // This should handle file validation, moving to the correct directory, etc.
        $targetDir = "../images/";
        $fileName = uniqid() . '_' . basename($image["name"]);
        $targetFilePath = $targetDir . $fileName;
        
        // Check if image file is an actual image
        $check = getimagesize($image["tmp_name"]);
        if($check === false) {
            return false;
        }
        
        // Check file size (limit to 2MB)
        if ($image["size"] > 2000000) {
            return false;
        }
        
        // Allow certain file formats
        $imageFileType = strtolower(pathinfo($targetFilePath, PATHINFO_EXTENSION));
        if(!in_array($imageFileType, ["jpg", "jpeg", "png", "gif"])) {
            return false;
        }
        
        // Try to upload file
        if (move_uploaded_file($image["tmp_name"], $targetFilePath)) {
            return $fileName;
        } else {
            return false;
        }
    }
}
?>