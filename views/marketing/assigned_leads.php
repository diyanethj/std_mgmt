<?php 
include __DIR__ . '/../layouts/header.php'; 
require_once __DIR__ . '/../../backend/controllers/LeadController.php';
$leadController = new LeadController($pdo);
$leads = $leadController->getAssignedLeads($_SESSION['user_id']);
?>
<h2>Assigned Leads</h2>
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
        <td><a href="/std_mgmt/views/marketing/lead_details.php?course=<?php echo urlencode($lead['form_name']); ?>" class="btn btn-primary">View Leads</a></td>
    </tr>
    <?php endforeach; ?>
</table>
<?php include __DIR__ . '/../layouts/footer.php'; ?>