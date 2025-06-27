<?php
include __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../../backend/controllers/LeadController.php';
require_once __DIR__ . '/../../backend/controllers/AuthController.php';
$leadController = new LeadController($pdo);
$authController = new AuthController($pdo);

$user = $authController->getCurrentUser();
if (!$user || $user['role'] !== 'marketing_manager') {
    header('Location: /std_mgmt/views/auth/login.php?error=Unauthorized access');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['csv_file'])) {
    $leadController->uploadLeads($_FILES['csv_file'], $user);
}
?>
<h2>Upload Leads</h2>
<?php if (isset($_GET['success'])): ?>
    <p style="color: green;"><?php echo htmlspecialchars($_GET['success']); ?></p>
<?php endif; ?>
<?php if (isset($_GET['error'])): ?>
    <p style="color: red;"><?php echo htmlspecialchars($_GET['error']); ?></p>
<?php endif; ?>
<form method="POST" enctype="multipart/form-data">
    <div class="form-group">
        <label for="csv_file">Upload CSV File</label>
        <input type="file" name="csv_file" id="csv_file" accept=".csv" required>
    </div>
    <button type="submit" class="btn btn-primary">Upload</button>
</form>
<?php include __DIR__ . '/../layouts/footer.php'; ?>