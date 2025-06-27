<?php
include __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../../backend/controllers/LeadController.php';
require_once __DIR__ . '/../../backend/controllers/AuthController.php';
$leadController = new LeadController($pdo);
$authController = new AuthController($pdo);

$user = $authController->getCurrentUser();
if (!$user || $user['role'] !== 'marketing_manager') {
    header('Location: /std_mgmt/views/auth/login.php?error=Unauthorized access');
    exit;
}

$course_list = $leadController->getLeadsByCourse(null);
$course = $_GET['course'] ?? '';

if ($course) {
    $leads = $leadController->getLeadsByCourse($course);
} else {
    $leads = [];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['assign_lead'])) {
    $lead_id = (int)$_POST['lead_id'];
    $user_id = (int)$_POST['user_id'];
    if ($leadController->assignLead($lead_id, $user_id)) {
        header('Location: /std_mgmt/views/marketing_manager/leads_list.php?course=' . urlencode($course) . '&success=Lead assigned successfully');
        exit;
    } else {
        $error = 'Failed to assign lead';
    }
}
$marketing_users = $authController->getUsersByRole('marketing_user');
?>
<h2>Leads List</h2>
<?php if (isset($_GET['success'])): ?>
    <p style="color: green;"><?php echo htmlspecialchars($_GET['success']); ?></p>
<?php endif; ?>
<?php if (isset($error)): ?>
    <p style="color: red;"><?php echo htmlspecialchars($error); ?></p>
<?php endif; ?>
<?php if (empty($course_list)): ?>
    <p>No leads found.</p>
<?php else: ?>
    <table class="table" id="dataTable">
        <tr>
            <th>Course Name</th>
            <th>Lead Count</th>
            <th>Action</th>
        </tr>
        <?php foreach (array_unique(array_column($course_list, 'form_name')) as $course_name): ?>
        <tr>
            <td><?php echo htmlspecialchars($course_name); ?></td>
            <td><?php echo count($leadController->getLeadsByCourse($course_name)); ?></td>
            <td><a href="?course=<?php echo urlencode($course_name); ?>" class="btn btn-primary">View Leads</a></td>
        </tr>
        <?php endforeach; ?>
    </table>
<?php endif; ?>
<?php if ($course && !empty($leads)): ?>
    <h3>Leads for <?php echo htmlspecialchars($course); ?></h3>
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
        <tr>
            <td><?php echo htmlspecialchars($lead['full_name']); ?></td>
            <td><?php echo htmlspecialchars($lead['email']); ?></td>
            <td><?php echo htmlspecialchars($lead['phone']); ?></td>
            <td>
                <?php if ($lead['assigned_user_id']): ?>
                    <?php echo htmlspecialchars($lead['username'] ?: 'N/A'); ?>
                <?php else: ?>
                    <form method="POST" action="/std_mgmt/views/marketing_manager/leads_list.php?course=<?php echo urlencode($course); ?>">
                        <input type="hidden" name="assign_lead" value="1">
                        <input type="hidden" name="lead_id" value="<?php echo htmlspecialchars((string)$lead['id']); ?>">
                        <select name="user_id" required>
                            <option value="">Select Marketing User</option>
                            <?php foreach ($marketing_users as $user): ?>
                                <option value="<?php echo htmlspecialchars((string)$user['id']); ?>"><?php echo htmlspecialchars($user['username']); ?></option>
                            <?php endforeach; ?>
                        </select>
                        <button type="submit" class="btn btn-primary">Assign</button>
                    </form>
                <?php endif; ?>
            </td>
            <td><?php echo htmlspecialchars($lead['registration_status'] ?: 'N/A'); ?></td>
            <td>
                <a href="/std_mgmt/views/marketing_manager/lead_details.php?lead_id=<?php echo htmlspecialchars((string)$lead['id']); ?>" class="btn btn-primary">View Details</a>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>
<?php endif; ?>
<?php include __DIR__ . '/../layouts/footer.php'; ?>