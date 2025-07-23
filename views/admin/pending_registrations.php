<?php
session_start();
require_once __DIR__ . '/../../backend/config/db_connect.php';
if (!isset($pdo)) {
    error_log("PDO not defined in pending_registrations.php at " . date('Y-m-d H:i:s'));
    die("Database connection error");
}
require_once __DIR__ . '/../../backend/controllers/AuthController.php';
require_once __DIR__ . '/../../backend/controllers/LeadController.php';
require_once __DIR__ . '/../../backend/controllers/RegistrationController.php';

$auth = new AuthController($pdo);
$user = $auth->getCurrentUser();
if (!$user || !isset($user['role']) || $user['role'] !== 'admin') { // Updated line
    header('Location: /std_mgmt/views/auth/login.php?error=Unauthorized%20access');
    exit;
}

$registrationController = new RegistrationController($pdo);
$leadController = new LeadController($pdo);
$course = $_GET['course'] ?? '';
$user_id = $_GET['user_id'] ?? '';
$marketing_users = $auth->getUsersByRole('marketing_user');

// Get distinct courses with pending registrations
$registrations = $registrationController->getPendingRegistrations();
$course_list = array_unique(array_column($registrations, 'form_name'));

if ($course) {
    $pending_registrations = $registrationController->getPendingRegistrations($user_id ?: null, $course);
} else {
    $pending_registrations = [];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $lead_id = (int)$_POST['lead_id'];
    $action = $_POST['action'];
    $role = $_POST['role'];
    if ($action === 'approve') {
        if ($role === 'marketing_manager' || $role === 'academic_user') {
            if ($registrationController->approveRegistration($lead_id, $role)) {
                header('Location: /std_mgmt/views/admin/pending_registrations.php?course=' . urlencode($course) . '&user_id=' . urlencode($user_id) . '&success=Registration%20approved%20successfully');
                exit;
            } else {
                $error = 'Failed to approve registration';
            }
        }
    } elseif ($action === 'decline') {
        if ($registrationController->declineRegistration($lead_id, $role)) {
            header('Location: /std_mgmt/views/admin/pending_registrations.php?course=' . urlencode($course) . '&user_id=' . urlencode($user_id) . '&success=Registration%20declined%20successfully');
            exit;
        } else {
            $error = 'Failed to decline registration';
        }
    } elseif ($action === 'create_user') {
        $username = trim($_POST['username'] ?? '');
        $password = trim($_POST['password'] ?? '');
        if ($username && $password && $registrationController->createUserForLead($lead_id, $username, $password)) {
            header('Location: /std_mgmt/views/admin/pending_registrations.php?course=' . urlencode($course) . '&user_id=' . urlencode($user_id) . '&success=User%20created%20successfully');
            exit;
        } else {
            $error = 'Failed to create user account';
        }
    }
}

define('BASE_PATH', '/std_mgmt');
$currentPage = basename($_SERVER['PHP_SELF']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pending Registrations - Student Management System</title>
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
        }
        .table tr:hover {
            background-color: #f9fafb;
        }
        .table .action-cell {
            white-space: nowrap;
            min-width: 500px; /* Increased width to accommodate new form */
        }
        .table .action-cell form, .table .action-cell a {
            display: inline-block;
            margin-right: 8px;
        }
        .form-group {
            margin-bottom: 0.5rem;
        }
        .form-group label {
            display: block;
            font-size: 0.875rem;
            color: #374151;
        }
        .form-group input {
            width: 100%;
            padding: 0.25rem;
            border: 1px solid #d1d5db;
            border-radius: 0.375rem;
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
                        <a href="<?php echo BASE_PATH; ?>/views/admin/add_payment_plan.php" class="block p-2 rounded hover:bg-red-700/30 <?php echo $currentPage === 'add_payment_plan.php' ? 'bg-red-700/50' : ''; ?>">
                            Add Payment Plan
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

            <!-- Pending Registrations Content -->
            <div class="max-w-4xl mx-auto bg-white p-6 rounded-lg shadow-md">
                <h1 class="text-2xl font-bold mb-4">Pending Registrations</h1>
                <p class="mb-6 text-gray-600">View and manage pending registrations by course and marketing user.</p>
                <?php if (isset($_GET['success'])): ?>
                    <div class="mb-4 p-4 bg-green-100 text-green-700 rounded-lg">
                        <?php echo htmlspecialchars($_GET['success']); ?>
                    </div>
                <?php endif; ?>
                <?php if (isset($error)): ?>
                    <div class="mb-4 p-4 bg-red-100 text-red-700 rounded-lg">
                        <?php echo htmlspecialchars($error); ?>
                    </div>
                <?php endif; ?>
                <!-- Filter Form -->
                <form method="GET" action="<?php echo BASE_PATH; ?>/views/admin/pending_registrations.php" class="mb-6 space-y-4 sm:flex sm:space-y-0 sm:space-x-4 sm:items-end">
                    <div class="form-group">
                        <label for="user_id" class="block text-sm font-medium text-gray-700">Filter by Marketing User</label>
                        <select name="user_id" id="user_id" class="mt-1 block w-full p-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
                            <option value="">All</option>
                            <?php foreach ($marketing_users as $user): ?>
                                <option value="<?php echo htmlspecialchars((string)$user['id']); ?>" <?php echo $user_id == $user['id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($user['username']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <?php if ($course): ?>
                        <input type="hidden" name="course" value="<?php echo htmlspecialchars($course); ?>">
                    <?php endif; ?>
                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">Apply Filter</button>
                </form>

                <?php if (empty($course_list)): ?>
                    <div class="p-4 text-gray-600">No pending registrations found.</div>
                <?php else: ?>
                    <h2 class="text-xl font-semibold mb-4">Courses</h2>
                    <div class="overflow-x-auto">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Course Name</th>
                                    <th>Pending Registration Count</th>
                                    <th class="action-cell">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($course_list as $course_name): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($course_name); ?></td>
                                        <td><?php echo htmlspecialchars((string)count($registrationController->getPendingRegistrations($user_id ?: null, $course_name))); ?></td>
                                        <td class="action-cell">
                                            <a href="?course=<?php echo urlencode($course_name); ?>&user_id=<?php echo urlencode($user_id); ?>" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">View Registrations</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
                <?php if ($course && !empty($pending_registrations)): ?>
                    <h2 class="text-xl font-semibold mt-8 mb-4">Pending Registrations for <?php echo htmlspecialchars($course); ?></h2>
                    <div class="overflow-x-auto">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Full Name</th>
                                    <th>Email</th>
                                    <th>Phone</th>
                                    <th>Assigned To</th>
                                    <th>Marketing Manager Approval</th>
                                    <th>Academic User Approval</th>
                                    <th class="action-cell">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($pending_registrations as $registration): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($registration['full_name']); ?></td>
                                        <td><?php echo htmlspecialchars($registration['email']); ?></td>
                                        <td><?php echo htmlspecialchars($registration['phone']); ?></td>
                                        <td><?php echo htmlspecialchars($registration['username'] ?: 'N/A'); ?></td>
                                        <td><?php echo htmlspecialchars($registration['marketing_manager_approval']); ?></td>
                                        <td><?php echo htmlspecialchars($registration['academic_user_approval']); ?></td>
                                        <td class="action-cell">
                                            <form method="POST" action="<?php echo BASE_PATH; ?>/views/admin/pending_registrations.php?course=<?php echo urlencode($course); ?>&user_id=<?php echo urlencode($user_id); ?>" class="inline-block">
                                                <input type="hidden" name="lead_id" value="<?php echo htmlspecialchars((string)$registration['lead_id']); ?>">
                                                <input type="hidden" name="role" value="marketing_manager">
                                                <button type="submit" name="action" value="approve" class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700" <?php echo $registration['marketing_manager_approval'] !== 'pending' ? 'disabled' : ''; ?>>Accept (MM)</button>
                                                <button type="submit" name="action" value="decline" class="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700" <?php echo $registration['marketing_manager_approval'] !== 'pending' ? 'disabled' : ''; ?>>Decline (MM)</button>
                                            </form>
                                            <form method="POST" action="<?php echo BASE_PATH; ?>/views/admin/pending_registrations.php?course=<?php echo urlencode($course); ?>&user_id=<?php echo urlencode($user_id); ?>" class="inline-block">
                                                <input type="hidden" name="lead_id" value="<?php echo htmlspecialchars((string)$registration['lead_id']); ?>">
                                                <input type="hidden" name="role" value="academic_user">
                                                <button type="submit" name="action" value="approve" class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700" <?php echo $registration['academic_user_approval'] !== 'pending' ? 'disabled' : ''; ?>>Accept (AU)</button>
                                                <button type="submit" name="action" value="decline" class="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700" <?php echo $registration['academic_user_approval'] !== 'pending' ? 'disabled' : ''; ?>>Decline (AU)</button>
                                            </form>
                                            <form method="POST" action="<?php echo BASE_PATH; ?>/views/admin/pending_registrations.php?course=<?php echo urlencode($course); ?>&user_id=<?php echo urlencode($user_id); ?>" class="inline-block">
                                                <input type="hidden" name="lead_id" value="<?php echo htmlspecialchars((string)$registration['lead_id']); ?>">
                                                <div class="form-group">
                                                    <label>Username</label>
                                                    <input type="text" name="username" required class="w-full">
                                                </div>
                                                <div class="form-group">
                                                    <label>Password</label>
                                                    <input type="password" name="password" required class="w-full">
                                                </div>
                                                <button type="submit" name="action" value="create_user" class="px-4 py-2 bg-purple-600 text-white rounded hover:bg-purple-700">Create User</button>
                                            </form>
                                            <a href="<?php echo BASE_PATH; ?>/views/admin/lead_details.php?lead_id=<?php echo htmlspecialchars((string)$registration['lead_id']); ?>" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">View Details</a>
                                        </td>
                                    </tr>
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