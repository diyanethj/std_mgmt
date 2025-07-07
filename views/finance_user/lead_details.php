<?php
session_start();
require_once __DIR__ . '/../../backend/config/db_connect.php';
if (!isset($pdo)) {
    error_log("PDO not defined in finance_user/lead_details.php at " . date('Y-m-d H:i:s'));
    die("Database connection error");
}
require_once __DIR__ . '/../../backend/controllers/AuthController.php';
require_once __DIR__ . '/../../backend/controllers/LeadController.php';
require_once __DIR__ . '/../../backend/controllers/DocumentController.php';
require_once __DIR__ . '/../../backend/controllers/RegistrationController.php';
require_once __DIR__ . '/../../backend/controllers/PaymentController.php'; // Added for payment details

$authController = new AuthController($pdo);
$leadController = new LeadController($pdo);
$documentController = new DocumentController($pdo);
$registrationController = new RegistrationController($pdo);
$paymentController = new PaymentController($pdo); // Added PaymentController

$user = $authController->getCurrentUser();
if (!$user || $user['role'] !== 'finance_user') {
    header('Location: /std_mgmt/views/auth/login.php?error=Unauthorized%20access');
    exit;
}

$lead_id = $_GET['lead_id'] ?? '';
if (!isset($lead_id) || !is_numeric($lead_id)) {
    $error = "Error: No valid lead ID provided.";
} else {
    $lead_id = (int)$lead_id;
    $lead = $leadController->getLeadById($lead_id);
    if (!$lead) {
        $error = "Error: Lead not found for lead_id=$lead_id";
        error_log("Lead not found for lead_id=$lead_id at " . date('Y-m-d H:i:s'));
    }
}

$documents = isset($lead_id) ? $documentController->getDocumentsByLead($lead_id) : [];
error_log("Documents retrieved for lead_id=$lead_id: " . print_r($documents, true) . " at " . date('Y-m-d H:i:s'));
$registration = isset($lead_id) ? $registrationController->getRegistrationByLeadId($lead_id) : null;
$payments = isset($lead_id) ? $paymentController->getPaymentsByLead($lead_id) : []; // Added to fetch payments

define('BASE_PATH', '/std_mgmt');
$currentPage = basename($_SERVER['PHP_SELF']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Details - Finance User - Student Management System</title>
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
            background-color: #fff;
            border-radius: 0.5rem;
            overflow: hidden;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .table th, .table td {
            padding: 1rem;
            text-align: left;
            border-bottom: 1px solid #e5e7eb;
        }
        .table th {
            background-color: #1e40af;
            color: #fff;
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
        .table .action-cell a {
            display: inline-block;
            width: 100%;
            text-align: center;
        }
    </style>
</head>
<body class="bg-gradient-to-br from-gray-100 to-gray-300 font-sans">
    <div class="flex min-h-screen">
        <!-- Sidebar -->
        <div id="sidebar" class="sidebar fixed inset-y-0 left-0 w-64 bg-gradient-to-b from-blue-900 to-blue-700 text-white p-4 md:relative md:translate-x-0 z-10 shadow-lg">
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-2xl font-bold text-glow">Finance User Panel</h2>
                <button id="closeSidebar" class="md:hidden text-white focus:outline-none">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            <nav aria-label="Main navigation">
                <ul class="space-y-2">
                    <li>
                        <a href="<?php echo BASE_PATH; ?>/views/finance_user/dashboard.php" class="block p-2 rounded hover:bg-red-700/30 <?php echo $currentPage === 'dashboard.php' ? 'bg-red-700/50' : ''; ?>">
                            Dashboard
                        </a>
                    </li>
                    <li>
                        <a href="<?php echo BASE_PATH; ?>/views/finance_user/registered_leads.php" class="block p-2 rounded hover:bg-red-700/30 <?php echo $currentPage === 'registered_leads.php' ? 'bg-red-700/50' : ''; ?>">
                            Registered Leads
                        </a>
                    </li>
                    <li>
                        <a href="<?php echo BASE_PATH; ?>/views/auth/logout.php" class="block p-2 rounded hover:bg-yellow-600/50 text-yellow-300">
                            Logout
                        </a>
                    </li>
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
                <h1 class="text-3xl font-bold mb-4 text-blue-900 text-shadow">Student Details</h1>
                <?php if (isset($error)): ?>
                    <div class="mb-4 p-4 bg-red-100 text-red-700 rounded-lg">
                        <?php echo htmlspecialchars($error); ?>
                    </div>
                    <a href="<?php echo BASE_PATH; ?>/views/finance_user/registered_leads.php" class="px-6 py-3 bg-gradient-to-r from-blue-600 to-blue-700 text-white rounded-lg hover:from-blue-700 hover:to-blue-800 shadow-md hover:shadow-lg transition-all duration-300">Back to Registered Leads</a>
                <?php else: ?>
                    <p class="mb-6 text-gray-700 text-lg">Detailed information about the selected student.</p>
                    <h2 class="text-xl font-semibold mb-4 text-blue-800">Student Information</h2>
                    <div class="overflow-x-auto">
                        <table class="table">
                            <tbody>
                                <tr>
                                    <th>Course Name</th>
                                    <td><?php echo htmlspecialchars($lead['form_name'] ?? 'N/A'); ?></td>
                                </tr>
                                <tr>
                                    <th>Full Name</th>
                                    <td><?php echo htmlspecialchars($lead['full_name'] ?? 'N/A'); ?></td>
                                </tr>
                                <tr>
                                    <th>Email</th>
                                    <td><?php echo htmlspecialchars($lead['email'] ?? 'N/A'); ?></td>
                                </tr>
                                <tr>
                                    <th>Phone</th>
                                    <td><?php echo htmlspecialchars($lead['phone'] ?? 'N/A'); ?></td>
                                </tr>
                                <tr>
                                    <th>Date of Birth</th>
                                    <td><?php echo htmlspecialchars($lead['date_of_birth'] ?? 'N/A'); ?></td>
                                </tr>
                                <tr>
                                    <th>NIC</th>
                                    <td><?php echo htmlspecialchars($lead['nic_number'] ?? 'N/A'); ?></td>
                                </tr>
                                <tr>
                                    <th>Permanent Address</th>
                                    <td><?php echo htmlspecialchars($lead['permanent_address'] ?? 'N/A'); ?></td>
                                </tr>
                                <tr>
                                    <th>Work Experience</th>
                                    <td><?php echo htmlspecialchars($lead['work_experience'] ?? 'N/A'); ?></td>
                                </tr>
                                <tr>
                                    <th>Status</th>
                                    <td><?php echo htmlspecialchars($lead['status'] ?? 'N/A'); ?></td>
                                </tr>
                                <tr>
                                    <th>Assigned To</th>
                                    <td><?php echo htmlspecialchars($lead['username'] ?? 'N/A'); ?></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <h2 class="text-xl font-semibold mt-8 mb-4 text-blue-800">Registration Status</h2>
                    <div class="overflow-x-auto">
                        <table class="table">
                            <tbody>
                                <tr>
                                    <th>Status</th>
                                    <td><?php echo htmlspecialchars($registration['status'] ?? 'N/A'); ?></td>
                                </tr>
                                <tr>
                                    <th>Marketing Manager Approval</th>
                                    <td><?php echo htmlspecialchars($registration['marketing_manager_approval'] ?? 'N/A'); ?></td>
                                </tr>
                                <tr>
                                    <th>Academic User Approval</th>
                                    <td><?php echo htmlspecialchars($registration['academic_user_approval'] ?? 'N/A'); ?></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

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
                                    <?php foreach ($documents as $doc): ?>
                                        <tr>
                                            <td><?php
                                                $type_labels = [
                                                    'nic' => 'NIC Copy',
                                                    'education' => 'Education Documents',
                                                    'work_experience' => 'Work Experience Documents',
                                                    'birth_certificate' => 'Birth Certificate'
                                                ];
                                                echo htmlspecialchars($type_labels[$doc['document_type']] ?? $doc['document_type'] ?? 'N/A');
                                            ?></td>
                                            <td class="action-cell"><a href="/std_mgmt/uploads/documents/<?php echo htmlspecialchars(basename($doc['file_path'] ?? '')); ?>" target="_blank" class="px-6 py-3 bg-gradient-to-r from-blue-600 to-blue-700 text-white rounded-lg hover:from-blue-700 hover:to-blue-800 shadow-md hover:shadow-lg transition-all duration-300">View</a></td>
                                            <td><?php echo htmlspecialchars($doc['uploaded_at'] ?? 'N/A'); ?></td>
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
                                        <th>Uploaded At</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($payments as $payment): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($payment['payment_name'] ?? 'N/A'); ?></td>
                                            <td><?php echo htmlspecialchars(number_format($payment['amount'], 2)); ?></td>
                                            <td class="action-cell"><a href="/std_mgmt/uploads/payments/<?php echo htmlspecialchars(basename($payment['receipt_path'] ?? '')); ?>" target="_blank" class="px-6 py-3 bg-gradient-to-r from-blue-600 to-blue-700 text-white rounded-lg hover:from-blue-700 hover:to-blue-800 shadow-md hover:shadow-lg transition-all duration-300">View</a></td>
                                            <td><?php echo htmlspecialchars($payment['created_at'] ?? 'N/A'); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>

                    <div class="mt-6">
                        <a href="<?php echo BASE_PATH; ?>/views/finance_user/registered_leads.php" class="px-6 py-3 bg-gradient-to-r from-blue-600 to-blue-700 text-white rounded-lg hover:from-blue-700 hover:to-blue-800 shadow-md hover:shadow-lg transition-all duration-300">Back to Registered Leads</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const openSidebar = document.getElementById('openSidebar');
            const closeSidebar = document.getElementById('closeSidebar');
            const sidebar = document.getElementById('sidebar');

            // Initialize sidebar state
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
        });
    </script>
</body>
</html>