<?php
include __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../../backend/controllers/RegistrationController.php';
require_once __DIR__ . '/../../backend/controllers/AuthController.php';
require_once __DIR__ . '/../../backend/controllers/LeadController.php';
$registrationController = new RegistrationController($pdo);
$authController = new AuthController($pdo);
$leadController = new LeadController($pdo);

$course = $_GET['course'] ?? '';
$user_id = $_GET['user_id'] ?? '';
$marketing_users = $authController->getUsersByRole('marketing_user');

// Get distinct courses with pending registrations
$registrations = $registrationController->getPendingRegistrations();
$course_list = array_unique(array_column($registrations, 'form_name'));

if ($course) {
    $pending_registrations = $registrationController->getPendingRegistrations($user_id ?: null, $course);
} else {
    $pending_registrations = [];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $lead_id = (int)$_POST['lead_id'];
    $action = $_POST['action'];
    $role = $_POST['role'];
    if ($action === 'approve') {
        if ($registrationController->approveRegistration($lead_id, $role)) {
            header('Location: /std_mgmt/views/admin/pending_registrations.php?course=' . urlencode($course) . '&user_id=' . urlencode($user_id) . '&success=Registration approved successfully');
            exit;
        } else {
            $error = 'Failed to approve registration';
        }
    } elseif ($action === 'decline') {
        if ($registrationController->declineRegistration($lead_id, $role)) {
            header('Location: /std_mgmt/views/admin/pending_registrations.php?course=' . urlencode($course) . '&user_id=' . urlencode($user_id) . '&success=Registration declined successfully');
            exit;
        } else {
            $error = 'Failed to decline registration';
        }
    }
}
?>
<h2>Pending Registrations</h2>
<?php if (isset($_GET['success'])): ?>
    <p style="color: green;"><?php echo htmlspecialchars($_GET['success']); ?></p>
<?php endif; ?>
<?php if (isset($error)): ?>
    <p style="color: red;"><?php echo htmlspecialchars($error); ?></p>
<?php endif; ?>

<!-- Filter by Marketing User -->
<form method="GET" action="/std_mgmt/views/admin/pending_registrations.php">
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
            <td><?php echo count($registrationController->getPendingRegistrations($user_id ?: null, $course_name)); ?></td>
            <td><a href="?course=<?php echo urlencode($course_name); ?>&user_id=<?php echo urlencode($user_id); ?>" class="btn btn-primary">View Registrations</a></td>
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
            <th>Assigned To</th>
            <th>Marketing Manager Approval</th>
            <th>Academic User Approval</th>
            <th>Actions</th>
        </tr>
        <?php foreach ($pending_registrations as $registration): ?>
        <tr>
            <td><?php echo htmlspecialchars($registration['full_name']); ?></td>
            <td><?php echo htmlspecialchars($registration['email']); ?></td>
            <td><?php echo htmlspecialchars($registration['phone']); ?></td>
            <td><?php echo htmlspecialchars($registration['username'] ?: 'N/A'); ?></td>
            <td><?php echo htmlspecialchars($registration['marketing_manager_approval']); ?></td>
            <td><?php echo htmlspecialchars($registration['academic_user_approval']); ?></td>
            <td>
                <form method="POST" action="/std_mgmt/views/admin/pending_registrations.php?course=<?php echo urlencode($course); ?>&user_id=<?php echo urlencode($user_id); ?>" style="display: inline;">
                    <input type="hidden" name="lead_id" value="<?php echo htmlspecialchars((string)$registration['lead_id']); ?>">
                    <input type="hidden" name="role" value="marketing_manager">
                    <button type="submit" name="action" value="approve" class="btn btn-success" <?php echo $registration['marketing_manager_approval'] !== 'pending' ? 'disabled' : ''; ?>>Accept (MM)</button>
                    <button type="submit" name="action" value="decline" class="btn btn-danger" <?php echo $registration['marketing_manager_approval'] !== 'pending' ? 'disabled' : ''; ?>>Decline (MM)</button>
                </form>
                <form method="POST" action="/std_mgmt/views/admin/pending_registrations.php?course=<?php echo urlencode($course); ?>&user_id=<?php echo urlencode($user_id); ?>" style="display: inline;">
                    <input type="hidden" name="lead_id" value="<?php echo htmlspecialchars((string)$registration['lead_id']); ?>">
                    <input type="hidden" name="role" value="academic_user">
                    <button type="submit" name="action" value="approve" class="btn btn-success" <?php echo $registration['academic_user_approval'] !== 'pending' ? 'disabled' : ''; ?>>Accept (AU)</button>
                    <button type="submit" name="action" value="decline" class="btn btn-danger" <?php echo $registration['academic_user_approval'] !== 'pending' ? 'disabled' : ''; ?>>Decline (AU)</button>
                </form>
                <a href="/std_mgmt/views/admin/lead_details.php?lead_id=<?php echo htmlspecialchars((string)$registration['lead_id']); ?>" class="btn btn-primary">View Details</a>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>
<?php endif; ?>
<?php include __DIR__ . '/../layouts/footer.php'; ?>