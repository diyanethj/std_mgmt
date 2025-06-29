<?php
include __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../../backend/controllers/RegistrationController.php';
require_once __DIR__ . '/../../backend/controllers/AuthController.php';

$registrationController = new RegistrationController($pdo);
$authController = new AuthController($pdo);

$user = $authController->getCurrentUser();
if (!$user || $user['role'] !== 'marketing_manager') {
    header('Location: /std_mgmt/views/auth/login.php?error=Unauthorized access');
    exit;
}

$course = $_GET['course'] ?? null;

// Get distinct courses with registered leads
$registered_leads = $registrationController->getRegisteredLeads();
error_log("Marketing manager registered leads (all): " . print_r($registered_leads, true));
$course_list = array_unique(array_column($registered_leads, 'form_name'));

if ($course) {
    $course_leads = $registrationController->getRegisteredLeads(null, $course);
    error_log("Marketing manager filtered registered leads (course: $course): " . print_r($course_leads, true));
}
?>
<div class="container">
    <h2 class="dashboard-title">Registered Leads</h2>
    <?php if (isset($_GET['success'])): ?>
        <p style="color: green;"><?php echo htmlspecialchars($_GET['success']); ?></p>
    <?php endif; ?>
    <?php if (isset($_GET['error'])): ?>
        <p style="color: red;"><?php echo htmlspecialchars($_GET['error']); ?></p>
    <?php endif; ?>

    <div class="form-group">
        <input type="text" id="searchInput" onkeyup="filterTable()" placeholder="Search leads by name or course..." class="search-input">
    </div>

    <?php if (empty($course_list)): ?>
        <p>No registered leads found.</p>
    <?php else: ?>
        <h3>Courses</h3>
        <table class="table" id="dataTable">
            <thead>
                <tr>
                    <th>Course Name</th>
                    <th>Lead Count</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($course_list as $course_name): ?>
                <tr>
                    <td><?php echo htmlspecialchars($course_name); ?></td>
                    <td><?php echo count($registrationController->getRegisteredLeads(null, $course_name)); ?></td>
                    <td><a href="?course=<?php echo urlencode($course_name); ?>" class="btn btn-primary">View Leads</a></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>

    <?php if ($course && !empty($course_leads)): ?>
        <h3>Registered Leads for <?php echo htmlspecialchars($course); ?></h3>
        <table class="table" id="courseTable">
            <thead>
                <tr>
                    <th>Full Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($course_leads as $lead): ?>
                <tr>
                    <td><?php echo htmlspecialchars($lead['full_name']); ?></td>
                    <td><?php echo htmlspecialchars($lead['email']); ?></td>
                    <td><?php echo htmlspecialchars($lead['phone']); ?></td>
                    <td><a href="/std_mgmt/views/marketing_manager/lead_details.php?lead_id=<?php echo htmlspecialchars((string)$lead['lead_id']); ?>&course=<?php echo urlencode($course); ?>" class="btn btn-primary">View Details</a></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<script>
function filterTable() {
    const input = document.getElementById('searchInput').value.toLowerCase();
    const tables = [document.getElementById('dataTable'), document.getElementById('courseTable')];
    
    tables.forEach(table => {
        if (table) {
            const rows = table.querySelectorAll('tbody tr');
            rows.forEach(row => {
                const cells = row.querySelectorAll('td');
                let match = false;
                cells.forEach(cell => {
                    if (cell.textContent.toLowerCase().includes(input)) {
                        match = true;
                    }
                });
                row.style.display = match ? '' : 'none';
            });
        }
    });
}
</script>
<?php include __DIR__ . '/../layouts/footer.php'; ?>