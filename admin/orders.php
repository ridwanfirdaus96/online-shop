<?php
$page_title = 'Kelola Pesanan';
include 'includes/header.php';

// Handle status update
if (isset($_POST['update_status']) && checkCsrf()) {
    $order_id = (int) $_POST['order_id'];
    $status = $_POST['status'];

    // Debug logging
    error_log("=== ORDER STATUS UPDATE DEBUG ===");
    error_log("Order ID: " . $order_id);
    error_log("New Status: " . $status);

    $stmt = $pdo->prepare("UPDATE orders SET status = ? WHERE id = ?");
    $result = $stmt->execute([$status, $order_id]);

    error_log("Execute Result: " . ($result ? 'TRUE' : 'FALSE'));
    error_log("Rows Affected: " . $stmt->rowCount());

    if ($result && $stmt->rowCount() > 0) {
        setFlash('success', 'Status pesanan berhasil diupdate!');
    } else {
        setFlash('danger', 'Gagal mengupdate status!');
    }
    redirect('orders.php');
}

// Get all orders
$orders = $pdo->query("
    SELECT o.*, pa.name as payment_name, pa.type as payment_type 
    FROM orders o
    LEFT JOIN payment_accounts pa ON o.payment_method_id = pa.id
    ORDER BY o.created_at DESC
")->fetchAll();
?>

<div class="container" style="margin-top: 2rem;">
    <h1 class="section-title">📋 Kelola Pesanan</h1>

    <div class="card">
        <?php if (empty($orders)): ?>
            <p style="text-align: center; color: var(--gray); padding: 3rem;">Belum ada pesanan</p>
        <?php else: ?>
            <table class="table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Pelanggan</th>
                        <th>Email</th>
                        <th>Total</th>
                        <th>Payment</th>
                        <th>Status</th>
                        <th>Tanggal</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($orders as $order): ?>
                        <tr>
                            <td>#<?php echo $order['id']; ?></td>
                            <td><?php echo htmlspecialchars($order['customer_name']); ?></td>
                            <td><?php echo htmlspecialchars($order['customer_email']); ?></td>
                            <td><?php echo formatRupiah($order['total']); ?></td>
                            <td>
                                <?php if ($order['payment_name']): ?>
                                    <?php
                                    $type_icons = [
                                        'bank_transfer' => '🏦',
                                        'e_wallet' => '📱',
                                        'cod' => '💵'
                                    ];
                                    echo $type_icons[$order['payment_type']] ?? '💳';
                                    ?>
                                    <span style="font-size: 0.875rem;">
                                        <?php echo htmlspecialchars($order['payment_name']); ?>
                                    </span>
                                <?php else: ?>
                                    <span style="color: var(--gray); font-size: 0.875rem;">-</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php
                                // Status badge styling
                                $status_colors = [
                                    'pending' => ['bg' => '#FEF3C7', 'text' => '#92400E', 'label' => '🟡 Pending'],
                                    'processing' => ['bg' => '#DBEAFE', 'text' => '#1E40AF', 'label' => '🔵 Processing'],
                                    'completed' => ['bg' => '#D1FAE5', 'text' => '#065F46', 'label' => '🟢 Completed'],
                                    'cancelled' => ['bg' => '#FEE2E2', 'text' => '#991B1B', 'label' => '🔴 Cancelled']
                                ];
                                $current_status = $order['status'];
                                $status_style = $status_colors[$current_status] ?? $status_colors['pending'];
                                ?>
                                <div style="display: flex; flex-direction: column; gap: 0.5rem;">
                                    <!-- Change Status Form -->
                                    <form method="POST" style="display: inline;">
                                        <?php echo getCsrfInput(); ?>
                                        <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                        <input type="hidden" name="update_status" value="1">
                                        <select name="status"
                                            style="padding: 0.375rem 0.5rem; border: 1px solid var(--border); border-radius: 6px; font-size: 0.875rem; cursor: pointer; width: 100%;"
                                            onchange="if(confirm('Ubah status pesanan #<?php echo $order['id']; ?>?')) this.form.submit();">
                                            <option value="pending" <?php echo $order['status'] === 'pending' ? 'selected' : ''; ?>>🟡 Pending</option>
                                            <option value="processing" <?php echo $order['status'] === 'processing' ? 'selected' : ''; ?>>🔵 Processing</option>
                                            <option value="completed" <?php echo $order['status'] === 'completed' ? 'selected' : ''; ?>>🟢 Completed</option>
                                            <option value="cancelled" <?php echo $order['status'] === 'cancelled' ? 'selected' : ''; ?>>🔴 Cancelled</option>
                                        </select>
                                    </form>
                                </div>
                            </td>
                            <td><?php echo date('d/m/Y H:i', strtotime($order['created_at'])); ?></td>
                            <td>
                                <button
                                    onclick="alert('Alamat: <?php echo addslashes($order['address']); ?>\nTelepon: <?php echo $order['customer_phone']; ?>')"
                                    class="btn btn-sm btn-primary">Detail</button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>

<?php include 'includes/footer.php'; ?>