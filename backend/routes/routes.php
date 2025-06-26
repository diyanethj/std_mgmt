<?php
  session_start();
  require_once __DIR__ . '/../config/db_connect.php';
  require_once __DIR__ . '/../controllers/AuthController.php';
  require_once __DIR__ . '/../controllers/LeadController.php';
  require_once __DIR__ . '/../controllers/DocumentController.php';
  require_once __DIR__ . '/../controllers/FollowupController.php';
  require_once __DIR__ . '/../controllers/RegistrationController.php';

  $authController = new AuthController($pdo);
  $leadController = new LeadController($pdo);
  $documentController = new DocumentController($pdo);
  $followupController = new FollowupController($pdo);
  $registrationController = new RegistrationController($pdo);

  $request = $_SERVER['REQUEST_URI'];
  $method = $_SERVER['REQUEST_METHOD'];

  switch ($request) {
      case '/std_mgmt/login':
          if ($method === 'POST') {
              if ($authController->login($_POST['username'], $_POST['password'])) {
                  $role = $_SESSION['role'];
                  $dashboard = $role === 'marketing_user' ? '/std_mgmt/views/marketing_user/dashboard.php' : "/std_mgmt/views/$role/dashboard.php";
                  header("Location: $dashboard");
                  exit;
              } else {
                  header('Location: /std_mgmt/views/auth/login.php?error=Invalid credentials');
                  exit;
              }
          } else {
              header('Location: /std_mgmt/views/auth/login.php');
              exit;
          }
          break;
      case '/std_mgmt/logout':
          $authController->logout();
          break;
      case '/std_mgmt/admin/upload_leads':
          if ($method === 'POST') {
              $user = $authController->getCurrentUser();
              if ($user && $user['role'] === 'admin') {
                  $leadController->uploadLeads($_FILES['csv_file']);
                  header('Location: /std_mgmt/views/admin/upload_leads.php?success=Leads uploaded successfully');
                  exit;
              } else {
                  header('Location: /std_mgmt/views/auth/login.php?error=Unauthorized access');
                  exit;
              }
          }
          break;
      case '/std_mgmt/admin/assign_lead':
          if ($method === 'POST') {
              $user = $authController->getCurrentUser();
              if ($user && $user['role'] === 'admin') {
                  if (isset($_POST['lead_id'], $_POST['user_id']) && is_numeric($_POST['lead_id']) && is_numeric($_POST['user_id'])) {
                      $leadController->assignLead($_POST['lead_id'], $_POST['user_id']);
                      header('Location: /std_mgmt/views/admin/leads_list.php?success=Lead assigned successfully');
                      exit;
                  } else {
                      header('Location: /std_mgmt/views/admin/leads_list.php?error=Invalid lead or user ID');
                      exit;
                  }
              } else {
                  header('Location: /std_mgmt/views/auth/login.php?error=Unauthorized access');
                  exit;
              }
          }
          break;
      case '/std_mgmt/marketing/send_registration':
          if ($method === 'POST') {
              $user = $authController->getCurrentUser();
              if ($user && $user['role'] === 'marketing_user') {
                  $leadController->sendToRegistration($_POST['lead_id']);
                  $registrationController->createRegistration($_POST['lead_id']);
                  header('Location: /std_mgmt/views/marketing_user/assigned_leads.php?success=Lead sent to registration');
                  exit;
              } else {
                  header('Location: /std_mgmt/views/auth/login.php?error=Unauthorized access');
                  exit;
              }
          }
          break;
      case '/std_mgmt/marketing/upload_document':
          if ($method === 'POST') {
              $user = $authController->getCurrentUser();
              if ($user && $user['role'] === 'marketing_user') {
                  $documentController->uploadDocument($_POST['lead_id'], $_POST['document_type'], $_FILES['document']);
                  header('Location: /std_mgmt/views/marketing_user/lead_details.php?lead_id=' . urlencode($_POST['lead_id']) . '&success=Document uploaded successfully');
                  exit;
              } else {
                  header('Location: /std_mgmt/views/auth/login.php?error=Unauthorized access');
                  exit;
              }
          }
          break;
      case '/std_mgmt/marketing/add_followup':
          if ($method === 'POST') {
              $user = $authController->getCurrentUser();
              if ($user && $user['role'] === 'marketing_user') {
                  $followupController->addFollowup($_POST['lead_id'], $_POST['number'], $_POST['followup_date'], $_POST['comment']);
                  header('Location: /std_mgmt/views/marketing_user/lead_details.php?lead_id=' . urlencode($_POST['lead_id']) . '&success=Follow-up added successfully');
                  exit;
              } else {
                  header('Location: /std_mgmt/views/auth/login.php?error=Unauthorized access');
                  exit;
              }
          }
          break;
      case '/std_mgmt/marketing/update_followup':
          if ($method === 'POST') {
              $user = $authController->getCurrentUser();
              if ($user && $user['role'] === 'marketing_user') {
                  $followupController->updateFollowup($_POST['followup_id'], $_POST['lead_id'], $_POST['number'], $_POST['followup_date'], $_POST['comment']);
                  header('Location: /std_mgmt/views/marketing_user/lead_details.php?lead_id=' . urlencode($_POST['lead_id']) . '&success=Follow-up updated successfully');
                  exit;
              } else {
                  header('Location: /std_mgmt/views/auth/login.php?error=Unauthorized access');
                  exit;
              }
          }
          break;
      case '/std_mgmt/marketing/delete_document':
          if ($method === 'POST') {
              $user = $authController->getCurrentUser();
              if ($user && $user['role'] === 'marketing_user') {
                  $documentController->deleteDocument($_POST['document_id'], $_POST['lead_id']);
                  header('Location: /std_mgmt/views/marketing_user/lead_details.php?lead_id=' . urlencode($_POST['lead_id']) . '&success=Document deleted successfully');
                  exit;
              } else {
                  header('Location: /std_mgmt/views/auth/login.php?error=Unauthorized access');
                  exit;
              }
          }
          break;
      case '/std_mgmt/marketing/delete_followup':
          if ($method === 'POST') {
              $user = $authController->getCurrentUser();
              if ($user && $user['role'] === 'marketing_user') {
                  $followupController->deleteFollowup($_POST['followup_id'], $_POST['lead_id']);
                  header('Location: /std_mgmt/views/marketing_user/lead_details.php?lead_id=' . urlencode($_POST['lead_id']) . '&success=Follow-up deleted successfully');
                  exit;
              } else {
                  header('Location: /std_mgmt/views/auth/login.php?error=Unauthorized access');
                  exit;
              }
          }
          break;
      case '/std_mgmt/registration/approve':
          if ($method === 'POST') {
              $user = $authController->getCurrentUser();
              if ($user && in_array($user['role'], ['marketing_manager', 'academic_user'])) {
                  $registrationController->approveRegistration($_POST['lead_id'], $_POST['role'], $_POST['status']);
                  header('Location: /std_mgmt/views/' . $user['role'] . '/pending_registrations.php?success=Registration updated');
                  exit;
              } else {
                  header('Location: /std_mgmt/views/auth/login.php?error=Unauthorized access');
                  exit;
              }
          }
          break;
  }