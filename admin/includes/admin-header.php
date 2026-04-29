<?php
// ============================================================
// admin/includes/admin-header.php
// ============================================================
require_once __DIR__ . '/../../includes/config.php';

// Protect all admin pages
if (!isAdmin()) {
    redirect(SITE_URL . '/admin/login.php');
}

$currentPage = basename($_SERVER['PHP_SELF'], '.php');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($pageTitle) ? e($pageTitle).' — ' : '' ?>Admin Panel</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700;900&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= SITE_URL ?>/admin/css/admin-style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>
<div class="admin-layout">

<!-- ── SIDEBAR ── -->
<aside class="sidebar" id="sidebar">
    <div class="sidebar-logo">
        <h2>🍕 FoodieExpress</h2>
        <p>Admin Panel</p>
    </div>

    <nav class="sidebar-nav">
        <div class="nav-section-title">Main</div>
        <a href="<?= SITE_URL ?>/admin/index.php" class="<?= $currentPage==='index' ? 'active' : '' ?>">
            <i class="fas fa-tachometer-alt"></i> Dashboard
        </a>

        <div class="nav-section-title">Menu</div>
        <a href="<?= SITE_URL ?>/admin/manage-food.php" class="<?= $currentPage==='manage-food' ? 'active' : '' ?>">
            <i class="fas fa-utensils"></i> Food Items
        </a>
        <a href="<?= SITE_URL ?>/admin/add-food.php" class="<?= $currentPage==='add-food' ? 'active' : '' ?>">
            <i class="fas fa-plus-circle"></i> Add Food Item
        </a>
        <a href="<?= SITE_URL ?>/admin/manage-categories.php" class="<?= $currentPage==='manage-categories' ? 'active' : '' ?>">
            <i class="fas fa-tags"></i> Categories
        </a>

        <div class="nav-section-title">Orders</div>
        <a href="<?= SITE_URL ?>/admin/manage-orders.php" class="<?= $currentPage==='manage-orders' ? 'active' : '' ?>">
            <i class="fas fa-receipt"></i> All Orders
        </a>

        <div class="nav-section-title">Users</div>
        <a href="<?= SITE_URL ?>/admin/manage-users.php" class="<?= $currentPage==='manage-users' ? 'active' : '' ?>">
            <i class="fas fa-users"></i> Users
        </a>

        <div class="nav-section-title">Site</div>
        <a href="<?= SITE_URL ?>/index.php" target="_blank">
            <i class="fas fa-external-link-alt"></i> View Website
        </a>
    </nav>

    <div class="sidebar-footer">
        <a href="<?= SITE_URL ?>/logout.php">
            <i class="fas fa-sign-out-alt"></i> Logout
        </a>
    </div>
</aside>

<!-- ── MAIN ── -->
<div class="admin-main">

    <!-- Top Bar -->
    <header class="admin-header">
        <div style="display:flex;align-items:center;gap:14px;">
            <button id="sidebarToggle" style="background:none;border:none;cursor:pointer;font-size:1.1rem;color:var(--text-muted);" onclick="document.getElementById('sidebar').classList.toggle('open')">
                <i class="fas fa-bars"></i>
            </button>
            <h1><?= isset($pageTitle) ? e($pageTitle) : 'Dashboard' ?></h1>
        </div>
        <div class="header-right">
            <span style="font-size:0.85rem;color:var(--text-muted);">
                <i class="fas fa-user-shield" style="color:var(--primary);"></i>
                <?= e($_SESSION['user_name'] ?? 'Admin') ?>
            </span>
            <div class="admin-avatar"><?= strtoupper(substr($_SESSION['user_name'] ?? 'A', 0, 1)) ?></div>
        </div>
    </header>

    <!-- Page Content starts -->
    <div class="admin-content">
