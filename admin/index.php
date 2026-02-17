<?php
$page_title = 'Admin Dashboard';
include 'includes/header.php';

// Get statistics
$total_products = $pdo->query("SELECT COUNT(*) FROM products")->fetchColumn();
$total_orders = $pdo->query("SELECT COUNT(*) FROM orders")->fetchColumn();
$total_revenue = $pdo->query("SELECT SUM(total) FROM orders WHERE status != 'cancelled'")->fetchColumn();
$pending_orders = $pdo->query("SELECT COUNT(*) FROM orders WHERE status = 'pending'")->fetchColumn();

// Get recent orders
$recent_orders = $pdo->query("SELECT * FROM orders ORDER BY created_at DESC LIMIT 5")->fetchAll();
?>

<div class="container" style="margin-top: 2rem;">
    <h1 class="section-title">📊 Dashboard Admin</h1>

    <!-- Statistics Cards -->
    <div
        style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1.5rem; margin-bottom: 3rem;">
        <div class="card" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
            <div style="font-size: 0.875rem; opacity: 0.9; margin-bottom: 0.5rem;">Total Produk</div>
            <div style="font-size: 2.5rem; font-weight: 700;"><?php echo $total_products; ?></div>
        </div>

        <div class="card" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); color: white;">
            <div style="font-size: 0.875rem; opacity: 0.9; margin-bottom: 0.5rem;">Total Pesanan</div>
            <div style="font-size: 2.5rem; font-weight: 700;"><?php echo $total_orders; ?></div>
        </div>

        <div class="card" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); color: white;">
            <div style="font-size: 0.875rem; opacity: 0.9; margin-bottom: 0.5rem;">Pesanan Pending</div>
            <div style="font-size: 2.5rem; font-weight: 700;"><?php echo $pending_orders; ?></div>
        </div>

        <div class="card" style="background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%); color: white;">
            <div style="font-size: 0.875rem; opacity: 0.9; margin-bottom: 0.5rem;">Total Revenue</div>
            <div style="font-size: 1.5rem; font-weight: 700;"><?php echo formatRupiah($total_revenue ?? 0); ?></div>
        </div>
    </div>

    <!-- Recent Orders -->
    <div class="card">
        <h3 class="card-title">Pesanan Terbaru</h3>
        <?php if (empty($recent_orders)): ?>
            <p style="color: var(--gray); text-align: center; padding: 2rem;">Belum ada pesanan</p>
        <?php else: ?>
            <table class="table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Pelanggan</th>
                        <th>Total</th>
                        <th>Status</th>
                        <th>Tanggal</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recent_orders as $order): ?>
                        <tr>
                            <td>#<?php echo $order['id']; ?></td>
                            <td><?php echo htmlspecialchars($order['customer_name']); ?></td>
                            <td><?php echo formatRupiah($order['total']); ?></td>
                            <td>
                                <span style="padding: 0.25rem 0.75rem; border-radius: 20px; font-size: 0.875rem; font-weight: 600;
                                             background: <?php
                                             echo $order['status'] === 'completed' ? '#d1fae5' :
                                                 ($order['status'] === 'pending' ? '#fef3c7' : '#fee2e2');
                                             ?>;
                                             color: <?php
                                             echo $order['status'] === 'completed' ? '#065f46' :
                                                 ($order['status'] === 'pending' ? '#92400e' : '#991b1b');
                                             ?>;">
                                    <?php echo ucfirst($order['status']); ?>
                                </span>
                            </td>
                            <td><?php echo date('d/m/Y H:i', strtotime($order['created_at'])); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <div style="text-align: center; margin-top: 1rem;">
                <a href="orders.php" class="btn btn-primary">Lihat Semua Pesanan</a>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include 'includes/footer.php'; ?>