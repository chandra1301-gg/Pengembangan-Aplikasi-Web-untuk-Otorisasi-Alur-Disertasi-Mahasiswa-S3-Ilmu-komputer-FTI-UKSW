<?php
/**
 * File: penguji/index.php - Redirect ke dashboard
 */

session_start();
require_once '../includes/db_connect.php';
require_once '../includes/auth.php';

require_dosen();

// Redirect ke dashboard dosen
header("Location: dashboard.php");
exit();
?>