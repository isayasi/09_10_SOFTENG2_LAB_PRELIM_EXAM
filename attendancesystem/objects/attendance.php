<?php
class Attendance {
    private $conn;
    private $table_name = "attendance";

    public $id;
    public $student_id;
    public $course_id;
    public $date;
    public $time_in;
    public $status;
    public $created_at;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function create() {
        $query = "INSERT INTO " . $this->table_name . " SET student_id=:student_id, course_id=:course_id, date=:date, time_in=:time_in, status=:status";
        $stmt = $this->conn->prepare($query);

        $this->student_id = htmlspecialchars(strip_tags($this->student_id));
        $this->course_id = htmlspecialchars(strip_tags($this->course_id));
        $this->date = htmlspecialchars(strip_tags($this->date));
        $this->time_in = htmlspecialchars(strip_tags($this->time_in));
        $this->status = htmlspecialchars(strip_tags($this->status));

        $stmt->bindParam(":student_id", $this->student_id);
        $stmt->bindParam(":course_id", $this->course_id);
        $stmt->bindParam(":date", $this->date);
        $stmt->bindParam(":time_in", $this->time_in);
        $stmt->bindParam(":status", $this->status);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    public function getAttendanceByStudentId($student_id) {
        $query = "SELECT a.*, c.course_code, c.course_name FROM " . $this->table_name . " a LEFT JOIN courses c ON a.course_id = c.id WHERE a.student_id = ? ORDER BY a.date DESC, a.time_in DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $student_id);
        $stmt->execute();
        return $stmt;
    }

    public function getAttendanceByCourseAndYear($course_program, $year_level) {
        $query = "SELECT a.*, s.first_name, s.last_name, s.course_program, s.year_level, c.course_code, c.course_name 
                  FROM " . $this->table_name . " a 
                  LEFT JOIN students s ON a.student_id = s.id 
                  LEFT JOIN courses c ON a.course_id = c.id 
                  WHERE s.course_program = ? AND s.year_level = ? 
                  ORDER BY a.date DESC, a.time_in DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $course_program);
        $stmt->bindParam(2, $year_level);
        $stmt->execute();
        return $stmt;
    }

    public function checkIfAlreadyAttended($student_id, $course_id, $date) {
        $query = "SELECT id FROM " . $this->table_name . " WHERE student_id = ? AND course_id = ? AND date = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $student_id);
        $stmt->bindParam(2, $course_id);
        $stmt->bindParam(3, $date);
        $stmt->execute();
        return $stmt->rowCount() > 0;
    }

    public function getAllAttendance() {
    $query = "SELECT a.*, s.first_name, s.last_name, c.course_name 
              FROM " . $this->table_name . " a 
              LEFT JOIN students s ON a.student_id = s.id 
              LEFT JOIN courses c ON a.course_id = c.id 
              ORDER BY a.date DESC, a.time_in DESC";
    
    $stmt = $this->conn->prepare($query);
    $stmt->execute();
    
    return $stmt;
    }
}
?>