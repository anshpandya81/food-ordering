<?php
// ============================================================
// includes/header.php — Common Header & Navigation Bar
// ============================================================
require_once __DIR__ . '/config.php';
?>
<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($pageTitle) ? e($pageTitle).' — ' : '' ?><?= SITE_NAME ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700;900&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= SITE_URL ?>/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>

<!-- ── TOP NAVBAR ── -->
<nav class="navbar" id="navbar">
    <div class="nav-container">

        <!-- Logo -->
        <a href="<?= SITE_URL ?>/index.php" class="logo">
            🍕 <span><?= SITE_NAME ?></span>
        </a>

        <!-- Search Bar -->
        <div class="nav-search">
            <i class="fas fa-search"></i>
            <input type="text" id="globalSearch" placeholder="Search for pizza, burgers...">
        </div>

        <!-- Nav Links -->
        <ul class="nav-links" id="navLinks">
            <li><a href="<?= SITE_URL ?>/index.php"><i class="fas fa-home"></i> Home</a></li>
            <li><a href="<?= SITE_URL ?>/menu.php"><i class="fas fa-utensils"></i> Menu</a></li>

            <?php if (isLoggedIn()): ?>
                <li><a href="<?= SITE_URL ?>/order-history.php"><i class="fas fa-receipt"></i> Orders</a></li>
                <li><a href="<?= SITE_URL ?>/logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                <?php if (isAdmin()): ?>
                    <li><a href="<?= SITE_URL ?>/admin/index.php" class="admin-link"><i class="fas fa-shield-alt"></i> Admin</a></li>
                <?php endif; ?>
            <?php else: ?>
                <li><a href="<?= SITE_URL ?>/login.php"><i class="fas fa-sign-in-alt"></i> Login</a></li>
                <li><a href="<?= SITE_URL ?>/register.php" class="btn-register">Register</a></li>
            <?php endif; ?>

            <!-- Cart Icon -->
            <li>
                <a href="<?= SITE_URL ?>/cart.php" class="cart-icon-link">
                    <i class="fas fa-shopping-cart"></i>
                    <span class="cart-badge" id="cartBadge">0</span>
                </a>
            </li>

            <!-- Dark Mode Toggle -->
            <li>
                <button class="theme-toggle" id="themeToggle" title="Toggle Dark Mode">
                    <i class="fas fa-moon"></i>
                </button>
            </li>
        </ul>

        <!-- Mobile Menu Toggle -->
        <button class="hamburger" id="hamburger">
            <span></span><span></span><span></span>
        </button>
    </div>
</nav>

<!-- ── TOAST NOTIFICATION ── -->
<div class="toast" id="toast"></div>

<!-- ── MAIN CONTENT STARTS ── -->
<main class="main-content">
