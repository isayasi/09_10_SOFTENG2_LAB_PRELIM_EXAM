<?php  

require_once 'Database.php';
/**
 * Class for handling User-related operations.
 * Inherits CRUD methods from the Database class.
 */
class User extends Database {

    /**
     * Starts a new session if one isn't already active.
     */
    public function startSession() {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
    }

    /**
     * Checks if the username already exists in the database.
     * @param string $username The username to check.
     * @return bool True if username exists, false otherwise.
     */
    public function usernameExists($username) {
        $sql = "SELECT COUNT(*) as username_count FROM fiverr_clone_users WHERE username = ?";
        $count = $this->executeQuerySingle($sql, [$username]);
        return $count['username_count'] > 0;
    }

    /**
     * Registers a new user.
     */
    public function registerUser($username, $email, $password, $contact_number, $is_client = 1) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $sql = "INSERT INTO fiverr_clone_users (username, email, password, is_client, contact_number) 
                VALUES (?, ?, ?, ?, ?)";
        try {
            $this->executeNonQuery($sql, [$username, $email, $hashed_password, $is_client, $contact_number]);
            return true;
        } catch (\PDOException $e) {
            return false;
        }
    }

    /**
     * Logs in a user by verifying credentials.
     */
    public function loginUser($email, $password) {
        $sql = "SELECT user_id, username, password, is_client, is_admin 
                FROM fiverr_clone_users WHERE email = ?";
        $user = $this->executeQuerySingle($sql, [$email]);

        if ($user && password_verify($password, $user['password'])) {
            $this->startSession();
            $_SESSION['user_id']   = $user['user_id'];
            $_SESSION['username']  = $user['username'];
            $_SESSION['is_client'] = (bool)$user['is_client'];
            $_SESSION['is_admin']  = (bool)$user['is_admin'];
            return true;
        }
        return false;
    }

    /**
     * Checks if a user is currently logged in.
     */
    public function isLoggedIn() {
        $this->startSession();
        return isset($_SESSION['user_id']);
    }

    /**
     * Checks if the logged-in user is an admin.
     */
    public function isAdmin() {
        $this->startSession();
        if (isset($_SESSION['user_id'])) {
            $sql = "SELECT is_admin FROM fiverr_clone_users WHERE user_id = ?";
            $user = $this->executeQuerySingle($sql, [$_SESSION['user_id']]);
            return $user && $user['is_admin'] == 1;
        }
        return false;
    }

    /**
     * Logs out the current user.
     */
    public function logout() {
        $this->startSession();
        session_unset();
        session_destroy();
    }

    /**
     * Retrieves users from the database.
     */
    public function getUsers($id = null) {
        if ($id) {
            $sql = "SELECT * FROM fiverr_clone_users WHERE user_id = ?";
            return $this->executeQuerySingle($sql, [$id]);
        }
        $sql = "SELECT * FROM fiverr_clone_users";
        return $this->executeQuery($sql);
    }

    /**
     * Updates a user's information.
     */
    public function updateUser($contact_number, $bio_description, $user_id, $display_picture="") {
        if (empty($display_picture)) {
            $sql = "UPDATE fiverr_clone_users 
                    SET contact_number = ?, bio_description = ? 
                    WHERE user_id = ?";
            return $this->executeNonQuery($sql, [$contact_number, $bio_description, $user_id]);
        }
    }

    /**
     * Deletes a user.
     */
    public function deleteUser($id) {
        $sql = "DELETE FROM fiverr_clone_users WHERE user_id = ?";
        return $this->executeNonQuery($sql, [$id]);
    }

    /**
     * Handles admin login and redirects.  // NEW
     */
    public function adminLogin($email, $password) {
        if ($this->loginUser($email, $password)) {
            if ($_SESSION['is_admin']) {
                header("Location: ../admin_categories.php");
                exit();
            } else {
                header("Location: login.php?error=Access+denied&debug=Not+an+admin");
                exit();
            }
        } else {
            header("Location: login.php?error=Invalid+credentials&debug=Login+failed");
            exit();
        }
    }
}

// --------- Handle form POST directly here (NEW) ----------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['do_admin_login'])) {
    $user = new User();
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $user->adminLogin($email, $password);
}

?>
