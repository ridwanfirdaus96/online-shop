<?php
$page_title = 'Register';
include 'includes/header.php';

// Redirect if already logged in
if (isLoggedIn()) {
    redirect('index.php');
}

// Handle registration
if ($_SERVER['REQUEST_METHOD'] === 'POST' && checkCsrf()) {
    $name = sanitize($_POST['name']);
    $email = sanitize($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    $errors = [];

    if (empty($name))
        $errors[] = 'Nama harus diisi';
    if (empty($email))
        $errors[] = 'Email harus diisi';
    if (empty($password))
        $errors[] = 'Password harus diisi';
    if ($password !== $confirm_password)
        $errors[] = 'Password tidak cocok';
    if (strlen($password) < 6)
        $errors[] = 'Password minimal 6 karakter';

    // Check if email already exists
    if (empty($errors)) {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $errors[] = 'Email sudah terdaftar!';
        }
    }

    if (empty($errors)) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        $stmt = $pdo->prepare("INSERT INTO users (name, email, password) VALUES (?, ?, ?)");
        if ($stmt->execute([$name, $email, $hashed_password])) {
            setFlash('success', 'Registrasi berhasil! Silakan login.');
            redirect('login.php');
        } else {
            $errors[] = 'Terjadi kesalahan saat registrasi.';
        }
    }
}
?>

<div class="container" style="margin-top: 3rem; margin-bottom: 3rem;">
    <div style="max-width: 450px; margin: 0 auto;">
        <div class="card">
            <h2 class="card-title" style="text-align: center;">📝 Daftar Akun Baru</h2>

            <?php if (isset($errors) && !empty($errors)): ?>
                <div class="alert alert-danger">
                    <ul style="margin: 0; padding-left: 1.5rem;">
                        <?php foreach ($errors as $error): ?>
                            <li><?php echo $error; ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <form method="POST">
                <?php echo getCsrfInput(); ?>
                <div class="form-group">
                    <label class="form-label">Nama Lengkap</label>
                    <input type="text" name="name" class="form-control" required
                        value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>">
                </div>

                <div class="form-group">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control" required
                        value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                </div>

                <div class="form-group">
                    <label class="form-label">Password</label>
                    <input type="password" name="password" class="form-control" required>
                    <small style="color: var(--gray);">Minimal 6 karakter</small>
                </div>

                <div class="form-group">
                    <label class="form-label">Konfirmasi Password</label>
                    <input type="password" name="confirm_password" class="form-control" required>
                </div>

                <button type="submit" class="btn btn-primary btn-block">
                    Daftar
                </button>
            </form>

            <div
                style="text-align: center; margin-top: 1.5rem; padding-top: 1.5rem; border-top: 1px solid var(--border);">
                <p style="color: var(--gray);">
                    Sudah punya akun?
                    <a href="login.php" style="color: var(--primary); font-weight: 600;">Login di sini</a>
                </p>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>