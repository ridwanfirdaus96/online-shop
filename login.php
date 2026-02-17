<?php
$page_title = 'Login';
include 'includes/header.php';

// Redirect if already logged in
if (isLoggedIn()) {
    redirect('index.php');
}

// Handle login
if ($_SERVER['REQUEST_METHOD'] === 'POST' && checkCsrf()) {
    $email = sanitize($_POST['email']);
    $password = $_POST['password'];

    $errors = [];

    if (empty($email))
        $errors[] = 'Email harus diisi';
    if (empty($password))
        $errors[] = 'Password harus diisi';

    if (empty($errors)) {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['role'] = $user['role'];

            setFlash('success', 'Selamat datang, ' . $user['name'] . '!');

            if ($user['role'] === 'admin') {
                redirect('admin/index.php');
            } else {
                redirect('index.php');
            }
        } else {
            $errors[] = 'Email atau password salah!';
        }
    }
}
?>

<div class="container" style="margin-top: 3rem; margin-bottom: 3rem;">
    <div style="max-width: 450px; margin: 0 auto;">
        <div class="card">
            <h2 class="card-title" style="text-align: center;">🔐 Login</h2>

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
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control" required
                        value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                </div>

                <div class="form-group">
                    <label class="form-label">Password</label>
                    <input type="password" name="password" class="form-control" required>
                </div>

                <button type="submit" class="btn btn-primary btn-block">
                    Login
                </button>
            </form>

            <div
                style="text-align: center; margin-top: 1.5rem; padding-top: 1.5rem; border-top: 1px solid var(--border);">
                <p style="color: var(--gray);">
                    Belum punya akun?
                    <a href="register.php" style="color: var(--primary); font-weight: 600;">Daftar di sini</a>
                </p>
            </div>

            <div
                style="margin-top: 1rem; padding: 1rem; background: var(--light); border-radius: 8px; font-size: 0.875rem;">
                <strong>Demo Account:</strong><br>
                Email: admin@shop.com<br>
                Password: admin123
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>