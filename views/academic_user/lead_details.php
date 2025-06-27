<?php
include __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../../backend/controllers/LeadController.php';
require_once __DIR__ . '/../../backend/controllers/DocumentController.php';
require_once __DIR__ . '/../../backend/controllers/FollowupController.php';
require_once __DIR__ . '/../../backend/controllers/RegistrationController.php';
require_once __DIR__ . '/../../backend/controllers/AuthController.php';

$leadController = new LeadController($pdo);
$documentController = new DocumentController($pdo);
$followupController = new FollowupController($pdo);
$registrationController = new RegistrationController($pdo);
$authController = new AuthController($pdo);

$user = $authController->getCurrentUser();
if (!$user || $user['role'] !== 'academic_user') {
    header('Location: /std_mgmt/views/auth/login.php?error=Unauthorized access');
    exit;
}

$lead_id = $_GET['lead_id'] ?? '';
$lead = $lead_id ? $leadController->getLeadById($lead_id) : null;
$documents = $lead_id && method_exists($documentController, 'getDocumentsByLeadId') ? $documentController->getDocumentsByLeadId($lead_id) : [];
$followups = $lead_id && method_exists($followupController, 'getFollowupsByLeadId') ? $followupController->getFollowupsByLeadId($lead_id) : [];
$registration = $lead_id ? $registrationController->getRegistrationByLeadId($lead_id) : null;

error_log("Lead details: lead_id=$lead_id, lead=" . print_r($lead, true));

if (!$lead) {
    header('Location: /std_mgmt/views/academic_user/registered_leads.php?error=Lead not found');
    exit;
}
?>
<h2>Lead Details</h2>
<?php if (isset($_GET['success'])): ?>
    <p style="color: green;"><?php echo htmlspecialchars($_GET['success']); ?></p>
<?php endif; ?>
<?php if (isset($_GET['error'])): ?>
    <p style="color: red;"><?php echo htmlspecialchars($_GET['error']); ?></p>
<?php endif; ?>

<h3>Lead Information</h3>
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

<h3>Documents</h3>
<?php if (empty($documents)): ?>
    <p>No documents uploaded.</p>
<?php else: ?>
    <table class="table">
        <tr><th>Document Type</th><th>File</th></tr>
        <?php foreach ($documents as $document): ?>
        <tr>
            <td><?php echo htmlspecialchars($document['document_type']); ?></td>
            <td><a href="/std_mgmt/uploads/<?php echo htmlspecialchars($document['file_name']); ?>" target="_blank">View Document</a></td>
        </tr>
        <?php endforeach; ?>
    </table>
<?php endif; ?>

<h3>Follow-ups</h3>
<?php if (empty($followups)): ?>
    <p>No follow-ups recorded.</p>
<?php else: ?>
    <table class="table">
        <tr><th>Follow-up Number</th><th>Date</th><th>Comment</th></tr>
        <?php foreach ($followups as $followup): ?>
        <tr>
            <td><?php echo htmlspecialchars($followup['number']); ?></td>
            <td><?php echo htmlspecialchars($followup['followup_date']); ?></td>
            <td><?php echo htmlspecialchars($followup['comment']); ?></td>
        </tr>
        <?php endforeach; ?>
    </table>
<?php endif; ?>

<a href="/std_mgmt/views/academic_user/registered_leads.php" class="btn btn-primary">Back to Registered Leads</a>
<?php include __DIR__ . '/../layouts/footer.php'; ?>