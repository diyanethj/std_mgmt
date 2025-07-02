<?php
session_start();
require_once __DIR__ . '/../../backend/config/db_connect.php';
if (!isset($pdo)) {
    error_log("PDO not defined in academic_user/declined_leads.php at " . date('Y-m-d H:i:s'));
    die("Database connection error");
}
require_once __DIR__ . '/../../backend/controllers/AuthController.php';
require_once __DIR__ . '/../../backend/controllers/RegistrationController.php';

$authController = new AuthController($pdo);
$registrationController = new RegistrationController($pdo);

$user = $authController->getCurrentUser();
if (!$user || $user['role'] !== 'academic_user') {
    header('Location: /std_mgmt/views/auth/login.php?error=Unauthorized%20access');
    exit;
}

$course = $_GET['course'] ?? '';

$declined_leads = $registrationController->getDeclinedLeads($course ?: null);
$course_list = array_unique(array_column($declined_leads, 'form_name'));

error_log("Academic user declined leads: course=$course, count=" . count($declined_leads) . " at " . date('Y-m-d H:i:s'));

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
        .table .action-cell a {
            display: inline-block;
            width: 100%;
            text-align: center;
        }
        .form-group select {
            width: 100%;
            padding: 8px;
            border: 1px solid #d1d5db;
            border-radius: 4px;
            margin-top: 4px;
            font-family: 'Roboto', sans-serif;
        }
    </style>
</head>
<body class="bg-gradient-to-br from-gray-100 to-gray-300 font-sans">
    <div class="flex min-h-screen">
        <!-- Sidebar -->
        <div id="sidebar" class="sidebar fixed inset-y-0 left-0 w-64 bg-gradient-to-b from-blue-900 to-blue-700 text-white p-4 md:relative md:translate-x-0 z-10 shadow-lg">
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-2xl font-bold text-glow">Academic User Panel</h2>
                <button id="closeSidebar" class="md:hidden text-white focus:outline-none">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            <nav aria-label="Main navigation">
                <ul class="space-y-2">
                    <li>
                        <a href="<?php echo BASE_PATH; ?>/views/academic_user/dashboard.php" class="block p-2 rounded hover:bg-red-700/30 <?php echo $currentPage === 'dashboard.php' ? 'bg-red-700/50' : ''; ?>">
                            Dashboard
                        </a>
                    </li>
                    <li>
                        <a href="<?php echo BASE_PATH; ?>/views/academic_user/pending_registrations.php" class="block p-2 rounded hover:bg-red-700/30 <?php echo $currentPage === 'pending_registrations.php' ? 'bg-red-700/50' : ''; ?>">
                            Pending Registrations
                        </a>
                    </li>
                    <li>
                        <a href="<?php echo BASE_PATH; ?>/views/academic_user/registered_leads.php" class="block p-2 rounded hover:bg-red-700/30 <?php echo $currentPage === 'registered_leads.php' ? 'bg-red-700/50' : ''; ?>">
                            Registered Leads
                        </a>
                    </li>
                    <li>
                        <a href="<?php echo BASE_PATH; ?>/views/academic_user/declined_leads.php" class="block p-2 rounded hover:bg-red-700/30 <?php echo $currentPage === 'declined_leads.php' ? 'bg-red-700/50' : ''; ?>">
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
                <!-- Filter Form -->
                <form method="GET" action="/std_mgmt/views/academic_user/declined_leads.php" class="space-y-4 mb-6">
                    <div class="form-group">
                        <label for="course" class="block text-sm font-medium text-gray-700">Filter by Course</label>
                        <select name="course" id="course" class="mt-1 focus:ring-blue-500 focus:border-blue-500">
                            <option value="">All</option>
                            <?php foreach (array_unique(array_column($declined_leads, 'form_name')) as $course_name): ?>
                                <option value="<?php echo htmlspecialchars($course_name); ?>" <?php echo $course === $course_name ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($course_name); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <button type="submit" class="px-6 py-3 bg-gradient-to-r from-blue-600 to-blue-700 text-white rounded-lg hover:from-blue-700 hover:to-blue-800 shadow-md hover:shadow-lg transition-all duration-300">Apply Filter</button>
                </form>
                <?php if (empty($declined_leads)): ?>
                    <div class="p-4 text-gray-600">No declined leads found.</div>
                <?php else: ?>
                    <div class="overflow-x-auto">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Lead ID</th>
                                    <th>Course Name</th>
                                    <th>Full Name</th>
                                    <th>Email</th>
                                    <th>Phone</th>
                                    <th>Assigned To</th>
                                    <th>Status</th>
                                    <th class="action-cell">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($declined_leads as $lead): ?>
                                    <tr>
                                        <td class="p-3"><?php echo htmlspecialchars($lead['lead_id'] ?? 'N/A'); ?></td>
                                        <td class="p-3"><?php echo htmlspecialchars($lead['form_name'] ?? 'N/A'); ?></td>
                                        <td class="p-3"><?php echo htmlspecialchars($lead['full_name'] ?? 'N/A'); ?></td>
                                        <td class="p-3"><?php echo htmlspecialchars($lead['email'] ?? 'N/A'); ?></td>
                                        <td class="p-3"><?php echo htmlspecialchars($lead['phone'] ?? 'N/A'); ?></td>
                                        <td class="p-3"><?php echo htmlspecialchars($lead['username'] ?? 'N/A'); ?></td>
                                        <td class="p-3"><?php echo htmlspecialchars($lead['status'] ?? 'N/A'); ?></td>
                                        <td class="action-cell">
                                            <a href="/std_mgmt/views/academic_user/lead_details.php?lead_id=<?php echo htmlspecialchars((string)$lead['lead_id']); ?>" class="px-6 py-3 bg-gradient-to-r from-blue-600 to-blue-700 text-white rounded-lg hover:from-blue-700 hover:to-blue-800 shadow-md hover:shadow-lg transition-all duration-300">View Details</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>

                <div class="mt-6">
                    <a href="<?php echo BASE_PATH; ?>/views/academic_user/dashboard.php" class="px-6 py-3 bg-gradient-to-r from-blue-600 to-blue-700 text-white rounded-lg hover:from-blue-700 hover:to-blue-800 shadow-md hover:shadow-lg transition-all duration-300">Back to Dashboard</a>
                </div>
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