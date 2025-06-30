<?php
session_start();
require_once __DIR__ . '/../../backend/config/db_connect.php';
if (!isset($pdo)) {
    error_log("PDO not defined in marketing_manager/assigned_leads.php");
    die("Database connection error");
}
require_once __DIR__ . '/../../backend/controllers/AuthController.php';
require_once __DIR__ . '/../../backend/controllers/LeadController.php';

$auth = new AuthController($pdo);
$leadController = new LeadController($pdo);

$user = $auth->getCurrentUser();
if (!$user || $user['role'] !== 'marketing_manager') {
    header('Location: /std_mgmt/views/auth/login.php?error=Unauthorized%20access');
    exit;
}

$course = $_GET['course'] ?? '';
$user_id = $_GET['user_id'] ?? '';
$registration_status = $_GET['registration_status'] ?? '';
$course_list = $leadController->getDistinctCourses();
$marketing_users = $auth->getUsersByRole('marketing_user');

if ($course) {
    $leads = $leadController->getLeadsByCourse($course, $user_id ?: null, $registration_status ?: null);
} else {
    $leads = [];
}

define('BASE_PATH', '/std_mgmt');
$currentPage = basename($_SERVER['PHP_SELF']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Assigned Leads - Student Management System</title>
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
        .form-group select, .form-group input {
            width: 100%;
            padding: 8px;
            border: 1px solid #d1d5db;
            border-radius: 4px;
            margin-top: 4px;
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

            <!-- Assigned Leads Content -->
            <div class="max-w-4xl mx-auto bg-white p-6 rounded-lg shadow-md">
                <h1 class="text-2xl font-bold mb-4">Assigned Leads</h1>
                <p class="mb-6 text-gray-600">View and filter assigned leads by course, marketing user, or registration status.</p>
                <?php if (isset($_GET['success'])): ?>
                    <div class="mb-4 p-4 bg-green-100 text-green-700 rounded-lg">
                        <?php echo htmlspecialchars($_GET['success']); ?>
                    </div>
                <?php endif; ?>
                <!-- Filters -->
                <form method="GET" action="/std_mgmt/views/marketing_manager/assigned_leads.php" class="space-y-4 mb-6">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="form-group">
                            <label for="user_id" class="block text-sm font-medium text-gray-700">Filter by Marketing User</label>
                            <select name="user_id" id="user_id" class="mt-1 focus:ring-blue-500 focus:border-blue-500">
                                <option value="">All</option>
                                <?php foreach ($marketing_users as $user): ?>
                                    <option value="<?php echo htmlspecialchars((string)$user['id']); ?>" <?php echo $user_id == $user['id'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($user['username']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="registration_status" class="block text-sm font-medium text-gray-700">Filter by Registration Status</label>
                            <select name="registration_status" id="registration_status" class="mt-1 focus:ring-blue-500 focus:border-blue-500">
                                <option value="">All</option>
                                <option value="pending" <?php echo $registration_status === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                <option value="registered" <?php echo $registration_status === 'registered' ? 'selected' : ''; ?>>Registered</option>
                                <option value="declined" <?php echo $registration_status === 'declined' ? 'selected' : ''; ?>>Declined</option>
                                <option value="N/A" <?php echo $registration_status === 'N/A' ? 'selected' : ''; ?>>N/A</option>
                            </select>
                        </div>
                    </div>
                    <?php if ($course): ?>
                        <input type="hidden" name="course" value="<?php echo htmlspecialchars($course); ?>">
                    <?php endif; ?>
                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">Apply Filters</button>
                </form>
                <?php if (empty($course_list)): ?>
                    <p class="text-gray-600">No assigned leads found.</p>
                <?php else: ?>
                    <h2 class="text-xl font-semibold mb-4">Courses</h2>
                    <div class="table-container">
                        <table class="w-full border-collapse bg-white">
                            <thead>
                                <tr class="bg-gray-100">
                                    <th class="p-3 text-left text-sm font-medium text-gray-700">Course Name</th>
                                    <th class="p-3 text-left text-sm font-medium text-gray-700">Lead Count</th>
                                    <th class="p-3 text-left text-sm font-medium text-gray-700">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($course_list as $course_name): ?>
                                    <tr class="border-b">
                                        <td class="p-3"><?php echo htmlspecialchars($course_name); ?></td>
                                        <td class="p-3"><?php echo count(array_filter($leadController->getLeadsByCourse($course_name, $user_id ?: null, $registration_status ?: null), fn($lead) => $lead['assigned_user_id'] !== null)); ?></td>
                                        <td class="p-3">
                                            <a href="?course=<?php echo urlencode($course_name); ?>&user_id=<?php echo urlencode($user_id); ?>&registration_status=<?php echo urlencode($registration_status); ?>" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">View Leads</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
                <?php if ($course && !empty($leads)): ?>
                    <h2 class="text-xl font-semibold mt-6 mb-4">Assigned Leads for <?php echo htmlspecialchars($course); ?></h2>
                    <div class="table-container">
                        <table class="w-full border-collapse bg-white">
                            <thead>
                                <tr class="bg-gray-100">
                                    <th class="p-3 text-left text-sm font-medium text-gray-700">Full Name</th>
                                    <th class="p-3 text-left text-sm font-medium text-gray-700">Email</th>
                                    <th class="p-3 text-left text-sm font-medium text-gray-700">Phone</th>
                                    <th class="p-3 text-left text-sm font-medium text-gray-700">Assigned To</th>
                                    <th class="p-3 text-left text-sm font-medium text-gray-700">Registration Status</th>
                                    <th class="p-3 text-left text-sm font-medium text-gray-700">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($leads as $lead): ?>
                                    <?php if ($lead['assigned_user_id'] !== null): ?>
                                        <tr class="border-b">
                                            <td class="p-3"><?php echo htmlspecialchars($lead['full_name']); ?></td>
                                            <td class="p-3"><?php echo htmlspecialchars($lead['email']); ?></td>
                                            <td class="p-3"><?php echo htmlspecialchars($lead['phone']); ?></td>
                                            <td class="p-3"><?php echo htmlspecialchars($lead['username'] ?: 'N/A'); ?></td>
                                            <td class="p-3"><?php echo htmlspecialchars($lead['registration_status'] ?: 'N/A'); ?></td>
                                            <td class="p-3">
                                                <a href="/std_mgmt/views/marketing_manager/lead_details.php?lead_id=<?php echo htmlspecialchars((string)$lead['id']); ?>" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">View Details</a>
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
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