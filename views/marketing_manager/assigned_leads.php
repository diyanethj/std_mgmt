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
$user_id = $_GET['user_id'] ?? '';
$registration_status = $_GET['registration_status'] ?? '';
$course_list = $leadController->getDistinctCourses();
$marketing_users = $authController->getUsersByRole('marketing_user');

if ($course) {
    $leads = $leadController->getLeadsByCourse($course, $user_id ?: null, $registration_status ?: null);
} else {
    $leads = [];
}
?>
<h2>Assigned Leads</h2>
<?php if (isset($_GET['success'])): ?>
    <p style="color: green;"><?php echo htmlspecialchars($_GET['success']); ?></p>
<?php endif; ?>

<!-- Filters -->
<form method="GET" action="/std_mgmt/views/marketing_manager/assigned_leads.php">
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
    <div class="form-group">
        <label for="registration_status">Filter by Registration Status</label>
        <select name="registration_status" id="registration_status">
            <option value="">All</option>
            <option value="pending" <?php echo $registration_status === 'pending' ? 'selected' : ''; ?>>Pending</option>
            <option value="registered" <?php echo $registration_status === 'registered' ? 'selected' : ''; ?>>Registered</option>
            <option value="declined" <?php echo $registration_status === 'declined' ? 'selected' : ''; ?>>Declined</option>
            <option value="N/A" <?php echo $registration_status === 'N/A' ? 'selected' : ''; ?>>N/A</option>
        </select>
    </div>
    <?php if ($course): ?>
        <input type="hidden" name="course" value="<?php echo htmlspecialchars($course); ?>">
    <?php endif; ?>
    <button type="submit" class="btn btn-primary">Apply Filters</button>
</form>

<?php if (empty($course_list)): ?>
    <p>No assigned leads found.</p>
<?php else: ?>
    <h3>Courses</h3>
    <table class="table" id="dataTable">
        <tr>
            <th>Course Name</th>
            <th>Lead Count</th>
            <th>Action</th>
        </tr>
        <?php foreach ($course_list as $course_name): ?>
        <tr>
            <td><?php echo htmlspecialchars($course_name); ?></td>
            <td><?php echo count(array_filter($leadController->getLeadsByCourse($course_name, $user_id ?: null, $registration_status ?: null), fn($lead) => $lead['assigned_user_id'] !== null)); ?></td>
            <td><a href="?course=<?php echo urlencode($course_name); ?>&user_id=<?php echo urlencode($user_id); ?>&registration_status=<?php echo urlencode($registration_status); ?>" class="btn btn-primary">View Leads</a></td>
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
                <a href="/std_mgmt/views/marketing_manager/lead_details.php?lead_id=<?php echo htmlspecialchars((string)$lead['id']); ?>" class="btn btn-primary">View Details</a>
            </td>
        </tr>
        <?php endif; ?>
        <?php endforeach; ?>
    </table>
<?php endif; ?>
<?php include __DIR__ . '/../layouts/footer.php'; ?>