<?php
session_start();
require_once '../../config/database.php';
require_once '../../includes/Auth.php';

$auth = new Auth($conn);

// Check if user is logged in
if (!$auth->isLoggedIn()) {
    header('Location: ../../index.php');
    exit;
}

$userId = $_SESSION['user_id'];
$userRole = $_SESSION['role'];
$pageTitle = 'Client Intake Wizard';
