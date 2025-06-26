<?php 
include __DIR__ . '/../layouts/header.php'; 
require_once __DIR__ . '/../../backend/controllers/LeadController.php';
require_once __DIR__ . '/../../backend/controllers/DocumentController.php';
require_once __DIR__ . '/../../backend/controllers/FollowupController.php';
$leadController = new LeadController($pdo);
$documentController = new DocumentController($pdo);
$followupController = new FollowupController($pdo);
$course = $_GET['course'];
$leads = $leadController->getLeadsByCourse($course);
$lead_id = $_GET['lead_id'] ?? null;

if ($lead_id) {
    $lead = $leadController->getLeadById($lead_id);
    $documents = $documentController->getDocumentsByLead($lead_id);
    $followups = $followupController->getFollowupsByLead($lead_id);
}
?>
<h2>Lead Details for <?php echo htmlspecialchars($course); ?></h2>
<table class="table">
    <tr>
        <th>Full Name</th>
        <th>Email</th>
        <th>Phone</th>
        <th>Action</th>
    </tr>
    <?php foreach ($leads as $lead): ?>
    <tr>
        <td><?php echo htmlspecialchars($lead['full_name']); ?></td>
        <td><?php echo htmlspecialchars($lead['email']); ?></td>
        <td><?php echo htmlspecialchars($lead['phone']); ?></td>
        <td><a href="/std_mgmt/views/marketing/lead_details.php?course=<?php echo urlencode($course); ?>&lead_id=<?php echo htmlspecialchars($lead['id']); ?>" class="btn btn-primary">View Details</a></td>
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
    <form method="POST" action="/std_mgmt/marketing/upload_document" enctype="multipart/form-data">
        <input type="hidden" name="lead_id" value="<?php echo htmlspecialchars($lead_id); ?>">
        <div class="form-group">
            <label>Document Type</label>
            <select name="document_type" required>
                <option value="nic">NIC Copy</option>
                <option value="educational">Educational Documents</option>
                <option value="birth_certificate">Birth Certificate</option>
                <option value="work_experience">Work Experience Documents</option>
            </select>
        </div>
        <div class="form-group">
            <label>File</label>
            <input type="file" name="document" required>
        </div>
        <button type="submit" class="btn btn-primary">Upload Document</button>
    </form>

    <table class="table">
        <tr>
            <th>Type</th>
            <th>File</th>
            <th>Action</th>
        </tr>
        <?php foreach ($documents as $document): ?>
        <tr>
            <td><?php echo htmlspecialchars($document['document_type']); ?></td>
            <td><a href="/std_mgmt/<?php echo htmlspecialchars($document['file_path']); ?>">View</a></td>
            <td><a href="/std_mgmt/marketing/delete_document?document_id=<?php echo htmlspecialchars($document['id']); ?>" class="btn btn-danger">Delete</a></td>
        </tr>
        <?php endforeach; ?>
    </table>

    <h4>Follow-ups</h4>
    <form method="POST" action="/std_mgmt/marketing/add_followup">
        <input type="hidden" name="lead_id" value="<?php echo htmlspecialchars($lead_id); ?>">
        <div class="form-group">
            <label>Date</label>
            <input type="date" name="date" required>
        </div>
        <div class="form-group">
            <label>Comment</label>
            <textarea name="comment" required></textarea>
        </div>
        <button type="submit" class="btn btn-primary">Add Follow-up</button>
    </form>

    <table class="table">
        <tr>
            <th>Date</th>
            <th>Comment</th>
            <th>Action</th>
        </tr>
        <?php foreach ($followups as $followup): ?>
        <tr>
            <td><?php echo htmlspecialchars($followup['followup_date']); ?></td>
            <td><?php echo htmlspecialchars($followup['comment']); ?></td>
            <td><a href="/std_mgmt/marketing/delete_followup?followup_id=<?php echo htmlspecialchars($followup['id']); ?>" class="btn btn-danger">Delete</a></td>
        </tr>
        <?php endforeach; ?>
    </table>

    <form method="POST" action="/std_mgmt/marketing/send_registration">
        <input type="hidden" name="lead_id" value="<?php echo htmlspecialchars($lead_id); ?>">
        <button type="submit" class="btn btn-primary">Send for Registration</button>
    </form>
<?php endif; ?>
<?php include __DIR__ . '/../layouts/footer.php'; ?>