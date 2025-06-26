<?php 
include __DIR__ . '/../layouts/header.php'; 
require_once __DIR__ . '/../../backend/controllers/RegistrationController.php';
require_once __DIR__ . '/../../backend/controllers/LeadController.php';
require_once __DIR__ . '/../../backend/controllers/DocumentController.php';
require_once __DIR__ . '/../../backend/controllers/FollowupController.php';
$registrationController = new RegistrationController($pdo);
$leadController = new LeadController($pdo);
$documentController = new DocumentController($pdo);
$followupController = new FollowupController($pdo);
$pending = $registrationController->getPendingRegistrations();
$lead_id = $_GET['lead_id'] ?? null;

if ($lead_id) {
    $lead = $leadController->getLeadById($lead_id);
    $documents = $documentController->getDocumentsByLead($lead_id);
    $followups = $followupController->getFollowupsByLead($lead_id);
}
?>
<h2>Pending Registrations</h2>
<table class="table">
    <tr>
        <th>Full Name</th>
        <th>Course</th>
        <th>Email</th>
        <th>Phone</th>
        <th>Action</th>
    </tr>
    <?php foreach ($pending as $registration): ?>
    <tr>
        <td><?php echo htmlspecialchars($registration['full_name']); ?></td>
        <td><?php echo htmlspecialchars($registration['form_name']); ?></td>
        <td><?php echo htmlspecialchars($registration['email']); ?></td>
        <td><?php echo htmlspecialchars($registration['phone']); ?></td>
        <td><a href="/std_mgmt/views/marketing_manager/pending_registrations.php?lead_id=<?php echo htmlspecialchars($registration['lead_id']); ?>" class="btn btn-primary">View Details</a></td>
    </tr>
    <?php endforeach; ?>
</table>

<?php if ($lead_id): ?>
    <h3>Lead Details</h3>
    <p>Name: <?php echo htmlspecialchars($lead['full_name']); ?></p>
    <p>Email: <?php echo htmlspecialchars($lead['email']); ?></p>
    <p>Phone: <?php echo htmlspecialchars($lead['phone']); ?></p>
    <p>Address: <?php echo htmlspecialchars($lead['permanent_address'] ?? 'Not provided'); ?></p>
    <p>Work Experience: <?php echo htmlspecialchars($lead['work_experience'] ?? 'Not provided'); ?></p>

    <h4>Documents</h4>
    <table class="table">
        <tr>
            <th>Type</th>
            <th>File</th>
        </tr>
        <?php foreach ($documents as $document): ?>
        <tr>
            <td><?php echo htmlspecialchars($document['document_type']); ?></td>
            <td><a href="/std_mgmt/<?php echo htmlspecialchars($document['file_path']); ?>">View</a></td>
        </tr>
        <?php endforeach; ?>
    </table>

    <h4>Follow-ups</h4>
    <table class="table">
        <tr>
            <th>Date</th>
            <th>Comment</th>
        </tr>
        <?php foreach ($followups as $followup): ?>
        <tr>
            <td><?php echo htmlspecialchars($followup['followup_date']); ?></td>
            <td><?php echo htmlspecialchars($followup['comment']); ?></td>
        </tr>
        <?php endforeach; ?>
    </table>

    <form method="POST" action="/std_mgmt/registration/approve">
        <input type="hidden" name="lead_id" value="<?php echo htmlspecialchars($lead_id); ?>">
        <input type="hidden" name="role" value="marketing_manager">
        <button type="submit" name="status" value="approved" class="btn btn-primary">Approve</button>
        <button type="submit" name="status" value="declined" class="btn btn-danger">Decline</button>
    </form>
<?php endif; ?>
<?php include __DIR__ . '/../layouts/footer.php'; ?>