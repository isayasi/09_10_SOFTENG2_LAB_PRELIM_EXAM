<?php  
/**
 * Class for handling Proposal-related operations.
 * Inherits CRUD methods from the Database class.
 */
class Proposal extends Database {
    
    /**
     * Creates a new Proposal.
     */
    public function createProposal($user_id, $description, $image, $min_price, $max_price, $category_id, $subcategory_id) {
        $sql = "INSERT INTO proposals (user_id, description, image, min_price, max_price, category_id, subcategory_id) 
                VALUES (?, ?, ?, ?, ?, ?, ?)";
        return $this->executeNonQuery($sql, [
            $user_id, $description, $image, $min_price, $max_price, $category_id, $subcategory_id
        ]);
    }

    /**
     * Retrieves Proposals.
     */
    public function getProposals($withUser = false) {
            if ($withUser) {
                $query = "SELECT p.proposal_id, p.user_id, p.description, p.image, 
                                p.min_price, p.max_price, p.subcategory_id, 
                                p.date_added AS proposals_date_added, 
                                u.username
                            FROM proposals p
                            JOIN fiverr_clone_users u ON p.user_id = u.user_id
                            ORDER BY p.date_added DESC";
            } else {
                $query = "SELECT proposal_id, user_id, description, image, 
                                min_price, max_price, subcategory_id, proposals_date_added 
                        FROM proposals
                        ORDER BY proposals_date_added DESC";
            }

            return $this->executeQuery($query);
        }


    /**
     * Get proposals by user.
     */
    public function getProposalsByUserID($user_id) {
        $sql = "SELECT proposals.*, fiverr_clone_users.*, 
                       proposals.date_added AS proposals_date_added
                FROM proposals 
                JOIN fiverr_clone_users ON proposals.user_id = fiverr_clone_users.user_id
                WHERE proposals.user_id = ?
                ORDER BY proposals.date_added DESC";
        return $this->executeQuery($sql, [$user_id]);
    }

    /**
     * Updates a Proposal.
     */
    public function updateProposal($description, $min_price, $max_price, $proposal_id, $image="") {
        if (!empty($image)) {
            $sql = "UPDATE proposals 
                    SET description = ?, image = ?, min_price = ?, max_price = ? 
                    WHERE proposal_id = ?";
            return $this->executeNonQuery($sql, [$description, $image, $min_price, $max_price, $proposal_id]);
        } else {
            $sql = "UPDATE proposals 
                    SET description = ?, min_price = ?, max_price = ? 
                    WHERE proposal_id = ?";
            return $this->executeNonQuery($sql, [$description, $min_price, $max_price, $proposal_id]);  
        }
    }

    public function addViewCount($proposal_id) {
        $sql = "UPDATE proposals SET view_count = view_count + 1 WHERE proposal_id = ?";
        return $this->executeNonQuery($sql, [$proposal_id]);
    }

    public function deleteProposal($id) {
        $sql = "DELETE FROM proposals WHERE proposal_id = ?";
        return $this->executeNonQuery($sql, [$id]);
    }
}
?>
