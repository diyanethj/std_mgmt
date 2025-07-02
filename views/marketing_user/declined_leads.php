<?php
session_start();
require_once __DIR__ . '/../../backend/config/db_connect.php';
if (!isset($pdo)) {
    error_log("PDO not defined in declined_leads.php at " . date('Y-m-d H:i:s'));
    die("Database connection error");
}
require_once __DIR__ . '/../../backend/controllers/RegistrationController.php';
require_once __DIR__ . '/../../backend/controllers/LeadController.php';
require_once __DIR__ . '/../../backend/controllers/AuthController.php';
$registrationController = new RegistrationController($pdo);
$leadController = new LeadController($pdo);
$authController = new AuthController($pdo);

$user = $authController->getCurrentUser();
if (!$user || $user['role'] !== 'marketing_user') {
    header('Location: /std_mgmt/views/auth/login.php?error=Unauthorized%20access');
    exit;
}

$course = $_GET['course'] ?? '';
$user_id = $user['role'] === 'marketing_user' ? $user['id'] : ($_GET['user_id'] ?? '');

// Get distinct courses with declined leads
$declined_leads = $registrationController->getDeclinedLeads($user['role'] === 'marketing_user' ? $user['id'] : null);
$course_list = array_unique(array_column($declined_leads, 'form_name'));

if ($course) {
    $declined_leads = $registrationController->getDeclinedLeads($user_id ?: ($user['role'] === 'marketing_user' ? $user['id'] : null), $course);
} else {
    $declined_leads = [];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $lead_id = (int)$_POST['lead_id'];
    if (isset($_POST['action']) && $_POST['action'] === 'resend') {
        if ($registrationController->resendToRegistration($lead_id)) {
            header('Location: /std_mgmt/views/marketing_user/declined_leads.php?course=' . urlencode($course) . '&user_id=' . urlencode($user_id) . '&success=Lead resent for registration successfully');
            exit;
        } else {
            $error = 'Failed to resend lead for registration';
        }
    } elseif (isset($_POST['update'])) {
        $permanent_address = $_POST['permanent_address'] ?? '';
        $work_experience = $_POST['work_experience'] ?? '';
        if ($leadController->updateLeadDetails($lead_id, $permanent_address, $work_experience)) {
            header('Location: /std_mgmt/views/marketing_user/declined_leads.php?course=' . urlencode($course) . '&user_id=' . urlencode($user_id) . '&success=Lead details updated successfully');
            exit;
        } else {
            $error = 'Failed to update lead details';
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
    <title>Declined Leads - Student Management System</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;700&family=Roboto:wght@300;400&display=swap" rel="stylesheet">
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
        .table .action-cell form, .table .action-cell a {
            display: inline-block;
            margin-right: 8px;
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
                    <li>
                        <a href="<?php echo BASE_PATH; ?>/views/marketing_user/dashboard.php" class="block p-2 rounded hover:bg-red-700/30 <?php echo $currentPage === 'dashboard.php' ? 'bg-red-700/50' : ''; ?>">
                            Dashboard
                        </a>
                    </li>
                    <li>
                        <a href="<?php echo BASE_PATH; ?>/views/marketing_user/assigned_leads.php" class="block p-2 rounded hover:bg-red-700/30 <?php echo $currentPage === 'assigned_leads.php' ? 'bg-red-700/50' : ''; ?>">
                            Assigned Leads
                        </a>
                    </li>
                    <li>
                        <a href="<?php echo BASE_PATH; ?>/views/marketing_user/pending_registrations.php" class="block p-2 rounded hover:bg-red-700/30 <?php echo $currentPage === 'pending_registrations.php' ? 'bg-red-700/50' : ''; ?>">
                            Pending Registrations
                        </a>
                    </li>
                    <li>
                        <a href="<?php echo BASE_PATH; ?>/views/marketing_user/registered_leads.php" class="block p-2 rounded hover:bg-red-700/30 <?php echo $currentPage === 'registered_leads.php' ? 'bg-red-700/50' : ''; ?>">
                            Registered Leads
                        </a>
                    </li>
                    <li>
                        <a href="<?php echo BASE_PATH; ?>/views/marketing_user/declined_leads.php" class="block p-2 rounded hover:bg-red-700/30 <?php echo $currentPage === 'declined_leads.php' ? 'bg-red-700/50' : ''; ?>">
                            Declined Leads
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

            <!-- Declined Leads Content -->
            <div class="max-w-4xl mx-auto bg-white/80 backdrop-blur-md p-6 rounded-xl shadow-xl">
                <h1 class="text-3xl font-bold mb-4 text-blue-900 text-shadow">Declined Leads</h1>
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

                <?php if ($user['role'] !== 'marketing_user'): ?>
                    <!-- Filter by Marketing User (only for non-marketing users) -->
                    <form method="GET" action="/std_mgmt/views/marketing_user/declined_leads.php" class="mb-6">
                        <div class="form-group">
                            <label for="user_id" class="block text-sm font-medium text-gray-700">Filter by Marketing User</label>
                            <select name="user_id" id="user_id" class="mt-1 block w-full p-2 border border-gray-300 rounded-md">
                                <option value="">All</option>
                                <?php
                                $marketing_users = $authController->getUsersByRole('marketing_user');
                                foreach ($marketing_users as $marketing_user): ?>
                                    <option value="<?php echo htmlspecialchars((string)$marketing_user['id']); ?>" <?php echo $user_id == $marketing_user['id'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($marketing_user['username']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <?php if ($course): ?>
                            <input type="hidden" name="course" value="<?php echo htmlspecialchars($course); ?>">
                        <?php endif; ?>
                        <button type="submit" class="px-4 py-2 bg-gradient-to-r from-blue-600 to-blue-700 text-white rounded-lg hover:from-blue-700 hover:to-blue-800 shadow-md hover:shadow-lg transition-all duration-300">Apply Filter</button>
                    </form>
                <?php endif; ?>

                <?php if (empty($course_list)): ?>
                    <div class="p-4 text-gray-600">No declined leads found.</div>
                <?php else: ?>
                    <h2 class="text-xl font-semibold mb-4 text-blue-800">Courses</h2>
                    <div class="overflow-x-auto">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Course Name</th>
                                    <th>Declined Lead Count</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($course_list as $course_name): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($course_name); ?></td>
                                        <td><?php echo count($registrationController->getDeclinedLeads($user_id ?: ($user['role'] === 'marketing_user' ? $user['id'] : null), $course_name)); ?></td>
                                        <td class="action-cell">
                                            <a href="?course=<?php echo urlencode($course_name); ?>&user_id=<?php echo urlencode($user_id); ?>" class="px-6 py-3 bg-gradient-to-r from-blue-600 to-blue-700 text-white rounded-lg hover:from-blue-700 hover:to-blue-800 shadow-md hover:shadow-lg transition-all duration-300">View Leads</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>

                <?php if ($course && !empty($declined_leads)): ?>
                    <h2 class="text-xl font-semibold mt-8 mb-4 text-blue-800">Declined Leads for <?php echo htmlspecialchars($course); ?></h2>
                    <div class="overflow-x-auto">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Full Name</th>
                                    <th>Email</th>
                                    <th>Phone</th>
                                    <th>Assigned To</th>
                                    <th>Registration Status</th>
                                    <th>Update Details</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($declined_leads as $lead): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($lead['full_name']); ?></td>
                                        <td><?php echo htmlspecialchars($lead['email']); ?></td>
                                        <td><?php echo htmlspecialchars($lead['phone']); ?></td>
                                        <td><?php echo htmlspecialchars($lead['username'] ?: 'N/A'); ?></td>
                                        <td><?php echo htmlspecialchars($lead['status']); ?></td>
                                        <td>
                                            <form method="POST" action="/std_mgmt/views/marketing_user/declined_leads.php?course=<?php echo urlencode($course); ?>&user_id=<?php echo urlencode($user_id); ?>" class="space-y-2">
                                                <input type="hidden" name="lead_id" value="<?php echo htmlspecialchars((string)$lead['lead_id']); ?>">
                                                <div class="form-group">
                                                    <label for="permanent_address_<?php echo $lead['lead_id']; ?>" class="block text-sm font-medium text-gray-700">Permanent Address</label>
                                                    <input type="text" name="permanent_address" id="permanent_address_<?php echo $lead['lead_id']; ?>" value="<?php echo htmlspecialchars($lead['permanent_address'] ?: ''); ?>" class="mt-1 block w-full p-2 border border-gray-300 rounded-md">
                                                </div>
                                                <div class="form-group">
                                                    <label for="work_experience_<?php echo $lead['lead_id']; ?>" class="block text-sm font-medium text-gray-700">Work Experience</label>
                                                    <textarea name="work_experience" id="work_experience_<?php echo $lead['lead_id']; ?>" class="mt-1 block w-full p-2 border border-gray-300 rounded-md"><?php echo htmlspecialchars($lead['work_experience'] ?: ''); ?></textarea>
                                                </div>
                                                <button type="submit" name="update" value="update" class="px-4 py-2 bg-gradient-to-r from-green-600 to-green-700 text-white rounded-lg hover:from-green-700 hover:to-green-800 shadow-md hover:shadow-lg transition-all duration-300">Update</button>
                                            </form>
                                        </td>
                                        <td class="action-cell">
                                            <form method="POST" action="/std_mgmt/views/marketing_user/declined_leads.php?course=<?php echo urlencode($course); ?>&user_id=<?php echo urlencode($user_id); ?>">
                                                <input type="hidden" name="lead_id" value="<?php echo htmlspecialchars((string)$lead['lead_id']); ?>">
                                                <button type="submit" name="action" value="resend" class="px-4 py-2 bg-gradient-to-r from-green-600 to-green-700 text-white rounded-lg hover:from-green-700 hover:to-green-800 shadow-md hover:shadow-lg transition-all duration-300">Resend for Registration</button>
                                            </form>
                                            <a href="/std_mgmt/views/marketing_user/lead_details.php?lead_id=<?php echo htmlspecialchars((string)$lead['lead_id']); ?>" class="px-4 py-2 bg-gradient-to-r from-blue-600 to-blue-700 text-white rounded-lg hover:from-blue-700 hover:to-blue-800 shadow-md hover:shadow-lg transition-all duration-300">View Details</a>
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
<?php include __DIR__ . '/../layouts/footer.php'; ?>