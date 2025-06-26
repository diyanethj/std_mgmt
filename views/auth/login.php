<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once __DIR__ . '/../../backend/config/db_connect.php';
require_once __DIR__ . '/../../backend/controllers/AuthController.php';
$auth = new AuthController($pdo);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($auth->login($_POST['username'], $_POST['password'])) {
        error_log("Login successful, role: " . $_SESSION['role']);
        $role = $_SESSION['role'];
        $dashboard = $role === 'marketing_user' ? '/std_mgmt/views/marketing_user/dashboard.php' : "/std_mgmt/views/$role/dashboard.php";
        header("Location: $dashboard");
        exit;
    } else {
        $error = "Invalid credentials";
        error_log("Login failed for username: " . $_POST['username']);
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Login</title>
    <link rel="stylesheet" href="/std_mgmt/css/style.css">
</head>
<body>
    <div class="container">
        <h2>Login</h2>
        <?php if (isset($error)): ?>
            <p style="color: red;"><?php echo htmlspecialchars($error); ?></p>
        <?php endif; ?>
        <form method="POST" action="/std_mgmt/login">
            <div class="form-group">
                <label>Username</label>
                <input type="text" name="username" required>
            </div>
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" required>
            </div>
            <button type="submit" class="btn btn-primary">Login</button>
        </form>
    </div>
</body>
</html>