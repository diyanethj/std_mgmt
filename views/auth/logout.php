<?php
require_once __DIR__ . '/../../backend/controllers/AuthController.php';
$auth = new AuthController($pdo);
$auth->logout();