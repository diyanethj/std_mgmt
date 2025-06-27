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

$course = $_GET['course'] ?? '';
$leads = $leadController->getLeadsByCourse($course, null, 'N/A'); // Unassigned leads
$course_list = $leadController->getDistinctCourses();

// Get marketing users for assignment
$marketing_users = $authController->getUsersByRole('marketing_user');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['lead_id'], $_POST['user_id'])) {
    $lead_id = (int)$_POST['lead_id'];
    $user_id = (int)$_POST['user_id'];
    if ($leadController->assignLead($lead_id, $user_id)) {
        header('Location: /std_mgmt/views/marketing_manager/assign_leads.php?course=' . urlencode($course) . '&success=Lead assigned successfully');
        exit;
    } else {
        $error = 'Failed to assign lead';
    }
}
?>
<h2>Assign Leads</h2>
<?php if (isset($_GET['success'])): ?>
    <p style="color: green;"><?php echo htmlspecialchars($_GET['success']); ?></p>
<?php endif; ?>
<?php if (isset($error)): ?>
    <p style="color: red;"><?php echo htmlspecialchars($error); ?></p>
<?php endif; ?>

<?php if (empty($course_list)): ?>
    <p>No courses found.</p>
<?php else: ?>
    <h3>Courses</h3>
    <table class="table" id="dataTable">
        <tr>
            <th>Course Name</th>
            <th>Unassigned Lead Count</th>
            <th>Action</th>
        </tr>
        <?php foreach ($course_list as $course_name): ?>
        <tr>
            <td><?php echo htmlspecialchars($course_name); ?></td>
            <td><?php echo count($leadController->getLeadsByCourse($course_name, null, 'N/A')); ?></td>
            <td><a href="?course=<?php echo urlencode($course_name); ?>" class="btn btn-primary">View Leads</a></td>
        </tr>
        <?php endforeach; ?>
    </table>
<?php endif; ?>
<?php if ($course && !empty($leads)): ?>
    <h3>Unassigned Leads for <?php echo htmlspecialchars($course); ?></h3>
    <table class="table">
        <tr>
            <th>Full Name</th>
            <th>Email</th>
            <th>Phone</th>
            <th>Assign To</th>
            <th>Actions</th>
        </tr>
        <?php foreach ($leads as $lead): ?>
        <tr>
            <td><?php echo htmlspecialchars($lead['full_name']); ?></td>
            <td><?php echo htmlspecialchars($lead['email']); ?></td>
            <td><?php echo htmlspecialchars($lead['phone']); ?></td>
            <td>
                <form method="POST" action="/std_mgmt/views/marketing_manager/assign_leads.php?course=<?php echo urlencode($course); ?>">
                    <input type="hidden" name="lead_id" value="<?php echo htmlspecialchars((string)$lead['id']); ?>">
                    <select name="user_id" required>
                        <option value="">Select Marketing User</option>
                        <?php foreach ($marketing_users as $m_user): ?>
                            <option value="<?php echo htmlspecialchars((string)$m_user['id']); ?>">
                                <?php echo htmlspecialchars($m_user['username']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <button type="submit" class="btn btn-success">Assign</button>
                </form>
            </td>
            <td>
                <a href="/std_mgmt/views/marketing_manager/lead_details.php?lead_id=<?php echo htmlspecialchars((string)$lead['id']); ?>" class="btn btn-primary">View Details</a>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>
<?php endif; ?>
<?php include __DIR__ . '/../layouts/footer.php'; ?>