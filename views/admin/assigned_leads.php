<?php 
include __DIR__ . '/../layouts/header.php'; 
require_once __DIR__ . '/../../backend/controllers/LeadController.php';
$leadController = new LeadController($pdo);
$leads = $leadController->getAssignedLeads();
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
        <td><a href="/std_mgmt/views/admin/assigned_leads.php?course=<?php echo urlencode($lead['form_name']); ?>" class="btn btn-primary">View Leads</a></td>
    </tr>
    <?php endforeach; ?>
</table>
<?php if (isset($_GET['course'])): ?>
    <h3>Assigned Leads for <?php echo htmlspecialchars($_GET['course']); ?></h3>
    <table class="table">
        <tr>
            <th>Full Name</th>
            <th>Email</th>
            <th>Phone</th>
            <th>Assigned User</th>
        </tr>
        <?php foreach ($leadController->getLeadsByCourse($_GET['course']) as $lead): ?>
        <tr>
            <td><?php echo htmlspecialchars($lead['full_name']); ?></td>
            <td><?php echo htmlspecialchars($lead['email']); ?></td>
            <td><?php echo htmlspecialchars($lead['phone']); ?></td>
            <td><?php echo htmlspecialchars($lead['assigned_user_id']); ?></td>
        </tr>
        <?php endforeach; ?>
    </table>
<?php endif; ?>
<?php include __DIR__ . '/../layouts/footer.php'; ?>