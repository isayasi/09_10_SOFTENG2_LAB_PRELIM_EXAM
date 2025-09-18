<?php
class Course {
    private $conn;
    private $table_name = "courses";

    public $id;
    public $course_code;
    public $course_name;
    public $created_by;
    public $created_at;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function create() {
        $query = "INSERT INTO " . $this->table_name . " SET course_code=:course_code, course_name=:course_name, created_by=:created_by";
        $stmt = $this->conn->prepare($query);

        $this->course_code = htmlspecialchars(strip_tags($this->course_code));
        $this->course_name = htmlspecialchars(strip_tags($this->course_name));
        $this->created_by = htmlspecialchars(strip_tags($this->created_by));

        $stmt->bindParam(":course_code", $this->course_code);
        $stmt->bindParam(":course_name", $this->course_name);
        $stmt->bindParam(":created_by", $this->created_by);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    public function update() {
        $query = "UPDATE " . $this->table_name . " SET course_code=:course_code, course_name=:course_name WHERE id=:id";
        $stmt = $this->conn->prepare($query);

        $this->course_code = htmlspecialchars(strip_tags($this->course_code));
        $this->course_name = htmlspecialchars(strip_tags($this->course_name));
        $this->id = htmlspecialchars(strip_tags($this->id));

        $stmt->bindParam(":course_code", $this->course_code);
        $stmt->bindParam(":course_name", $this->course_name);
        $stmt->bindParam(":id", $this->id);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }

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

    public function read() {
        $query = "SELECT c.*, u.username as created_by_name FROM " . $this->table_name . " c LEFT JOIN users u ON c.created_by = u.id ORDER BY c.created_at DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    public function getCourseById($id) {
        $query = "SELECT * FROM " . $this->table_name . " WHERE id = ? LIMIT 0,1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $id);
        $stmt->execute();
        
        if($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $this->id = $row['id'];
            $this->course_code = $row['course_code'];
            $this->course_name = $row['course_name'];
            $this->created_by = $row['created_by'];
            $this->created_at = $row['created_at'];
            return true;
        }
        return false;
    }
}
?>