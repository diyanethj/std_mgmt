<?php
class AuthController {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function login($username, $password) {
        $stmt = $this->pdo->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['role'] = $user['role'];
            return true;
        }
        return false;
    }

    public function getCurrentUser() {
        if (isset($_SESSION['user_id'])) {
            $stmt = $this->pdo->prepare("SELECT * FROM users WHERE id = ?");
            $stmt->execute([$_SESSION['user_id']]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        }
        return null;
    }

    public function getUsersByRole($role) {
        $stmt = $this->pdo->prepare("SELECT id, username FROM users WHERE role = ?");
        $stmt->execute([$role]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function logout() {
        session_start(); // Ensure session is started
        session_unset(); // Remove all session variables
        session_destroy(); // Destroy the session
        setcookie(session_name(), '', time() - 3600, '/'); // Delete the session cookie
        header('Location: /std_mgmt/views/auth/login.php'); // Redirect to login page
        exit; // Ensure script stops
    }
}