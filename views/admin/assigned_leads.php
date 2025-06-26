<?php
include __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../../backend/controllers/LeadController.php';
$leadController = new LeadController($pdo);

$course_list = $leadController->getDistinctCourses();
$course = $_GET['course'] ?? '';

if ($course) {
    $leads = $leadController->getLeadsByCourse($course);
} else {
    $leads = [];
}
?>
<h2>Assigned Leads</h2>
<?php if (isset($_GET['success'])): ?>
    <p style="color: green;"><?php echo htmlspecialchars($_GET['success']); ?></p>
<?php endif; ?>
<?php if (empty($course_list)): ?>
    <p>No assigned leads found.</p>
<?php else: ?>
    <table class="table" id="dataTable">
        <tr>
            <th>Course Name</th>
            <th>Lead Count</th>
            <th>Action</th>
        </tr>
        <?php foreach ($course_list as $course_name): ?>
        <tr>
            <td><?php echo htmlspecialchars($course_name); ?></td>
            <td><?php echo count(array_filter($leadController->getLeadsByCourse($course_name), fn($lead) => $lead['assigned_user_id'] !== null)); ?></td>
            <td><a href="?course=<?php echo urlencode($course_name); ?>" class="btn btn-primary">View Leads</a></td>
        </tr>
        <?php endforeach; ?>
    </table>
<?php endif; ?>
<?php if ($course && !empty($leads)): ?>
    <h3>Assigned Leads for <?php echo htmlspecialchars($course); ?></h3>
    <table class="table">
        <tr>
            <th>Full Name</th>
            <th>Email</th>
            <th>Phone</th>
            <th>Assigned To</th>
            <th>Registration Status</th>
            <th>Action</th>
        </tr>
        <?php foreach ($leads as $lead): ?>
        <?php if ($lead['assigned_user_id'] !== null): ?>
        <tr>
            <td><?php echo htmlspecialchars($lead['full_name']); ?></td>
            <td><?php echo htmlspecialchars($lead['email']); ?></td>
            <td><?php echo htmlspecialchars($lead['phone']); ?></td>
            <td><?php echo htmlspecialchars($lead['username'] ?: 'N/A'); ?></td>
            <td><?php echo htmlspecialchars($lead['registration_status'] ?: 'N/A'); ?></td>
            <td>
                <a href="/std_mgmt/views/admin/lead_details.php?lead_id=<?php echo htmlspecialchars((string)$lead['id']); ?>" class="btn btn-primary">View Details</a>
            </td>
        </tr>
        <?php endif; ?>
        <?php endforeach; ?>
    </table>
<?php endif; ?>
<?php include __DIR__ . '/../layouts/footer.php'; ?>