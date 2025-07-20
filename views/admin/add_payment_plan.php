<?php
session_start();
require_once __DIR__ . '/../../backend/config/db_connect.php';
if (!isset($pdo)) {
    error_log("PDO not defined in add_payment_plan.php at " . date('Y-m-d H:i:s'));
    die("Database connection error");
}
require_once __DIR__ . '/../../backend/controllers/AuthController.php';
require_once __DIR__ . '/../../backend/controllers/PaymentPlanController.php';

$authController = new AuthController($pdo);
$paymentPlanController = new PaymentPlanController($pdo);

$user = $authController->getCurrentUser();
if (!$user || $user['role'] !== 'admin') {
    header('Location: /std_mgmt/views/auth/login.php?error=Unauthorized%20access');
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $plan_name = trim($_POST['plan_name'] ?? '');
    $total_amount = floatval($_POST['total_amount'] ?? 0);
    $installments = [];
    $installment_count = intval($_POST['installment_count'] ?? 0);

    for ($i = 0; $i < $installment_count; $i++) {
        $installment_name = trim($_POST["installment_name_$i"] ?? '');
        $installment_amount = floatval($_POST["installment_amount_$i"] ?? 0);
        if ($installment_name && $installment_amount > 0) {
            $installments[] = ['name' => $installment_name, 'amount' => $installment_amount];
        }
    }

    $installment_total = array_sum(array_column($installments, 'amount'));

    if (empty($plan_name) || $total_amount <= 0) {
        $error = 'Please provide a valid plan name and total amount.';
    } elseif (count($installments) === 0) {
        $error = 'Please add at least one installment.';
    } elseif ($installment_total !== $total_amount) {
        $error = 'The sum of installment amounts must equal the total amount.';
    } else {
        $result = $paymentPlanController->createPaymentPlan($plan_name, $total_amount, $installments);
        if ($result) {
            $success = 'Payment plan created successfully.';
        } else {
            $error = 'Failed to create payment plan. Please try again.';
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
    <title>Add Payment Plan - Student Management System</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;700&family=Roboto:wght@300;400&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/std_mgmt/css/style.css?v=<?php echo time(); ?>">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/gsap.min.js"></script>
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
        .installment-row {
            display: flex;
            gap: 1rem;
            margin-bottom: 1rem;
        }
    </style>
</head>
<body class="bg-gradient-to-br from-gray-100 to-gray-300 font-sans">
    <div class="flex min-h-screen">
        <!-- Sidebar -->
        <div id="sidebar" class="sidebar fixed inset-y-0 left-0 w-64 bg-gradient-to-b from-blue-900 to-blue-700 text-white p-4 md:relative md:translate-x-0 z-10 shadow-lg">
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-2xl font-bold text-glow">Admin Panel</h2>
                <button id="closeSidebar" class="md:hidden text-white focus:outline-none">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            <nav aria-label="Main navigation">
                <ul class="space-y-2">
                    <li><a href="<?php echo BASE_PATH; ?>/views/admin/dashboard.php" class="block p-2 rounded hover:bg-red-700/30 <?php echo $currentPage === 'dashboard.php' ? 'bg-red-700/50' : ''; ?>">Dashboard</a></li>
                    <li><a href="<?php echo BASE_PATH; ?>/views/admin/upload_leads.php" class="block p-2 rounded hover:bg-red-700/30 <?php echo $currentPage === 'upload_leads.php' ? 'bg-red-700/50' : ''; ?>">Upload Leads</a></li>
                    <li><a href="<?php echo BASE_PATH; ?>/views/admin/leads_list.php" class="block p-2 rounded hover:bg-red-700/30 <?php echo $currentPage === 'leads_list.php' ? 'bg-red-700/50' : ''; ?>">Leads List</a></li>
                    <li><a href="<?php echo BASE_PATH; ?>/views/admin/assigned_leads.php" class="block p-2 rounded hover:bg-red-700/30 <?php echo $currentPage === 'assigned_leads.php' ? 'bg-red-700/50' : ''; ?>">Assigned Leads</a></li>
                    <li><a href="<?php echo BASE_PATH; ?>/views/admin/pending_registrations.php" class="block p-2 rounded hover:bg-red-700/30 <?php echo $currentPage === 'pending_registrations.php' ? 'bg-red-700/50' : ''; ?>">Pending Registrations</a></li>
                    <li><a href="<?php echo BASE_PATH; ?>/views/admin/registered_leads.php" class="block p-2 rounded hover:bg-red-700/30 <?php echo $currentPage === 'registered_leads.php' ? 'bg-red-700/50' : ''; ?>">Registered Leads</a></li>
                    <li><a href="<?php echo BASE_PATH; ?>/views/admin/declined_leads.php" class="block p-2 rounded hover:bg-red-700/30 <?php echo $currentPage === 'declined_leads.php' ? 'bg-red-700/50' : ''; ?>">Declined Leads</a></li>
                    <li><a href="<?php echo BASE_PATH; ?>/views/admin/add_payment_plan.php" class="block p-2 rounded hover:bg-red-700/30 <?php echo $currentPage === 'add_payment_plan.php' ? 'bg-red-700/50' : ''; ?>">Add Payment Plan</a></li>
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

            <!-- Add Payment Plan Content -->
            <div class="max-w-4xl mx-auto bg-white/80 backdrop-blur-md p-6 rounded-xl shadow-xl">
                <h1 class="text-3xl font-bold mb-4 text-blue-900 text-shadow">Add Payment Plan</h1>
                <?php if ($error): ?>
                    <div class="mb-4 p-4 bg-red-100 text-red-700 rounded-lg"><?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>
                <?php if ($success): ?>
                    <div class="mb-4 p-4 bg-green-100 text-green-700 rounded-lg"><?php echo htmlspecialchars($success); ?></div>
                <?php endif; ?>

                <form method="POST" action="" class="space-y-6">
                    <div>
                        <label for="plan_name" class="block text-sm font-medium text-blue-800">Payment Plan Name</label>
                        <input type="text" name="plan_name" id="plan_name" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50" required>
                    </div>
                    <div>
                        <label for="total_amount" class="block text-sm font-medium text-blue-800">Total Amount (LKR)</label>
                        <input type="number" name="total_amount" id="total_amount" step="0.01" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-blue-800">Installments</label>
                        <div id="installments-container" class="space-y-4"></div>
                        <button type="button" id="add-installment" class="mt-2 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">Add Installment</button>
                        <input type="hidden" name="installment_count" id="installment_count" value="0">
                    </div>
                    <button type="submit" class="px-6 py-3 bg-gradient-to-r from-blue-600 to-blue-700 text-white rounded-lg hover:from-blue-700 hover:to-blue-800 shadow-md hover:shadow-lg transition-all duration-300">Save Payment Plan</button>
                </form>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const openSidebar = document.getElementById('openSidebar');
            const closeSidebar = document.getElementById('closeSidebar');
            const sidebar = document.getElementById('sidebar');

            // Initialize sidebar state
            if (window.innerWidth < 768) {
                sidebar.classList.add('translate-x-[-100%]');
            }

            openSidebar.addEventListener('click', () => {
                gsap.to(sidebar, { duration: 0.5, x: 0, ease: "power2.out" });
            });

            closeSidebar.addEventListener('click', () => {
                gsap.to(sidebar, { duration: 0.5, x: '-100%', ease: "power2.out" });
            });

            // Fade-in animation for content
            gsap.from(".max-w-4xl", { duration: 1, opacity: 0, y: 50, ease: "power2.out", delay: 0.2 });

            const container = document.getElementById('installments-container');
            const addButton = document.getElementById('add-installment');
            const countInput = document.getElementById('installment_count');
            let installmentCount = 0;

            addButton.addEventListener('click', () => {
                const row = document.createElement('div');
                row.className = 'installment-row';
                row.innerHTML = `
                    <input type="text" name="installment_name_${installmentCount}" placeholder="Installment Name" class="flex-1 rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50" required>
                    <input type="number" name="installment_amount_${installmentCount}" placeholder="Amount (LKR)" step="0.01" class="w-1/4 rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50" required>
                    <button type="button" class="remove-installment px-2 py-1 bg-red-600 text-white rounded-lg hover:bg-red-700" data-index="${installmentCount}">Remove</button>
                `;
                container.appendChild(row);
                gsap.from(row, { duration: 0.5, opacity: 0, y: 20, ease: "power2.out" });
                installmentCount++;
                countInput.value = installmentCount;

                row.querySelector('.remove-installment').addEventListener('click', () => {
                    gsap.to(row, { duration: 0.5, opacity: 0, y: -20, ease: "power2.out", onComplete: () => {
                        row.remove();
                        installmentCount--;
                        countInput.value = installmentCount;
                    }});
                });
            });
        });
    </script>
</body>
</html>