<?php
session_start();
ob_start();
require_once __DIR__ . '/backend/routes/routes.php';
ob_end_flush();