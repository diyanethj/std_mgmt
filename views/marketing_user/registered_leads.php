<?php
include __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../../backend/controllers/RegistrationController.php';
require_once __DIR__ . '/../../backend/controllers/AuthController.php';
$registrationController = new RegistrationController($pdo);
$authController = new AuthController($pdo);

$user = $authController->getCurrentUser();
$course = $_GET['course'] ?? '';
$user_id = $user['role'] === 'marketing_user' ? $user['id'] : ($_GET['user_id'] ?? '');

// Get distinct courses with registered leads
$registrations = $registrationController->getRegisteredLeads($user['role'] === 'marketing_user' ? $user['id'] : null);
$course_list = array_unique(array_column($registrations, 'form_name'));

if ($course) {
    $registered_leads = $registrationController->getRegisteredLeads($user_id ?: ($user['role'] === 'marketing_user' ? $user['id'] : null), $course);
} else {
    $registered_leads = [];
}
?>
<h2>Registered Leads</h2>
<?php if (isset($_GET['success'])): ?>
    <p style="color: green;"><?php echo htmlspecialchars($_GET['success']); ?></p>
<?php endif; ?>
<?php if (isset($error)): ?>
    <p style="color: red;"><?php echo htmlspecialchars($error); ?></p>
<?php endif; ?>

<?php if ($user['role'] !== 'marketing_user'): ?>
<!-- Filter by Marketing User (only for non-marketing users) -->
<form method="GET" action="/std_mgmt/views/marketing_user/registered_leads.php">
    <div class="form-group">
        <label for="user_id">Filter by Marketing User</label>
        <select name="user_id" id="user_id">
            <option value="">All</option>
            <?php
            $marketing_users = $authController->getUsersByRole('marketing_user');
            foreach ($marketing_users as $marketing_user): ?>
                <option value="<?php echo htmlspecialchars((string)$marketing_user['id']); ?>" <?php echo $user_id == $marketing_user['id'] ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($marketing_user['username']); ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>
    <?php if ($course): ?>
        <input type="hidden" name="course" value="<?php echo htmlspecialchars($course); ?>">
    <?php endif; ?>
    <button type="submit" class="btn btn-primary">Apply Filter</button>
</form>
<?php endif; ?>

<?php if (empty($course_list)): ?>
    <p>No registered leads found.</p>
<?php else: ?>
    <h3>Courses</h3>
    <table class="table" id="dataTable">
        <tr>
            <th>Course Name</th>
            <th>Registered Lead Count</th>
            <th>Action</th>
        </tr>
        <?php foreach ($course_list as $course_name): ?>
        <tr>
            <td><?php echo htmlspecialchars($course_name); ?></td>
            <td><?php echo count($registrationController->getRegisteredLeads($user_id ?: ($user['role'] === 'marketing_user' ? $user['id'] : null), $course_name)); ?></td>
            <td><a href="?course=<?php echo urlencode($course_name); ?>&user_id=<?php echo urlencode($user_id); ?>" class="btn btn-primary">View Leads</a></td>
        </tr>
        <?php endforeach; ?>
    </table>
<?php endif; ?>
<?php if ($course && !empty($registered_leads)): ?>
    <h3>Registered Leads for <?php echo htmlspecialchars($course); ?></h3>
    <table class="table">
        <tr>
            <th>Full Name</th>
            <th>Email</th>
            <th>Phone</th>
            <th>Assigned To</th>
            <th>Registration Status</th>
            <th>Action</th>
        </tr>
        <?php foreach ($registered_leads as $lead): ?>
        <tr>
            <td><?php echo htmlspecialchars($lead['full_name']); ?></td>
            <td><?php echo htmlspecialchars($lead['email']); ?></td>
            <td><?php echo htmlspecialchars($lead['phone']); ?></td>
            <td><?php echo htmlspecialchars($lead['username'] ?: 'N/A'); ?></td>
            <td><?php echo htmlspecialchars($lead['status']); ?></td>
            <td>
                <a href="/std_mgmt/views/marketing_user/lead_details.php?lead_id=<?php echo htmlspecialchars((string)$lead['lead_id']); ?>" class="btn btn-primary">View Details</a>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>
<?php endif; ?>
<?php include __DIR__ . '/../layouts/footer.php'; ?>