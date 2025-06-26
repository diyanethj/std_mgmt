<?php 
include __DIR__ . '/../layouts/header.php'; 
require_once __DIR__ . '/../../backend/controllers/RegistrationController.php';
$registrationController = new RegistrationController($pdo);
$pending = $registrationController->getPendingRegistrations();
?>
<h2>Pending Registrations</h2>
<table class="table">
    <tr>
        <th>Full Name</th>
        <th>Course</th>
        <th>Email</th>
        <th>Phone</th>
        <th>Action</th>
    </tr>
    <?php foreach ($pending as $registration): ?>
    <tr>
        <td><?php echo htmlspecialchars($registration['full_name']); ?></td>
        <td><?php echo htmlspecialchars($registration['form_name']); ?></td>
        <td><?php echo htmlspecialchars($registration['email']); ?></td>
        <td><?php echo htmlspecialchars($registration['phone']); ?></td>
        <td><a href="/std_mgmt/views/marketing/lead_details.php?lead_id=<?php echo htmlspecialchars($registration['lead_id']); ?>" class="btn btn-primary">View Details</a></td>
    </tr>
    <?php endforeach; ?>
</table>
<?php include __DIR__ . '/../layouts/footer.php'; ?>