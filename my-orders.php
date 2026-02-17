<?php
$page_title = 'My Orders';
include 'includes/header.php';

// Check if user is logged in
if (!isLoggedIn()) {
    setFlash('warning', 'Silakan login terlebih dahulu untuk melihat pesanan Anda.');
    redirect('login.php');
}

// Get user's orders
$user_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC");
$stmt->execute([$user_id]);
$orders = $stmt->fetchAll();

// Get order items for each order
$order_items = [];
if (!empty($orders)) {
    $order_ids = array_column($orders, 'id');
    $placeholders = str_repeat('?,', count($order_ids) - 1) . '?';

    $stmt = $pdo->prepare("SELECT * FROM order_items WHERE order_id IN ($placeholders) ORDER BY order_id, id");
    $stmt->execute($order_ids);
    $items = $stmt->fetchAll();

    // Group items by order_id
    foreach ($items as $item) {
        $order_items[$item['order_id']][] = $item;
    }
}

// Status configuration
$status_config = [
    'pending' => ['bg' => '#FEF3C7', 'text' => '#92400E', 'label' => '🟡 Pending', 'desc' => 'Pesanan sedang diproses'],
    'processing' => ['bg' => '#DBEAFE', 'text' => '#1E40AF', 'label' => '🔵 Processing', 'desc' => 'Pesanan sedang disiapkan'],
    'completed' => ['bg' => '#D1FAE5', 'text' => '#065F46', 'label' => '🟢 Completed', 'desc' => 'Pesanan selesai'],
    'cancelled' => ['bg' => '#FEE2E2', 'text' => '#991B1B', 'label' => '🔴 Cancelled', 'desc' => 'Pesanan dibatalkan']
];
?>

<style>
    .order-card {
        background: white;
        border: 1px solid var(--border);
        border-radius: 12px;
        padding: 1.5rem;
        margin-bottom: 1.5rem;
        transition: all 0.3s ease;
    }

    .order-card:hover {
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        transform: translateY(-2px);
    }

    .order-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1rem;
        padding-bottom: 1rem;
        border-bottom: 2px solid var(--border);
    }

    .order-id {
        font-size: 1.25rem;
        font-weight: 700;
        color: var(--primary);
    }

    .order-date {
        color: var(--gray);
        font-size: 0.875rem;
    }

    .status-badge {
        padding: 0.5rem 1rem;
        border-radius: 8px;
        font-weight: 600;
        font-size: 0.875rem;
        display: inline-block;
    }

    .order-summary {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1rem;
        margin-bottom: 1rem;
    }

    .summary-item {
        display: flex;
        flex-direction: column;
    }

    .summary-label {
        color: var(--gray);
        font-size: 0.875rem;
        margin-bottom: 0.25rem;
    }

    .summary-value {
        font-weight: 600;
        font-size: 1.125rem;
    }

    .order-items {
        margin-top: 1rem;
    }

    .order-items-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 0.75rem;
        background: var(--light);
        border-radius: 8px;
        cursor: pointer;
        user-select: none;
        transition: background 0.2s;
    }

    .order-items-header:hover {
        background: #e5e7eb;
    }

    .order-items-content {
        display: none;
        margin-top: 1rem;
        padding: 1rem;
        background: #f9fafb;
        border-radius: 8px;
    }

    .order-items-content.active {
        display: block;
    }

    .item-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 0.75rem 0;
        border-bottom: 1px solid var(--border);
    }

    .item-row:last-child {
        border-bottom: none;
    }

    .item-info {
        flex: 1;
    }

    .item-name {
        font-weight: 600;
        margin-bottom: 0.25rem;
    }

    .item-details {
        color: var(--gray);
        font-size: 0.875rem;
    }

    .item-subtotal {
        font-weight: 600;
        color: var(--primary);
    }

    .status-timeline {
        display: flex;
        justify-content: space-between;
        margin: 1.5rem 0;
        position: relative;
    }

    .status-timeline::before {
        content: '';
        position: absolute;
        top: 15px;
        left: 0;
        right: 0;
        height: 2px;
        background: var(--border);
        z-index: 0;
    }

    .timeline-step {
        display: flex;
        flex-direction: column;
        align-items: center;
        position: relative;
        z-index: 1;
        flex: 1;
    }

    .timeline-dot {
        width: 30px;
        height: 30px;
        border-radius: 50%;
        background: white;
        border: 3px solid var(--border);
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 0.5rem;
    }

    .timeline-dot.active {
        border-color: var(--primary);
        background: var(--primary);
        color: white;
    }

    .timeline-label {
        font-size: 0.75rem;
        text-align: center;
        color: var(--gray);
    }

    .timeline-label.active {
        color: var(--primary);
        font-weight: 600;
    }

    @media (max-width: 768px) {
        .order-header {
            flex-direction: column;
            align-items: flex-start;
            gap: 1rem;
        }

        .order-summary {
            grid-template-columns: 1fr;
        }

        .status-timeline {
            flex-direction: column;
            gap: 1rem;
        }

        .status-timeline::before {
            display: none;
        }

        .timeline-step {
            flex-direction: row;
            gap: 1rem;
        }
    }
</style>

<div class="container" style="margin-top: 2rem;">
    <h1 class="section-title">📦 My Orders</h1>

    <?php if (empty($orders)): ?>
        <!-- Empty State -->
        <div class="card" style="text-align: center; padding: 4rem 2rem;">
            <div style="font-size: 5rem; margin-bottom: 1rem;">📦</div>
            <h2 style="margin-bottom: 1rem;">Belum Ada Pesanan</h2>
            <p style="color: var(--gray); margin-bottom: 2rem;">
                Anda belum pernah melakukan pemesanan. Yuk mulai belanja sekarang!
            </p>
            <a href="products.php" class="btn btn-primary">
                Lihat Produk
            </a>
        </div>
    <?php else: ?>
        <!-- Order List -->
        <?php foreach ($orders as $order):
            $status = $order['status'];
            $status_style = $status_config[$status] ?? $status_config['pending'];
            $items = $order_items[$order['id']] ?? [];
            ?>
            <div class="order-card">
                <!-- Order Header -->
                <div class="order-header">
                    <div>
                        <div class="order-id">#<?php echo $order['id']; ?></div>
                        <div class="order-date">
                            <?php echo date('d F Y, H:i', strtotime($order['created_at'])); ?>
                        </div>
                    </div>
                    <div>
                        <span class="status-badge" style="
                            background: <?php echo $status_style['bg']; ?>;
                            color: <?php echo $status_style['text']; ?>;">
                            <?php echo $status_style['label']; ?>
                        </span>
                    </div>
                </div>

                <!-- Status Timeline -->
                <div class="status-timeline">
                    <?php
                    $timeline_steps = ['pending', 'processing', 'completed'];
                    $current_index = array_search($status, $timeline_steps);
                    if ($status === 'cancelled')
                        $current_index = -1;

                    foreach ($timeline_steps as $index => $step):
                        $is_active = $index <= $current_index;
                        $step_config = $status_config[$step];
                        ?>
                        <div class="timeline-step">
                            <div class="timeline-dot <?php echo $is_active ? 'active' : ''; ?>">
                                <?php if ($is_active): ?>✓<?php endif; ?>
                            </div>
                            <div class="timeline-label <?php echo $is_active ? 'active' : ''; ?>">
                                <?php echo $step_config['desc']; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- Order Summary -->
                <div class="order-summary">
                    <div class="summary-item">
                        <div class="summary-label">Total Pembayaran</div>
                        <div class="summary-value"><?php echo formatRupiah($order['total']); ?></div>
                    </div>
                    <div class="summary-item">
                        <div class="summary-label">Jumlah Item</div>
                        <div class="summary-value"><?php echo count($items); ?> item</div>
                    </div>
                    <div class="summary-item">
                        <div class="summary-label">Penerima</div>
                        <div class="summary-value" style="font-size: 1rem;">
                            <?php echo htmlspecialchars($order['customer_name']); ?></div>
                    </div>
                </div>

                <!-- Order Items (Collapsible) -->
                <div class="order-items">
                    <div class="order-items-header" onclick="toggleOrderItems(<?php echo $order['id']; ?>)">
                        <strong>📋 Detail Pesanan (<?php echo count($items); ?> item)</strong>
                        <span id="toggle-icon-<?php echo $order['id']; ?>">▼</span>
                    </div>
                    <div class="order-items-content" id="items-<?php echo $order['id']; ?>">
                        <?php foreach ($items as $item): ?>
                            <div class="item-row">
                                <div class="item-info">
                                    <div class="item-name"><?php echo htmlspecialchars($item['product_name']); ?></div>
                                    <div class="item-details">
                                        <?php echo $item['quantity']; ?> x <?php echo formatRupiah($item['price']); ?>
                                    </div>
                                </div>
                                <div class="item-subtotal">
                                    <?php echo formatRupiah($item['subtotal']); ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Shipping Info -->
                <div style="margin-top: 1rem; padding-top: 1rem; border-top: 1px solid var(--border);">
                    <div
                        style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1rem; font-size: 0.875rem;">
                        <div>
                            <strong>📍 Alamat Pengiriman:</strong><br>
                            <span style="color: var(--gray);"><?php echo nl2br(htmlspecialchars($order['address'])); ?></span>
                        </div>
                        <div>
                            <strong>📞 Telepon:</strong><br>
                            <span style="color: var(--gray);"><?php echo htmlspecialchars($order['customer_phone']); ?></span>
                        </div>
                        <div>
                            <strong>📧 Email:</strong><br>
                            <span style="color: var(--gray);"><?php echo htmlspecialchars($order['customer_email']); ?></span>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<script>
    function toggleOrderItems(orderId) {
        const content = document.getElementById('items-' + orderId);
        const icon = document.getElementById('toggle-icon-' + orderId);

        if (content.classList.contains('active')) {
            content.classList.remove('active');
            icon.textContent = '▼';
        } else {
            content.classList.add('active');
            icon.textContent = '▲';
        }
    }
</script>

<?php include 'includes/footer.php'; ?>