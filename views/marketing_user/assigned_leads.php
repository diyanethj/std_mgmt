<?php
include __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../../backend/controllers/LeadController.php';
require_once __DIR__ . '/../../backend/controllers/AuthController.php';
require_once __DIR__ . '/../../backend/controllers/RegistrationController.php';
$leadController = new LeadController($pdo);
$authController = new AuthController($pdo);
$registrationController = new RegistrationController($pdo);

$user = $authController->getCurrentUser();
$course = $_GET['course'] ?? '';
$user_id = $user['id']; // Restrict to logged-in marketing user

// Get distinct courses with assigned leads for this marketing user
$leads = $leadController->getAssignedLeads($user_id);
$course_list = array_unique(array_column($leads, 'form_name'));

if ($course) {
    $assigned_leads = $leadController->getAssignedLeads($user_id, $course);
} else {
    $assigned_leads = [];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'send_to_registration') {
    $lead_id = (int)$_POST['lead_id'];
    if ($registrationController->createRegistration($lead_id)) {
        header('Location: /std_mgmt/views/marketing_user/assigned_leads.php?course=' . urlencode($course) . '&success=Lead sent to registration successfully');
        exit;
    } else {
        $error = 'Failed to send lead to registration';
    }
}
?>
<h2>Assigned Leads</h2>
<?php if (isset($_GET['success'])): ?>
    <p style="color: green;"><?php echo htmlspecialchars($_GET['success']); ?></p>
<?php endif; ?>
<?php if (isset($error)): ?>
    <p style="color: red;"><?php echo htmlspecialchars($error); ?></p>
<?php endif; ?>

<?php if (empty($course_list)): ?>
    <p>No assigned leads found.</p>
<?php else: ?>
    <h3>Courses</h3>
    <table class="table" id="dataTable">
        <tr>
            <th>Course Name</th>
            <th>Assigned Lead Count</th>
            <th>Action</th>
        </tr>
        <?php foreach ($course_list as $course_name): ?>
        <tr>
            <td><?php echo htmlspecialchars($course_name); ?></td>
            <td><?php echo count($leadController->getAssignedLeads($user_id, $course_name)); ?></td>
            <td><a href="?course=<?php echo urlencode($course_name); ?>" class="btn btn-primary">View Leads</a></td>
        </tr>
        <?php endforeach; ?>
    </table>
<?php endif; ?>
<?php if ($course && !empty($assigned_leads)): ?>
    <h3>Assigned Leads for <?php echo htmlspecialchars($course); ?></h3>
    <table class="table">
        <tr>
            <th>Full Name</th>
            <th>Email</th>
            <th>Phone</th>
            <th>Permanent Address</th>
            <th>Work Experience</th>
            <th>Status</th>
            <th>Actions</th>
        </tr>
        <?php foreach ($assigned_leads as $lead): ?>
        <tr>
            <td><?php echo htmlspecialchars($lead['full_name']); ?></td>
            <td><?php echo htmlspecialchars($lead['email']); ?></td>
            <td><?php echo htmlspecialchars($lead['phone']); ?></td>
            <td><?php echo htmlspecialchars($lead['permanent_address'] ?: 'N/A'); ?></td>
            <td><?php echo htmlspecialchars($lead['work_experience'] ?: 'N/A'); ?></td>
            <td><?php echo htmlspecialchars($lead['status']); ?></td>
            <td>
                <form method="POST" action="/std_mgmt/views/marketing_user/assigned_leads.php?course=<?php echo urlencode($course); ?>" style="display: inline;">
                    <input type="hidden" name="lead_id" value="<?php echo htmlspecialchars((string)$lead['id']); ?>">
                    <button type="submit" name="action" value="send_to_registration" class="btn btn-success">Send to Registration</button>
                </form>
                <a href="/std_mgmt/views/marketing_user/lead_details.php?lead_id=<?php echo htmlspecialchars((string)$lead['id']); ?>" class="btn btn-primary">View Details</a>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>
<?php endif; ?>
<?php include __DIR__ . '/../layouts/footer.php'; ?>