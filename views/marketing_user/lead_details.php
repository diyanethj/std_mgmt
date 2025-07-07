<?php
session_start();
require_once __DIR__ . '/../../backend/config/db_connect.php';
if (!isset($pdo)) {
    error_log("PDO not defined in lead_details.php at " . date('Y-m-d H:i:s'));
    die("Database connection error");
}
require_once __DIR__ . '/../../backend/controllers/LeadController.php';
require_once __DIR__ . '/../../backend/controllers/DocumentController.php';
require_once __DIR__ . '/../../backend/controllers/FollowupController.php';
require_once __DIR__ . '/../../backend/controllers/AuthController.php';
require_once __DIR__ . '/../../backend/controllers/PaymentController.php';

$leadController = new LeadController($pdo);
$documentController = new DocumentController($pdo);
$followupController = new FollowupController($pdo);
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

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $user['role'] === 'marketing_user') {
    if (isset($_POST['update_details'])) {
        $permanent_address = trim($_POST['permanent_address'] ?? '');
        $work_experience = trim($_POST['work_experience'] ?? '');
        $date_of_birth = trim($_POST['date_of_birth'] ?? ''); // Added
        $nic_number = trim($_POST['nic_number'] ?? ''); // Added
        if ($leadController->updateLeadDetails($lead_id, $permanent_address, $work_experience, $date_of_birth, $nic_number)) { // Updated to include new fields
            header('Location: /std_mgmt/views/marketing_user/lead_details.php?lead_id=' . $lead_id . '&success=Details updated successfully');
            exit;
        } else {
            $error = 'Failed to update details';
        }
    } elseif (isset($_POST['upload_document'])) {
        if (isset($_FILES['document']) && $_FILES['document']['error'] === UPLOAD_ERR_OK) {
            $document_type = $_POST['document_type'];
            if (in_array($document_type, ['nic', 'education', 'work_experience', 'birth_certificate'])) {
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
    } elseif (isset($_POST['update_followups'])) {
        $followupUpdates = [];
        if (isset($_POST['followup_id']) && is_array($_POST['followup_id'])) {
            foreach ($_POST['followup_id'] as $index => $followup_id) {
                $number = (int)($_POST['number'][$index] ?? 0);
                $followup_date = $_POST['followup_date'][$index] ?? '';
                $comment = trim($_POST['comment'][$index] ?? '');
                if ($number > 0 && $followup_date && $comment) {
                    $followupUpdates[$followup_id] = ['number' => $number, 'followup_date' => $followup_date, 'comment' => $comment];
                }
            }
        }
        $success = false;
        if (!empty($followupUpdates)) {
            if (method_exists($followupController, 'updateMultipleFollowups')) {
                $success = $followupController->updateMultipleFollowups($lead_id, $followupUpdates);
            } else {
                foreach ($followupUpdates as $followup_id => $data) {
                    $success = $followupController->updateFollowup($followup_id, $lead_id, $data['number'], $data['followup_date'], $data['comment']);
                    if (!$success) break;
                }
            }
        }
        if ($success) {
            header('Location: /std_mgmt/views/marketing_user/lead_details.php?lead_id=' . $lead_id . '&success=Follow-ups updated successfully');
            exit;
        } else {
            $error = 'Failed to update follow-ups or no valid updates provided';
        }
    } elseif (isset($_POST['add_payment'])) {
        $amount = (float)($_POST['amount'] ?? 0);
        $payment_name = trim($_POST['payment_name'] ?? ''); // Added payment name
        if (isset($_FILES['receipt']) && $_FILES['receipt']['error'] === UPLOAD_ERR_OK && $amount > 0) {
            if ($paymentController->addPayment($lead_id, $amount, $payment_name, $_FILES['receipt'])) { // Updated to include payment_name
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
    } elseif (isset($_POST['delete_followup'])) {
        $followup_id = (int)$_POST['followup_id'];
        if ($followupController->deleteFollowup($followup_id, $lead_id)) {
            header('Location: /std_mgmt/views/marketing_user/lead_details.php?lead_id=' . $lead_id . '&success=Follow-up deleted successfully');
            exit;
        } else {
            $error = 'Failed to delete follow-up';
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
$followups = $followupController->getFollowupsByLead($lead_id);
$payments = $paymentController->getPaymentsByLead($lead_id);
error_log("Documents: " . print_r($documents, true) . " at " . date('Y-m-d H:i:s'));
error_log("Follow-ups: " . print_r($followups, true) . " at " . date('Y-m-d H:i:s'));
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
                            <tr><th>Course Name</th><td><?php echo htmlspecialchars($lead['form_name']); ?></td></tr>
                            <tr><th>Full Name</th><td><?php echo htmlspecialchars($lead['full_name']); ?></td></tr>
                            <tr><th>Email</th><td><?php echo htmlspecialchars($lead['email']); ?></td></tr>
                            <tr><th>Phone</th><td><?php echo htmlspecialchars($lead['phone']); ?></td></tr>
                            <tr><th>Date of Birth</th><td><?php echo htmlspecialchars($lead['date_of_birth'] ?: 'N/A'); ?></td></tr>
                            <tr><th>NIC Number</th><td><?php echo htmlspecialchars($lead['nic_number'] ?: 'N/A'); ?></td></tr>
                            <tr><th>Permanent Address</th><td><?php echo htmlspecialchars($lead['permanent_address'] ?: 'N/A'); ?></td></tr>
                            <tr><th>Work Experience</th><td><?php echo htmlspecialchars($lead['work_experience'] ?: 'N/A'); ?></td></tr>
                            <tr><th>Status</th><td><?php echo htmlspecialchars($lead['status']); ?></td></tr>
                            <tr><th>Created At</th><td><?php echo htmlspecialchars($lead['created_at']); ?></td></tr>
                        </tbody>
                    </table>
                </div>

                <?php if ($user['role'] === 'marketing_user'): ?>
                    <h2 class="text-xl font-semibold mt-8 mb-4 text-blue-800">Update Address and Work Experience</h2>
                    <form method="POST" action="/std_mgmt/views/marketing_user/lead_details.php?lead_id=<?php echo htmlspecialchars((string)$lead_id); ?>" class="bg-gray-50 p-6 rounded-lg shadow-sm">
                        <input type="hidden" name="update_details" value="1">
                        <div class="form-group">
                            <label>Date of Birth</label>
                            <input type="date" name="date_of_birth" value="<?php echo htmlspecialchars($lead['date_of_birth'] ?: ''); ?>">
                        </div>
                        <div class="form-group">
                            <label>NIC Number</label>
                            <input type="text" name="nic_number" value="<?php echo htmlspecialchars($lead['nic_number'] ?: ''); ?>" placeholder="Enter NIC number">
                        </div>
                        <div class="form-group">
                            <label>Permanent Address</label>
                            <textarea name="permanent_address" class="form-control"><?php echo htmlspecialchars($lead['permanent_address'] ?: ''); ?></textarea>
                        </div>
                        <div class="form-group">
                            <label>Work Experience</label>
                            <textarea name="work_experience" class="form-control"><?php echo htmlspecialchars($lead['work_experience'] ?: ''); ?></textarea>
                        </div>
                        <button type="submit" class="px-6 py-3 bg-gradient-to-r from-blue-600 to-blue-700 text-white rounded-lg hover:from-blue-700 hover:to-blue-800 shadow-md hover:shadow-lg transition-all duration-300">Update Details</button>
                    </form>

                    <h2 class="text-xl font-semibold mt-8 mb-4 text-blue-800">Upload Document</h2>
                    <form method="POST" action="/std_mgmt/views/marketing_user/lead_details.php?lead_id=<?php echo htmlspecialchars((string)$lead_id); ?>" enctype="multipart/form-data" class="bg-gray-50 p-6 rounded-lg shadow-sm">
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
                        <button type="submit" class="px-6 py-3 bg-gradient-to-r from-blue-600 to-blue-700 text-white rounded-lg hover:from-blue-700 hover:to-blue-800 shadow-md hover:shadow-lg transition-all duration-300">Upload Document</button>
                    </form>

                    <h2 class="text-xl font-semibold mt-8 mb-4 text-blue-800">Add Follow-up</h2>
                    <form method="POST" action="/std_mgmt/views/marketing_user/lead_details.php?lead_id=<?php echo htmlspecialchars((string)$lead_id); ?>" class="bg-gray-50 p-6 rounded-lg shadow-sm">
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
                        <button type="submit" class="px-6 py-3 bg-gradient-to-r from-blue-600 to-blue-700 text-white rounded-lg hover:from-blue-700 hover:to-blue-800 shadow-md hover:shadow-lg transition-all duration-300">Add Follow-up</button>
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
                                    'nic' => 'NIC Copy',
                                    'education' => 'Education Documents',
                                    'work_experience' => 'Work Experience Documents',
                                    'birth_certificate' => 'Birth Certificate'
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

                <h2 class="text-xl font-semibold mt-8 mb-4 text-blue-800">Follow-ups</h2>
                <?php if (empty($followups)): ?>
                    <div class="p-4 text-gray-600">No follow-ups added.</div>
                <?php else: ?>
                    <div class="overflow-x-auto" id="followup-table">
                        <form method="POST" action="/std_mgmt/views/marketing_user/lead_details.php?lead_id=<?php echo htmlspecialchars((string)$lead_id); ?>">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Number</th>
                                        <th>Date</th>
                                        <th>Comment</th>
                                        <th>Created At</th>
                                        <?php if ($user['role'] === 'marketing_user'): ?>
                                            <th>Action</th>
                                        <?php endif; ?>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($followups as $index => $followup): ?>
                                        <tr>
                                            <td>
                                                <input type="number" name="number[<?php echo $index; ?>]" value="<?php echo htmlspecialchars($followup['number']); ?>" min="1" required class="w-full">
                                            </td>
                                            <td>
                                                <input type="date" name="followup_date[<?php echo $index; ?>]" value="<?php echo htmlspecialchars($followup['followup_date']); ?>" required class="w-full">
                                            </td>
                                            <td>
                                                <textarea name="comment[<?php echo $index; ?>]" required class="w-full"><?php echo htmlspecialchars($followup['comment']); ?></textarea>
                                            </td>
                                            <td><?php echo htmlspecialchars($followup['created_at']); ?></td>
                                            <?php if ($user['role'] === 'marketing_user'): ?>
                                                <td class="action-cell">
                                                    <form method="POST" action="/std_mgmt/views/marketing_user/lead_details.php?lead_id=<?php echo htmlspecialchars((string)$lead_id); ?>" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this follow-up?');">
                                                        <input type="hidden" name="delete_followup" value="1">
                                                        <input type="hidden" name="followup_id" value="<?php echo htmlspecialchars((string)$followup['id']); ?>">
                                                        <button type="submit" class="px-4 py-2 bg-gradient-to-r from-red-600 to-red-700 text-white rounded-lg hover:from-red-700 hover:to-red-800 shadow-md hover:shadow-lg transition-all duration-300">Delete</button>
                                                    </form>
                                                </td>
                                            <?php endif; ?>
                                        </tr>
                                        <input type="hidden" name="followup_id[<?php echo $index; ?>]" value="<?php echo htmlspecialchars((string)$followup['id']); ?>">
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                            <?php if ($user['role'] === 'marketing_user'): ?>
                                <input type="hidden" name="update_followups" value="1">
                                <button type="submit" class="px-6 py-3 bg-gradient-to-r from-blue-600 to-blue-700 text-white rounded-lg hover:from-blue-700 hover:to-blue-800 shadow-md hover:shadow-lg transition-all duration-300 mt-4">Update Details</button>
                            <?php endif; ?>
                        </form>
                    </div>
                <?php endif; ?>

                <div class="mt-6">
                    <a href="/std_mgmt/views/<?php echo $user['role']; ?>/pending_registrations.php?course=<?php echo urlencode($lead['form_name']); ?>" class="px-6 py-3 bg-gradient-to-r from-blue-600 to-blue-700 text-white rounded-lg hover:from-blue-700 hover:to-blue-800 shadow-md hover:shadow-lg transition-all duration-300">Back to Pending Registrations</a>
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