<?php 
  include __DIR__ . '/../layouts/header.php'; 
  require_once __DIR__ . '/../../backend/controllers/RegistrationController.php';
  $registrationController = new RegistrationController($pdo);
  $user = $_SESSION['user_id'] ?? null;
  $role = $_SESSION['role'] ?? null;
  $courses = $registrationController->getRegisteredLeads($user, $role);
  $course_list = array_unique(array_column($courses, 'form_name'));
  ?>
  <h2>Registered Leads</h2>
  <?php if (empty($course_list)): ?>
      <p>No registered leads found.</p>
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
      <h3>Registered Leads for <?php echo htmlspecialchars($_GET['course']); ?></h3>
      <?php $leads = array_filter($courses, fn($c) => $c['form_name'] === $_GET['course']); ?>
      <?php if (empty($leads)): ?>
          <p>No registered leads for this course.</p>
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
                  </td>
              </tr>
              <?php endforeach; ?>
          </table>
      <?php endif; ?>
  <?php endif; ?>
  <?php include __DIR__ . '/../layouts/footer.php'; ?>