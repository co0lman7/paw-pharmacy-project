<?php
/**
 * Common Header
 *
 * This file contains the common header HTML for all public pages.
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/functions.php';

$categories = getCategories();
$cartCount = getCartCount();
$currentUser = getCurrentUser();

// Determine active page for navigation highlighting
$currentPage = basename($_SERVER['PHP_SELF'], '.php');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="PharmaCare - Your trusted online pharmacy for medications, vitamins, and health products.">
    <title><?php echo isset($pageTitle) ? sanitize($pageTitle) . ' - ' : ''; ?>PharmaCare Pharmacy</title>
    <link rel="icon" type="image/svg+xml" href="<?php echo getBaseUrl(); ?>/assets/favicon.svg">
    <link rel="stylesheet" href="<?php echo getBaseUrl(); ?>/assets/css/style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <header class="main-header">
        <div class="header-top">
            <div class="container">
                <div class="header-top-content">
                    <span class="contact-info">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path></svg>
                        +1 (800) 123-4567
                    </span>
                    <span class="working-hours">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
                        Mon-Sat: 8:00 AM - 9:00 PM
                    </span>
                </div>
            </div>
        </div>

        <nav class="main-nav">
            <div class="container">
                <div class="nav-content">
                    <a href="<?php echo getBaseUrl(); ?>/index.php" class="logo">
                        <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M8 2v4"></path><path d="M16 2v4"></path><rect width="16" height="14" x="4" y="6" rx="2"></rect><path d="M9 14h6"></path><path d="M12 11v6"></path></svg>
                        <span>PharmaCare</span>
                    </a>

                    <form action="<?php echo getBaseUrl(); ?>/pages/search.php" method="GET" class="search-form">
                        <input type="text" name="q" placeholder="Search for medicines, vitamins..." class="search-input" value="<?php echo isset($_GET['q']) ? sanitize($_GET['q']) : ''; ?>">
                        <button type="submit" class="search-btn">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                        </button>
                    </form>

                    <div class="nav-actions">
                        <?php if (isLoggedIn()): ?>
                            <div class="user-dropdown">
                                <button class="user-btn">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
                                    <span class="user-name"><?php echo sanitize($currentUser['first_name']); ?></span>
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"></polyline></svg>
                                </button>
                                <div class="dropdown-menu">
                                    <a href="<?php echo getBaseUrl(); ?>/pages/profile.php">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
                                        My Profile
                                    </a>
                                    <?php if (isAdmin()): ?>
                                        <a href="<?php echo getBaseUrl(); ?>/admin/index.php">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect><line x1="3" y1="9" x2="21" y2="9"></line><line x1="9" y1="21" x2="9" y2="9"></line></svg>
                                            Admin Panel
                                        </a>
                                    <?php endif; ?>
                                    <a href="<?php echo getBaseUrl(); ?>/actions/auth.php?action=logout">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path><polyline points="16 17 21 12 16 7"></polyline><line x1="21" y1="12" x2="9" y2="12"></line></svg>
                                        Logout
                                    </a>
                                </div>
                            </div>
                        <?php else: ?>
                            <a href="<?php echo getBaseUrl(); ?>/pages/login.php" class="nav-link">
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"></path><polyline points="10 17 15 12 10 7"></polyline><line x1="15" y1="12" x2="3" y2="12"></line></svg>
                                Login
                            </a>
                        <?php endif; ?>

                        <a href="<?php echo getBaseUrl(); ?>/pages/cart.php" class="cart-link">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="21" r="1"></circle><circle cx="20" cy="21" r="1"></circle><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path></svg>
                            <span class="cart-count" id="cart-count"><?php echo $cartCount; ?></span>
                        </a>

                        <button class="mobile-menu-toggle" id="mobile-menu-toggle">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="3" y1="12" x2="21" y2="12"></line><line x1="3" y1="6" x2="21" y2="6"></line><line x1="3" y1="18" x2="21" y2="18"></line></svg>
                        </button>
                    </div>
                </div>
            </div>
        </nav>

        <nav class="category-nav" id="category-nav">
            <div class="container">
                <ul class="category-list">
                    <li>
                        <a href="<?php echo getBaseUrl(); ?>/pages/products.php" class="<?php echo $currentPage === 'products' && !isset($_GET['category']) ? 'active' : ''; ?>">
                            All Products
                        </a>
                    </li>
                    <?php foreach ($categories as $category): ?>
                        <li>
                            <a href="<?php echo getBaseUrl(); ?>/pages/products.php?category=<?php echo sanitize($category['slug']); ?>" class="<?php echo isset($_GET['category']) && $_GET['category'] === $category['slug'] ? 'active' : ''; ?>">
                                <?php echo sanitize($category['name']); ?>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </nav>
    </header>

    <main class="main-content">
        <div class="container">
            <?php echo displayFlashMessages(); ?>
