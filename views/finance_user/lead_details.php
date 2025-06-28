<?php
include __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../../backend/controllers/LeadController.php';
require_once __DIR__ . '/../../backend/controllers/RegistrationController.php';
require_once __DIR__ . '/../../backend/controllers/AuthController.php';

$leadController = new LeadController($pdo);
$registrationController = new RegistrationController($pdo);
$authController = new AuthController($pdo);

$user = $authController->getCurrentUser();
if (!$user || $user['role'] !== 'finance_user') {
    header('Location: /std_mgmt/views/auth/login.php?error=Unauthorized access');
    exit;
}

$lead_id = $_GET['lead_id'] ?? '';
$lead = $lead_id ? $leadController->getLeadById($lead_id) : null;
$registration = $lead_id ? $registrationController->getRegistrationByLeadId($lead_id) : null;

error_log("Finance user lead details: lead_id=$lead_id, lead=" . print_r($lead, true));

if (!$lead) {
    header('Location: /std_mgmt/views/finance_user/registered_leads.php?error=Lead not found');
    exit;
}
?>
<h2>Student Details</h2>
<?php if (isset($_GET['success'])): ?>
    <p style="color: green;"><?php echo htmlspecialchars($_GET['success']); ?></p>
<?php endif; ?>
<?php if (isset($_GET['error'])): ?>
    <p style="color: red;"><?php echo htmlspecialchars($_GET['error']); ?></p>
<?php endif; ?>

<h3>Student Information</h3>
<table class="table">
    <tr><th>Course</th><td><?php echo htmlspecialchars($lead['form_name']); ?></td></tr>
    <tr><th>Full Name</th><td><?php echo htmlspecialchars($lead['full_name']); ?></td></tr>
    <tr><th>Email</th><td><?php echo htmlspecialchars($lead['email']); ?></td></tr>
    <tr><th>Phone</th><td><?php echo htmlspecialchars($lead['phone']); ?></td></tr>
    <tr><th>Permanent Address</th><td><?php echo htmlspecialchars($lead['permanent_address'] ?: 'N/A'); ?></td></tr>
    <tr><th>Work Experience</th><td><?php echo htmlspecialchars($lead['work_experience'] ?: 'N/A'); ?></td></tr>
    <tr><th>Status</th><td><?php echo htmlspecialchars($lead['status']); ?></td></tr>
    <tr><th>Assigned To</th><td><?php echo htmlspecialchars($lead['username'] ?: 'N/A'); ?></td></tr>
</table>

<h3>Registration Status</h3>
<table class="table">
    <tr><th>Status</th><td><?php echo htmlspecialchars($registration['status'] ?: 'N/A'); ?></td></tr>
    <tr><th>Marketing Manager Approval</th><td><?php echo htmlspecialchars($registration['marketing_manager_approval'] ?: 'N/A'); ?></td></tr>
    <tr><th>Academic User Approval</th><td><?php echo htmlspecialchars($registration['academic_user_approval'] ?: 'N/A'); ?></td></tr>
</table>

<a href="/std_mgmt/views/finance_user/registered_leads.php" class="btn btn-primary">Back to Registered Students</a>
<?php include __DIR__ . '/../layouts/footer.php'; ?>