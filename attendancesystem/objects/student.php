<?php
class Student {
    private $conn;
    private $table_name = "students";

    public $id;
    public $user_id;
    public $first_name;
    public $last_name;
    public $course_program;
    public $year_level;
    public $course_id;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function create() {
    // Check if course_id column exists
    $column_check = $this->conn->query("SHOW COLUMNS FROM students LIKE 'course_id'");
    $course_id_exists = ($column_check->rowCount() > 0);
    
        if ($course_id_exists) {
            $query = "INSERT INTO " . $this->table_name . " 
                    SET user_id=:user_id, first_name=:first_name, 
                    last_name=:last_name, course_program=:course_program, 
                    year_level=:year_level, course_id=:course_id";
        } else {
            $query = "INSERT INTO " . $this->table_name . " 
                    SET user_id=:user_id, first_name=:first_name, 
                    last_name=:last_name, course_program=:course_program, 
                    year_level=:year_level";
        }
        
        $stmt = $this->conn->prepare($query);

        $this->user_id = htmlspecialchars(strip_tags($this->user_id));
        $this->first_name = htmlspecialchars(strip_tags($this->first_name));
        $this->last_name = htmlspecialchars(strip_tags($this->last_name));
        $this->course_program = htmlspecialchars(strip_tags($this->course_program));
        $this->year_level = htmlspecialchars(strip_tags($this->year_level));

        $stmt->bindParam(":user_id", $this->user_id);
        $stmt->bindParam(":first_name", $this->first_name);
        $stmt->bindParam(":last_name", $this->last_name);
        $stmt->bindParam(":course_program", $this->course_program);
        $stmt->bindParam(":year_level", $this->year_level);
        
        if ($course_id_exists) {
            $this->course_id = htmlspecialchars(strip_tags($this->course_id));
            $stmt->bindParam(":course_id", $this->course_id);
        }

        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    public function getStudentByUserId($user_id) {
    // First, check if course_id column exists
    $column_check = $this->conn->query("SHOW COLUMNS FROM students LIKE 'course_id'");
    $course_id_exists = ($column_check->rowCount() > 0);
    
    if ($course_id_exists) {
        $query = "SELECT s.*, c.course_code, c.course_name 
                  FROM " . $this->table_name . " s 
                  LEFT JOIN courses c ON s.course_id = c.id 
                  WHERE s.user_id = :user_id";
    } else {
        $query = "SELECT s.*, '' as course_code, '' as course_name 
                  FROM " . $this->table_name . " s 
                  WHERE s.user_id = :user_id";
    }
    
    $stmt = $this->conn->prepare($query);
    $stmt->bindParam(":user_id", $user_id);
    $stmt->execute();
    
    if($stmt->rowCount() > 0) {
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Set object properties
        $this->id = $row['id'];
        $this->user_id = $row['user_id'];
        $this->first_name = $row['first_name'];
        $this->last_name = $row['last_name'];
        $this->course_program = $row['course_program'];
        $this->year_level = $row['year_level'];
        
        // Set course_id if it exists
        if ($course_id_exists && isset($row['course_id'])) {
            $this->course_id = $row['course_id'];
        }
        
        // For backward compatibility, ensure course_program is set
        if (empty($this->course_program) && !empty($row['course_code'])) {
            $this->course_program = $row['course_code'];
        }
        
        return true;
    }

    return false;
}

    public function getAllStudents() {
        $query = "SELECT s.*, u.username, c.course_code, c.course_name 
                  FROM " . $this->table_name . " s 
                  LEFT JOIN users u ON s.user_id = u.id 
                  LEFT JOIN courses c ON s.course_id = c.id";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }
    
    // Get student's course information
    public function getStudentCourse() {
        if (!$this->id) return false;
        
        $query = "SELECT c.* FROM " . $this->table_name . " s 
                  LEFT JOIN courses c ON s.course_id = c.id 
                  WHERE s.id = :student_id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":student_id", $this->id);
        $stmt->execute();
        
        if ($stmt->rowCount() > 0) {
            return $stmt->fetch(PDO::FETCH_ASSOC);
        }
        
        return false;
    }
    
    public function getCourseProgram() {
        return $this->course_program;
    }

    // Update student's course information
    public function updateCourse($course_id) {
        $query = "UPDATE " . $this->table_name . " 
                  SET course_id = :course_id 
                  WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":course_id", $course_id);
        $stmt->bindParam(":id", $this->id);
        
        if($stmt->execute()) {
            $this->course_id = $course_id;
            return true;
        }
        return false;
    }

    // Get students by course program
    public function getStudentsByProgram($program) {
        $query = "SELECT s.*, u.username 
                  FROM " . $this->table_name . " s 
                  LEFT JOIN users u ON s.user_id = u.id 
                  WHERE s.course_program = :program 
                  OR s.course_id IN (SELECT id FROM courses WHERE course_code = :program)";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":program", $program);
        $stmt->execute();
        
        return $stmt;
    }
}
?>