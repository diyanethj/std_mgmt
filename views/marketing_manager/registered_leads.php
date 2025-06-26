<?php 
include __DIR__ . '/../layouts/header.php'; 
require_once __DIR__ . '/../../backend/controllers/LeadController.php';
require_once __DIR__ . '/../../backend/controllers/DocumentController.php';
require_once __DIR__ . '/../../backend/controllers/FollowupController.php';
$leadController = new LeadController($pdo);
$documentController = new DocumentController($pdo);
$followupController = new FollowupController($pdo);
$leads = $leadController->getAssignedLeads(null, 'registered');
$course = $_GET['course'] ?? null;
$lead_id = $_GET['lead_id'] ?? null;

if ($course) {
    $course_leads = $leadController->getLeadsByCourse($course);
}
if ($lead_id) {
    $lead = $leadController->getLeadById($lead_id);
    $documents = $documentController->getDocumentsByLead($lead_id);
    $followups = $followupController->getFollowupsByLead($lead_id);
}
?>
<h2>Registered Leads</h2>
<input type="text" id="searchInput" onkeyup="filterTable()" placeholder="Search leads...">
<table class="table" id="dataTable">
    <tr>
        <th>Course Name</th>
        <th>Lead Count</th>
        <th>Action</th>
    </tr>
    <?php foreach ($leads as $lead): ?>
    <tr>
        <td><?php echo htmlspecialchars($lead['form_name']); ?></td>
        <td><?php echo count($leadController->getLeadsByCourse($lead['form_name'])); ?></td>
        <td><a href="/std_mgmt/views/marketing_manager/registered_leads.php?course=<?php echo urlencode($lead['form_name']); ?>" class="btn btn-primary">View Leads</a></td>
    </tr>
    <?php endforeach; ?>
</table>

<?php if ($course): ?>
    <h3>Registered Leads for <?php echo htmlspecialchars($course); ?></h3>
    <table class="table">
        <tr>
            <th>Full Name</th>
            <th>Email</th>
            <th>Phone</th>
            <th>Action</th>
        </tr>
        <?php foreach ($course_leads as $lead): ?>
        <tr>
            <td><?php echo htmlspecialchars($lead['full_name']); ?></td>
            <td><?php echo htmlspecialchars($lead['email']); ?></td>
            <td><?php echo htmlspecialchars($lead['phone']); ?></td>
            <td><a href="/std_mgmt/views/marketing_manager/registered_leads.php?course=<?php echo urlencode($course); ?>&lead_id=<?php echo htmlspecialchars($lead['id']); ?>" class="btn btn-primary">View Details</a></td>
        </tr>
        <?php endforeach; ?>
    </table>
<?php endif; ?>

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
<?php endif; ?>
<?php include __DIR__ . '/../layouts/footer.php'; ?>