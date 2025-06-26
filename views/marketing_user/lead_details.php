<?php 
  include __DIR__ . '/../layouts/header.php'; 
  require_once __DIR__ . '/../../backend/controllers/LeadController.php';
  $leadController = new LeadController($pdo);

  if (!isset($_GET['lead_id']) || !is_numeric($_GET['lead_id'])) {
      echo '<p style="color: red;">Error: No valid lead ID provided.</p>';
      include __DIR__ . '/../layouts/footer.php';
      exit;
  }
  $lead_id = (int)$_GET['lead_id'];
  $lead = $leadController->getLeadById($lead_id);

  if (!$lead || $lead['assigned_user_id'] != $_SESSION['user_id']) {
      echo '<p style="color: red;">Error: Lead not found or not assigned to you.</p>';
      include __DIR__ . '/../layouts/footer.php';
      exit;
  }
  ?>
  <h2>Lead Details</h2>
  <table class="table">
      <tr>
          <th>Field</th>
          <th>Value</th>
      </tr>
      <tr>
          <td>Course Name</td>
          <td><?php echo htmlspecialchars($lead['form_name']); ?></td>
      </tr>
      <tr>
          <td>Full Name</td>
          <td><?php echo htmlspecialchars($lead['full_name']); ?></td>
      </tr>
      <tr>
          <td>Email</td>
          <td><?php echo htmlspecialchars($lead['email']); ?></td>
      </tr>
      <tr>
          <td>Phone</td>
          <td><?php echo htmlspecialchars($lead['phone']); ?></td>
      </tr>
      <tr>
          <td>Permanent Address</td>
          <td><?php echo htmlspecialchars($lead['permanent_address'] ?: 'N/A'); ?></td>
      </tr>
      <tr>
          <td>Work Experience</td>
          <td><?php echo htmlspecialchars($lead['work_experience'] ?: 'N/A'); ?></td>
      </tr>
      <tr>
          <td>Status</td>
          <td><?php echo htmlspecialchars($lead['status']); ?></td>
      </tr>
      <tr>
          <td>Created At</td>
          <td><?php echo htmlspecialchars($lead['created_at']); ?></td>
      </tr>
  </table>
  <a href="/std_mgmt/views/marketing_user/assigned_leads.php?course=<?php echo urlencode($lead['form_name']); ?>" class="btn btn-primary">Back to Assigned Leads</a>
  <?php include __DIR__ . '/../layouts/footer.php'; ?>