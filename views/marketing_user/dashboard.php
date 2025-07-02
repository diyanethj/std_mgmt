<?php
session_start();
require_once __DIR__ . '/../../backend/config/db_connect.php';
if (!isset($pdo)) {
    error_log("PDO not defined in dashboard.php at " . date('Y-m-d H:i:s'));
    die("Database connection error");
}
require_once __DIR__ . '/../../backend/controllers/AuthController.php';
require_once __DIR__ . '/../../backend/controllers/LeadController.php'; // Added to fetch lead counts

$authController = new AuthController($pdo);
$leadController = new LeadController($pdo); // Ensure PDO is passed if required by constructor

$user = $authController->getCurrentUser();
if (!$user || $user['role'] !== 'marketing_user') {
    header('Location: /std_mgmt/views/auth/login.php?error=Unauthorized%20access');
    exit;
}

define('BASE_PATH', '/std_mgmt');
$currentPage = basename($_SERVER['PHP_SELF']);

$user_id = $user['id']; // Assuming user array has 'id' key matching assigned_user_id
$getAssignedUserLeadsCount = $leadController->getAssignedUserLeadsCount($user_id); // Updated method name
$getRegisteredUserLeadsCount = $leadController->getRegisteredUserLeadsCount($user_id); // Assuming this method still 
$getPendingUserLeadsCount = $leadController->getPendingUserLeadsCount($user_id); // Assuming this method still 
$getDeclinedUserLeadsCount = $leadController->getDeclinedUserLeadsCount($user_id); // Assuming this method still 

error_log("Marketing user dashboard: user_id=$user_id, getAssignedUserLeadsCount=$getAssignedUserLeadsCount, getRegisteredUserLeadsCount=$getRegisteredUserLeadsCount, getPendingUserLeadsCount=$getPendingUserLeadsCount, getDeclinedUserLeadsCount=$getDeclinedUserLeadsCount at " . date('Y-m-d H:i:s'));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Marketing User Dashboard - Student Management System</title>
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

            <!-- Dashboard Content -->
            <div class="max-w-4xl mx-auto bg-white/80 backdrop-blur-md p-6 rounded-xl shadow-xl">
                <h1 class="text-3xl font-bold mb-4 text-blue-900 text-shadow">Marketing User Dashboard</h1>
                <p class="text-lg text-gray-700">Welcome to the marketing user dashboard. You can view your assigned leads and manage their details.</p>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 mb-6">
                    <div class="bg-blue-100 p-4 rounded-lg shadow">
                        <h2 class="text-xl font-semibold text-blue-800">Assigned Leads</h2>
                        <p class="text-3xl font-bold text-blue-600"><?php echo htmlspecialchars($getAssignedUserLeadsCount ?? 0); ?></p>
                    </div>
                    <div class="bg-yellow-100 p-4 rounded-lg shadow">
                        <h2 class="text-xl font-semibold text-yellow-800">Registered Leads</h2>
                        <p class="text-3xl font-bold text-yellow-600"><?php echo htmlspecialchars($getRegisteredUserLeadsCount ?? 0); ?></p>
                    </div>
                    <div class="bg-green-100 p-4 rounded-lg shadow">
                        <h2 class="text-xl font-semibold text-green-800">Pending Registrations </h2>
                        <p class="text-3xl font-bold text-green-600"><?php echo htmlspecialchars($getPendingUserLeadsCount ?? 0); ?></p>
                    </div>
                    <div class="bg-blue-100 p-4 rounded-lg shadow">
                        <h2 class="text-xl font-semibold text-blue-800">Declined Registrations </h2>
                        <p class="text-3xl font-bold text-blue-600"><?php echo htmlspecialchars($getDeclinedUserLeadsCount ?? 0); ?></p>
                    </div>
                </div>
                <div class="flex flex-wrap gap-4">
                    <a href="<?php echo BASE_PATH; ?>/views/marketing_user/assigned_leads.php" class="px-6 py-3 bg-gradient-to-r from-blue-600 to-blue-700 text-white rounded-lg hover:from-blue-700 hover:to-blue-800 shadow-md hover:shadow-lg transition-all duration-300">View Assigned Leads</a>
                    <a href="<?php echo BASE_PATH; ?>/views/marketing_user/pending_registrations.php" class="px-6 py-3 bg-gradient-to-r from-blue-600 to-blue-700 text-white rounded-lg hover:from-blue-700 hover:to-blue-800 shadow-md hover:shadow-lg transition-all duration-300">View Pending Registrations</a>
                    <a href="<?php echo BASE_PATH; ?>/views/marketing_user/registered_leads.php" class="px-6 py-3 bg-gradient-to-r from-blue-600 to-blue-700 text-white rounded-lg hover:from-blue-700 hover:to-blue-800 shadow-md hover:shadow-lg transition-all duration-300">View Registered Leads</a>
                    <a href="<?php echo BASE_PATH; ?>/views/marketing_user/declined_leads.php" class="px-6 py-3 bg-gradient-to-r from-blue-600 to-blue-700 text-white rounded-lg hover:from-blue-700 hover:to-blue-800 shadow-md hover:shadow-lg transition-all duration-300">View Declined Leads</a>
                    
                    
                    
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