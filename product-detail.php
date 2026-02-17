<?php
$page_title = 'Detail Produk';
include 'includes/header.php';

// Handle add to cart
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_to_cart']) && checkCsrf()) {
    $product_id = (int) $_POST['product_id'];
    $quantity = (int) $_POST['quantity'];

    if ($quantity > 0) {
        addToCart($product_id, $quantity);
        setFlash('success', 'Produk berhasil ditambahkan ke keranjang!');
        redirect('cart.php');
    }
}

// Get product details
$product_id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

$stmt = $pdo->prepare("SELECT p.*, c.name as category_name 
                       FROM products p 
                       LEFT JOIN categories c ON p.category_id = c.id 
                       WHERE p.id = ?");
$stmt->execute([$product_id]);
$product = $stmt->fetch();

if (!$product) {
    setFlash('danger', 'Produk tidak ditemukan!');
    redirect('products.php');
}

// Get related products
$stmt = $pdo->prepare("SELECT * FROM products 
                       WHERE category_id = ? AND id != ? 
                       LIMIT 4");
$stmt->execute([$product['category_id'], $product_id]);
$related_products = $stmt->fetchAll();
?>

<div class="container" style="margin-top: 2rem;">
    <a href="products.php"
        style="color: var(--primary); text-decoration: none; margin-bottom: 1rem; display: inline-block;">
        ← Kembali ke Produk
    </a>

    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 3rem; margin-bottom: 3rem;">
        <!-- Product Image -->
        <div>
            <div style="background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%); 
                        border-radius: 12px; 
                        height: 500px; 
                        display: flex; 
                        align-items: center; 
                        justify-content: center;
                        font-size: 8rem;">
                <?php if ($product['image']): ?>
                    <img src="uploads/<?php echo htmlspecialchars($product['image']); ?>"
                        alt="<?php echo htmlspecialchars($product['name']); ?>"
                        style="width: 100%; height: 100%; object-fit: cover; border-radius: 12px;">
                <?php else: ?>
                    📦
                <?php endif; ?>
            </div>
        </div>

        <!-- Product Info -->
        <div>
            <span
                style="display: inline-block; background: var(--primary); color: white; padding: 0.5rem 1rem; border-radius: 20px; font-size: 0.875rem; margin-bottom: 1rem;">
                <?php echo htmlspecialchars($product['category_name'] ?? 'Uncategorized'); ?>
            </span>

            <h1 style="font-size: 2.5rem; margin-bottom: 1rem;">
                <?php echo htmlspecialchars($product['name']); ?>
            </h1>

            <div style="font-size: 3rem; font-weight: 700; color: var(--primary); margin-bottom: 1.5rem;">
                <?php echo formatRupiah($product['price']); ?>
            </div>

            <div style="margin-bottom: 1.5rem;">
                <strong>Stok:</strong>
                <span style="color: <?php echo $product['stock'] > 0 ? 'var(--success)' : 'var(--danger)'; ?>">
                    <?php echo $product['stock'] > 0 ? $product['stock'] . ' tersedia' : 'Habis'; ?>
                </span>
            </div>

            <div style="margin-bottom: 2rem; line-height: 1.8; color: var(--gray);">
                <?php echo nl2br(htmlspecialchars($product['description'])); ?>
            </div>

            <?php if ($product['stock'] > 0): ?>
                <form method="POST" style="margin-bottom: 2rem;">
                    <?php echo getCsrfInput(); ?>
                    <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                    <div class="form-group">
                        <label class="form-label">Jumlah</label>
                        <input type="number" name="quantity" class="form-control" value="1" min="1"
                            max="<?php echo $product['stock']; ?>" style="max-width: 150px;">
                    </div>
                    <button type="submit" name="add_to_cart" class="btn btn-primary"
                        style="padding: 1rem 3rem; font-size: 1.1rem;">
                        🛒 Tambah ke Keranjang
                    </button>
                </form>
            <?php else: ?>
                <div class="alert alert-danger">
                    Maaf, produk ini sedang habis.
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Related Products -->
    <?php if (!empty($related_products)): ?>
        <section class="section">
            <h2 class="section-title">Produk Terkait</h2>
            <div class="product-grid">
                <?php foreach ($related_products as $related): ?>
                    <div class="product-card">
                        <div class="product-image">
                            <?php if ($related['image']): ?>
                                <img src="uploads/<?php echo htmlspecialchars($related['image']); ?>"
                                    alt="<?php echo htmlspecialchars($related['name']); ?>"
                                    style="width: 100%; height: 100%; object-fit: cover;">
                            <?php else: ?>
                                📦
                            <?php endif; ?>
                        </div>
                        <div class="product-info">
                            <h3 class="product-name"><?php echo htmlspecialchars($related['name']); ?></h3>
                            <div class="product-price"><?php echo formatRupiah($related['price']); ?></div>
                            <a href="product-detail.php?id=<?php echo $related['id']; ?>" class="btn btn-primary btn-block">
                                Lihat Detail
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>