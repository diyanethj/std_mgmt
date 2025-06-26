<?php 
include __DIR__ . '/../layouts/header.php'; 
require_once __DIR__ . '/../../backend/controllers/LeadController.php';
$leadController = new LeadController($pdo);
?>
<h2>Upload Leads</h2>
<?php if (isset($_GET['success'])): ?>
    <p style="color: green;"><?php echo htmlspecialchars($_GET['success']); ?></p>
<?php endif; ?>
<?php if (isset($_GET['error'])): ?>
    <p style="color: red;"><?php echo htmlspecialchars($_GET['error']); ?></p>
<?php endif; ?>
<form method="POST" enctype="multipart/form-data" action="/std_mgmt/admin/upload_leads">
    <div class="form-group">
        <label>CSV File</label>
        <input type="file" name="csv_file" accept=".csv" required>
    </div>
    <button type="submit" class="btn btn-primary">Upload</button>
</form>
<?php include __DIR__ . '/../layouts/footer.php'; ?>