<?php 
  include __DIR__ . '/../layouts/header.php'; 
  require_once __DIR__ . '/../../backend/controllers/RegistrationController.php';
  require_once __DIR__ . '/../../backend/controllers/LeadController.php';
  require_once __DIR__ . '/../../backend/controllers/DocumentController.php';
  require_once __DIR__ . '/../../backend/controllers/FollowupController.php';
  $registrationController = new RegistrationController($pdo);
  $leadController = new LeadController($pdo);
  $documentController = new DocumentController($pdo);
  $followupController = new FollowupController($pdo);

  $user = $_SESSION['user_id'] ?? null;
  $role = $_SESSION['role'] ?? null;
  $courses = $registrationController->getPendingRegistrations($user, $role);
  $course_list = array_unique(array_column($courses, 'form_name'));

  if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['approve_registration']) && in_array($role, ['marketing_manager', 'academic_user'])) {
      $lead_id = (int)$_POST['lead_id'];
      $status = $_POST['status'];
      if ($registrationController->approveRegistration($lead_id, $role, $status)) {
          header('Location: /std_mgmt/views/' . $role . '/pending_registrations.php?success=Registration ' . $status);
          exit;
      } else {
          echo '<p style="color: red;">Error: Failed to update registration.</p>';
      }
  }
  ?>
  <h2>Pending Registrations</h2>
  <?php if (isset($_GET['success'])): ?>
      <p style="color: green;"><?php echo htmlspecialchars($_GET['success']); ?></p>
  <?php endif; ?>
  <?php if (empty($course_list)): ?>
      <p>No pending registrations found.</p>
  <?php else: ?>
      <table class="table" id="dataTable">
          <tr>
              <th>Course Name</th>
              <th>Lead Count</th>
              <th>Action</th>
          </tr>
          <?php foreach ($course_list as $course): ?>
          <tr>
              <td><?php echo htmlspecialchars($course); ?></td>
              <td><?php echo count(array_filter($courses, fn($c) => $c['form_name'] === $course)); ?></td>
              <td><a href="?course=<?php echo urlencode($course); ?>" class="btn btn-primary">View Leads</a></td>
          </tr>
          <?php endforeach; ?>
      </table>
  <?php endif; ?>
  <?php if (isset($_GET['course'])): ?>
      <h3>Pending Registrations for <?php echo htmlspecialchars($_GET['course']); ?></h3>
      <?php $leads = array_filter($courses, fn($c) => $c['form_name'] === $_GET['course']); ?>
      <?php if (empty($leads)): ?>
          <p>No pending registrations for this course.</p>
      <?php else: ?>
          <table class="table">
              <tr>
                  <th>Full Name</th>
                  <th>Email</th>
                  <th>Phone</th>
                  <th>Action</th>
              </tr>
              <?php foreach ($leads as $lead): ?>
              <tr>
                  <td><?php echo htmlspecialchars($lead['full_name']); ?></td>
                  <td><?php echo htmlspecialchars($lead['email']); ?></td>
                  <td><?php echo htmlspecialchars($lead['phone']); ?></td>
                  <td>
                      <a href="/std_mgmt/views/<?php echo $role; ?>/lead_details.php?lead_id=<?php echo htmlspecialchars((string)$lead['lead_id']); ?>" class="btn btn-primary">View Details</a>
                      <?php if (in_array($role, ['marketing_manager', 'academic_user'])): ?>
                          <form method="POST" style="display:inline;">
                              <input type="hidden" name="approve_registration" value="1">
                              <input type="hidden" name="lead_id" value="<?php echo htmlspecialchars((string)$lead['lead_id']); ?>">
                              <input type="hidden" name="status" value="accepted">
                              <button type="submit" class="btn btn-success">Accept</button>
                          </form>
                          <form method="POST" style="display:inline;">
                              <input type="hidden" name="approve_registration" value="1">
                              <input type="hidden" name="lead_id" value="<?php echo htmlspecialchars((string)$lead['lead_id']); ?>">
                              <input type="hidden" name="status" value="declined">
                              <button type="submit" class="btn btn-danger">Decline</button>
                          </form>
                      <?php endif; ?>
                  </td>
              </tr>
              <?php endforeach; ?>
          </table>
      <?php endif; ?>
  <?php endif; ?>
  <?php include __DIR__ . '/../layouts/footer.php'; ?>