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
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Arial', sans-serif;
        }
        
        body {
            background-color: #f5f5f5;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        
        .container {
            background-color: white;
            padding: 2rem;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 400px;
        }
        
        h2 {
            text-align: center;
            margin-bottom: 1.5rem;
            color: #333;
        }
        
        .form-group {
            margin-bottom: 1.5rem;
        }
        
        label {
            display: block;
            margin-bottom: 0.5rem;
            color: #555;
            font-weight: 600;
        }
        
        input {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 1rem;
            transition: border-color 0.3s;
        }
        
        input:focus {
            outline: none;
            border-color: #4a90e2;
        }
        
        .btn {
            display: inline-block;
            background-color: #4a90e2;
            color: white;
            border: none;
            padding: 0.75rem 1.5rem;
            border-radius: 4px;
            cursor: pointer;
            font-size: 1rem;
            font-weight: 600;
            width: 100%;
            transition: background-color 0.3s;
        }
        
        .btn:hover {
            background-color: #3a7bc8;
        }
        
        .btn-primary {
            background-color: #4a90e2;
        }
        
        .btn-primary:hover {
            background-color: #3a7bc8;
        }
        
        p[style="color: red;"] {
            text-align: center;
            margin-bottom: 1rem;
            padding: 0.5rem;
            background-color: #ffebee;
            border-radius: 4px;
        }
        
        @media (max-width: 480px) {
            .container {
                margin: 1rem;
                padding: 1.5rem;
            }
        }
    </style>
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