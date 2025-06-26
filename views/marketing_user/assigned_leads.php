<?php 
  include __DIR__ . '/../layouts/header.php'; 
  require_once __DIR__ . '/../../backend/controllers/LeadController.php';
  $leadController = new LeadController($pdo);
  $courses = $leadController->getLeadsByCourse(null, $_SESSION['user_id']);
  ?>
  <h2>Assigned Leads</h2>
  <?php if (isset($_GET['success'])): ?>
      <p style="color: green;"><?php echo htmlspecialchars($_GET['success']); ?></p>
  <?php endif; ?>
  <?php if (isset($_GET['error'])): ?>
      <p style="color: red;"><?php echo htmlspecialchars($_GET['error']); ?></p>
  <?php endif; ?>
  <input type="text" id="searchInput" onkeyup="filterTable()" placeholder="Search courses...">
  <?php if (empty($courses)): ?>
      <p>No assigned leads found.</p>
  <?php else: ?>
      <table class="table" id="dataTable">
          <tr>
              <th>Course Name</th>
              <th>Lead Count</th>
              <th>Action</th>
          </tr>
          <?php foreach ($courses as $course): ?>
          <tr>
              <td><?php echo htmlspecialchars($course['form_name']); ?></td>
              <td><?php echo count($leadController->getLeadsByCourse($course['form_name'], $_SESSION['user_id'])); ?></td>
              <td><a href="/std_mgmt/views/marketing_user/assigned_leads.php?course=<?php echo urlencode($course['form_name']); ?>" class="btn btn-primary">View Leads</a></td>
          </tr>
          <?php endforeach; ?>
      </table>
  <?php endif; ?>
  <?php if (isset($_GET['course'])): ?>
      <h3>Assigned Leads for <?php echo htmlspecialchars($_GET['course']); ?></h3>
      <?php $leads = $leadController->getLeadsByCourse($_GET['course'], $_SESSION['user_id']); ?>
      <?php if (empty($leads)): ?>
          <p>No leads assigned for this course.</p>
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
                      <a href="/std_mgmt/views/marketing_user/lead_details.php?lead_id=<?php echo htmlspecialchars((string)$lead['id']); ?>" class="btn btn-primary">View Details</a>
                      <a href="/std_mgmt/views/marketing_user/send_registration.php?lead_id=<?php echo htmlspecialchars((string)$lead['id']); ?>" class="btn btn-primary">Send to Registration</a>
                  </td>
              </tr>
              <?php endforeach; ?>
          </table>
      <?php endif; ?>
  <?php endif; ?>
  <?php include __DIR__ . '/../layouts/footer.php'; ?>