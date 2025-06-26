<?php
include __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../../backend/controllers/RegistrationController.php';
require_once __DIR__ . '/../../backend/controllers/AuthController.php';
$registrationController = new RegistrationController($pdo);
$authController = new AuthController($pdo);

$user = $authController->getCurrentUser();
$course = $_GET['course'] ?? '';
$user_id = $user['id']; // Restrict to logged-in marketing user

// Get distinct courses with pending registrations for this marketing user
$pending_registrations = $registrationController->getPendingRegistrations($user_id);
$course_list = array_unique(array_column($pending_registrations, 'form_name'));

if ($course) {
    $pending_registrations = $registrationController->getPendingRegistrations($user_id, $course);
} else {
    $pending_registrations = [];
}
?>
<h2>Pending Registrations</h2>
<?php if (isset($_GET['success'])): ?>
    <p style="color: green;"><?php echo htmlspecialchars($_GET['success']); ?></p>
<?php endif; ?>
<?php if (isset($error)): ?>
    <p style="color: red;"><?php echo htmlspecialchars($error); ?></p>
<?php endif; ?>

<?php if (empty($course_list)): ?>
    <p>No pending registrations found.</p>
<?php else: ?>
    <h3>Courses</h3>
    <table class="table" id="dataTable">
        <tr>
            <th>Course Name</th>
            <th>Pending Registration Count</th>
            <th>Action</th>
        </tr>
        <?php foreach ($course_list as $course_name): ?>
        <tr>
            <td><?php echo htmlspecialchars($course_name); ?></td>
            <td><?php echo count($registrationController->getPendingRegistrations($user_id, $course_name)); ?></td>
            <td><a href="?course=<?php echo urlencode($course_name); ?>" class="btn btn-primary">View Registrations</a></td>
        </tr>
        <?php endforeach; ?>
    </table>
<?php endif; ?>
<?php if ($course && !empty($pending_registrations)): ?>
    <h3>Pending Registrations for <?php echo htmlspecialchars($course); ?></h3>
    <table class="table">
        <tr>
            <th>Full Name</th>
            <th>Email</th>
            <th>Phone</th>
            <th>Permanent Address</th>
            <th>Work Experience</th>
            <th>Marketing Manager Approval</th>
            <th>Academic User Approval</th>
            <th>Actions</th>
        </tr>
        <?php foreach ($pending_registrations as $registration): ?>
        <tr>
            <td><?php echo htmlspecialchars($registration['full_name']); ?></td>
            <td><?php echo htmlspecialchars($registration['email']); ?></td>
            <td><?php echo htmlspecialchars($registration['phone']); ?></td>
            <td><?php echo htmlspecialchars($registration['permanent_address'] ?: 'N/A'); ?></td>
            <td><?php echo htmlspecialchars($registration['work_experience'] ?: 'N/A'); ?></td>
            <td><?php echo htmlspecialchars($registration['marketing_manager_approval']); ?></td>
            <td><?php echo htmlspecialchars($registration['academic_user_approval']); ?></td>
            <td>
                <a href="/std_mgmt/views/marketing_user/lead_details.php?lead_id=<?php echo htmlspecialchars((string)$registration['lead_id']); ?>" class="btn btn-primary">View Details</a>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>
<?php endif; ?>
<?php include __DIR__ . '/../layouts/footer.php'; ?>