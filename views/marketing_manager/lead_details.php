<?php
include __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../../backend/controllers/LeadController.php';
require_once __DIR__ . '/../../backend/controllers/DocumentController.php';
require_once __DIR__ . '/../../backend/controllers/FollowupController.php';
require_once __DIR__ . '/../../backend/controllers/RegistrationController.php';
$leadController = new LeadController($pdo);
$documentController = new DocumentController($pdo);
$followupController = new FollowupController($pdo);
$registrationController = new RegistrationController($pdo);

if (!isset($_GET['lead_id']) || !is_numeric($_GET['lead_id'])) {
    echo '<p style="color: red;">Error: No valid lead ID provided.</p>';
    include __DIR__ . '/../layouts/footer.php';
    exit;
}
$lead_id = (int)$_GET['lead_id'];
$lead = $leadController->getLeadById($lead_id);

if (!$lead) {
    echo '<p style="color: red;">Error: Lead not found.</p>';
    include __DIR__ . '/../layouts/footer.php';
    exit;
}

$documents = $documentController->getDocumentsByLead($lead_id);
$followups = $followupController->getFollowupsByLead($lead_id);
$registration = $registrationController->getPendingRegistrations(null, null);
$registration = array_filter($registration, fn($r) => $r['lead_id'] == $lead_id);
$registration = !empty($registration) ? reset($registration) : null;
?>
<h2>Lead Details</h2>
<h3>Lead Information</h3>
<table class="table">
    <tr><th>Course Name</th><td><?php echo htmlspecialchars($lead['form_name']); ?></td></tr>
    <tr><th>Full Name</th><td><?php echo htmlspecialchars($lead['full_name']); ?></td></tr>
    <tr><th>Email</th><td><?php echo htmlspecialchars($lead['email']); ?></td></tr>
    <tr><th>Phone</th><td><?php echo htmlspecialchars($lead['phone']); ?></td></tr>
    <tr><th>Permanent Address</th><td><?php echo htmlspecialchars($lead['permanent_address'] ?: 'N/A'); ?></td></tr>
    <tr><th>Work Experience</th><td><?php echo htmlspecialchars($lead['work_experience'] ?: 'N/A'); ?></td></tr>
    <tr><th>Assigned To</th><td><?php echo htmlspecialchars($lead['username'] ?: 'N/A'); ?></td></tr>
    <tr><th>Lead Status</th><td><?php echo htmlspecialchars($lead['status']); ?></td></tr>
    <tr><th>Registration Status</th><td><?php echo htmlspecialchars($lead['registration_status'] ?: 'N/A'); ?></td></tr>
    <?php if ($registration): ?>
        <tr><th>Marketing Manager Approval</th><td><?php echo htmlspecialchars($registration['marketing_manager_approval']); ?></td></tr>
        <tr><th>Academic User Approval</th><td><?php echo htmlspecialchars($registration['academic_user_approval']); ?></td></tr>
    <?php endif; ?>
    <tr><th>Created At</th><td><?php echo htmlspecialchars($lead['created_at']); ?></td></tr>
</table>

<h3>Documents</h3>
<?php if (empty($documents)): ?>
    <p>No documents uploaded.</p>
<?php else: ?>
    <table class="table">
        <tr>
            <th>Document Type</th>
            <th>File</th>
            <th>Uploaded At</th>
        </tr>
        <?php foreach ($documents as $doc): ?>
        <tr>
            <td><?php
                $type_labels = [
                    'nic' => 'NIC Copy',
                    'education' => 'Education Documents',
                    'work_experience' => 'Work Experience Documents',
                    'birth_certificate' => 'Birth Certificate'
                ];
                echo htmlspecialchars($type_labels[$doc['document_type']] ?? $doc['document_type']);
            ?></td>
            <td><a href="/std_mgmt/uploads/documents/<?php echo htmlspecialchars(basename($doc['file_path'])); ?>" target="_blank">View</a></td>
            <td><?php echo htmlspecialchars($doc['uploaded_at']); ?></td>
        </tr>
        <?php endforeach; ?>
    </table>
<?php endif; ?>

<h3>Follow-ups</h3>
<?php if (empty($followups)): ?>
    <p>No follow-ups added.</p>
<?php else: ?>
    <table class="table">
        <tr>
            <th>Number</th>
            <th>Date</th>
            <th>Comment</th>
            <th>Created At</th>
        </tr>
        <?php foreach ($followups as $followup): ?>
        <tr>
            <td><?php echo htmlspecialchars($followup['number']); ?></td>
            <td><?php echo htmlspecialchars($followup['followup_date']); ?></td>
            <td><?php echo htmlspecialchars($followup['comment']); ?></td>
            <td><?php echo htmlspecialchars($followup['created_at']); ?></td>
        </tr>
        <?php endforeach; ?>
    </table>
<?php endif; ?>

<a href="/std_mgmt/views/marketing_manager/leads_list.php?course=<?php echo urlencode($lead['form_name']); ?>" class="btn btn-primary">Back</a>
<?php include __DIR__ . '/../layouts/footer.php'; ?>