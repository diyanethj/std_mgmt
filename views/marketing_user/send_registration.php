<?php 
  include __DIR__ . '/../layouts/header.php'; 
  require_once __DIR__ . '/../../backend/controllers/LeadController.php';
  require_once __DIR__ . '/../../backend/controllers/RegistrationController.php';
  $leadController = new LeadController($pdo);
  $registrationController = new RegistrationController($pdo);

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

  if ($_SERVER['REQUEST_METHOD'] === 'POST') {
      if ($leadController->sendToRegistration($lead_id) && $registrationController->createRegistration($lead_id)) {
          header('Location: /std_mgmt/views/marketing_user/assigned_leads.php?course=' . urlencode($lead['form_name']) . '&success=Lead sent to registration');
          exit;
      } else {
          echo '<p style="color: red;">Error: Failed to send lead to registration.</p>';
      }
  }
  ?>
  <h2>Send Lead to Registration</h2>
  <p>Lead: <?php echo htmlspecialchars($lead['full_name']); ?> (<?php echo htmlspecialchars($lead['form_name']); ?>)</p>
  <form method="POST" action="/std_mgmt/views/marketing_user/send_registration.php?lead_id=<?php echo htmlspecialchars((string)$lead_id); ?>">
      <button type="submit" class="btn btn-primary">Confirm Send to Registration</button>
  </form>
  <a href="/std_mgmt/views/marketing_user/assigned_leads.php?course=<?php echo urlencode($lead['form_name']); ?>" class="btn btn-secondary">Cancel</a>
  <?php include __DIR__ . '/../layouts/footer.php'; ?>