<?php
session_start();
require_once __DIR__ . '/../../backend/config/db_connect.php';
if (!isset($pdo)) {
    error_log("PDO not defined in lead_details.php at " . date('Y-m-d H:i:s'));
    die("Database connection error");
}
require_once __DIR__ . '/../../backend/controllers/AuthController.php';
require_once __DIR__ . '/../../backend/controllers/LeadController.php';
require_once __DIR__ . '/../../backend/controllers/DocumentController.php';
require_once __DIR__ . '/../../backend/controllers/FollowupController.php';
require_once __DIR__ . '/../../backend/controllers/PaymentController.php'; // Added for payment details

$authController = new AuthController($pdo);
$leadController = new LeadController($pdo);
$documentController = new DocumentController($pdo);
$followupController = new FollowupController($pdo);
$paymentController = new PaymentController($pdo); // Added PaymentController

$user = $authController->getCurrentUser();
if (!$user || $user['role'] !== 'marketing_manager') {
    header('Location: /std_mgmt/views/auth/login.php?error=Unauthorized%20access');
    exit;
}

if (!isset($_GET['lead_id']) || !is_numeric($_GET['lead_id'])) {
    $error = "Error: No valid lead ID provided.";
} else {
    $lead_id = (int)$_GET['lead_id'];
    $lead = $leadController->getLeadById($lead_id);
    if (!$lead) {
        $error = "Error: Lead not found.";
        error_log("Lead not found for lead_id=$lead_id at " . date('Y-m-d H:i:s'));
    }
}

$documents = isset($lead_id) ? $documentController->getDocumentsByLead($lead_id) : [];
$followups = isset($lead_id) ? $followupController->getFollowupsByLead($lead_id) : [];
$payments = isset($lead_id) ? $paymentController->getPaymentsByLead($lead_id) : []; // Added to fetch payments

$course = $_GET['course'] ?? null;
define('BASE_PATH', '/std_mgmt');
$currentPage = basename($_SERVER['PHP_SELF']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lead Details - Student Management System</title>
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
        .table-container {
            overflow-x: auto;
        }
    </style>
</head>
<body class="bg-gray-100 font-sans">
    <div class="flex min-h-screen">
        <!-- Sidebar -->
        <div class="sidebar fixed inset-y-0 left-0 w-64 bg-blue-800 text-white p-4 md:relative md:translate-x-0 z-10">
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-xl font-bold">Marketing Manager Panel</h2>
                <button id="closeSidebar" class="md:hidden text-white focus:outline-none">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            <nav aria-label="Main navigation">
                <ul class="space-y-2">
                    <li>
                        <a href="<?php echo BASE_PATH; ?>/views/marketing_manager/dashboard.php" class="block p-2 rounded hover:bg-blue-700 <?php echo $currentPage === 'dashboard.php' ? 'bg-blue-700' : ''; ?>">
                            Dashboard
                        </a>
                    </li>
                    <li>
                        <a href="<?php echo BASE_PATH; ?>/views/marketing_manager/upload_leads.php" class="block p-2 rounded hover:bg-blue-700 <?php echo $currentPage === 'upload_leads.php' ? 'bg-blue-700' : ''; ?>">
                            Upload Leads
                        </a>
                    </li>
                    <li>
                        <a href="<?php echo BASE_PATH; ?>/views/marketing_manager/leads_list.php" class="block p-2 rounded hover:bg-blue-700 <?php echo $currentPage === 'leads_list.php' ? 'bg-blue-700' : ''; ?>">
                            Leads List
                        </a>
                    </li>
                    <li>
                        <a href="<?php echo BASE_PATH; ?>/views/marketing_manager/assigned_leads.php" class="block p-2 rounded hover:bg-blue-700 <?php echo $currentPage === 'assigned_leads.php' ? 'bg-blue-700' : ''; ?>">
                            Assigned Leads
                        </a>
                    </li>
                    <li>
                        <a href="<?php echo BASE_PATH; ?>/views/marketing_manager/pending_registrations.php" class="block p-2 rounded hover:bg-blue-700 <?php echo $currentPage === 'pending_registrations.php' ? 'bg-blue-700' : ''; ?>">
                            Pending Registrations
                        </a>
                    </li>
                    <li>
                        <a href="<?php echo BASE_PATH; ?>/views/marketing_manager/registered_leads.php" class="block p-2 rounded hover:bg-blue-700 <?php echo $currentPage === 'registered_leads.php' ? 'bg-blue-700' : ''; ?>">
                            Registered Leads
                        </a>
                    </li>
                    <li>
                        <a href="<?php echo BASE_PATH; ?>/views/marketing_manager/declined_leads.php" class="block p-2 rounded hover:bg-blue-700 <?php echo $currentPage === 'declined_leads.php' ? 'bg-blue-700' : ''; ?>">
                            Declined Leads
                        </a>
                    </li>
                    <li>
                        <a href="<?php echo BASE_PATH; ?>/views/auth/logout.php" class="block p-2 rounded hover:bg-red-600">
                            Logout
                        </a>
                    </li>
                </ul>
            </nav>
        </div>

        <!-- Main Content -->
        <div class="flex-1 p-4 md:p-8">
            <!-- Mobile Menu Button -->
            <button id="openSidebar" class="md:hidden mb-4 p-2 bg-blue-600 text-white rounded focus:outline-none">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16m-7 6h7"></path>
                </svg>
            </button>

            <!-- Lead Details Content -->
            <div class="max-w-4xl mx-auto bg-white p-6 rounded-lg shadow-md">
                <h1 class="text-2xl font-bold mb-4">Lead Details</h1>
                <p class="mb-6 text-gray-600">Detailed information about the selected lead.</p>
                <?php if (isset($error)): ?>
                    <div class="mb-4 p-4 bg-red-100 text-red-700 rounded-lg">
                        <?php echo htmlspecialchars($error); ?>
                    </div>
                    <a href="<?php echo BASE_PATH; ?>/views/marketing_manager/registered_leads.php?course=<?php echo urlencode($course ?? ''); ?>" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">Back to Leads</a>
                <?php else: ?>
                    <h2 class="text-xl font-semibold mb-4">Lead Information</h2>
                    <div class="table-container">
                        <table class="w-full border-collapse bg-white">
                            <tbody>
                                <tr class="border-b">
                                    <th class="p-3 text-left text-sm font-medium text-gray-700">Course Name</th>
                                    <td class="p-3"><?php echo htmlspecialchars($lead['form_name'] ?? 'N/A'); ?></td>
                                </tr>
                                <tr class="border-b">
                                    <th class="p-3 text-left text-sm font-medium text-gray-700">Full Name</th>
                                    <td class="p-3"><?php echo htmlspecialchars($lead['full_name'] ?? 'N/A'); ?></td>
                                </tr>
                                <tr class="border-b">
                                    <th class="p-3 text-left text-sm font-medium text-gray-700">Email</th>
                                    <td class="p-3"><?php echo htmlspecialchars($lead['email'] ?? 'N/A'); ?></td>
                                </tr>
                                <tr class="border-b">
                                    <th class="p-3 text-left text-sm font-medium text-gray-700">Phone</th>
                                    <td class="p-3"><?php echo htmlspecialchars($lead['phone'] ?? 'N/A'); ?></td>
                                </tr>
                                <tr class="border-b">
                                    <th class="p-3 text-left text-sm font-medium text-gray-700">Date of Birth</th>
                                    <td class="p-3"><?php echo htmlspecialchars($lead['date_of_birth'] ?? 'N/A'); ?></td>
                                </tr>
                                <tr class="border-b">
                                    <th class="p-3 text-left text-sm font-medium text-gray-700">NIC</th>
                                    <td class="p-3"><?php echo htmlspecialchars($lead['nic_number'] ?? 'N/A'); ?></td>
                                </tr>
                                <tr class="border-b">
                                    <th class="p-3 text-left text-sm font-medium text-gray-700">Permanent Address</th>
                                    <td class="p-3"><?php echo htmlspecialchars($lead['permanent_address'] ?? 'N/A'); ?></td>
                                </tr>
                                <tr class="border-b">
                                    <th class="p-3 text-left text-sm font-medium text-gray-700">Work Experience</th>
                                    <td class="p-3"><?php echo htmlspecialchars($lead['work_experience'] ?? 'N/A'); ?></td>
                                </tr>
                                <tr class="border-b">
                                    <th class="p-3 text-left text-sm font-medium text-gray-700">Assigned To</th>
                                    <td class="p-3"><?php echo htmlspecialchars($lead['username'] ?? 'N/A'); ?></td>
                                </tr>
                                <tr class="border-b">
                                    <th class="p-3 text-left text-sm font-medium text-gray-700">Lead Status</th>
                                    <td class="p-3"><?php echo htmlspecialchars($lead['status'] ?? 'N/A'); ?></td>
                                </tr>
                                <tr class="border-b">
                                    <th class="p-3 text-left text-sm font-medium text-gray-700">Created At</th>
                                    <td class="p-3"><?php echo htmlspecialchars($lead['created_at'] ?? 'N/A'); ?></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <h2 class="text-xl font-semibold mt-8 mb-4">Documents</h2>
                    <?php if (empty($documents)): ?>
                        <p class="p-4 text-gray-600">No documents uploaded.</p>
                    <?php else: ?>
                        <div class="table-container">
                            <table class="w-full border-collapse bg-white">
                                <thead>
                                    <tr class="bg-gray-100">
                                        <th class="p-3 text-left text-sm font-medium text-gray-700">Document Type</th>
                                        <th class="p-3 text-left text-sm font-medium text-gray-700">File</th>
                                        <th class="p-3 text-left text-sm font-medium text-gray-700">Uploaded At</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($documents as $doc): ?>
                                        <tr class="border-b">
                                            <td class="p-3"><?php
                                                $type_labels = [
                                                    'nic' => 'NIC Copy',
                                                    'education' => 'Education Documents',
                                                    'work_experience' => 'Work Experience Documents',
                                                    'birth_certificate' => 'Birth Certificate'
                                                ];
                                                echo htmlspecialchars($type_labels[$doc['document_type']] ?? $doc['document_type']);
                                            ?></td>
                                            <td class="p-3">
                                                <a href="/std_mgmt/uploads/documents/<?php echo htmlspecialchars(basename($doc['file_path'])); ?>" target="_blank" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">View</a>
                                            </td>
                                            <td class="p-3"><?php echo htmlspecialchars($doc['uploaded_at'] ?? 'N/A'); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>

                    <h2 class="text-xl font-semibold mt-8 mb-4">Follow-ups</h2>
                    <?php if (empty($followups)): ?>
                        <p class="p-4 text-gray-600">No follow-ups added.</p>
                    <?php else: ?>
                        <div class="table-container">
                            <table class="w-full border-collapse bg-white">
                                <thead>
                                    <tr class="bg-gray-100">
                                        <th class="p-3 text-left text-sm font-medium text-gray-700">Number</th>
                                        <th class="p-3 text-left text-sm font-medium text-gray-700">Date</th>
                                        <th class="p-3 text-left text-sm font-medium text-gray-700">Comment</th>
                                        <th class="p-3 text-left text-sm font-medium text-gray-700">Created At</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($followups as $followup): ?>
                                        <tr class="border-b">
                                            <td class="p-3"><?php echo htmlspecialchars($followup['number'] ?? 'N/A'); ?></td>
                                            <td class="p-3"><?php echo htmlspecialchars($followup['followup_date'] ?? 'N/A'); ?></td>
                                            <td class="p-3"><?php echo htmlspecialchars($followup['comment'] ?? 'N/A'); ?></td>
                                            <td class="p-3"><?php echo htmlspecialchars($followup['created_at'] ?? 'N/A'); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>

                    <h2 class="text-xl font-semibold mt-8 mb-4">Payments</h2>
                    <?php if (empty($payments)): ?>
                        <p class="p-4 text-gray-600">No payments recorded.</p>
                    <?php else: ?>
                        <div class="table-container">
                            <table class="w-full border-collapse bg-white">
                                <thead>
                                    <tr class="bg-gray-100">
                                        <th class="p-3 text-left text-sm font-medium text-gray-700">Payment Name</th>
                                        <th class="p-3 text-left text-sm font-medium text-gray-700">Amount (INR)</th>
                                        <th class="p-3 text-left text-sm font-medium text-gray-700">Receipt</th>
                                        <th class="p-3 text-left text-sm font-medium text-gray-700">Paid At</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($payments as $payment): ?>
                                        <tr class="border-b">
                                            <td class="p-3"><?php echo htmlspecialchars($payment['payment_name'] ?? 'N/A'); ?></td>
                                            <td class="p-3"><?php echo htmlspecialchars(number_format($payment['amount'], 2)); ?></td>
                                            <td class="p-3">
                                                <a href="/std_mgmt/uploads/payments/<?php echo htmlspecialchars(basename($payment['receipt_path'])); ?>" target="_blank" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">View</a>
                                            </td>
                                            <td class="p-3"><?php echo htmlspecialchars($payment['created_at'] ?? 'N/A'); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>

                    <div class="mt-6">
                        <a href="/std_mgmt/views/marketing_manager/registered_leads.php?course=<?php echo urlencode($course ?? ''); ?>" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">Back</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
        const openSidebar = document.getElementById('openSidebar');
        const closeSidebar = document.getElementById('closeSidebar');
        const sidebar = document.querySelector('.sidebar');

        openSidebar.addEventListener('click', () => {
            sidebar.classList.add('open');
        });

        closeSidebar.addEventListener('click', () => {
            sidebar.classList.remove('open');
        });
    </script>
</body>
</html>