<?php 
include __DIR__ . '/../layouts/header.php'; 
require_once __DIR__ . '/../../backend/controllers/LeadController.php';
if (!isset($pdo)) {
    error_log("PDO not defined in leads_list.php");
    die("Database connection error");
}
$leadController = new LeadController($pdo);
$courses = $leadController->getLeadsByCourse(null);
?>
<h2>Leads List</h2>
<input type="text" id="searchInput" onkeyup="filterTable()" placeholder="Search leads...">
<?php if (empty($courses)): ?>
    <p>No leads found.</p>
<?php else: ?>
    <table class="table" id="dataTable">
        <tr>
            <th>Course Name</th>
            <th>Lead Count</th>
            <th>Action</th>
        </tr>
        <?php foreach ($courses as $course): ?>
        <tr>
            <td><?php echo htmlspecialchars($course['form_name']); ?></td>
            <td><?php echo count($leadController->getLeadsByCourse($course['form_name'])); ?></td>
            <td><a href="/std_mgmt/views/admin/leads_list.php?course=<?php echo urlencode($course['form_name']); ?>" class="btn btn-primary">View Leads</a></td>
        </tr>
        <?php endforeach; ?>
    </table>
<?php endif; ?>
<?php if (isset($_GET['course'])): ?>
    <h3>Leads for <?php echo htmlspecialchars($_GET['course']); ?></h3>
    <?php $leads = $leadController->getLeadsByCourse($_GET['course']); ?>
    <?php if (empty($leads)): ?>
        <p>No leads found for this course.</p>
    <?php else: ?>
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
                <td>
                    <a href="/std_mgmt/views/admin/assign_lead.php?lead_id=<?php echo htmlspecialchars((string)$lead['id']); ?>" class="btn btn-primary">Assign Lead</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </table>
    <?php endif; ?>
<?php endif; ?>
<?php include __DIR__ . '/../layouts/footer.php'; ?>