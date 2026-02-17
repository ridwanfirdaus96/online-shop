<?php
$page_title = 'Kelola Produk';
include 'includes/header.php';

// Handle delete
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_product']) && checkCsrf()) {
    $id = (int) $_POST['product_id'];
    $stmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
    if ($stmt->execute([$id])) {
        setFlash('success', 'Produk berhasil dihapus!');
    } else {
        setFlash('danger', 'Gagal menghapus produk!');
    }
    redirect('products.php');
}

// Get all products
$products = $pdo->query("SELECT p.*, c.name as category_name 
                         FROM products p 
                         LEFT JOIN categories c ON p.category_id = c.id 
                         ORDER BY p.created_at DESC")->fetchAll();
?>

<div class="container" style="margin-top: 2rem;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
        <h1 class="section-title" style="margin: 0;">📦 Kelola Produk</h1>
        <a href="add-product.php" class="btn btn-primary">+ Tambah Produk</a>
    </div>

    <div class="card">
        <?php if (empty($products)): ?>
            <p style="text-align: center; color: var(--gray); padding: 3rem;">Belum ada produk</p>
        <?php else: ?>
            <table class="table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nama</th>
                        <th>Kategori</th>
                        <th>Harga</th>
                        <th>Stok</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($products as $product): ?>
                        <tr>
                            <td><?php echo $product['id']; ?></td>
                            <td><?php echo htmlspecialchars($product['name']); ?></td>
                            <td><?php echo htmlspecialchars($product['category_name'] ?? '-'); ?></td>
                            <td><?php echo formatRupiah($product['price']); ?></td>
                            <td><?php echo $product['stock']; ?></td>
                            <td>
                                <a href="edit-product.php?id=<?php echo $product['id']; ?>"
                                    class="btn btn-sm btn-primary">Edit</a>
                                <form method="POST" style="display: inline;"
                                    onsubmit="return confirmDelete('Hapus produk ini?')">
                                    <?php echo getCsrfInput(); ?>
                                    <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                                    <button type="submit" name="delete_product" class="btn btn-sm btn-danger">Hapus</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>

<?php include 'includes/footer.php'; ?>