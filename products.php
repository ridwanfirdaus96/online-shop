<?php
$page_title = 'Produk';
include 'includes/header.php';

// Get filter parameters
$category_id = isset($_GET['category']) ? (int) $_GET['category'] : null;
$search = isset($_GET['search']) ? sanitize($_GET['search']) : '';

// Build query
$sql = "SELECT p.*, c.name as category_name 
        FROM products p 
        LEFT JOIN categories c ON p.category_id = c.id 
        WHERE 1=1";

$params = [];

if ($category_id) {
    $sql .= " AND p.category_id = ?";
    $params[] = $category_id;
}

if ($search) {
    $sql .= " AND (p.name LIKE ? OR p.description LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

$sql .= " ORDER BY p.created_at DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll();

// Get all categories for filter
$categories = $pdo->query("SELECT * FROM categories")->fetchAll();
?>

<div class="container" style="margin-top: 2rem;">
    <h1 class="section-title">Semua Produk</h1>

    <!-- Filter Section -->
    <div class="card">
        <form method="GET" action="products.php">
            <div style="display: grid; grid-template-columns: 1fr 1fr auto; gap: 1rem; align-items: end;">
                <div class="form-group" style="margin-bottom: 0;">
                    <label class="form-label">Cari Produk</label>
                    <input type="text" name="search" class="form-control" placeholder="Nama produk..."
                        value="<?php echo htmlspecialchars($search); ?>">
                </div>
                <div class="form-group" style="margin-bottom: 0;">
                    <label class="form-label">Kategori</label>
                    <select name="category" class="form-control">
                        <option value="">Semua Kategori</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?php echo $cat['id']; ?>" <?php echo $category_id == $cat['id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($cat['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <button type="submit" class="btn btn-primary">Filter</button>
                    <a href="products.php" class="btn btn-outline">Reset</a>
                </div>
            </div>
        </form>
    </div>

    <!-- Products Grid -->
    <?php if (empty($products)): ?>
        <div class="card" style="text-align: center; padding: 3rem;">
            <h3>Tidak ada produk ditemukan</h3>
            <p style="color: var(--gray);">Coba ubah filter atau kata kunci pencarian Anda</p>
        </div>
    <?php else: ?>
        <div class="product-grid">
            <?php foreach ($products as $product): ?>
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
                        <span
                            style="display: inline-block; background: var(--primary); color: white; padding: 0.25rem 0.75rem; border-radius: 20px; font-size: 0.75rem; margin-bottom: 0.5rem;">
                            <?php echo htmlspecialchars($product['category_name'] ?? 'Uncategorized'); ?>
                        </span>
                        <h3 class="product-name"><?php echo htmlspecialchars($product['name']); ?></h3>
                        <p class="product-description"><?php echo htmlspecialchars($product['description']); ?></p>
                        <div class="product-price"><?php echo formatRupiah($product['price']); ?></div>
                        <div style="font-size: 0.875rem; color: var(--gray); margin-bottom: 1rem;">
                            Stok: <?php echo $product['stock']; ?>
                        </div>
                        <a href="product-detail.php?id=<?php echo $product['id']; ?>" class="btn btn-primary btn-block">
                            Lihat Detail
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>