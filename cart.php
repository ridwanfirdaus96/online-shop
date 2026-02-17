<?php
$page_title = 'Keranjang Belanja';
include 'includes/header.php';

// Handle cart updates
if ($_SERVER['REQUEST_METHOD'] === 'POST' && checkCsrf()) {
    if (isset($_POST['update_cart'])) {
        $product_id = (int)$_POST['product_id'];
        $quantity = (int)$_POST['quantity'];
        updateCartQuantity($product_id, $quantity);
        setFlash('success', 'Keranjang berhasil diupdate!');
        redirect('cart.php');
    }
    
    if (isset($_POST['remove_item'])) {
        $product_id = (int)$_POST['product_id'];
        removeFromCart($product_id);
        setFlash('success', 'Produk berhasil dihapus dari keranjang!');
        redirect('cart.php');
    }
}

// Get cart items with product details
$cart_items = [];
$total = 0;

if (isset($_SESSION['cart']) && !empty($_SESSION['cart'])) {
    $product_ids = array_keys($_SESSION['cart']);
    $placeholders = str_repeat('?,', count($product_ids) - 1) . '?';
    
    $stmt = $pdo->prepare("SELECT * FROM products WHERE id IN ($placeholders)");
    $stmt->execute($product_ids);
    $products_raw = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Index products by ID for easy lookup
    $products = [];
    foreach ($products_raw as $product) {
        $products[$product['id']] = $product;
    }
    
    foreach ($_SESSION['cart'] as $product_id => $item) {
        if (isset($products[$product_id])) {
            $product = $products[$product_id];
            $subtotal = $product['price'] * $item['quantity'];
            $total += $subtotal;
            
            $cart_items[] = [
                'product' => $product,
                'quantity' => $item['quantity'],
                'subtotal' => $subtotal
            ];
        }
    }
}
?>

<div class="container" style="margin-top: 2rem;">
    <h1 class="section-title">🛒 Keranjang Belanja</h1>

    <?php if (empty($cart_items)): ?>
        <div class="card" style="text-align: center; padding: 4rem 2rem;">
            <div style="font-size: 5rem; margin-bottom: 1rem;">🛒</div>
            <h2 style="margin-bottom: 1rem;">Keranjang Anda Kosong</h2>
            <p style="color: var(--gray); margin-bottom: 2rem;">
                Belum ada produk di keranjang. Yuk mulai belanja!
            </p>
            <a href="products.php" class="btn btn-primary">
                Lihat Produk
            </a>
        </div>
    <?php else: ?>
        <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 2rem;">
            <!-- Cart Items -->
            <div>
                <?php foreach ($cart_items as $item): ?>
                    <div class="cart-item">
                        <div class="cart-item-image">
                            <?php if ($item['product']['image']): ?>
                                <img src="uploads/<?php echo htmlspecialchars($item['product']['image']); ?>" 
                                     alt="<?php echo htmlspecialchars($item['product']['name']); ?>"
                                     style="width: 100%; height: 100%; object-fit: cover; border-radius: 8px;">
                            <?php else: ?>
                                <div style="width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; font-size: 3rem; background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%); border-radius: 8px;">
                                    📦
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="cart-item-info">
                            <h3 class="cart-item-name">
                                <?php echo htmlspecialchars($item['product']['name']); ?>
                            </h3>
                            <div class="cart-item-price">
                                <?php echo formatRupiah($item['product']['price']); ?>
                            </div>
                            <div class="quantity-control">
                                <form method="POST" style="display: inline;">
                                    <?php echo getCsrfInput(); ?>
                                    <input type="hidden" name="product_id" value="<?php echo $item['product']['id']; ?>">
                                    <input type="hidden" name="quantity" value="<?php echo $item['quantity'] - 1; ?>">
                                    <button type="submit" name="update_cart" class="btn btn-sm btn-outline">-</button>
                                </form>
                                
                                <input type="number" value="<?php echo $item['quantity']; ?>" 
                                       min="1" max="<?php echo $item['product']['stock']; ?>" readonly>
                                
                                <form method="POST" style="display: inline;">
                                    <?php echo getCsrfInput(); ?>
                                    <input type="hidden" name="product_id" value="<?php echo $item['product']['id']; ?>">
                                    <input type="hidden" name="quantity" value="<?php echo $item['quantity'] + 1; ?>">
                                    <button type="submit" name="update_cart" class="btn btn-sm btn-outline" 
                                            <?php echo $item['quantity'] >= $item['product']['stock'] ? 'disabled' : ''; ?>>+</button>
                                </form>
                            </div>
                            <div style="margin-top: 0.5rem; font-weight: 600;">
                                Subtotal: <?php echo formatRupiah($item['subtotal']); ?>
                            </div>
                        </div>
                        <div>
                            <form method="POST">
                                <?php echo getCsrfInput(); ?>
                                <input type="hidden" name="product_id" value="<?php echo $item['product']['id']; ?>">
                                <button type="submit" name="remove_item" class="btn btn-danger btn-sm" 
                                        onclick="return confirmDelete('Hapus produk dari keranjang?')">
                                    🗑️ Hapus
                                </button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Order Summary -->
            <div>
                <div class="card" style="position: sticky; top: 100px;">
                    <h3 class="card-title">Ringkasan Pesanan</h3>
                    <div style="margin-bottom: 1rem; padding-bottom: 1rem; border-bottom: 2px solid var(--border);">
                        <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem;">
                            <span>Subtotal</span>
                            <strong><?php echo formatRupiah($total); ?></strong>
                        </div>
                        <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem; color: var(--gray); font-size: 0.9rem;">
                            <span>Ongkir</span>
                            <span>Gratis</span>
                        </div>
                    </div>
                    <div style="display: flex; justify-content: space-between; margin-bottom: 2rem; font-size: 1.5rem; font-weight: 700;">
                        <span>Total</span>
                        <span style="color: var(--primary);"><?php echo formatRupiah($total); ?></span>
                    </div>
                    <a href="checkout.php" class="btn btn-primary btn-block" style="padding: 1rem;">
                        Lanjut ke Checkout
                    </a>
                    <a href="products.php" class="btn btn-outline btn-block" style="margin-top: 0.5rem;">
                        Lanjut Belanja
                    </a>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>
