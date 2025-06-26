<?php
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
                error_log("Session after login: " . print_r($_SESSION, true));
                header('Location: /std_mgmt/views/' . $_SESSION['role'] . '/dashboard.php');
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
            $leadController->assignLead($_POST['lead_id'], $_POST['user_id']);
        }
        break;
    case '/std_mgmt/marketing/send_registration':
        if ($method === 'POST') {
            $leadController->sendToRegistration($_POST['lead_id']);
            $registrationController->createRegistration($_POST['lead_id']);
        }
        break;
    case '/std_mgmt/marketing/upload_document':
        if ($method === 'POST') {
            $documentController->uploadDocument($_POST['lead_id'], $_POST['document_type'], $_FILES['document']);
        }
        break;
    case '/std_mgmt/marketing/add_followup':
        if ($method === 'POST') {
            $followupController->addFollowup($_POST['lead_id'], $_POST['date'], $_POST['comment']);
        }
        break;
    case '/std_mgmt/registration/approve':
        if ($method === 'POST') {
            $registrationController->approveRegistration($_POST['lead_id'], $_POST['role'], $_POST['status']);
        }
        break;
}