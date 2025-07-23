<?php
session_start();
require_once __DIR__ . '/../../backend/config/db_connect.php';
if (!isset($pdo)) {
    error_log("PDO not defined in dashboard.php at " . date('Y-m-d H:i:s'));
    header('Location: /std_mgmt/views/error.php?message=Database%20connection%20error');
    exit;
}
require_once __DIR__ . '/../../backend/controllers/AuthController.php';
require_once __DIR__ . '/../../backend/controllers/LeadController.php';
require_once __DIR__ . '/../../backend/controllers/DocumentController.php';
require_once __DIR__ . '/../../backend/controllers/PaymentController.php';
require_once __DIR__ . '/../../backend/controllers/PaymentPlanController.php';

$authController = new AuthController($pdo);
$leadController = new LeadController($pdo);
$documentController = new DocumentController($pdo);
$paymentController = new PaymentController($pdo);
$paymentPlanController = new PaymentPlanController($pdo);

$user = $authController->getCurrentUser();
if (!$user || $user['role'] !== 'student') {
    header('Location: /std_mgmt/views/auth/login.php?error=Unauthorized%20access');
    exit;
}

$lead = $leadController->getLeadByUserId($user['id']);
if (!$lead) {
    $error = 'No lead details found for your account.';
}

$lead_id = $lead['id'] ?? null;
$documents = $lead_id ? $documentController->getDocumentsByLead($lead_id) : [];
$payments = $lead_id ? $paymentController->getPaymentsByLead($lead_id) : [];
$payment_plans = $paymentPlanController->getAllPaymentPlans();
$assigned_plan = $lead_id ? $paymentController->getAssignedPaymentPlan($lead_id) : null;
$installments = [];
if ($assigned_plan && is_array($assigned_plan) && isset($assigned_plan['plan_id'])) {
    $installments = $paymentController->getInstallmentsForPlan($lead_id, $assigned_plan['plan_id']);
} else {
    error_log("Assigned plan is invalid or missing plan_id for lead $lead_id at " . date('Y-m-d H:i:s') . ". Assigned plan: " . print_r($assigned_plan, true));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // CSRF token validation
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $error = 'CSRF validation failed';
    } else {
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

            if (!filter_var($email_address, FILTER_VALIDATE_EMAIL)) {
                $error = 'Invalid email address';
            } elseif (!filter_var($office_email, FILTER_VALIDATE_EMAIL, FILTER_NULL_ON_FAILURE)) {
                $error = 'Invalid office email address';
            } elseif ($lead_id && $leadController->updateLeadDetails($lead_id, $form_name, $title, $full_name, $nic_number, $passport_number, $date_of_birth, $gender, $nationality, $marital_status, $permanent_address, $current_address, $mobile_no, $email_address, $office_address, $office_email, $parent_guardian_name, $parent_contact_number, $parent_address, $company_institution, $postcode)) {
                header('Location: /std_mgmt/views/student/dashboard.php?success=Details updated successfully');
                exit;
            } else {
                $error = 'Failed to update details';
            }
        } elseif (isset($_POST['upload_document'])) {
            if (isset($_FILES['document']) && $_FILES['document']['error'] === UPLOAD_ERR_OK) {
                $max_size = 10 * 1024 * 1024; // 10MB limit
                $allowed_types = ['image/jpeg', 'image/png', 'application/pdf'];
                if ($_FILES['document']['size'] > $max_size) {
                    $error = 'File size exceeds 10MB limit';
                } elseif (!in_array(mime_content_type($_FILES['document']['tmp_name']), $allowed_types)) {
                    $error = 'Invalid file type. Only JPEG, PNG, and PDF are allowed';
                } else {
                    $document_type = $_POST['document_type'];
                    $valid_types = ['nic_passport', 'academic_docs', 'diploma_certificate', 'employment_history', 'birth_certificate', 'passport_photos'];
                    if ($lead_id && in_array($document_type, $valid_types)) {
                        if ($documentController->uploadDocument($lead_id, $document_type, $_FILES['document'])) {
                            header('Location: /std_mgmt/views/student/dashboard.php?success=Document uploaded successfully');
                            exit;
                        } else {
                            $error = 'Failed to upload document';
                        }
                    } else {
                        $error = 'Invalid document type';
                    }
                }
            } else {
                $error = 'Invalid document file';
            }
        } elseif (isset($_POST['add_payment'])) {
            $payment_name = 'registration payment';
            $amount = (float)($_POST['amount'] ?? 0);
            if (isset($_FILES['receipt']) && $_FILES['receipt']['error'] === UPLOAD_ERR_OK) {
                $max_size = 10 * 1024 * 1024; // 10MB limit
                $allowed_types = ['image/jpeg', 'image/png', 'application/pdf'];
                if ($_FILES['receipt']['size'] > $max_size) {
                    $error = 'Receipt file size exceeds 10MB limit';
                } elseif (!in_array(mime_content_type($_FILES['receipt']['tmp_name']), $allowed_types)) {
                    $error = 'Invalid receipt file type. Only JPEG, PNG, and PDF are allowed';
                } elseif ($lead_id && $amount > 0) {
                    if ($paymentController->addPayment($lead_id, $amount, $payment_name, $_FILES['receipt'])) {
                        header('Location: /std_mgmt/views/student/dashboard.php?success=Payment submitted successfully');
                        exit;
                    } else {
                        $error = 'Failed to submit payment';
                    }
                } else {
                    $error = 'Invalid payment details';
                }
            } else {
                $error = 'Invalid receipt file';
            }
        } elseif (isset($_POST['assign_payment_plan'])) {
            $plan_id = (int)($_POST['plan_id'] ?? 0);
            $paid_amounts = [];
            foreach ($_POST as $key => $value) {
                if (strpos($key, 'paid_amount_') === 0) {
                    $installment_id = substr($key, 12);
                    $paid_amounts[$installment_id] = (float)$value;
                }
            }
            if ($plan_id > 0 && $paymentController->assignPaymentPlan($lead_id, $plan_id, $paid_amounts)) {
                header('Location: /std_mgmt/views/student/dashboard.php?success=Payment plan assigned successfully');
                exit;
            } else {
                $error = 'Failed to assign payment plan';
            }
        } elseif (isset($_POST['update_installment'])) {
            $installment_id = (int)($_POST['installment_id'] ?? 0);
            $paid_amount = (float)($_POST['paid_amount'] ?? 0);
            $paid_date = $_POST['paid_date'] ?? date('Y-m-d');
            $receipt_path = null;
            if (isset($_FILES['receipt']) && $_FILES['receipt']['error'] === UPLOAD_ERR_OK) {
                $max_size = 10 * 1024 * 1024; // 10MB limit
                $allowed_types = ['image/jpeg', 'image/png', 'application/pdf'];
                if ($_FILES['receipt']['size'] > $max_size) {
                    $error = 'Receipt file size exceeds 10MB limit';
                } elseif (!in_array(mime_content_type($_FILES['receipt']['tmp_name']), $allowed_types)) {
                    $error = 'Invalid receipt file type. Only JPEG, PNG, and PDF are allowed';
                } else {
                    $uploadDir = __DIR__ . '/../../uploads/receipts/';
                    if (!is_dir($uploadDir)) {
                        mkdir($uploadDir, 0777, true);
                    }
                    $fileName = uniqid() . '_' . basename($_FILES['receipt']['name']);
                    $filePath = $uploadDir . $fileName;
                    if (move_uploaded_file($_FILES['receipt']['tmp_name'], $filePath)) {
                        $receipt_path = $filePath;
                    } else {
                        $error = 'Failed to upload receipt';
                    }
                }
            }
            if (!$error) {
                $stmt = $pdo->prepare("INSERT INTO payment_records (lead_id, plan_installment_id, amount_paid, paid_date, receipt_path, created_at) VALUES (?, ?, ?, ?, ?, NOW()) ON DUPLICATE KEY UPDATE amount_paid = VALUES(amount_paid), paid_date = VALUES(paid_date), receipt_path = VALUES(receipt_path)");
                $success = $stmt->execute([$lead_id, $installment_id, $paid_amount, $paid_date, $receipt_path]);
                if ($success) {
                    header('Location: /std_mgmt/views/student/dashboard.php?success=Installment updated successfully');
                    exit;
                } else {
                    $error = 'Failed to update installment';
                }
            }
        }
    }
}

$_SESSION['csrf_token'] = bin2hex(random_bytes(32));
define('BASE_PATH', '/std_mgmt');
$currentPage = basename($_SERVER['PHP_SELF']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Student Management System</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;700&family=Roboto:wght@300;400&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/std_mgmt/css/style.css?v=<?php echo time(); ?>">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .sidebar { transition: transform 0.3s ease-in-out; }
        @media (max-width: 768px) { .sidebar { transform: translateX(-100%); } .sidebar.open { transform: translateX(0); } }
        .table { width: 100%; border-collapse: collapse; }
        .table th, .table td { padding: 12px; text-align: left; border-bottom: 1px solid #e5e7eb; }
        .table th { background-color: #f3f4f6; font-weight: 600; font-family: 'Poppins', sans-serif; }
        .table td { font-family: 'Roboto', sans-serif; }
        .table tr:hover { background-color: #f9fafb; }
        .form-group { margin-bottom: 1rem; }
        .form-group label { display: block; font-family: 'Poppins', sans-serif; font-weight: 500; color: #1f2937; margin-bottom: 0.5rem; }
        .form-group input, .form-group textarea, .form-group select { width: 100%; padding: 8px; border: 1px solid #d1d5db; border-radius: 6px; font-family: 'Roboto', sans-serif; background-color: #f9fafb; }
        .form-group textarea { resize: vertical; min-height: 100px; }
        .installment-table { margin-top: 1rem; border: 1px solid #e5e7eb; border-radius: 6px; }
        .installment-table th, .installment-table td { padding: 8px; text-align: left; }
        .installment-table th { background-color: #f3f4f6; }
    </style>
</head>
<body class="bg-gradient-to-br from-gray-100 to-gray-300 font-sans">
    <div class="flex min-h-screen">
        <!-- Sidebar -->
        <div id="sidebar" class="sidebar fixed inset-y-0 left-0 w-64 bg-gradient-to-b from-blue-900 to-blue-700 text-white p-4 md:relative md:translate-x-0 z-10 shadow-lg">
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-2xl font-bold text-glow">Student Panel</h2>
                <button id="closeSidebar" class="md:hidden text-white focus:outline-none">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            <nav aria-label="Main navigation">
                <ul class="space-y-2">
                    <li><a href="<?php echo BASE_PATH; ?>/views/student/dashboard.php" class="block p-2 rounded hover:bg-red-700/30 bg-red-700/50">Dashboard</a></li>
                    <li><a href="<?php echo BASE_PATH; ?>/views/auth/logout.php" class="block p-2 rounded hover:bg-yellow-600/50 text-yellow-300">Logout</a></li>
                </ul>
            </nav>
        </div>

        <!-- Main Content -->
        <div class="flex-1 p-4 md:p-8">
            <button id="openSidebar" class="md:hidden mb-4 p-2 bg-gradient-to-r from-blue-600 to-blue-800 text-white rounded-lg shadow-md focus:outline-none">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16m-7 6h7"></path>
                </svg>
            </button>

            <div class="max-w-4xl mx-auto bg-white/80 backdrop-blur-md p-6 rounded-xl shadow-xl">
                <h1 class="text-3xl font-bold mb-4 text-blue-900 text-shadow">My Profile</h1>
                <?php if (isset($_GET['success'])): ?>
                    <div class="mb-4 p-4 bg-green-100 text-green-700 rounded-lg"><?php echo htmlspecialchars($_GET['success']); ?></div>
                <?php endif; ?>
                <?php if (isset($error)): ?>
                    <div class="mb-4 p-4 bg-red-100 text-red-700 rounded-lg"><?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>

                <?php if ($lead): ?>
                    <!-- Lead Information -->
                    <h2 class="text-xl font-semibold mb-4 text-blue-800">Your Information</h2>
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

                    <!-- Update Profile Form -->
                    <h2 class="text-xl font-semibold mt-8 mb-4 text-blue-800">Update Your Details</h2>
                    <form method="POST" action="/std_mgmt/views/student/dashboard.php" class="bg-gray-50 p-6 rounded-lg shadow-sm mb-6">
                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                        <input type="hidden" name="update_details" value="1">
                        <h3 class="text-lg font-semibold mb-4 text-blue-700">General Information</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="form-group">
                                <label>Course Name</label>
                                <input type="text" name="form_name" value="<?php echo htmlspecialchars($lead['form_name'] ?? ''); ?>" required>
                            </div>
                            <div class="form-group">
                                <label>Title (Salutation)</label>
                                <select name="title" required>
                                    <option value="" <?php echo !$lead['title'] ? 'selected' : ''; ?>>Select Title</option>
                                    <option value="Rev" <?php echo $lead['title'] === 'Rev' ? 'selected' : ''; ?>>Rev</option>
                                    <option value="Dr" <?php echo $lead['title'] === 'Dr' ? 'selected' : ''; ?>>Dr</option>
                                    <option value="Mr" <?php echo $lead['title'] === 'Mr' ? 'selected' : ''; ?>>Mr</option>
                                    <option value="Mrs" <?php echo $lead['title'] === 'Mrs' ? 'selected' : ''; ?>>Mrs</option>
                                    <option value="Ms" <?php echo $lead['title'] === 'Ms' ? 'selected' : ''; ?>>Ms</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Full Name</label>
                                <input type="text" name="full_name" value="<?php echo htmlspecialchars($lead['full_name'] ?? ''); ?>" required>
                            </div>
                            <div class="form-group">
                                <label>NIC Number</label>
                                <input type="text" name="nic_number" value="<?php echo htmlspecialchars($lead['nic_number'] ?? ''); ?>" placeholder="Enter NIC number">
                            </div>
                            <div class="form-group">
                                <label>Passport Number</label>
                                <input type="text" name="passport_number" value="<?php echo htmlspecialchars($lead['passport_number'] ?? ''); ?>" placeholder="Enter Passport number">
                            </div>
                            <div class="form-group">
                                <label>Date of Birth</label>
                                <input type="date" name="date_of_birth" value="<?php echo htmlspecialchars($lead['date_of_birth'] ?? ''); ?>">
                            </div>
                            <div class="form-group">
                                <label>Gender</label>
                                <select name="gender" required>
                                    <option value="" <?php echo !$lead['gender'] ? 'selected' : ''; ?>>Select Gender</option>
                                    <option value="Male" <?php echo $lead['gender'] === 'Male' ? 'selected' : ''; ?>>Male</option>
                                    <option value="Female" <?php echo $lead['gender'] === 'Female' ? 'selected' : ''; ?>>Female</option>
                                    <option value="Other" <?php echo $lead['gender'] === 'Other' ? 'selected' : ''; ?>>Other</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Nationality</label>
                                <input type="text" name="nationality" value="<?php echo htmlspecialchars($lead['nationality'] ?? ''); ?>" placeholder="e.g., Sri Lankan">
                            </div>
                            <div class="form-group">
                                <label>Marital Status</label>
                                <select name="marital_status" required>
                                    <option value="" <?php echo !$lead['marital_status'] ? 'selected' : ''; ?>>Select Status</option>
                                    <option value="Single" <?php echo $lead['marital_status'] === 'Single' ? 'selected' : ''; ?>>Single</option>
                                    <option value="Married" <?php echo $lead['marital_status'] === 'Married' ? 'selected' : ''; ?>>Married</option>
                                    <option value="Divorced" <?php echo $lead['marital_status'] === 'Divorced' ? 'selected' : ''; ?>>Divorced</option>
                                    <option value="Widowed" <?php echo $lead['marital_status'] === 'Widowed' ? 'selected' : ''; ?>>Widowed</option>
                                </select>
                            </div>
                        </div>

                        <h3 class="text-lg font-semibold mt-6 mb-4 text-blue-700">Contact Information</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="form-group">
                                <label>Permanent Address</label>
                                <textarea name="permanent_address"><?php echo htmlspecialchars($lead['permanent_address'] ?? ''); ?></textarea>
                            </div>
                            <div class="form-group">
                                <label>Current Address</label>
                                <textarea name="current_address"><?php echo htmlspecialchars($lead['current_address'] ?? ''); ?></textarea>
                            </div>
                            <div class="form-group">
                                <label>Postcode</label>
                                <input type="text" name="postcode" value="<?php echo htmlspecialchars($lead['postcode'] ?? ''); ?>" placeholder="Enter postcode">
                            </div>
                            <div class="form-group">
                                <label>Mobile No</label>
                                <input type="text" name="mobile_no" value="<?php echo htmlspecialchars($lead['mobile_no'] ?? ''); ?>" placeholder="Enter mobile number">
                            </div>
                            <div class="form-group">
                                <label>Email Address</label>
                                <input type="email" name="email_address" value="<?php echo htmlspecialchars($lead['email_address'] ?? ''); ?>" placeholder="Enter email">
                            </div>
                            <div class="form-group">
                                <label>Office Address</label>
                                <textarea name="office_address"><?php echo htmlspecialchars($lead['office_address'] ?? ''); ?></textarea>
                            </div>
                            <div class="form-group">
                                <label>Office Email</label>
                                <input type="email" name="office_email" value="<?php echo htmlspecialchars($lead['office_email'] ?? ''); ?>" placeholder="Enter office email">
                            </div>
                        </div>

                        <h3 class="text-lg font-semibold mt-6 mb-4 text-blue-700">Parent's / Guardian's Contact Details</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="form-group">
                                <label>Parent's / Guardian's Name</label>
                                <input type="text" name="parent_guardian_name" value="<?php echo htmlspecialchars($lead['parent_guardian_name'] ?? ''); ?>" placeholder="Enter name">
                            </div>
                            <div class="form-group">
                                <label>Contact Number</label>
                                <input type="text" name="parent_contact_number" value="<?php echo htmlspecialchars($lead['parent_contact_number'] ?? ''); ?>" placeholder="Enter contact number">
                            </div>
                            <div class="form-group">
                                <label>Address</label>
                                <textarea name="parent_address"><?php echo htmlspecialchars($lead['parent_address'] ?? ''); ?></textarea>
                            </div>
                        </div>

                        <h3 class="text-lg font-semibold mt-6 mb-4 text-blue-700">Working Experience</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="form-group">
                                <label>Company / Institution</label>
                                <input type="text" name="company_institution" value="<?php echo htmlspecialchars($lead['company_institution'] ?? ''); ?>" placeholder="Enter company/institution name">
                            </div>
                        </div>

                        <button type="submit" class="px-6 py-3 bg-gradient-to-r from-blue-600 to-blue-700 text-white rounded-lg hover:from-blue-700 hover:to-blue-800 shadow-md hover:shadow-lg transition-all duration-300">Update Profile</button>
                    </form>

                    <!-- Upload Document Form -->
                    <h2 class="text-xl font-semibold mt-8 mb-4 text-blue-800">Upload Documents</h2>
                    <form method="POST" action="/std_mgmt/views/student/dashboard.php" enctype="multipart/form-data" class="bg-gray-50 p-6 rounded-lg shadow-sm mb-6">
                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                        <input type="hidden" name="upload_document" value="1">
                        <div class="form-group">
                            <label>Document Type</label>
                            <select name="document_type" required>
                                <option value="">Select Document Type</option>
                                <option value="nic_passport">NIC/Passport</option>
                                <option value="academic_docs">Academic Documents</option>
                                <option value="diploma_certificate">Diploma Certificate</option>
                                <option value="employment_history">Employment History</option>
                                <option value="birth_certificate">Birth Certificate</option>
                                <option value="passport_photos">Passport Photos</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Upload Document</label>
                            <input type="file" name="document" accept=".jpg,.jpeg,.png,.pdf" required>
                        </div>
                        <button type="submit" class="px-6 py-3 bg-gradient-to-r from-blue-600 to-blue-700 text-white rounded-lg hover:from-blue-700 hover:to-blue-800 shadow-md hover:shadow-lg transition-all duration-300">Upload Document</button>
                    </form>

                    <!-- Registration Payment Form -->
                    <h2 class="text-xl font-semibold mt-8 mb-4 text-blue-800">Registration Payment</h2>
                    <form method="POST" action="/std_mgmt/views/student/dashboard.php" enctype="multipart/form-data" class="bg-gray-50 p-6 rounded-lg shadow-sm mb-6">
                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                        <input type="hidden" name="add_payment" value="1">
                        <div class="form-group">
                            <label>Payment Name</label>
                            <input type="text" name="payment_name" value="registration payment" readonly>
                        </div>
                        <div class="form-group">
                            <label>Amount (LKR)</label>
                            <input type="number" name="amount" step="0.01" min="0" required placeholder="Enter amount in LKR">
                        </div>
                        <div class="form-group">
                            <label>Upload Receipt</label>
                            <input type="file" name="receipt" accept=".jpg,.jpeg,.png,.pdf" required>
                        </div>
                        <div class="form-group">
                            <label>Full Name</label>
                            <input type="text" name="full_name" value="<?php echo htmlspecialchars($lead['full_name'] ?? $user['username'] ?? ''); ?>" readonly>
                        </div>
                        <div class="form-group">
                            <label>Email</label>
                            <input type="email" name="email" value="<?php echo htmlspecialchars($lead['email_address'] ?? $user['username'] ?? ''); ?>" readonly>
                        </div>
                        <button type="submit" class="px-6 py-3 bg-gradient-to-r from-blue-600 to-blue-700 text-white rounded-lg hover:from-blue-700 hover:to-blue-800 shadow-md hover:shadow-lg transition-all duration-300">Submit Payment</button>
                    </form>

                    <!-- Payment Plan Section -->
                    

                    <!-- Documents Section -->
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
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>

                    <!-- Payments Section -->
                    <h2 class="text-xl font-semibold mt-8 mb-4 text-blue-800">Payments</h2>
                    <?php if (empty($payments)): ?>
                        <div class="p-4 text-gray-600">No payments recorded.</div>
                    <?php else: ?>
                        <div class="overflow-x-auto">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Payment Name</th>
                                        <th>Amount (LKR)</th>
                                        <th>Receipt</th>
                                        <th>Paid At</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($payments as $payment): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($payment['payment_name'] ?? 'N/A'); ?></td>
                                            <td><?php echo htmlspecialchars(number_format($payment['amount'], 2)); ?></td>
                                            <td><a href="/std_mgmt/uploads/payments/<?php echo htmlspecialchars(basename($payment['receipt_path'])); ?>" target="_blank" class="text-blue-600 hover:underline">View</a></td>
                                            <td><?php echo htmlspecialchars($payment['created_at']); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                <?php else: ?>
                    <div class="p-4 text-gray-600">No profile information available.</div>
                <?php endif; ?>
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
                sidebar.classList.add('open');
                sidebar.classList.remove('translate-x-[-100%]');
            });
            closeSidebar.addEventListener('click', () => {
                sidebar.classList.remove('open');
                sidebar.classList.add('translate-x-[-100%]');
            });
        };
    </script>
</body>
</html>