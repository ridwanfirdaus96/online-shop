<?php
$page_title = 'Tambah Rekening Payment';
include 'includes/header.php';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_account']) && checkCsrf()) {
    $type = sanitize($_POST['type']);
    $name = sanitize($_POST['name']);
    $account_number = sanitize($_POST['account_number']);
    $account_holder = sanitize($_POST['account_holder']);
    $instructions = sanitize($_POST['instructions']);
    $is_active = isset($_POST['is_active']) ? 1 : 0;

    // Validation
    $errors = [];
    if (empty($type))
        $errors[] = 'Tipe harus dipilih';
    if (empty($name))
        $errors[] = 'Nama harus diisi';

    if (empty($errors)) {
        $stmt = $pdo->prepare("INSERT INTO payment_accounts (type, name, account_number, account_holder, instructions, is_active) 
                               VALUES (?, ?, ?, ?, ?, ?)");

        if ($stmt->execute([$type, $name, $account_number, $account_holder, $instructions, $is_active])) {
            setFlash('success', 'Rekening payment berhasil ditambahkan!');
            redirect('payment-accounts.php');
        } else {
            $errors[] = 'Gagal menambahkan rekening payment!';
        }
    }
}
?>

<div class="container" style="margin-top: 2rem;">
    <div style="margin-bottom: 2rem;">
        <a href="payment-accounts.php" class="btn btn-secondary">← Kembali</a>
    </div>

    <h1 class="section-title">➕ Tambah Rekening Payment</h1>

    <?php if (isset($errors) && !empty($errors)): ?>
        <div class="alert alert-danger">
            <ul style="margin: 0; padding-left: 1.5rem;">
                <?php foreach ($errors as $error): ?>
                    <li><?php echo $error; ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <div class="card">
        <form method="POST">
            <?php echo getCsrfInput(); ?>

            <div class="form-group">
                <label class="form-label">Tipe Payment *</label>
                <select name="type" class="form-control" required>
                    <option value="">-- Pilih Tipe --</option>
                    <option value="bank_transfer" <?php echo (isset($_POST['type']) && $_POST['type'] === 'bank_transfer') ? 'selected' : ''; ?>>
                        🏦 Bank Transfer
                    </option>
                    <option value="e_wallet" <?php echo (isset($_POST['type']) && $_POST['type'] === 'e_wallet') ? 'selected' : ''; ?>>
                        📱 E-Wallet
                    </option>
                    <option value="cod" <?php echo (isset($_POST['type']) && $_POST['type'] === 'cod') ? 'selected' : ''; ?>>
                        💵 Cash on Delivery (COD)
                    </option>
                </select>
            </div>

            <div class="form-group">
                <label class="form-label">Nama *</label>
                <input type="text" name="name" class="form-control" required
                    placeholder="Contoh: BCA, Mandiri, GoPay, OVO"
                    value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>">
                <small style="color: var(--gray);">Nama rekening atau e-wallet</small>
            </div>

            <div class="form-group">
                <label class="form-label">Nomor Rekening / Nomor HP</label>
                <input type="text" name="account_number" class="form-control" placeholder="Contoh: 1234567890"
                    value="<?php echo isset($_POST['account_number']) ? htmlspecialchars($_POST['account_number']) : ''; ?>">
                <small style="color: var(--gray);">Kosongkan jika COD</small>
            </div>

            <div class="form-group">
                <label class="form-label">Nama Pemegang</label>
                <input type="text" name="account_holder" class="form-control" placeholder="Contoh: Toko Shop Online"
                    value="<?php echo isset($_POST['account_holder']) ? htmlspecialchars($_POST['account_holder']) : ''; ?>">
                <small style="color: var(--gray);">Atas nama siapa rekening ini</small>
            </div>

            <div class="form-group">
                <label class="form-label">Instruksi Pembayaran</label>
                <textarea name="instructions" class="form-control" rows="5"
                    placeholder="Contoh: Transfer ke rekening BCA a.n. Toko Shop Online&#10;Nomor Rekening: 1234567890&#10;Setelah transfer, harap konfirmasi via WhatsApp."><?php echo isset($_POST['instructions']) ? htmlspecialchars($_POST['instructions']) : ''; ?></textarea>
                <small style="color: var(--gray);">Instruksi yang akan ditampilkan ke customer</small>
            </div>

            <div class="form-group">
                <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer;">
                    <input type="checkbox" name="is_active" value="1" <?php echo (!isset($_POST['is_active']) || isset($_POST['is_active'])) ? 'checked' : ''; ?>>
                    <span>Aktifkan rekening ini</span>
                </label>
            </div>

            <div style="display: flex; gap: 1rem;">
                <button type="submit" name="add_account" class="btn btn-primary">
                    💾 Simpan
                </button>
                <a href="payment-accounts.php" class="btn btn-secondary">Batal</a>
            </div>
        </form>
    </div>
</div>

<?php include 'includes/footer.php'; ?>