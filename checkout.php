<?php
$page_title = 'Checkout';
include 'includes/header.php';

// Check if cart is empty
if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
    setFlash('warning', 'Keranjang Anda kosong!');
    redirect('products.php');
}

// Get cart items
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

$cart_items = [];
$total = 0;

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

// Handle checkout
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['place_order']) && checkCsrf()) {
    $name = sanitize($_POST['name']);
    $email = sanitize($_POST['email']);
    $phone = sanitize($_POST['phone']);
    $address = sanitize($_POST['address']);
    $payment_method_id = isset($_POST['payment_method_id']) ? (int) $_POST['payment_method_id'] : null;

    // Validation
    $errors = [];
    if (empty($name))
        $errors[] = 'Nama harus diisi';
    if (empty($email))
        $errors[] = 'Email harus diisi';
    if (empty($phone))
        $errors[] = 'Nomor telepon harus diisi';
    if (empty($address))
        $errors[] = 'Alamat harus diisi';
    if (empty($payment_method_id))
        $errors[] = 'Metode pembayaran harus dipilih';

    if (empty($errors)) {
        // Validate stock availability
        foreach ($cart_items as $item) {
            // Get current stock from database
            $stock_stmt = $pdo->prepare("SELECT stock FROM products WHERE id = ?");
            $stock_stmt->execute([$item['product']['id']]);
            $current_stock = $stock_stmt->fetchColumn();

            if ($current_stock === false) {
                $errors[] = 'Produk "' . $item['product']['name'] . '" tidak ditemukan';
            } elseif ($item['quantity'] > $current_stock) {
                $errors[] = 'Stok "' . $item['product']['name'] . '" tidak mencukupi (tersedia: ' . $current_stock . ', diminta: ' . $item['quantity'] . ')';
            }
        }
    }

    if (empty($errors)) {
        try {
            $pdo->beginTransaction();

            // Create order
            $stmt = $pdo->prepare("INSERT INTO orders (user_id, total, customer_name, customer_email, customer_phone, address, payment_method_id) 
                                   VALUES (?, ?, ?, ?, ?, ?, ?)");
            $user_id = isLoggedIn() ? $_SESSION['user_id'] : null;
            $stmt->execute([$user_id, $total, $name, $email, $phone, $address, $payment_method_id]);
            $order_id = $pdo->lastInsertId();

            // Create order items
            $stmt = $pdo->prepare("INSERT INTO order_items (order_id, product_id, product_name, price, quantity, subtotal) 
                                   VALUES (?, ?, ?, ?, ?, ?)");

            foreach ($cart_items as $item) {
                $stmt->execute([
                    $order_id,
                    $item['product']['id'],
                    $item['product']['name'],
                    $item['product']['price'],
                    $item['quantity'],
                    $item['subtotal']
                ]);

                // Update stock
                $update_stmt = $pdo->prepare("UPDATE products SET stock = stock - ? WHERE id = ? AND stock >= ?");
                $result = $update_stmt->execute([$item['quantity'], $item['product']['id'], $item['quantity']]);

                // Check if stock update was successful
                if ($update_stmt->rowCount() === 0) {
                    throw new Exception('Stok "' . $item['product']['name'] . '" tidak mencukupi');
                }
            }

            $pdo->commit();

            // Clear cart
            clearCart();

            setFlash('success', 'Pesanan berhasil dibuat! Nomor pesanan: #' . $order_id);
            redirect('index.php');

        } catch (Exception $e) {
            $pdo->rollBack();
            $errors[] = 'Terjadi kesalahan: ' . $e->getMessage();
        }
    }
}
?>

<div class="container" style="margin-top: 2rem;">
    <h1 class="section-title">💳 Checkout</h1>

    <?php if (isset($errors) && !empty($errors)): ?>
        <div class="alert alert-danger">
            <ul style="margin: 0; padding-left: 1.5rem;">
                <?php foreach ($errors as $error): ?>
                    <li><?php echo $error; ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <div style="display: grid; grid-template-columns: 1.5fr 1fr; gap: 2rem;">
        <!-- Checkout Form -->
        <div>
            <div class="card">
                <h3 class="card-title">Informasi Pengiriman</h3>
                <form method="POST">
                    <?php echo getCsrfInput(); ?>
                    <div class="form-group">
                        <label class="form-label">Nama Lengkap *</label>
                        <input type="text" name="name" class="form-control" required
                            value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>">
                    </div>

                    <div class="form-group">
                        <label class="form-label">Email *</label>
                        <input type="email" name="email" class="form-control" required
                            value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                    </div>

                    <div class="form-group">
                        <label class="form-label">Nomor Telepon *</label>
                        <input type="tel" name="phone" class="form-control" required
                            value="<?php echo isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : ''; ?>">
                    </div>

                    <div class="form-group">
                        <label class="form-label">Alamat Lengkap *</label>
                        <textarea name="address" class="form-control" required
                            rows="4"><?php echo isset($_POST['address']) ? htmlspecialchars($_POST['address']) : ''; ?></textarea>
                    </div>

                    <?php
                    // Get active payment accounts
                    $payment_accounts = $pdo->query("SELECT * FROM payment_accounts WHERE is_active = 1 ORDER BY type, name")->fetchAll();
                    ?>

                    <div class="form-group">
                        <label class="form-label">Metode Pembayaran *</label>
                        <?php if (empty($payment_accounts)): ?>
                            <p style="color: var(--danger); padding: 1rem; background: #FEE2E2; border-radius: 8px;">
                                Belum ada metode pembayaran yang tersedia. Silakan hubungi admin.
                            </p>
                        <?php else: ?>
                            <div style="display: flex; flex-direction: column; gap: 1rem;">
                                <?php foreach ($payment_accounts as $account): ?>
                                    <label
                                        style="display: block; padding: 1rem; border: 2px solid var(--border); border-radius: 8px; cursor: pointer; transition: all 0.2s;"
                                        onmouseover="this.style.borderColor='var(--primary)'; this.style.backgroundColor='#F0F9FF';"
                                        onmouseout="if(!this.querySelector('input').checked) { this.style.borderColor='var(--border)'; this.style.backgroundColor='transparent'; }">
                                        <div style="display: flex; align-items: start; gap: 1rem;">
                                            <input type="radio" name="payment_method_id" value="<?php echo $account['id']; ?>"
                                                required <?php echo (isset($_POST['payment_method_id']) && $_POST['payment_method_id'] == $account['id']) ? 'checked' : ''; ?>
                                                style="margin-top: 0.25rem;"
                                                onchange="document.querySelectorAll('label').forEach(l => { l.style.borderColor='var(--border)'; l.style.backgroundColor='transparent'; }); this.parentElement.parentElement.style.borderColor='var(--primary)'; this.parentElement.parentElement.style.backgroundColor='#F0F9FF';">
                                            <div style="flex: 1;">
                                                <div style="font-weight: 600; margin-bottom: 0.5rem;">
                                                    <?php
                                                    $type_labels = [
                                                        'bank_transfer' => '🏦',
                                                        'e_wallet' => '📱',
                                                        'cod' => '💵'
                                                    ];
                                                    echo $type_labels[$account['type']] ?? '';
                                                    ?>
                                                    <?php echo htmlspecialchars($account['name']); ?>
                                                </div>
                                                <?php if ($account['account_number']): ?>
                                                    <div style="color: var(--gray); font-size: 0.875rem; margin-bottom: 0.25rem;">
                                                        <strong>No. Rekening:</strong>
                                                        <?php echo htmlspecialchars($account['account_number']); ?>
                                                    </div>
                                                <?php endif; ?>
                                                <?php if ($account['account_holder']): ?>
                                                    <div style="color: var(--gray); font-size: 0.875rem; margin-bottom: 0.5rem;">
                                                        <strong>Atas Nama:</strong>
                                                        <?php echo htmlspecialchars($account['account_holder']); ?>
                                                    </div>
                                                <?php endif; ?>
                                                <?php if ($account['instructions']): ?>
                                                    <div
                                                        style="color: var(--gray); font-size: 0.875rem; white-space: pre-line; padding-top: 0.5rem; border-top: 1px solid var(--border);">
                                                        <?php echo nl2br(htmlspecialchars($account['instructions'])); ?>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </label>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <button type="submit" name="place_order" class="btn btn-primary btn-block"
                        style="padding: 1rem; font-size: 1.1rem;">
                        🛍️ Buat Pesanan
                    </button>
                </form>
            </div>
        </div>

        <!-- Order Summary -->
        <div>
            <div class="card" style="position: sticky; top: 100px;">
                <h3 class="card-title">Ringkasan Pesanan</h3>

                <div style="margin-bottom: 1.5rem;">
                    <?php foreach ($cart_items as $item): ?>
                        <div
                            style="display: flex; justify-content: space-between; margin-bottom: 1rem; padding-bottom: 1rem; border-bottom: 1px solid var(--border);">
                            <div>
                                <div style="font-weight: 600; margin-bottom: 0.25rem;">
                                    <?php echo htmlspecialchars($item['product']['name']); ?>
                                </div>
                                <div style="color: var(--gray); font-size: 0.875rem;">
                                    <?php echo $item['quantity']; ?> x
                                    <?php echo formatRupiah($item['product']['price']); ?>
                                </div>
                            </div>
                            <div style="font-weight: 600;">
                                <?php echo formatRupiah($item['subtotal']); ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div style="margin-bottom: 1rem; padding-bottom: 1rem; border-bottom: 2px solid var(--border);">
                    <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem;">
                        <span>Subtotal</span>
                        <strong><?php echo formatRupiah($total); ?></strong>
                    </div>
                    <div style="display: flex; justify-content: space-between; color: var(--gray); font-size: 0.9rem;">
                        <span>Ongkir</span>
                        <span>Gratis</span>
                    </div>
                </div>

                <div style="display: flex; justify-content: space-between; font-size: 1.5rem; font-weight: 700;">
                    <span>Total</span>
                    <span style="color: var(--primary);"><?php echo formatRupiah($total); ?></span>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>