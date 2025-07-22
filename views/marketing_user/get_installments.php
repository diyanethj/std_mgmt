<?php
require_once __DIR__ . '/../../backend/config/db_connect.php';
require_once __DIR__ . '/../../backend/controllers/PaymentPlanController.php';

$pdo = new PDO("mysql:host=localhost;dbname=std_mgmt", "root", "", [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
$paymentPlanController = new PaymentPlanController($pdo);
$plan_id = $_GET['plan_id'] ?? 0;
$installments = $paymentPlanController->getInstallmentsByPlanId($plan_id);
echo json_encode($installments);
?>