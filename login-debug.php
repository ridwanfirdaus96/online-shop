<?php
$page_title = 'Login (Debug Mode)';
session_start();
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';

// Redirect if already logged in
if (isLoggedIn()) {
    redirect('index.php');
}

// Handle login with detailed debugging
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    echo "<div style='padding: 20px; font-family: monospace; background: #f5f5f5;'>";
    echo "<h2>🔍 Login Debug Information</h2>";

    // Check CSRF
    echo "<h3>1️⃣ CSRF Token Check:</h3>";
    $csrf_valid = checkCsrf();
    echo "CSRF Valid: " . ($csrf_valid ? "<span style='color: green;'>✓ YES</span>" : "<span style='color: red;'>✗ NO</span>") . "<br>";

    if (!$csrf_valid) {
        echo "<span style='color: orange;'>⚠️ CSRF validation failed. This might be the issue!</span><br>";
    }

    $email = sanitize($_POST['email']);
    $password = $_POST['password'];

    echo "<h3>2️⃣ Form Data Received:</h3>";
    echo "Email (after sanitize): <strong>" . htmlspecialchars($email) . "</strong><br>";
    echo "Password length: " . strlen($password) . " characters<br>";

    $errors = [];

    if (empty($email))
        $errors[] = 'Email harus diisi';
    if (empty($password))
        $errors[] = 'Password harus diisi';

    echo "<h3>3️⃣ Validation:</h3>";
    if (empty($errors)) {
        echo "<span style='color: green;'>✓ Validation passed</span><br>";
    } else {
        echo "<span style='color: red;'>✗ Validation errors:</span><br>";
        foreach ($errors as $error) {
            echo "- $error<br>";
        }
    }

    if (empty($errors) && $csrf_valid) {
        echo "<h3>4️⃣ Database Query:</h3>";
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user) {
            echo "<span style='color: green;'>✓ User found in database</span><br>";
            echo "User ID: " . $user['id'] . "<br>";
            echo "User Name: " . $user['name'] . "<br>";
            echo "User Email: " . $user['email'] . "<br>";
            echo "User Role: " . $user['role'] . "<br>";
            echo "Password Hash: " . substr($user['password'], 0, 30) . "...<br>";

            echo "<h3>5️⃣ Password Verification:</h3>";
            $password_match = password_verify($password, $user['password']);

            if ($password_match) {
                echo "<span style='color: green; font-size: 18px;'>✓✓✓ PASSWORD CORRECT! ✓✓✓</span><br>";
                echo "<br><strong>Login should succeed now. Redirecting...</strong><br>";

                // Perform actual login
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['name'];
                $_SESSION['role'] = $user['role'];

                setFlash('success', 'Selamat datang, ' . $user['name'] . '!');

                echo "<script>setTimeout(function() { window.location.href = '" . ($user['role'] === 'admin' ? 'admin/index.php' : 'index.php') . "'; }, 2000);</script>";
            } else {
                echo "<span style='color: red; font-size: 18px;'>✗✗✗ PASSWORD WRONG! ✗✗✗</span><br>";
                echo "<br><strong>This is the problem!</strong><br>";
                echo "<br>Possible causes:<br>";
                echo "1. The password you entered is incorrect<br>";
                echo "2. The password hash in database is wrong<br>";
                echo "<br>To fix: Run <a href='verify-password.php'>verify-password.php</a> to regenerate the correct hash.<br>";
            }
        } else {
            echo "<span style='color: red;'>✗ User NOT found in database</span><br>";
            echo "Email searched: <strong>" . htmlspecialchars($email) . "</strong><br>";
            echo "<br>The email doesn't exist in the database. Try:<br>";
            echo "- admin@shop.com (for admin)<br>";
            echo "- Or register a new account<br>";
        }
    }

    echo "<hr>";
    echo "<a href='login-debug.php' style='display: inline-block; margin-top: 10px; padding: 10px 20px; background: #007bff; color: white; text-decoration: none; border-radius: 5px;'>Try Again</a> ";
    echo "<a href='verify-password.php' style='display: inline-block; margin-top: 10px; padding: 10px 20px; background: #28a745; color: white; text-decoration: none; border-radius: 5px;'>Fix Password Hash</a>";
    echo "</div>";
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/shop-online/assets/css/style.css">
</head>

<body>
    <div class="container" style="margin-top: 3rem; margin-bottom: 3rem;">
        <div style="max-width: 450px; margin: 0 auto;">
            <div class="card">
                <h2 class="card-title" style="text-align: center;">🔐 Login (Debug Mode)</h2>

                <div
                    style="background: #fff3cd; padding: 10px; border-radius: 5px; margin-bottom: 20px; border: 1px solid #ffc107;">
                    <strong>⚠️ Debug Mode Active</strong><br>
                    This page will show detailed information about the login process.
                </div>

                <form method="POST">
                    <?php echo getCsrfInput(); ?>
                    <div class="form-group">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control" required
                            value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : 'admin@shop.com'; ?>">
                    </div>

                    <div class="form-group">
                        <label class="form-label">Password</label>
                        <input type="password" name="password" class="form-control" required value="admin123">
                    </div>

                    <button type="submit" class="btn btn-primary btn-block">
                        🔍 Login with Debug
                    </button>
                </form>

                <div
                    style="text-align: center; margin-top: 1.5rem; padding-top: 1.5rem; border-top: 1px solid var(--border);">
                    <p>
                        <a href="login.php" style="color: var(--primary); font-weight: 600;">← Back to Normal Login</a>
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
</body>

</html>