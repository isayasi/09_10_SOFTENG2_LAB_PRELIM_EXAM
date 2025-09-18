<?php
class ExcuseLetter {
    private $conn;
    private $table_name = "excuse_letters";

    public $id;
    public $student_id;
    public $course_id;
    public $absence_date;
    public $submission_date;
    public $reason;
    public $supporting_document;
    public $status;
    public $admin_notes;
    public $reviewed_by;
    public $reviewed_at;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Create new excuse letter
    public function create() {
        $query = "INSERT INTO " . $this->table_name . " 
                 (student_id, course_id, absence_date, reason, supporting_document) 
                 VALUES (:student_id, :course_id, :absence_date, :reason, :supporting_document)";
        
        $stmt = $this->conn->prepare($query);
        
        // Sanitize input
        $this->student_id = htmlspecialchars(strip_tags($this->student_id));
        $this->course_id = htmlspecialchars(strip_tags($this->course_id));
        $this->absence_date = htmlspecialchars(strip_tags($this->absence_date));
        $this->reason = htmlspecialchars(strip_tags($this->reason));
        $this->supporting_document = htmlspecialchars(strip_tags($this->supporting_document));
        
        // Bind parameters
        $stmt->bindParam(":student_id", $this->student_id);
        $stmt->bindParam(":course_id", $this->course_id);
        $stmt->bindParam(":absence_date", $this->absence_date);
        $stmt->bindParam(":reason", $this->reason);
        $stmt->bindParam(":supporting_document", $this->supporting_document);
        
        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    // Get excuse letters by student
    public function getByStudent($student_id) {
        $query = "SELECT el.*, c.course_code, c.course_name 
                 FROM " . $this->table_name . " el 
                 LEFT JOIN courses c ON el.course_id = c.id 
                 WHERE el.student_id = :student_id 
                 ORDER BY el.submission_date DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":student_id", $student_id);
        $stmt->execute();
        
        return $stmt;
    }

    // Get excuse letters by status and program
    public function getByStatusAndProgram($status, $program = null) {
        try {
            $query = "SELECT el.*, s.first_name, s.last_name, s.course_program 
                    FROM excuse_letters el 
                    INNER JOIN students s ON el.student_id = s.id 
                    WHERE el.status = :status";
            
            $params = [':status' => $status];
            
            // Add program filter if specified
            if (!empty($program)) {
                $query .= " AND s.course_program = :program";
                $params[':program'] = $program;
            }
            
            $query .= " ORDER BY el.submission_date DESC";
            
            $stmt = $this->conn->prepare($query);
            $stmt->execute($params);
            
            return $stmt;
            
        } catch (PDOException $e) {
            error_log("Database error in getByStatusAndProgram: " . $e->getMessage());
            // Return empty result set on error
            return new PDOStatement();
        }
    }

    // Update excuse letter status
    public function updateStatus($excuse_id, $status, $reviewed_by) {
    $query = "UPDATE " . $this->table_name . " 
             SET status = :status, reviewed_by = :reviewed_by, reviewed_at = NOW() 
             WHERE id = :id";
    
    $stmt = $this->conn->prepare($query);
    
    $stmt->bindParam(":status", $status);
    $stmt->bindParam(":reviewed_by", $reviewed_by);
    $stmt->bindParam(":id", $excuse_id);
    
    return $stmt->execute();
    }

    // Get excuse letter by ID
    public function getById($excuse_id) {
        $query = "SELECT el.*, s.first_name, s.last_name, s.course_program, 
                         c.course_code, c.course_name, u.username as admin_reviewer
                 FROM " . $this->table_name . " el 
                 LEFT JOIN students s ON el.student_id = s.id 
                 LEFT JOIN courses c ON el.course_id = c.id 
                 LEFT JOIN users u ON el.reviewed_by = u.id 
                 WHERE el.id = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $excuse_id);
        $stmt->execute();
        
        return $stmt;
    }
 
}
?>