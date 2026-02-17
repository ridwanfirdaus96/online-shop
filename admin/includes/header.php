<?php
session_start();
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/functions.php';

// Check if user is admin
if (!isAdmin()) {
    setFlash('danger', 'Akses ditolak! Anda harus login sebagai admin.');
    redirect('../login.php');
}

$flash = getFlash();
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title . ' - ' : ''; ?>Admin - Shop Online</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/shop-online/assets/css/style.css">
    <?php if (isset($extra_css)): ?>
        <?php echo $extra_css; ?>
    <?php endif; ?>
</head>

<body>
    <nav class="navbar">
        <div class="container">
            <a href="/shop-online/admin/" class="logo">🛒 Admin Panel</a>

            <nav>
                <ul>
                    <li><a href="/shop-online/admin/products.php">Produk</a></li>
                    <li><a href="/shop-online/admin/orders.php">Pesanan</a></li>
                    <li><a href="/shop-online/admin/payment-accounts.php">Rekening Payment</a></li>
                    <li><a href="/shop-online/">Lihat Toko</a></li>
                    <li><a href="/shop-online/logout.php">Logout</a></li>
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