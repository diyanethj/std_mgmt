<?php 
  include __DIR__ . '/../layouts/header.php'; 
  require_once __DIR__ . '/../../backend/controllers/LeadController.php';
  require_once __DIR__ . '/../../backend/controllers/DocumentController.php';
  require_once __DIR__ . '/../../backend/controllers/FollowupController.php';
  $leadController = new LeadController($pdo);
  $documentController = new DocumentController($pdo);
  $followupController = new FollowupController($pdo);

  if (!isset($_GET['lead_id']) || !is_numeric($_GET['lead_id'])) {
      echo '<p style="color: red;">Error: No valid lead ID provided.</p>';
      include __DIR__ . '/../layouts/footer.php';
      exit;
  }
  $lead_id = (int)$_GET['lead_id'];
  $lead = $leadController->getLeadById($lead_id);
  $role = $_SESSION['role'] ?? null;

  if (!$lead || ($role === 'marketing_user' && $lead['assigned_user_id'] != $_SESSION['user_id'])) {
      echo '<p style="color: red;">Error: Lead not found or not assigned to you.</p>';
      include __DIR__ . '/../layouts/footer.php';
      exit;
  }

  if ($_SERVER['REQUEST_METHOD'] === 'POST' && $role === 'marketing_user') {
      if (isset($_POST['update_details'])) {
          $permanent_address = trim($_POST['permanent_address'] ?? '');
          $work_experience = trim($_POST['work_experience'] ?? '');
          if (method_exists($leadController, 'updateLeadDetails') && $leadController->updateLeadDetails($lead_id, $permanent_address, $work_experience)) {
              header('Location: /std_mgmt/views/marketing_user/lead_details.php?lead_id=' . $lead_id . '&success=Details updated successfully');
              exit;
          } else {
              $error = 'Failed to update details';
          }
      } elseif (isset($_POST['upload_document'])) {
          if (isset($_FILES['document']) && $_FILES['document']['error'] === UPLOAD_ERR_OK) {
              $document_type = $_POST['document_type'];
              if (method_exists($documentController, 'uploadDocument') && in_array($document_type, ['nic', 'education', 'work_experience', 'birth_certificate'])) {
                  if ($documentController->uploadDocument($lead_id, $document_type, $_FILES['document'])) {
                      header('Location: /std_mgmt/views/marketing_user/lead_details.php?lead_id=' . $lead_id . '&success=Document uploaded successfully');
                      exit;
                  } else {
                      $error = 'Failed to upload document';
                  }
              } else {
                  $error = 'Invalid document type';
              }
          } else {
              $error = 'Invalid document file';
          }
      } elseif (isset($_POST['add_followup'])) {
          $number = (int)($_POST['number'] ?? 0);
          $followup_date = $_POST['followup_date'] ?? '';
          $comment = trim($_POST['comment'] ?? '');
          if ($number > 0 && $followup_date && $comment) {
              if ($followupController->addFollowup($lead_id, $number, $followup_date, $comment)) {
                  header('Location: /std_mgmt/views/marketing_user/lead_details.php?lead_id=' . $lead_id . '&success=Follow-up added successfully');
                  exit;
              } else {
                  $error = 'Failed to add follow-up';
              }
          } else {
              $error = 'All follow-up fields are required';
          }
      } elseif (isset($_POST['update_followup'])) {
          $followup_id = (int)$_POST['followup_id'];
          $number = (int)($_POST['number'] ?? 0);
          $followup_date = $_POST['followup_date'] ?? '';
          $comment = trim($_POST['comment'] ?? '');
          if ($number > 0 && $followup_date && $comment) {
              if ($followupController->updateFollowup($followup_id, $lead_id, $number, $followup_date, $comment)) {
                  header('Location: /std_mgmt/views/marketing_user/lead_details.php?lead_id=' . $lead_id . '&success=Follow-up updated successfully');
                  exit;
              } else {
                  $error = 'Failed to update follow-up';
              }
          } else {
              $error = 'All follow-up fields are required';
          }
      } elseif (isset($_POST['delete_document'])) {
          $document_id = (int)$_POST['document_id'];
          if ($documentController->deleteDocument($document_id, $lead_id)) {
              header('Location: /std_mgmt/views/marketing_user/lead_details.php?lead_id=' . $lead_id . '&success=Document deleted successfully');
              exit;
          } else {
              $error = 'Failed to delete document';
          }
      } elseif (isset($_POST['delete_followup'])) {
          $followup_id = (int)$_POST['followup_id'];
          if ($followupController->deleteFollowup($followup_id, $lead_id)) {
              header('Location: /std_mgmt/views/marketing_user/lead_details.php?lead_id=' . $lead_id . '&success=Follow-up deleted successfully');
              exit;
          } else {
              $error = 'Failed to delete follow-up';
          }
      }
  }

  $documents = $documentController->getDocumentsByLead($lead_id);
  $followups = $followupController->getFollowupsByLead($lead_id);
  ?>
  <h2>Lead Details</h2>
  <?php if (isset($_GET['success'])): ?>
      <p style="color: green;"><?php echo htmlspecialchars($_GET['success']); ?></p>
  <?php endif; ?>
  <?php if (isset($error)): ?>
      <p style="color: red;"><?php echo htmlspecialchars($error); ?></p>
  <?php endif; ?>

  <h3>Lead Information</h3>
  <table class="table">
      <tr><th>Course Name</th><td><?php echo htmlspecialchars($lead['form_name']); ?></td></tr>
      <tr><th>Full Name</th><td><?php echo htmlspecialchars($lead['full_name']); ?></td></tr>
      <tr><th>Email</th><td><?php echo htmlspecialchars($lead['email']); ?></td></tr>
      <tr><th>Phone</th><td><?php echo htmlspecialchars($lead['phone']); ?></td></tr>
      <tr><th>Permanent Address</th><td><?php echo htmlspecialchars($lead['permanent_address'] ?: 'N/A'); ?></td></tr>
      <tr><th>Work Experience</th><td><?php echo htmlspecialchars($lead['work_experience'] ?: 'N/A'); ?></td></tr>
      <tr><th>Status</th><td><?php echo htmlspecialchars($lead['status']); ?></td></tr>
      <tr><th>Created At</th><td><?php echo htmlspecialchars($lead['created_at']); ?></td></tr>
  </table>

  <?php if ($role === 'marketing_user'): ?>
      <h3>Update Address and Work Experience</h3>
      <form method="POST" action="/std_mgmt/views/marketing_user/lead_details.php?lead_id=<?php echo htmlspecialchars((string)$lead_id); ?>">
          <input type="hidden" name="update_details" value="1">
          <div class="form-group">
              <label>Permanent Address</label>
              <textarea name="permanent_address" class="form-control"><?php echo htmlspecialchars($lead['permanent_address'] ?: ''); ?></textarea>
          </div>
          <div class="form-group">
              <label>Work Experience</label>
              <textarea name="work_experience" class="form-control"><?php echo htmlspecialchars($lead['work_experience'] ?: ''); ?></textarea>
          </div>
          <button type="submit" class="btn btn-primary">Update Details</button>
      </form>

      <h3>Upload Document</h3>
      <form method="POST" action="/std_mgmt/views/marketing_user/lead_details.php?lead_id=<?php echo htmlspecialchars((string)$lead_id); ?>" enctype="multipart/form-data">
          <input type="hidden" name="upload_document" value="1">
          <div class="form-group">
              <label>Document Type</label>
              <select name="document_type" required>
                  <option value="nic">NIC Copy</option>
                  <option value="education">Education Documents</option>
                  <option value="work_experience">Work Experience Documents</option>
                  <option value="birth_certificate">Birth Certificate</option>
              </select>
          </div>
          <div class="form-group">
              <label>File</label>
              <input type="file" name="document" required>
          </div>
          <button type="submit" class="btn btn-primary">Upload Document</button>
      </form>

      <h3>Add Follow-up</h3>
      <form method="POST" action="/std_mgmt/views/marketing_user/lead_details.php?lead_id=<?php echo htmlspecialchars((string)$lead_id); ?>">
          <input type="hidden" name="add_followup" value="1">
          <div class="form-group">
              <label>Follow-up Number</label>
              <input type="number" name="number" min="1" required>
          </div>
          <div class="form-group">
              <label>Follow-up Date</label>
              <input type="date" name="followup_date" required>
          </div>
          <div class="form-group">
              <label>Comment</label>
              <textarea name="comment" class="form-control" required></textarea>
          </div>
          <button type="submit" class="btn btn-primary">Add Follow-up</button>
      </form>
  <?php endif; ?>

  <h3>Documents</h3>
  <?php if (empty($documents)): ?>
      <p>No documents uploaded.</p>
  <?php else: ?>
      <table class="table">
          <tr>
              <th>Document Type</th>
              <th>File</th>
              <th>Uploaded At</th>
              <?php if ($role === 'marketing_user'): ?>
                  <th>Action</th>
              <?php endif; ?>
          </tr>
          <?php foreach ($documents as $doc): ?>
          <tr>
              <td><?php
                  $type_labels = [
                      'nic' => 'NIC Copy',
                      'education' => 'Education Documents',
                      'work_experience' => 'Work Experience Documents',
                      'birth_certificate' => 'Birth Certificate'
                  ];
                  echo htmlspecialchars($type_labels[$doc['document_type']] ?? $doc['document_type']);
              ?></td>
              <td><a href="/std_mgmt/uploads/documents/<?php echo htmlspecialchars(basename($doc['file_path'])); ?>" target="_blank">View</a></td>
              <td><?php echo htmlspecialchars($doc['uploaded_at']); ?></td>
              <?php if ($role === 'marketing_user'): ?>
                  <td>
                      <form method="POST" action="/std_mgmt/views/marketing_user/lead_details.php?lead_id=<?php echo htmlspecialchars((string)$lead_id); ?>" onsubmit="return confirm('Are you sure you want to delete this document?');">
                          <input type="hidden" name="delete_document" value="1">
                          <input type="hidden" name="document_id" value="<?php echo htmlspecialchars((string)$doc['id']); ?>">
                          <button type="submit" class="btn btn-danger">Delete</button>
                      </form>
                  </td>
              <?php endif; ?>
          </tr>
          <?php endforeach; ?>
      </table>
  <?php endif; ?>

  <h3>Follow-ups</h3>
  <?php if (empty($followups)): ?>
      <p>No follow-ups added.</p>
  <?php else: ?>
      <table class="table">
          <tr>
              <th>Number</th>
              <th>Date</th>
              <th>Comment</th>
              <th>Created At</th>
              <?php if ($role === 'marketing_user'): ?>
                  <th>Actions</th>
              <?php endif; ?>
          </tr>
          <?php foreach ($followups as $followup): ?>
          <tr>
              <td><?php echo htmlspecialchars($followup['number']); ?></td>
              <td><?php echo htmlspecialchars($followup['followup_date']); ?></td>
              <td><?php echo htmlspecialchars($followup['comment']); ?></td>
              <td><?php echo htmlspecialchars($followup['created_at']); ?></td>
              <?php if ($role === 'marketing_user'): ?>
                  <td>
                      <button class="btn btn-primary" onclick="editFollowup(<?php echo htmlspecialchars((string)$followup['id']); ?>, '<?php echo htmlspecialchars($followup['number']); ?>', '<?php echo htmlspecialchars($followup['followup_date']); ?>', '<?php echo htmlspecialchars($followup['comment']); ?>')">Edit</button>
                      <form method="POST" action="/std_mgmt/views/marketing_user/lead_details.php?lead_id=<?php echo htmlspecialchars((string)$lead_id); ?>" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this follow-up?');">
                          <input type="hidden" name="delete_followup" value="1">
                          <input type="hidden" name="followup_id" value="<?php echo htmlspecialchars((string)$followup['id']); ?>">
                          <button type="submit" class="btn btn-danger">Delete</button>
                      </form>
                  </td>
              <?php endif; ?>
          </tr>
          <?php endforeach; ?>
      </table>
  <?php endif; ?>

  <?php if ($role === 'marketing_user'): ?>
      <div id="editFollowupModal" style="display:none; position:fixed; top:20%; left:20%; right:20%; background:white; padding:20px; border:1px solid #ccc;">
          <h3>Edit Follow-up</h3>
          <form method="POST" action="/std_mgmt/views/marketing_user/lead_details.php?lead_id=<?php echo htmlspecialchars((string)$lead_id); ?>">
              <input type="hidden" name="update_followup" value="1">
              <input type="hidden" name="followup_id" id="edit_followup_id">
              <div class="form-group">
                  <label>Follow-up Number</label>
                  <input type="number" name="number" id="edit_number" min="1" required>
              </div>
              <div class="form-group">
                  <label>Follow-up Date</label>
                  <input type="date" name="followup_date" id="edit_followup_date" required>
              </div>
              <div class="form-group">
                  <label>Comment</label>
                  <textarea name="comment" id="edit_comment" class="form-control" required></textarea>
              </div>
              <button type="submit" class="btn btn-primary">Update Follow-up</button>
              <button type="button" class="btn btn-secondary" onclick="closeModal()">Cancel</button>
          </form>
      </div>

      <script>
          function editFollowup(id, number, date, comment) {
              document.getElementById('edit_followup_id').value = id;
              document.getElementById('edit_number').value = number;
              document.getElementById('edit_followup_date').value = date;
              document.getElementById('edit_comment').value = comment;
              document.getElementById('editFollowupModal').style.display = 'block';
          }
          function closeModal() {
              document.getElementById('editFollowupModal').style.display = 'none';
          }
      </script>
  <?php endif; ?>

  <a href="/std_mgmt/views/<?php echo $role; ?>/pending_registrations.php?course=<?php echo urlencode($lead['form_name']); ?>" class="btn btn-primary">Back to Pending Registrations</a>
  <?php include __DIR__ . '/../layouts/footer.php'; ?>