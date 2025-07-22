<?php
require_once __DIR__ . '/../../../backend/config/db_connect.php';
require_once __DIR__ . '/../../../backend/controllers/PaymentPlanController.php';

header('Content-Type: application/json');

if (!isset($_GET['plan_id']) {
    echo json_encode([]);
    exit;
}

$plan_id = (int)$_GET['plan_id'];
$controller = new PaymentPlanController($pdo);
$installments = $controller->getInstallmentsByPlanId($plan_id);

echo json_encode($installments);