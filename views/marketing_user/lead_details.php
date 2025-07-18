<?php
session_start();
require_once __DIR__ . '/../../backend/config/db_connect.php';
if (!isset($pdo)) {
    error_log("PDO not defined in lead_details.php at " . date('Y-m-d H:i:s'));
    die("Database connection error");
}
require_once __DIR__ . '/../../backend/controllers/LeadController.php';
require_once __DIR__ . '/../../backend/controllers/DocumentController.php';
require_once __DIR__ . '/../../backend/controllers/AuthController.php';
require_once __DIR__ . '/../../backend/controllers/PaymentController.php';

$leadController = new LeadController($pdo);
$documentController = new DocumentController($pdo);
$authController = new AuthController($pdo);
$paymentController = new PaymentController($pdo);

$user = $authController->getCurrentUser();
if (!$user || $user['role'] !== 'marketing_user') {
    header('Location: /std_mgmt/views/auth/login.php?error=Unauthorized%20access');
    exit;
}

if (!isset($_GET['lead_id']) || !is_numeric($_GET['lead_id'])) {
    $error = 'No valid lead ID provided.';
} else {
    $lead_id = (int)$_GET['lead_id'];
    $lead = $leadController->getLeadById($lead_id);
    error_log("Lead data: " . print_r($lead, true) . " at " . date('Y-m-d H:i:s'));
    if (!$lead || ($user['role'] === 'marketing_user' && $lead['assigned_user_id'] != $user['id'])) {
        $error = 'Lead not found or not assigned to you.';
    }
}

if (isset($error)) {
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Error - Student Management System</title>
        <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;700&family=Roboto:wght@300;400&display=swap" rel="stylesheet">
        <link rel="stylesheet" href="/std_mgmt/css/style.css?v=<?php echo time(); ?>">
        <script src="https://cdn.tailwindcss.com"></script>
        <style>
            .sidebar {
                transition: transform 0.3s ease-in-out;
            }
            @media (max-width: 768px) {
                .sidebar {
                    transform: translateX(-100%);
                }
                .sidebar.open {
                    transform: translateX(0);
                }
            }
        </style>
    </head>
    <body class="bg-gradient-to-br from-gray-100 to-gray-300 font-sans">
        <div class="flex min-h-screen">
            <!-- Sidebar -->
            <div id="sidebar" class="sidebar fixed inset-y-0 left-0 w-64 bg-gradient-to-b from-blue-900 to-blue-700 text-white p-4 md:relative md:translate-x-0 z-10 shadow-lg">
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-2xl font-bold text-glow">Marketing User Panel</h2>
                    <button id="closeSidebar" class="md:hidden text-white focus:outline-none">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
                <nav aria-label="Main navigation">
                    <ul class="space-y-2">
                        <li><a href="/std_mgmt/views/marketing_user/dashboard.php" class="block p-2 rounded hover:bg-red-700/30">Dashboard</a></li>
                        <li><a href="/std_mgmt/views/marketing_user/assigned_leads.php" class="block p-2 rounded hover:bg-red-700/30">Assigned Leads</a></li>
                        <li><a href="/std_mgmt/views/marketing_user/pending_registrations.php" class="block p-2 rounded hover:bg-red-700/30 bg-red-700/50">Pending Registrations</a></li>
                        <li><a href="/std_mgmt/views/marketing_user/registered_leads.php" class="block p-2 rounded hover:bg-red-700/30">Registered Leads</a></li>
                        <li><a href="/std_mgmt/views/marketing_user/declined_leads.php" class="block p-2 rounded hover:bg-red-700/30">Declined Leads</a></li>
                        <li><a href="/std_mgmt/views/auth/logout.php" class="block p-2 rounded hover:bg-yellow-600/50 text-yellow-300">Logout</a></li>
                    </ul>
                </nav>
            </div>
            <div class="flex-1 p-4 md:p-8">
                <button id="openSidebar" class="md:hidden mb-4 p-2 bg-gradient-to-r from-blue-600 to-blue-800 text-white rounded-lg shadow-md focus:outline-none">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16m-7 6h7"></path>
                    </svg>
                </button>
                <div class="max-w-4xl mx-auto bg-white/80 backdrop-blur-md p-6 rounded-xl shadow-xl">
                    <div class="p-4 bg-red-100 text-red-700 rounded-lg"><?php echo htmlspecialchars($error); ?></div>
                </div>
            </div>
        </div>
        <script>
            window.onload = () => {
                const openSidebar = document.getElementById('openSidebar');
                const closeSidebar = document.getElementById('closeSidebar');
                const sidebar = document.getElementById('sidebar');
                if (window.innerWidth < 768px) {
                    sidebar.classList.add('translate-x-[-100%]');
                    sidebar.classList.remove('open');
                }
                openSidebar.addEventListener('click', () => {
                    console.log('Opening sidebar');
                    sidebar.classList.add('open');
                    sidebar.classList.remove('translate-x-[-100%]');
                });
                closeSidebar.addEventListener('click', () => {
                    console.log('Closing sidebar');
                    sidebar.classList.remove('open');
                    sidebar.classList.add('translate-x-[-100%]');
                });
            };
        </script>
    </body>
    </html>
    <?php
    exit;
}

// Initialize variables with default values from $lead
$form_name = $lead['form_name'] ?? null;
$title = $lead['title'] ?? null;
$full_name = $lead['full_name'] ?? null;
$nic_number = $lead['nic_number'] ?? null;
$passport_number = $lead['passport_number'] ?? null;
$date_of_birth = $lead['date_of_birth'] ?? null;
$gender = $lead['gender'] ?? null;
$nationality = $lead['nationality'] ?? null;
$marital_status = $lead['marital_status'] ?? null;
$permanent_address = $lead['permanent_address'] ?? null;
$current_address = $lead['current_address'] ?? null;
$mobile_no = $lead['mobile_no'] ?? null;
$email_address = $lead['email_address'] ?? null;
$office_address = $lead['office_address'] ?? null;
$office_email = $lead['office_email'] ?? null;
$parent_guardian_name = $lead['parent_guardian_name'] ?? null;
$parent_contact_number = $lead['parent_contact_number'] ?? null;
$parent_address = $lead['parent_address'] ?? null;
$company_institution = $lead['company_institution'] ?? null;
$postcode = $lead['postcode'] ?? null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $user['role'] === 'marketing_user') {
    if (isset($_POST['update_details'])) {
        $form_name = trim($_POST['form_name'] ?? '');
        $title = trim($_POST['title'] ?? '');
        $full_name = trim($_POST['full_name'] ?? '');
        $nic_number = trim($_POST['nic_number'] ?? '');
        $passport_number = trim($_POST['passport_number'] ?? '');
        $date_of_birth = trim($_POST['date_of_birth'] ?? '');
        $gender = trim($_POST['gender'] ?? '');
        $nationality = trim($_POST['nationality'] ?? '');
        $marital_status = trim($_POST['marital_status'] ?? '');
        $permanent_address = trim($_POST['permanent_address'] ?? '');
        $current_address = trim($_POST['current_address'] ?? '');
        $mobile_no = trim($_POST['mobile_no'] ?? '');
        $email_address = trim($_POST['email_address'] ?? '');
        $office_address = trim($_POST['office_address'] ?? '');
        $office_email = trim($_POST['office_email'] ?? '');
        $parent_guardian_name = trim($_POST['parent_guardian_name'] ?? '');
        $parent_contact_number = trim($_POST['parent_contact_number'] ?? '');
        $parent_address = trim($_POST['parent_address'] ?? '');
        $company_institution = trim($_POST['company_institution'] ?? '');
        $postcode = trim($_POST['postcode'] ?? '');

        if ($leadController->updateLeadDetails($lead_id, $form_name, $title, $full_name, $nic_number, $passport_number, $date_of_birth, $gender, $nationality, $marital_status, $permanent_address, $current_address, $mobile_no, $email_address, $office_address, $office_email, $parent_guardian_name, $parent_contact_number, $parent_address, $company_institution, $postcode)) {
            header('Location: /std_mgmt/views/marketing_user/lead_details.php?lead_id=' . $lead_id . '&success=Details updated successfully');
            exit;
        } else {
            $error = 'Failed to update details';
        }
    } elseif (isset($_POST['upload_document'])) {
        if (isset($_FILES['document']) && $_FILES['document']['error'] === UPLOAD_ERR_OK) {
            $document_type = $_POST['document_type'];
            $valid_types = ['nic_passport', 'academic_docs', 'diploma_certificate', 'employment_history', 'birth_certificate', 'passport_photos'];
            if (in_array($document_type, $valid_types)) {
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
    } elseif (isset($_POST['add_payment'])) {
        $amount = (float)($_POST['amount'] ?? 0);
        $payment_name = trim($_POST['payment_name'] ?? '');
        if (isset($_FILES['receipt']) && $_FILES['receipt']['error'] === UPLOAD_ERR_OK && $amount > 0) {
            if ($paymentController->addPayment($lead_id, $amount, $payment_name, $_FILES['receipt'])) {
                header('Location: /std_mgmt/views/marketing_user/lead_details.php?lead_id=' . $lead_id . '&success=Payment added successfully');
                exit;
            } else {
                $error = 'Failed to add payment';
            }
        } else {
            $error = 'Invalid payment amount or receipt file';
        }
    } elseif (isset($_POST['delete_document'])) {
        $document_id = (int)$_POST['document_id'];
        if ($documentController->deleteDocument($document_id, $lead_id)) {
            header('Location: /std_mgmt/views/marketing_user/lead_details.php?lead_id=' . $lead_id . '&success=Document deleted successfully');
            exit;
        } else {
            $error = 'Failed to delete document';
        }
    } elseif (isset($_POST['delete_payment'])) {
        $payment_id = (int)$_POST['payment_id'];
        if ($paymentController->deletePayment($payment_id, $lead_id)) {
            header('Location: /std_mgmt/views/marketing_user/lead_details.php?lead_id=' . $lead_id . '&success=Payment deleted successfully');
            exit;
        } else {
            $error = 'Failed to delete payment';
        }
    }
}

$documents = $documentController->getDocumentsByLead($lead_id);
$payments = $paymentController->getPaymentsByLead($lead_id);
error_log("Documents: " . print_r($documents, true) . " at " . date('Y-m-d H:i:s'));
error_log("Payments: " . print_r($payments, true) . " at " . date('Y-m-d H:i:s'));

define('BASE_PATH', '/std_mgmt');
$currentPage = basename($_SERVER['PHP_SELF']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lead Details - Student Management System</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;700&family=Roboto:wght@300;400&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/std_mgmt/css/style.css?v=<?php echo time(); ?>">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .sidebar {
            transition: transform 0.3s ease-in-out;
        }
        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
            }
            .sidebar.open {
                transform: translateX(0);
            }
        }
        .table {
            width: 100%;
            border-collapse: collapse;
        }
        .table th, .table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #e5e7eb;
        }
        .table th {
            background-color: #f3f4f6;
            font-weight: 600;
            font-family: 'Poppins', sans-serif;
        }
        .table td {
            font-family: 'Roboto', sans-serif;
        }
        .table tr:hover {
            background-color: #f9fafb;
        }
        .table .action-cell {
            white-space: nowrap;
            min-width: 120px;
        }
        .table .action-cell form, .table .action-cell a, .table .action-cell button {
            display: inline-block;
            margin-right: 8px;
        }
        .form-group {
            margin-bottom: 1rem;
        }
        .form-group label {
            display: block;
            font-family: 'Poppins', sans-serif;
            font-weight: 500;
            color: #1f2937;
            margin-bottom: 0.5rem;
        }
        .form-group input, .form-group textarea, .form-group select {
            width: 100%;
            padding: 8px;
            border: 1px solid #d1d5db;
            border-radius: 6px;
            font-family: 'Roboto', sans-serif;
            background-color: #f9fafb;
        }
        .form-group textarea {
            resize: vertical;
            min-height: 100px;
        }
    </style>
</head>
<body class="bg-gradient-to-br from-gray-100 to-gray-300 font-sans">
    <div class="flex min-h-screen">
        <!-- Sidebar -->
        <div id="sidebar" class="sidebar fixed inset-y-0 left-0 w-64 bg-gradient-to-b from-blue-900 to-blue-700 text-white p-4 md:relative md:translate-x-0 z-10 shadow-lg">
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-2xl font-bold text-glow">Marketing User Panel</h2>
                <button id="closeSidebar" class="md:hidden text-white focus:outline-none">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            <nav aria-label="Main navigation">
                <ul class="space-y-2">
                    <li><a href="<?php echo BASE_PATH; ?>/views/marketing_user/dashboard.php" class="block p-2 rounded hover:bg-red-700/30 <?php echo $currentPage === 'dashboard.php' ? 'bg-red-700/50' : ''; ?>">Dashboard</a></li>
                    <li><a href="<?php echo BASE_PATH; ?>/views/marketing_user/assigned_leads.php" class="block p-2 rounded hover:bg-red-700/30 <?php echo $currentPage === 'assigned_leads.php' ? 'bg-red-700/50' : ''; ?>">Assigned Leads</a></li>
                    <li><a href="<?php echo BASE_PATH; ?>/views/marketing_user/pending_registrations.php" class="block p-2 rounded hover:bg-red-700/30 <?php echo $currentPage === 'pending_registrations.php' ? 'bg-red-700/50' : ''; ?>">Pending Registrations</a></li>
                    <li><a href="<?php echo BASE_PATH; ?>/views/marketing_user/registered_leads.php" class="block p-2 rounded hover:bg-red-700/30 <?php echo $currentPage === 'registered_leads.php' ? 'bg-red-700/50' : ''; ?>">Registered Leads</a></li>
                    <li><a href="<?php echo BASE_PATH; ?>/views/marketing_user/declined_leads.php" class="block p-2 rounded hover:bg-red-700/30 <?php echo $currentPage === 'declined_leads.php' ? 'bg-red-700/50' : ''; ?>">Declined Leads</a></li>
                    <li><a href="<?php echo BASE_PATH; ?>/views/auth/logout.php" class="block p-2 rounded hover:bg-yellow-600/50 text-yellow-300">Logout</a></li>
                </ul>
            </nav>
        </div>

        <!-- Main Content -->
        <div class="flex-1 p-4 md:p-8">
            <!-- Mobile Menu Button -->
            <button id="openSidebar" class="md:hidden mb-4 p-2 bg-gradient-to-r from-blue-600 to-blue-800 text-white rounded-lg shadow-md focus:outline-none">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16m-7 6h7"></path>
                </svg>
            </button>

            <!-- Lead Details Content -->
            <div class="max-w-4xl mx-auto bg-white/80 backdrop-blur-md p-6 rounded-xl shadow-xl">
                <h1 class="text-3xl font-bold mb-4 text-blue-900 text-shadow">Lead Details</h1>
                <?php if (isset($_GET['success'])): ?>
                    <div class="mb-4 p-4 bg-green-100 text-green-700 rounded-lg"><?php echo htmlspecialchars($_GET['success']); ?></div>
                <?php endif; ?>
                <?php if (isset($error)): ?>
                    <div class="mb-4 p-4 bg-red-100 text-red-700 rounded-lg"><?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>

                <h2 class="text-xl font-semibold mb-4 text-blue-800">Lead Information</h2>
                <div class="overflow-x-auto">
                    <table class="table">
                        <tbody>
                            <tr><th>Course Name</th><td><?php echo htmlspecialchars($lead['form_name'] ?? 'N/A'); ?></td></tr>
                            <tr><th>Title</th><td><?php echo htmlspecialchars($lead['title'] ?? 'N/A'); ?></td></tr>
                            <tr><th>Full Name</th><td><?php echo htmlspecialchars($lead['full_name'] ?? 'N/A'); ?></td></tr>
                            <tr><th>NIC Number</th><td><?php echo htmlspecialchars($lead['nic_number'] ?? 'N/A'); ?></td></tr>
                            <tr><th>Passport Number</th><td><?php echo htmlspecialchars($lead['passport_number'] ?? 'N/A'); ?></td></tr>
                            <tr><th>Date of Birth</th><td><?php echo htmlspecialchars($lead['date_of_birth'] ?? 'N/A'); ?></td></tr>
                            <tr><th>Gender</th><td><?php echo htmlspecialchars($lead['gender'] ?? 'N/A'); ?></td></tr>
                            <tr><th>Nationality</th><td><?php echo htmlspecialchars($lead['nationality'] ?? 'N/A'); ?></td></tr>
                            <tr><th>Marital Status</th><td><?php echo htmlspecialchars($lead['marital_status'] ?? 'N/A'); ?></td></tr>
                            <tr><th>Permanent Address</th><td><?php echo htmlspecialchars($lead['permanent_address'] ?? 'N/A'); ?></td></tr>
                            <tr><th>Current Address</th><td><?php echo htmlspecialchars($lead['current_address'] ?? 'N/A'); ?></td></tr>
                            <tr><th>Postcode</th><td><?php echo htmlspecialchars($lead['postcode'] ?? 'N/A'); ?></td></tr>
                            <tr><th>Mobile No</th><td><?php echo htmlspecialchars($lead['mobile_no'] ?? 'N/A'); ?></td></tr>
                            <tr><th>Email Address</th><td><?php echo htmlspecialchars($lead['email_address'] ?? 'N/A'); ?></td></tr>
                            <tr><th>Office Address</th><td><?php echo htmlspecialchars($lead['office_address'] ?? 'N/A'); ?></td></tr>
                            <tr><th>Office Email</th><td><?php echo htmlspecialchars($lead['office_email'] ?? 'N/A'); ?></td></tr>
                            <tr><th>Parent's / Guardian's Name</th><td><?php echo htmlspecialchars($lead['parent_guardian_name'] ?? 'N/A'); ?></td></tr>
                            <tr><th>Parent Contact Number</th><td><?php echo htmlspecialchars($lead['parent_contact_number'] ?? 'N/A'); ?></td></tr>
                            <tr><th>Parent Address</th><td><?php echo htmlspecialchars($lead['parent_address'] ?? 'N/A'); ?></td></tr>
                            <tr><th>Company / Institution</th><td><?php echo htmlspecialchars($lead['company_institution'] ?? 'N/A'); ?></td></tr>
                            <tr><th>Status</th><td><?php echo htmlspecialchars($lead['status'] ?? 'N/A'); ?></td></tr>
                            <tr><th>Created At</th><td><?php echo htmlspecialchars($lead['created_at'] ?? 'N/A'); ?></td></tr>
                        </tbody>
                    </table>
                </div>

                <?php if ($user['role'] === 'marketing_user'): ?>
                    <h2 class="text-xl font-semibold mt-8 mb-4 text-blue-800">Update Lead Details</h2>
                    <form method="POST" action="/std_mgmt/views/marketing_user/lead_details.php?lead_id=<?php echo htmlspecialchars((string)$lead_id); ?>" class="bg-gray-50 p-6 rounded-lg shadow-sm">
                        <input type="hidden" name="update_details" value="1">
                        <h3 class="text-lg font-semibold mb-4 text-blue-700">1.1.1. General Information</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="form-group">
                                <label>Course Name</label>
                                <input type="text" name="form_name" value="<?php echo htmlspecialchars($form_name ?? ''); ?>" required>
                            </div>
                            <div class="form-group">
                                <label>Title (Salutation)</label>
                                <select name="title" required>
                                    <option value="" <?php echo !$title ? 'selected' : ''; ?>>Select Title</option>
                                    <option value="Rev" <?php echo $title === 'Rev' ? 'selected' : ''; ?>>Rev</option>
                                    <option value="Dr" <?php echo $title === 'Dr' ? 'selected' : ''; ?>>Dr</option>
                                    <option value="Mr" <?php echo $title === 'Mr' ? 'selected' : ''; ?>>Mr</option>
                                    <option value="Mrs" <?php echo $title === 'Mrs' ? 'selected' : ''; ?>>Mrs</option>
                                    <option value="Ms" <?php echo $title === 'Ms' ? 'selected' : ''; ?>>Ms</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Full Name</label>
                                <input type="text" name="full_name" value="<?php echo htmlspecialchars($full_name ?? ''); ?>" required>
                            </div>
                            <div class="form-group">
                                <label>NIC Number</label>
                                <input type="text" name="nic_number" value="<?php echo htmlspecialchars($nic_number ?? ''); ?>" placeholder="Enter NIC number">
                            </div>
                            <div class="form-group">
                                <label>Passport Number</label>
                                <input type="text" name="passport_number" value="<?php echo htmlspecialchars($passport_number ?? ''); ?>" placeholder="Enter Passport number">
                            </div>
                            <div class="form-group">
                                <label>Date of Birth</label>
                                <input type="date" name="date_of_birth" value="<?php echo htmlspecialchars($date_of_birth ?? ''); ?>">
                            </div>
                            <div class="form-group">
                                <label>Gender</label>
                                <select name="gender" required>
                                    <option value="" <?php echo !$gender ? 'selected' : ''; ?>>Select Gender</option>
                                    <option value="Male" <?php echo $gender === 'Male' ? 'selected' : ''; ?>>Male</option>
                                    <option value="Female" <?php echo $gender === 'Female' ? 'selected' : ''; ?>>Female</option>
                                    <option value="Other" <?php echo $gender === 'Other' ? 'selected' : ''; ?>>Other</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Nationality</label>
                                <input type="text" name="nationality" value="<?php echo htmlspecialchars($nationality ?? ''); ?>" placeholder="e.g., Sri Lankan">
                            </div>
                            <div class="form-group">
                                <label>Marital Status</label>
                                <select name="marital_status" required>
                                    <option value="" <?php echo !$marital_status ? 'selected' : ''; ?>>Select Status</option>
                                    <option value="Single" <?php echo $marital_status === 'Single' ? 'selected' : ''; ?>>Single</option>
                                    <option value="Married" <?php echo $marital_status === 'Married' ? 'selected' : ''; ?>>Married</option>
                                    <option value="Divorced" <?php echo $marital_status === 'Divorced' ? 'selected' : ''; ?>>Divorced</option>
                                    <option value="Widowed" <?php echo $marital_status === 'Widowed' ? 'selected' : ''; ?>>Widowed</option>
                                </select>
                            </div>
                        </div>

                        <h3 class="text-lg font-semibold mt-6 mb-4 text-blue-700">1.1.2. Contact Information</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="form-group">
                                <label>Permanent Address</label>
                                <textarea name="permanent_address" class="form-control"><?php echo htmlspecialchars($permanent_address ?? ''); ?></textarea>
                            </div>
                            <div class="form-group">
                                <label>Current Address</label>
                                <textarea name="current_address" class="form-control"><?php echo htmlspecialchars($current_address ?? ''); ?></textarea>
                            </div>
                            <div class="form-group">
                                <label>Postcode</label>
                                <input type="text" name="postcode" value="<?php echo htmlspecialchars($postcode ?? ''); ?>" placeholder="Enter postcode">
                            </div>
                            <div class="form-group">
                                <label>Mobile No</label>
                                <input type="text" name="mobile_no" value="<?php echo htmlspecialchars($mobile_no ?? ''); ?>" placeholder="Enter mobile number">
                            </div>
                            <div class="form-group">
                                <label>Email Address</label>
                                <input type="email" name="email_address" value="<?php echo htmlspecialchars($email_address ?? ''); ?>" placeholder="Enter email">
                            </div>
                            <div class="form-group">
                                <label>Office Address</label>
                                <textarea name="office_address" class="form-control"><?php echo htmlspecialchars($office_address ?? ''); ?></textarea>
                            </div>
                            <div class="form-group">
                                <label>Office Email</label>
                                <input type="email" name="office_email" value="<?php echo htmlspecialchars($office_email ?? ''); ?>" placeholder="Enter office email">
                            </div>
                        </div>

                        <h3 class="text-lg font-semibold mt-6 mb-4 text-blue-700">1.1.3. Parent's / Guardian's Contact Details</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="form-group">
                                <label>Parent's / Guardian's Name</label>
                                <input type="text" name="parent_guardian_name" value="<?php echo htmlspecialchars($parent_guardian_name ?? ''); ?>" placeholder="Enter name">
                            </div>
                            <div class="form-group">
                                <label>Contact Number</label>
                                <input type="text" name="parent_contact_number" value="<?php echo htmlspecialchars($parent_contact_number ?? ''); ?>" placeholder="Enter contact number">
                            </div>
                            <div class="form-group">
                                <label>Address</label>
                                <textarea name="parent_address" class="form-control"><?php echo htmlspecialchars($parent_address ?? ''); ?></textarea>
                            </div>
                        </div>

                        <h3 class="text-lg font-semibold mt-6 mb-4 text-blue-700">1.1.4. Working Experience</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="form-group">
                                <label>Company / Institution</label>
                                <input type="text" name="company_institution" value="<?php echo htmlspecialchars($company_institution ?? ''); ?>" placeholder="Enter company/institution name">
                            </div>
                        </div>

                        <button type="submit" class="px-6 py-3 bg-gradient-to-r from-blue-600 to-blue-700 text-white rounded-lg hover:from-blue-700 hover:to-blue-800 shadow-md hover:shadow-lg transition-all duration-300">Update Details</button>
                    </form>

                    <h2 class="text-xl font-semibold mt-8 mb-4 text-blue-800">Upload Document</h2>
                    <form method="POST" action="/std_mgmt/views/marketing_user/lead_details.php?lead_id=<?php echo htmlspecialchars((string)$lead_id); ?>" enctype="multipart/form-data" class="bg-gray-50 p-6 rounded-lg shadow-sm">
                        <input type="hidden" name="upload_document" value="1">
                        <div class="form-group">
                            <label>Document Type</label>
                            <select name="document_type" required>
                                <option value="nic_passport">Copy of NIC/Passport</option>
                                <option value="academic_docs">Copy of Academic Documents (GCE A/L and O/L)</option>
                                <option value="diploma_certificate">Diploma/Higher Diploma/Postgraduate/Degree Certificate</option>
                                <option value="employment_history">Copies of Employment History</option>
                                <option value="birth_certificate">Copy of Birth Certificate</option>
                                <option value="passport_photos">2 Passport Size Photos</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>File</label>
                            <input type="file" name="document" required>
                        </div>
                        <button type="submit" class="px-6 py-3 bg-gradient-to-r from-blue-600 to-blue-700 text-white rounded-lg hover:from-blue-700 hover:to-blue-800 shadow-md hover:shadow-lg transition-all duration-300">Upload Document</button>
                    </form>

                    <h2 class="text-xl font-semibold mt-8 mb-4 text-blue-800">Registration Payment</h2>
                    <form method="POST" action="/std_mgmt/views/marketing_user/lead_details.php?lead_id=<?php echo htmlspecialchars((string)$lead_id); ?>" enctype="multipart/form-data" class="bg-gray-50 p-6 rounded-lg shadow-sm">
                        <input type="hidden" name="add_payment" value="1">
                        <div class="form-group">
                            <label>Payment Name</label>
                            <input type="text" name="payment_name" required placeholder="e.g., First Installment">
                        </div>
                        <div class="form-group">
                            <label>Payment Amount (INR)</label>
                            <input type="number" name="amount" min="0" step="0.01" required placeholder="Enter amount">
                        </div>
                        <div class="form-group">
                            <label>Receipt</label>
                            <input type="file" name="receipt" required>
                        </div>
                        <button type="submit" class="px-6 py-3 bg-gradient-to-r from-blue-600 to-blue-700 text-white rounded-lg hover:from-blue-700 hover:to-blue-800 shadow-md hover:shadow-lg transition-all duration-300">Add Payment</button>
                    </form>
                <?php endif; ?>

                <h2 class="text-xl font-semibold mt-8 mb-4 text-blue-800">Documents</h2>
                <?php if (empty($documents)): ?>
                    <div class="p-4 text-gray-600">No documents uploaded.</div>
                <?php else: ?>
                    <div class="overflow-x-auto">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Document Type</th>
                                    <th>File</th>
                                    <th>Uploaded At</th>
                                    <?php if ($user['role'] === 'marketing_user'): ?>
                                        <th>Action</th>
                                    <?php endif; ?>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $type_labels = [
                                    'nic_passport' => 'Copy of NIC/Passport',
                                    'academic_docs' => 'Copy of Academic Documents (GCE A/L and O/L)',
                                    'diploma_certificate' => 'Diploma/Higher Diploma/Postgraduate/Degree Certificate',
                                    'employment_history' => 'Copies of Employment History',
                                    'birth_certificate' => 'Copy of Birth Certificate',
                                    'passport_photos' => '2 Passport Size Photos'
                                ];
                                foreach ($documents as $doc): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($type_labels[$doc['document_type']] ?? $doc['document_type']); ?></td>
                                        <td><a href="/std_mgmt/uploads/documents/<?php echo htmlspecialchars(basename($doc['file_path'])); ?>" target="_blank" class="text-blue-600 hover:underline">View</a></td>
                                        <td><?php echo htmlspecialchars($doc['uploaded_at']); ?></td>
                                        <?php if ($user['role'] === 'marketing_user'): ?>
                                            <td class="action-cell">
                                                <form method="POST" action="/std_mgmt/views/marketing_user/lead_details.php?lead_id=<?php echo htmlspecialchars((string)$lead_id); ?>" onsubmit="return confirm('Are you sure you want to delete this document?');">
                                                    <input type="hidden" name="delete_document" value="1">
                                                    <input type="hidden" name="document_id" value="<?php echo htmlspecialchars((string)$doc['id']); ?>">
                                                    <button type="submit" class="px-4 py-2 bg-gradient-to-r from-red-600 to-red-700 text-white rounded-lg hover:from-red-700 hover:to-red-800 shadow-md hover:shadow-lg transition-all duration-300">Delete</button>
                                                </form>
                                            </td>
                                        <?php endif; ?>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>

                <h2 class="text-xl font-semibold mt-8 mb-4 text-blue-800">Payments</h2>
                <?php if (empty($payments)): ?>
                    <div class="p-4 text-gray-600">No payments recorded.</div>
                <?php else: ?>
                    <div class="overflow-x-auto">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Payment Name</th>
                                    <th>Amount (INR)</th>
                                    <th>Receipt</th>
                                    <th>Paid At</th>
                                    <?php if ($user['role'] === 'marketing_user'): ?>
                                        <th>Action</th>
                                    <?php endif; ?>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($payments as $payment): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($payment['payment_name'] ?? 'N/A'); ?></td>
                                        <td><?php echo htmlspecialchars(number_format($payment['amount'], 2)); ?></td>
                                        <td><a href="/std_mgmt/uploads/payments/<?php echo htmlspecialchars(basename($payment['receipt_path'])); ?>" target="_blank" class="text-blue-600 hover:underline">View</a></td>
                                        <td><?php echo htmlspecialchars($payment['created_at']); ?></td>
                                        <?php if ($user['role'] === 'marketing_user'): ?>
                                            <td class="action-cell">
                                                <form method="POST" action="/std_mgmt/views/marketing_user/lead_details.php?lead_id=<?php echo htmlspecialchars((string)$lead_id); ?>" onsubmit="return confirm('Are you sure you want to delete this payment?');">
                                                    <input type="hidden" name="delete_payment" value="1">
                                                    <input type="hidden" name="payment_id" value="<?php echo htmlspecialchars((string)$payment['id']); ?>">
                                                    <button type="submit" class="px-4 py-2 bg-gradient-to-r from-red-600 to-red-700 text-white rounded-lg hover:from-red-700 hover:to-red-800 shadow-md hover:shadow-lg transition-all duration-300">Delete</button>
                                                </form>
                                            </td>
                                        <?php endif; ?>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>

                <div class="mt-6">
                    <a href="/std_mgmt/views/<?php echo $user['role']; ?>/pending_registrations.php?course=<?php echo urlencode($lead['form_name'] ?? ''); ?>" class="px-6 py-3 bg-gradient-to-r from-blue-600 to-blue-700 text-white rounded-lg hover:from-blue-700 hover:to-blue-800 shadow-md hover:shadow-lg transition-all duration-300">Back to Pending Registrations</a>
                </div>
            </div>
        </div>
    </div>

    <script>
        window.onload = () => {
            console.log('Page fully loaded');

            const openSidebar = document.getElementById('openSidebar');
            const closeSidebar = document.getElementById('closeSidebar');
            const sidebar = document.getElementById('sidebar');

            if (!openSidebar || !closeSidebar || !sidebar) {
                console.error('Missing elements:', {
                    openSidebar: !!openSidebar,
                    closeSidebar: !!closeSidebar,
                    sidebar: !!sidebar
                });
                return;
            }

            // Sidebar toggle
            if (window.innerWidth < 768px) {
                sidebar.classList.add('translate-x-[-100%]');
                sidebar.classList.remove('open');
            }
            openSidebar.addEventListener('click', () => {
                console.log('Opening sidebar');
                sidebar.classList.add('open');
                sidebar.classList.remove('translate-x-[-100%]');
            });
            closeSidebar.addEventListener('click', () => {
                console.log('Closing sidebar');
                sidebar.classList.remove('open');
                sidebar.classList.add('translate-x-[-100%]');
            });
        };
    </script>
</body>
</html>