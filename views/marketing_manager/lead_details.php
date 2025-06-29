<?php
include __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../../backend/controllers/LeadController.php';
require_once __DIR__ . '/../../backend/controllers/DocumentController.php';
require_once __DIR__ . '/../../backend/controllers/FollowupController.php';
require_once __DIR__ . '/../../backend/controllers/AuthController.php';

$leadController = new LeadController($pdo);
$documentController = new DocumentController($pdo);
$followupController = new FollowupController($pdo);
$authController = new AuthController($pdo);

$user = $authController->getCurrentUser();
if (!$user || $user['role'] !== 'marketing_manager') {
    header('Location: /std_mgmt/views/auth/login.php?error=Unauthorized access');
    exit;
}

$lead_id = $_GET['lead_id'] ?? null;
$course = $_GET['course'] ?? null;

if (!$lead_id) {
    header('Location: /std_mgmt/views/marketing_manager/registered_leads.php?error=Lead ID not provided');
    exit;
}

$lead = $leadController->getLeadById($lead_id);
$documents = method_exists($documentController, 'getDocumentsByLeadId') ? $documentController->getDocumentsByLeadId($lead_id) : [];
$followups = method_exists($followupController, 'getFollowupsByLeadId') ? $followupController->getFollowupsByLeadId($lead_id) : [];

error_log("Marketing manager view details: lead_id=$lead_id, course=$course, lead=" . print_r($lead, true));

if (!$lead) {
    header('Location: /std_mgmt/views/marketing_manager/registered_leads.php?error=Lead not found');
    exit;
}
?>
<div class="container">
    <h2 class="dashboard-title">Lead Details</h2>
    <?php if (isset($_GET['success'])): ?>
        <p style="color: green;"><?php echo htmlspecialchars($_GET['success']); ?></p>
    <?php endif; ?>
    <?php if (isset($_GET['error'])): ?>
        <p style="color: red;"><?php echo htmlspecialchars($_GET['error']); ?></p>
    <?php endif; ?>

    <div class="lead-details">
        <p><strong>Name:</strong> <?php echo htmlspecialchars($lead['full_name']); ?></p>
        <p><strong>Email:</strong> <?php echo htmlspecialchars($lead['email']); ?></p>
        <p><strong>Phone:</strong> <?php echo htmlspecialchars($lead['phone']); ?></p>
        <p><strong>Address:</strong> <?php echo htmlspecialchars($lead['permanent_address'] ?? 'Not provided'); ?></p>
        <p><strong>Work Experience:</strong> <?php echo htmlspecialchars($lead['work_experience'] ?? 'Not provided'); ?></p>
        <p><strong>Course:</strong> <?php echo htmlspecialchars($lead['form_name']); ?></p>
        <p><strong>Status:</strong> <?php echo htmlspecialchars($lead['status']); ?></p>
    </div>

    <h3>Documents</h3>
    <?php if (empty($documents)): ?>
        <p>No documents uploaded.</p>
    <?php else: ?>
        <table class="table">
            <thead>
                <tr>
                    <th>Type</th>
                    <th>File</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($documents as $document): ?>
                <tr>
                    <td><?php echo htmlspecialchars($document['document_type']); ?></td>
                    <td><a href="/std_mgmt/uploads/<?php echo htmlspecialchars($document['file_name']); ?>" target="_blank">View</a></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>

    <h3>Follow-ups</h3>
    <?php if (empty($followups)): ?>
        <p>No follow-ups recorded.</p>
    <?php else: ?>
        <table class="table">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Comment</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($followups as $followup): ?>
                <tr>
                    <td><?php echo htmlspecialchars($followup['followup_date']); ?></td>
                    <td><?php echo htmlspecialchars($followup['comment']); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>

    <a href="/std_mgmt/views/marketing_manager/registered_leads.php?course=<?php echo urlencode($course); ?>" class="btn btn-secondary">Back to Leads</a>
</div>
<?php include __DIR__ . '/../layouts/footer.php'; ?>