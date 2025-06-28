<?php
session_start();
require_once __DIR__ . '/../../backend/config/db_connect.php';
if (!isset($pdo)) {
    error_log("PDO not defined in header.php");
    die("Database connection error");
}
require_once __DIR__ . '/../../backend/controllers/AuthController.php';
$auth = new AuthController($pdo);
$user = $auth->getCurrentUser();
if (!$user) {
    header('Location: /std_mgmt/views/auth/login.php');
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Student Management System</title>
    <link rel="stylesheet" href="/std_mgmt/css/style.css">
</head>
<body>
    <div class="container">
        <h1>Welcome, <?php echo htmlspecialchars($user['username']); ?> (<?php echo htmlspecialchars($user['role']); ?>)</h1>
        <nav>
            <?php if ($user['role'] === 'admin'): ?>
                <a href="/std_mgmt/views/admin/dashboard.php">Dashboard</a>
                <a href="/std_mgmt/views/admin/upload_leads.php">Upload Leads</a>
                <a href="/std_mgmt/views/admin/leads_list.php">Leads List</a>
                <a href="/std_mgmt/views/admin/assigned_leads.php">Assigned Leads</a>
                <a href="/std_mgmt/views/admin/pending_registrations.php">Pending Registrations</a>
                <a href="/std_mgmt/views/admin/registered_leads.php">Registered Leads</a>
                <a href="/std_mgmt/views/admin/declined_leads.php">Declined Leads</a>
            <?php elseif ($user['role'] === 'marketing_user'): ?>
                <a href="/std_mgmt/views/marketing_user/dashboard.php">Dashboard</a>
                <a href="/std_mgmt/views/marketing_user/assigned_leads.php">Assigned Leads</a>
                <a href="/std_mgmt/views/marketing_user/pending_registrations.php">Pending Registrations</a>
                <a href="/std_mgmt/views/marketing_user/registered_leads.php">Registered Leads</a>
                <a href="/std_mgmt/views/marketing_user/declined_leads.php">Declined Leads</a>
            <?php elseif ($user['role'] === 'finance_user'): ?>
                <a href="/std_mgmt/views/finance_user/dashboard.php">Dashboard</a>
                <a href="/std_mgmt/views/finance_user/registered_leads.php">Registered Leads</a>
            <?php elseif ($user['role'] === 'marketing_manager'): ?>
                <a href="/std_mgmt/views/marketing_manager/dashboard.php">Dashboard</a>
                <a href="/std_mgmt/views/marketing_manager/upload_leads.php">Upload Leads</a>
                <a href="/std_mgmt/views/marketing_manager/leads_list.php">Leads List</a>
                <a href="/std_mgmt/views/marketing_manager/assigned_leads.php">Assigned Leads</a>
                <a href="/std_mgmt/views/marketing_manager/pending_registrations.php">Pending Registrations</a>
                <a href="/std_mgmt/views/marketing_manager/registered_leads.php">Registered Leads</a>
                <a href="/std_mgmt/views/marketing_manager/declined_leads.php">Declined Leads</a>
            <?php elseif ($user['role'] === 'academic_user'): ?>
                <a href="/std_mgmt/views/academic_user/dashboard.php">Dashboard</a>
                <a href="/std_mgmt/views/academic_user/pending_registrations.php">Pending Registrations</a>
                <a href="/std_mgmt/views/academic_user/registered_leads.php">Registered Leads</a>
            <?php endif; ?>
            <a href="/std_mgmt/views/auth/logout.php">Logout</a>
        </nav>