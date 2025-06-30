<?php
session_start();
require_once __DIR__ . '/../../backend/config/db_connect.php';
if (!isset($pdo)) {
    error_log("PDO not defined in academic_user/dashboard.php at " . date('Y-m-d H:i:s'));
    die("Database connection error");
}
require_once __DIR__ . '/../../backend/controllers/AuthController.php';
require_once __DIR__ . '/../../backend/controllers/LeadController.php';

$auth = new AuthController($pdo);
$leadController = new LeadController($pdo);
$user = $auth->getCurrentUser();
if (!$user || $user['role'] !== 'academic_user') {
    header('Location: /std_mgmt/views/auth/login.php?error=Unauthorized%20access');
    exit;
}

$totalLeads = method_exists($leadController, 'getTotalLeads') ? $leadController->getTotalLeads() : 'N/A';
$pendingRegistrations = method_exists($leadController, 'getPendingRegistrationsCount') ? $leadController->getPendingRegistrationsCount() : 'N/A';
$assignedLeads = method_exists($leadController, 'getAssignedLeadsCount') ? $leadController->getAssignedLeadsCount() : 'N/A';

define('BASE_PATH', '/std_mgmt');
$currentPage = basename($_SERVER['PHP_SELF']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Academic User Dashboard - Student Management System</title>
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

            <!-- Dashboard Content -->
            <div class="max-w-4xl mx-auto bg-white/80 backdrop-blur-md p-6 rounded-xl shadow-xl">
                <h1 class="text-3xl font-bold mb-4 text-blue-900 text-shadow">Welcome, <?php echo htmlspecialchars($user['username']); ?> (Academic User)</h1>
                <p class="mb-6 text-gray-700 text-lg">Review pending registrations and registered leads from this dashboard.</p>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-6 mb-6">
                    <div class="bg-gradient-to-br from-blue-100 to-blue-200 p-4 rounded-xl text-center shadow-lg hover:shadow-2xl transition-all duration-300">
                        <h3 class="text-lg font-semibold text-blue-800">Total Leads</h3>
                        <p class="text-3xl font-bold text-blue-600"><?php echo htmlspecialchars($totalLeads); ?></p>
                    </div>
                    <div class="bg-gradient-to-br from-yellow-100 to-yellow-200 p-4 rounded-xl text-center shadow-lg hover:shadow-2xl transition-all duration-300">
                        <h3 class="text-lg font-semibold text-yellow-800">Pending Registrations</h3>
                        <p class="text-3xl font-bold text-yellow-600"><?php echo htmlspecialchars($pendingRegistrations); ?></p>
                    </div>
                    <div class="bg-gradient-to-br from-green-100 to-green-200 p-4 rounded-xl text-center shadow-lg hover:shadow-2xl transition-all duration-300">
                        <h3 class="text-lg font-semibold text-green-800">Assigned Leads</h3>
                        <p class="text-3xl font-bold text-green-600"><?php echo htmlspecialchars($assignedLeads); ?></p>
                    </div>
                </div>
                <div class="flex flex-wrap gap-4">
                    <a href="<?php echo BASE_PATH; ?>/views/academic_user/pending_registrations.php" class="px-6 py-3 bg-gradient-to-r from-blue-600 to-blue-700 text-white rounded-lg hover:from-blue-700 hover:to-blue-800 shadow-md hover:shadow-lg transition-all duration-300">View Pending Registrations</a>
                    <a href="<?php echo BASE_PATH; ?>/views/academic_user/registered_leads.php" class="px-6 py-3 bg-gradient-to-r from-blue-600 to-blue-700 text-white rounded-lg hover:from-blue-700 hover:to-blue-800 shadow-md hover:shadow-lg transition-all duration-300">View Registered Leads</a>
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