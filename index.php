<?php
$page_title = 'Home';
include 'includes/header.php';

// Get featured products
$stmt = $pdo->query("SELECT p.*, c.name as category_name 
                     FROM products p 
                     LEFT JOIN categories c ON p.category_id = c.id 
                     ORDER BY p.created_at DESC 
                     LIMIT 6");
$featured_products = $stmt->fetchAll();

// Get categories
$categories = $pdo->query("SELECT * FROM categories")->fetchAll();
?>

<div class="hero">
    <div class="container">
        <h1>🛍️ Selamat Datang di Shop Online</h1>
        <p>Temukan produk berkualitas dengan harga terbaik. Belanja mudah, cepat, dan aman!</p>
    </div>
</div>

<div class="container">
    <!-- Categories Section -->
    <section class="section">
        <h2 class="section-title">Kategori Produk</h2>
        <div class="product-grid">
            <?php foreach ($categories as $category): ?>
                <a href="products.php?category=<?php echo $category['id']; ?>" style="text-decoration: none;">
                    <div class="product-card">
                        <div class="product-image">📦</div>
                        <div class="product-info">
                            <h3 class="product-name"><?php echo htmlspecialchars($category['name']); ?></h3>
                            <p class="product-description"><?php echo htmlspecialchars($category['description']); ?></p>
                        </div>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
    </section>

    <!-- Featured Products -->
    <section class="section">
        <h2 class="section-title">Produk Terbaru</h2>
        <div class="product-grid">
            <?php foreach ($featured_products as $product): ?>
                <div class="product-card">
                    <div class="product-image">
                        <?php if ($product['image']): ?>
                            <img src="uploads/<?php echo htmlspecialchars($product['image']); ?>"
                                alt="<?php echo htmlspecialchars($product['name']); ?>"
                                style="width: 100%; height: 100%; object-fit: cover;">
                        <?php else: ?>
                            📦
                        <?php endif; ?>
                    </div>
                    <div class="product-info">
                        <h3 class="product-name"><?php echo htmlspecialchars($product['name']); ?></h3>
                        <p class="product-description"><?php echo htmlspecialchars($product['description']); ?></p>
                        <div class="product-price"><?php echo formatRupiah($product['price']); ?></div>
                        <a href="product-detail.php?id=<?php echo $product['id']; ?>" class="btn btn-primary btn-block">
                            Lihat Detail
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="section" style="text-align: center; padding: 3rem 0;">
        <div class="card" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
            <h2 style="font-size: 2rem; margin-bottom: 1rem;">Mulai Belanja Sekarang!</h2>
            <p style="font-size: 1.1rem; margin-bottom: 2rem; opacity: 0.9;">
                Dapatkan penawaran terbaik untuk produk pilihan Anda
            </p>
            <a href="products.php" class="btn btn-secondary" style="display: inline-block;">
                Lihat Semua Produk →
            </a>
        </div>
    </section>
</div>

<?php include 'includes/footer.php'; ?>