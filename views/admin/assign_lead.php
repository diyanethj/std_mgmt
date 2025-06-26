<?php 
include __DIR__ . '/../layouts/header.php'; 
require_once __DIR__ . '/../../backend/controllers/LeadController.php';
require_once __DIR__ . '/../../backend/models/User.php';
$leadController = new LeadController($pdo);
$userModel = new User($pdo);
$marketing_users = $userModel->getMarketingUsers();
$lead_id = $_GET['lead_id'];
?>
<h2>Assign Lead</h2>
<form method="POST" action="/std_mgmt/admin/assign_lead">
    <input type="hidden" name="lead_id" value="<?php echo htmlspecialchars($lead_id); ?>">
    <div class="form-group">
        <label>Assign to Marketing User</label>
        <select name="user_id" required>
            <?php foreach ($marketing_users as $user): ?>
                <option value="<?php echo htmlspecialchars($user['id']); ?>"><?php echo htmlspecialchars($user['username']); ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <button type="submit" class="btn btn-primary">Assign</button>
</form>
<?php include __DIR__ . '/../layouts/footer.php'; ?>