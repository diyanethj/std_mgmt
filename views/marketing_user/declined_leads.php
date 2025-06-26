<?php
include __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../../backend/controllers/RegistrationController.php';
require_once __DIR__ . '/../../backend/controllers/LeadController.php';
require_once __DIR__ . '/../../backend/controllers/AuthController.php';
$registrationController = new RegistrationController($pdo);
$leadController = new LeadController($pdo);
$authController = new AuthController($pdo);

$user = $authController->getCurrentUser();
$course = $_GET['course'] ?? '';
$user_id = $user['role'] === 'marketing_user' ? $user['id'] : ($_GET['user_id'] ?? '');

// Get distinct courses with declined leads
$declined_leads = $registrationController->getDeclinedLeads($user['role'] === 'marketing_user' ? $user['id'] : null);
$course_list = array_unique(array_column($declined_leads, 'form_name'));

if ($course) {
    $declined_leads = $registrationController->getDeclinedLeads($user_id ?: ($user['role'] === 'marketing_user' ? $user['id'] : null), $course);
} else {
    $declined_leads = [];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $lead_id = (int)$_POST['lead_id'];
    if (isset($_POST['action']) && $_POST['action'] === 'resend') {
        if ($registrationController->resendToRegistration($lead_id)) {
            header('Location: /std_mgmt/views/marketing_user/declined_leads.php?course=' . urlencode($course) . '&user_id=' . urlencode($user_id) . '&success=Lead resent for registration successfully');
            exit;
        } else {
            $error = 'Failed to resend lead for registration';
        }
    } elseif (isset($_POST['update'])) {
        $permanent_address = $_POST['permanent_address'] ?? '';
        $work_experience = $_POST['work_experience'] ?? '';
        if ($leadController->updateLeadDetails($lead_id, $permanent_address, $work_experience)) {
            header('Location: /std_mgmt/views/marketing_user/declined_leads.php?course=' . urlencode($course) . '&user_id=' . urlencode($user_id) . '&success=Lead details updated successfully');
            exit;
        } else {
            $error = 'Failed to update lead details';
        }
    }
}
?>
<h2>Declined Leads</h2>
<?php if (isset($_GET['success'])): ?>
    <p style="color: green;"><?php echo htmlspecialchars($_GET['success']); ?></p>
<?php endif; ?>
<?php if (isset($error)): ?>
    <p style="color: red;"><?php echo htmlspecialchars($error); ?></p>
<?php endif; ?>

<?php if ($user['role'] !== 'marketing_user'): ?>
<!-- Filter by Marketing User (only for non-marketing users) -->
<form method="GET" action="/std_mgmt/views/marketing_user/declined_leads.php">
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
    <p>No declined leads found.</p>
<?php else: ?>
    <h3>Courses</h3>
    <table class="table" id="dataTable">
        <tr>
            <th>Course Name</th>
            <th>Declined Lead Count</th>
            <th>Action</th>
        </tr>
        <?php foreach ($course_list as $course_name): ?>
        <tr>
            <td><?php echo htmlspecialchars($course_name); ?></td>
            <td><?php echo count($registrationController->getDeclinedLeads($user_id ?: ($user['role'] === 'marketing_user' ? $user['id'] : null), $course_name)); ?></td>
            <td><a href="?course=<?php echo urlencode($course_name); ?>&user_id=<?php echo urlencode($user_id); ?>" class="btn btn-primary">View Leads</a></td>
        </tr>
        <?php endforeach; ?>
    </table>
<?php endif; ?>
<?php if ($course && !empty($declined_leads)): ?>
    <h3>Declined Leads for <?php echo htmlspecialchars($course); ?></h3>
    <table class="table">
        <tr>
            <th>Full Name</th>
            <th>Email</th>
            <th>Phone</th>
            <th>Assigned To</th>
            <th>Registration Status</th>
            <th>Update Details</th>
            <th>Actions</th>
        </tr>
        <?php foreach ($declined_leads as $lead): ?>
        <tr>
            <td><?php echo htmlspecialchars($lead['full_name']); ?></td>
            <td><?php echo htmlspecialchars($lead['email']); ?></td>
            <td><?php echo htmlspecialchars($lead['phone']); ?></td>
            <td><?php echo htmlspecialchars($lead['username'] ?: 'N/A'); ?></td>
            <td><?php echo htmlspecialchars($lead['status']); ?></td>
            <td>
                <form method="POST" action="/std_mgmt/views/marketing_user/declined_leads.php?course=<?php echo urlencode($course); ?>&user_id=<?php echo urlencode($user_id); ?>">
                    <input type="hidden" name="lead_id" value="<?php echo htmlspecialchars((string)$lead['lead_id']); ?>">
                    <div class="form-group">
                        <label for="permanent_address_<?php echo $lead['lead_id']; ?>">Permanent Address</label>
                        <input type="text" name="permanent_address" id="permanent_address_<?php echo $lead['lead_id']; ?>" value="<?php echo htmlspecialchars($lead['permanent_address'] ?: ''); ?>">
                    </div>
                    <div class="form-group">
                        <label for="work_experience_<?php echo $lead['lead_id']; ?>">Work Experience</label>
                        <textarea name="work_experience" id="work_experience_<?php echo $lead['lead_id']; ?>"><?php echo htmlspecialchars($lead['work_experience'] ?: ''); ?></textarea>
                    </div>
                    <button type="submit" name="update" value="update" class="btn btn-primary">Update</button>
                </form>
            </td>
            <td>
                <form method="POST" action="/std_mgmt/views/marketing_user/declined_leads.php?course=<?php echo urlencode($course); ?>&user_id=<?php echo urlencode($user_id); ?>" style="display: inline;">
                    <input type="hidden" name="lead_id" value="<?php echo htmlspecialchars((string)$lead['lead_id']); ?>">
                    <button type="submit" name="action" value="resend" class="btn btn-success">Resend for Registration</button>
                </form>
                <a href="/std_mgmt/views/marketing_user/lead_details.php?lead_id=<?php echo htmlspecialchars((string)$lead['lead_id']); ?>" class="btn btn-primary">View Details</a>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>
<?php endif; ?>
<?php include __DIR__ . '/../layouts/footer.php'; ?>