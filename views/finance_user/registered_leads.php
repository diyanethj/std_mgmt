<?php
include __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../../backend/controllers/RegistrationController.php';
require_once __DIR__ . '/../../backend/controllers/AuthController.php';
$registrationController = new RegistrationController($pdo);
$authController = new AuthController($pdo);

$user = $authController->getCurrentUser();
if (!$user || $user['role'] !== 'finance_user') {
    header('Location: /std_mgmt/views/auth/login.php?error=Unauthorized access');
    exit;
}

$course = $_GET['course'] ?? '';
$user_id = $_GET['user_id'] ?? '';
$marketing_users = $authController->getUsersByRole('marketing_user');

// Get distinct courses with registered leads
$registered_leads = $registrationController->getRegisteredLeads();
error_log("Finance user registered leads (all): " . print_r($registered_leads, true));
$course_list = array_unique(array_column($registered_leads, 'form_name'));

if ($course) {
    $registered_leads = $registrationController->getRegisteredLeads($user_id ?: null, $course);
    error_log("Finance user filtered registered leads (course: $course, user_id: $user_id): " . print_r($registered_leads, true));
} else {
    $registered_leads = [];
}
?>
<h2>Registered Students</h2>
<?php if (isset($_GET['success'])): ?>
    <p style="color: green;"><?php echo htmlspecialchars($_GET['success']); ?></p>
<?php endif; ?>
<?php if (isset($_GET['error'])): ?>
    <p style="color: red;"><?php echo htmlspecialchars($_GET['error']); ?></p>
<?php endif; ?>

<!-- Filter by Marketing User -->
<form method="GET" action="/std_mgmt/views/finance_user/registered_leads.php">
    <div class="form-group">
        <label for="user_id">Filter by Marketing User</label>
        <select name="user_id" id="user_id">
            <option value="">All</option>
            <?php foreach ($marketing_users as $user): ?>
                <option value="<?php echo htmlspecialchars((string)$user['id']); ?>" <?php echo $user_id == $user['id'] ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($user['username']); ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>
    <?php if ($course): ?>
        <input type="hidden" name="course" value="<?php echo htmlspecialchars($course); ?>">
    <?php endif; ?>
    <button type="submit" class="btn btn-primary">Apply Filter</button>
</form>

<?php if (empty($course_list)): ?>
    <p>No registered students found.</p>
<?php else: ?>
    <h3>Courses</h3>
    <table class="table" id="dataTable">
        <tr>
            <th>Course Name</th>
            <th>Registered Student Count</th>
            <th>Action</th>
        </tr>
        <?php foreach ($course_list as $course_name): ?>
        <tr>
            <td><?php echo htmlspecialchars($course_name); ?></td>
            <td><?php echo count($registrationController->getRegisteredLeads($user_id ?: null, $course_name)); ?></td>
            <td><a href="?course=<?php echo urlencode($course_name); ?>&user_id=<?php echo urlencode($user_id); ?>" class="btn btn-primary">View Students</a></td>
        </tr>
        <?php endforeach; ?>
    </table>
<?php endif; ?>
<?php if ($course && !empty($registered_leads)): ?>
    <h3>Registered Students for <?php echo htmlspecialchars($course); ?></h3>
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
                <a href="/std_mgmt/views/finance_user/lead_details.php?lead_id=<?php echo htmlspecialchars((string)$lead['lead_id']); ?>" class="btn btn-primary">View Details</a>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>
<?php endif; ?>
<?php include __DIR__ . '/../layouts/footer.php'; ?>