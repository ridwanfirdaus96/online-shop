<?php
$page_title = 'Kelola Rekening Payment';
include 'includes/header.php';

// Handle delete
if (isset($_GET['delete']) && checkCsrf()) {
    $id = (int) $_GET['delete'];
    $stmt = $pdo->prepare("DELETE FROM payment_accounts WHERE id = ?");
    if ($stmt->execute([$id])) {
        setFlash('success', 'Rekening payment berhasil dihapus!');
    } else {
        setFlash('danger', 'Gagal menghapus rekening payment!');
    }
    redirect('payment-accounts.php');
}

// Handle toggle active status
if (isset($_GET['toggle']) && checkCsrf()) {
    $id = (int) $_GET['toggle'];
    $stmt = $pdo->prepare("UPDATE payment_accounts SET is_active = NOT is_active WHERE id = ?");
    if ($stmt->execute([$id])) {
        setFlash('success', 'Status rekening payment berhasil diubah!');
    } else {
        setFlash('danger', 'Gagal mengubah status!');
    }
    redirect('payment-accounts.php');
}

// Get all payment accounts
$accounts = $pdo->query("SELECT * FROM payment_accounts ORDER BY type, name")->fetchAll();
?>

<div class="container" style="margin-top: 2rem;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
        <h1 class="section-title">💳 Kelola Rekening Payment</h1>
        <a href="add-payment-account.php" class="btn btn-primary">
            ➕ Tambah Rekening
        </a>
    </div>

    <div class="card">
        <?php if (empty($accounts)): ?>
            <p style="text-align: center; color: var(--gray); padding: 3rem;">Belum ada rekening payment</p>
        <?php else: ?>
            <table class="table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Tipe</th>
                        <th>Nama</th>
                        <th>Nomor Rekening</th>
                        <th>Pemegang</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($accounts as $account): ?>
                        <tr>
                            <td>#<?php echo $account['id']; ?></td>
                            <td>
                                <?php
                                $type_labels = [
                                    'bank_transfer' => '🏦 Bank Transfer',
                                    'e_wallet' => '📱 E-Wallet',
                                    'cod' => '💵 COD'
                                ];
                                echo $type_labels[$account['type']] ?? $account['type'];
                                ?>
                            </td>
                            <td><strong><?php echo htmlspecialchars($account['name']); ?></strong></td>
                            <td><?php echo htmlspecialchars($account['account_number'] ?? '-'); ?></td>
                            <td><?php echo htmlspecialchars($account['account_holder'] ?? '-'); ?></td>
                            <td>
                                <?php if ($account['is_active']): ?>
                                    <span
                                        style="display: inline-block; padding: 0.25rem 0.75rem; background: #D1FAE5; color: #065F46; border-radius: 6px; font-size: 0.875rem; font-weight: 600;">
                                        ✓ Aktif
                                    </span>
                                <?php else: ?>
                                    <span
                                        style="display: inline-block; padding: 0.25rem 0.75rem; background: #FEE2E2; color: #991B1B; border-radius: 6px; font-size: 0.875rem; font-weight: 600;">
                                        ✗ Nonaktif
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div style="display: flex; gap: 0.5rem;">
                                    <a href="edit-payment-account.php?id=<?php echo $account['id']; ?>"
                                        class="btn btn-sm btn-primary">Edit</a>
                                    <a href="?toggle=<?php echo $account['id']; ?>&csrf_token=<?php echo $_SESSION['csrf_token']; ?>"
                                        class="btn btn-sm btn-warning" onclick="return confirm('Ubah status rekening ini?')">
                                        <?php echo $account['is_active'] ? 'Nonaktifkan' : 'Aktifkan'; ?>
                                    </a>
                                    <a href="?delete=<?php echo $account['id']; ?>&csrf_token=<?php echo $_SESSION['csrf_token']; ?>"
                                        class="btn btn-sm btn-danger"
                                        onclick="return confirm('Yakin ingin menghapus rekening ini?')">Hapus</a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>

<?php include 'includes/footer.php'; ?>