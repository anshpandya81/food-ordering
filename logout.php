<?php
// ============================================================
// logout.php — Destroy Session and Redirect
// ============================================================
require_once 'includes/config.php';

// Destroy all session data
$_SESSION = [];
session_destroy();

// Redirect to homepage
header("Location: " . SITE_URL . "/index.php");
exit();
