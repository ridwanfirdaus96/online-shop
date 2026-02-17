<?php
session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/functions.php';

$cart_count = getCartCount();
$flash = getFlash();
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title . ' - ' : ''; ?>Shop Online</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/shop-online/assets/css/output.css">
    <link rel="stylesheet" href="/shop-online/assets/css/style.css">
</head>

<body>
    <nav class="navbar">
        <div class="container">
            <a href="/shop-online/" class="logo">
                🛒 Shop Online
            </a>

            <form class="search-form" method="GET" action="/shop-online/products.php">
                <label for="search" class="sr-only">Search</label>
                <div class="search-wrapper">
                    <div class="search-icon-wrapper">
                        <svg class="search-svg-icon" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24"
                            height="24" fill="none" viewBox="0 0 24 24">
                            <path stroke="currentColor" stroke-linecap="round" stroke-width="2"
                                d="m21 21-3.5-3.5M17 10a7 7 0 1 1-14 0 7 7 0 0 1 14 0Z" />
                        </svg>
                    </div>
                    <input type="search" id="search" name="search" class="search-input-field"
                        placeholder="Cari produk..." />
                    <button type="submit" class="search-button">Search</button>
                </div>
            </form>

            <nav>
                <ul>
                    <li><a href="/shop-online/products.php">Produk</a></li>
                    <?php if (isLoggedIn()): ?>
                        <?php if (isAdmin()): ?>
                            <li><a href="/shop-online/admin/">Admin</a></li>
                        <?php else: ?>
                            <li><a href="/shop-online/my-orders.php">My Orders</a></li>
                        <?php endif; ?>
                        <li><a href="/shop-online/profile.php">Profile</a></li>
                        <li><a href="/shop-online/logout.php">Logout</a></li>
                    <?php else: ?>
                        <li><a href="/shop-online/login.php">Login</a></li>
                    <?php endif; ?>
                    <li>
                        <a href="/shop-online/cart.php" class="cart-icon">
                            🛒
                            <?php if ($cart_count > 0): ?>
                                <span class="cart-badge"><?php echo $cart_count; ?></span>
                            <?php endif; ?>
                        </a>
                    </li>
                </ul>
            </nav>
        </div>
    </nav>

    <?php if ($flash): ?>
        <div class="container" style="margin-top: 1.5rem;">
            <div class="alert alert-<?php echo $flash['type']; ?>">
                <?php echo $flash['message']; ?>
            </div>
        </div>
    <?php endif; ?>