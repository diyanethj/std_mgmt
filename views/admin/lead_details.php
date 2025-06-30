<?php
session_start();
require_once __DIR__ . '/../../backend/config/db_connect.php';
if (!isset($pdo)) {
    error_log("PDO not defined in lead_details.php");
    die("Database connection error");
}
require_once __DIR__ . '/../../backend/controllers/AuthController.php';
require_once __DIR__ . '/../../backend/controllers/LeadController.php';
require_once __DIR__ . '/../../backend/controllers/DocumentController.php';
require_once __DIR__ . '/../../backend/controllers/FollowupController.php';
require_once __DIR__ . '/../../backend/controllers/RegistrationController.php';

$auth = new AuthController($pdo);
$user = $auth->getCurrentUser();
if (!$user || $user['role'] !== 'admin') {
    header('Location: /std_mgmt/views/auth/login.php?error=Unauthorized%20access');
    exit;
}

$leadController = new LeadController($pdo);
$documentController = new DocumentController($pdo);
$followupController = new FollowupController($pdo);
$registrationController = new RegistrationController($pdo);

if (!isset($_GET['lead_id']) || !is_numeric($_GET['lead_id'])) {
    $error = "Error: No valid lead ID provided.";
} else {
    $lead_id = (int)$_GET['lead_id'];
    $lead = $leadController->getLeadById($lead_id);
    if (!$lead) {
        $error = "Error: Lead not found.";
    }
}

$documents = isset($lead_id) ? $documentController->getDocumentsByLead($lead_id) : [];
$followups = isset($lead_id) ? $followupController->getFollowupsByLead($lead_id) : [];
$registration = isset($lead_id) ? $registrationController->getPendingRegistrations(null, null) : [];
$registration = !empty($registration) ? array_filter($registration, fn($r) => $r['lead_id'] == $lead_id) : [];
$registration = !empty($registration) ? reset($registration) : null;

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
            width: 30%;
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
<body class="bg-gray-100 font-sans">
    <div class="flex min-h-screen">
        <!-- Sidebar -->
        <div class="sidebar fixed inset-y-0 left-0 w-64 bg-blue-800 text-white p-4 md:relative md:translate-x-0 z-10">
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-xl font-bold">Admin Panel</h2>
                <button id="closeSidebar" class="md:hidden text-white focus:outline-none">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            <nav aria-label="Main navigation">
                <ul class="space-y-2">
                    <li>
                        <a href="<?php echo BASE_PATH; ?>/views/admin/dashboard.php" class="block p-2 rounded hover:bg-blue-700 <?php echo $currentPage === 'dashboard.php' ? 'bg-blue-700' : ''; ?>">
                            Dashboard
                        </a>
                    </li>
                    <li>
                        <a href="<?php echo BASE_PATH; ?>/views/admin/upload_leads.php" class="block p-2 rounded hover:bg-blue-700 <?php echo $currentPage === 'upload_leads.php' ? 'bg-blue-700' : ''; ?>">
                            Upload Leads
                        </a>
                    </li>
                    <li>
                        <a href="<?php echo BASE_PATH; ?>/views/admin/leads_list.php" class="block p-2 rounded hover:bg-blue-700 <?php echo $currentPage === 'leads_list.php' ? 'bg-blue-700' : ''; ?>">
                            Leads List
                        </a>
                    </li>
                    <li>
                        <a href="<?php echo BASE_PATH; ?>/views/admin/assigned_leads.php" class="block p-2 rounded hover:bg-blue-700 <?php echo $currentPage === 'assigned_leads.php' ? 'bg-blue-700' : ''; ?>">
                            Assigned Leads
                        </a>
                    </li>
                    <li>
                        <a href="<?php echo BASE_PATH; ?>/views/admin/pending_registrations.php" class="block p-2 rounded hover:bg-blue-700 <?php echo $currentPage === 'pending_registrations.php' ? 'bg-blue-700' : ''; ?>">
                            Pending Registrations
                        </a>
                    </li>
                    <li>
                        <a href="<?php echo BASE_PATH; ?>/views/admin/registered_leads.php" class="block p-2 rounded hover:bg-blue-700 <?php echo $currentPage === 'registered_leads.php' ? 'bg-blue-700' : ''; ?>">
                            Registered Leads
                        </a>
                    </li>
                    <li>
                        <a href="<?php echo BASE_PATH; ?>/views/admin/declined_leads.php" class="block p-2 rounded hover:bg-blue-700 <?php echo $currentPage === 'declined_leads.php' ? 'bg-blue-700' : ''; ?>">
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
                <?php if (isset($error)): ?>
                    <div class="mb-4 p-4 bg-red-100 text-red-700 rounded-lg">
                        <?php echo htmlspecialchars($error); ?>
                    </div>
                    <a href="<?php echo BASE_PATH; ?>/views/admin/leads_list.php" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">Back to Leads List</a>
                <?php else: ?>
                    <p class="mb-6 text-gray-600">Detailed information about the selected lead.</p>
                    <h2 class="text-xl font-semibold mb-4">Lead Information</h2>
                    <div class="overflow-x-auto">
                        <table class="table">
                            <tbody>
                                <tr>
                                    <th>Course Name</th>
                                    <td><?php echo htmlspecialchars($lead['form_name']); ?></td>
                                </tr>
                                <tr>
                                    <th>Full Name</th>
                                    <td><?php echo htmlspecialchars($lead['full_name']); ?></td>
                                </tr>
                                <tr>
                                    <th>Email</th>
                                    <td><?php echo htmlspecialchars($lead['email']); ?></td>
                                </tr>
                                <tr>
                                    <th>Phone</th>
                                    <td><?php echo htmlspecialchars($lead['phone']); ?></td>
                                </tr>
                                <tr>
                                    <th>Permanent Address</th>
                                    <td><?php echo htmlspecialchars($lead['permanent_address'] ?: 'N/A'); ?></td>
                                </tr>
                                <tr>
                                    <th>Work Experience</th>
                                    <td><?php echo htmlspecialchars($lead['work_experience'] ?: 'N/A'); ?></td>
                                </tr>
                                <tr>
                                    <th>Assigned To</th>
                                    <td><?php echo htmlspecialchars($lead['username'] ?: 'N/A'); ?></td>
                                </tr>
                                <tr>
                                    <th>Lead Status</th>
                                    <td><?php echo htmlspecialchars($lead['status']); ?></td>
                                </tr>
                                <tr>
                                    <th>Registration Status</th>
                                    <td><?php echo htmlspecialchars($lead['registration_status'] ?: 'N/A'); ?></td>
                                </tr>
                                <?php if ($registration): ?>
                                    <tr>
                                        <th>Marketing Manager Approval</th>
                                        <td><?php echo htmlspecialchars($registration['marketing_manager_approval']); ?></td>
                                    </tr>
                                    <tr>
                                        <th>Academic User Approval</th>
                                        <td><?php echo htmlspecialchars($registration['academic_user_approval']); ?></td>
                                    </tr>
                                <?php endif; ?>
                                <tr>
                                    <th>Created At</th>
                                    <td><?php echo htmlspecialchars($lead['created_at']); ?></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <h2 class="text-xl font-semibold mt-8 mb-4">Documents</h2>
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
                                                echo htmlspecialchars($type_labels[$doc['document_type']] ?? $doc['document_type']);
                                            ?></td>
                                            <td><a href="/std_mgmt/uploads/documents/<?php echo htmlspecialchars(basename($doc['file_path'])); ?>" target="_blank" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">View</a></td>
                                            <td><?php echo htmlspecialchars($doc['uploaded_at']); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>

                    <h2 class="text-xl font-semibold mt-8 mb-4">Follow-ups</h2>
                    <?php if (empty($followups)): ?>
                        <div class="p-4 text-gray-600">No follow-ups added.</div>
                    <?php else: ?>
                        <div class="overflow-x-auto">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Number</th>
                                        <th>Date</th>
                                        <th>Comment</th>
                                        <th>Created At</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($followups as $followup): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($followup['number']); ?></td>
                                            <td><?php echo htmlspecialchars($followup['followup_date']); ?></td>
                                            <td><?php echo htmlspecialchars($followup['comment']); ?></td>
                                            <td><?php echo htmlspecialchars($followup['created_at']); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                    </tbody>
                            </table>
                        </div>
                    <?php endif; ?>

                    <div class="mt-6">
                        <a href="/std_mgmt/views/admin/<?php echo $lead['status'] === 'registered' ? 'registered_leads.php?course=' . urlencode($lead['form_name']) : 'assigned_leads.php?course=' . urlencode($lead['form_name']); ?>" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">Back</a>
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