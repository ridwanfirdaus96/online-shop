<?php
$page_title = 'My Profile';
include 'includes/header.php';

// Check if user is logged in
if (!isLoggedIn()) {
    setFlash('warning', 'Silakan login terlebih dahulu.');
    redirect('login.php');
}

$user_id = $_SESSION['user_id'];

// Get user data
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

if (!$user) {
    setFlash('danger', 'User tidak ditemukan!');
    redirect('index.php');
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && checkCsrf()) {

    // Update Basic Profile
    if (isset($_POST['update_profile'])) {
        $name = sanitize($_POST['name']);
        $email = sanitize($_POST['email']);
        $phone = sanitize($_POST['phone'] ?? '');

        $errors = [];

        // Validation
        if (empty($name) || strlen($name) < 3) {
            $errors[] = 'Nama minimal 3 karakter';
        }

        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Email tidak valid';
        }

        // Check email uniqueness (exclude current user)
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
        $stmt->execute([$email, $user_id]);
        if ($stmt->fetch()) {
            $errors[] = 'Email sudah digunakan oleh user lain';
        }

        if (empty($errors)) {
            $stmt = $pdo->prepare("UPDATE users SET name = ?, email = ?, phone = ? WHERE id = ?");
            if ($stmt->execute([$name, $email, $phone, $user_id])) {
                $_SESSION['user_name'] = $name; // Update session
                setFlash('success', 'Profile berhasil diupdate!');
                redirect('profile.php');
            } else {
                $errors[] = 'Gagal mengupdate profile';
            }
        }
    }

    // Change Password
    if (isset($_POST['change_password'])) {
        $current_password = $_POST['current_password'];
        $new_password = $_POST['new_password'];
        $confirm_password = $_POST['confirm_password'];

        $errors = [];

        // Verify current password
        if (!password_verify($current_password, $user['password'])) {
            $errors[] = 'Password saat ini salah';
        }

        // Validate new password
        if (strlen($new_password) < 6) {
            $errors[] = 'Password baru minimal 6 karakter';
        }

        // Check confirm password
        if ($new_password !== $confirm_password) {
            $errors[] = 'Konfirmasi password tidak cocok';
        }

        if (empty($errors)) {
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
            if ($stmt->execute([$hashed_password, $user_id])) {
                setFlash('success', 'Password berhasil diubah!');
                redirect('profile.php');
            } else {
                $errors[] = 'Gagal mengubah password';
            }
        }
    }

    // Update Address
    if (isset($_POST['update_address'])) {
        $address = sanitize($_POST['address'] ?? '');

        $stmt = $pdo->prepare("UPDATE users SET address = ? WHERE id = ?");
        if ($stmt->execute([$address, $user_id])) {
            setFlash('success', 'Alamat berhasil diupdate!');
            redirect('profile.php');
        } else {
            setFlash('danger', 'Gagal mengupdate alamat');
            redirect('profile.php');
        }
    }

    // Refresh user data after update
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch();
}
?>

<style>
    .profile-container {
        max-width: 900px;
        margin: 2rem auto;
    }

    .profile-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 2rem;
        border-radius: 12px 12px 0 0;
        text-align: center;
    }

    .avatar {
        width: 100px;
        height: 100px;
        border-radius: 50%;
        background: white;
        color: #667eea;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 3rem;
        font-weight: 700;
        margin: 0 auto 1rem;
        border: 4px solid rgba(255, 255, 255, 0.3);
    }

    .profile-name {
        font-size: 1.75rem;
        font-weight: 700;
        margin-bottom: 0.5rem;
    }

    .profile-role {
        display: inline-block;
        padding: 0.375rem 1rem;
        background: rgba(255, 255, 255, 0.2);
        border-radius: 20px;
        font-size: 0.875rem;
        font-weight: 600;
    }

    .profile-body {
        background: white;
        border: 1px solid var(--border);
        border-top: none;
        border-radius: 0 0 12px 12px;
        padding: 2rem;
    }

    .tabs {
        display: flex;
        gap: 0.5rem;
        border-bottom: 2px solid var(--border);
        margin-bottom: 2rem;
    }

    .tab {
        padding: 1rem 1.5rem;
        background: none;
        border: none;
        cursor: pointer;
        font-weight: 600;
        color: var(--gray);
        border-bottom: 3px solid transparent;
        transition: all 0.3s;
    }

    .tab:hover {
        color: var(--primary);
    }

    .tab.active {
        color: var(--primary);
        border-bottom-color: var(--primary);
    }

    .tab-content {
        display: none;
    }

    .tab-content.active {
        display: block;
    }

    .info-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 1.5rem;
        margin-bottom: 2rem;
    }

    .info-item {
        display: flex;
        flex-direction: column;
    }

    .info-label {
        color: var(--gray);
        font-size: 0.875rem;
        margin-bottom: 0.5rem;
        font-weight: 600;
    }

    .info-value {
        font-size: 1.125rem;
        color: var(--dark);
    }

    .info-icon {
        margin-right: 0.5rem;
    }

    @media (max-width: 768px) {
        .tabs {
            flex-direction: column;
        }

        .tab {
            border-bottom: 1px solid var(--border);
            border-left: 3px solid transparent;
        }

        .tab.active {
            border-bottom-color: var(--border);
            border-left-color: var(--primary);
        }
    }
</style>

<div class="container profile-container">
    <!-- Profile Header -->
    <div class="profile-header">
        <div class="avatar">
            <?php echo strtoupper(substr($user['name'], 0, 1)); ?>
        </div>
        <div class="profile-name"><?php echo htmlspecialchars($user['name']); ?></div>
        <div class="profile-role">
            <?php echo $user['role'] === 'admin' ? '👑 Admin' : '👤 User'; ?>
        </div>
        <div style="margin-top: 1rem; opacity: 0.9; font-size: 0.875rem;">
            🎖️ Member since <?php echo date('F Y', strtotime($user['created_at'])); ?>
        </div>
    </div>

    <!-- Profile Body -->
    <div class="profile-body">
        <?php if (isset($errors) && !empty($errors)): ?>
            <div class="alert alert-danger">
                <ul style="margin: 0; padding-left: 1.5rem;">
                    <?php foreach ($errors as $error): ?>
                        <li><?php echo $error; ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <!-- Tabs -->
        <div class="tabs">
            <button class="tab active" onclick="switchTab('info')">📋 Profile Info</button>
            <button class="tab" onclick="switchTab('edit')">✏️ Edit Profile</button>
            <button class="tab" onclick="switchTab('password')">🔐 Change Password</button>
            <button class="tab" onclick="switchTab('address')">📍 Address</button>
        </div>

        <!-- Tab 1: Profile Info (View Mode) -->
        <div id="tab-info" class="tab-content active">
            <h3 style="margin-bottom: 1.5rem;">Personal Information</h3>
            <div class="info-grid">
                <div class="info-item">
                    <div class="info-label"><span class="info-icon">👤</span>Full Name</div>
                    <div class="info-value"><?php echo htmlspecialchars($user['name']); ?></div>
                </div>
                <div class="info-item">
                    <div class="info-label"><span class="info-icon">📧</span>Email</div>
                    <div class="info-value"><?php echo htmlspecialchars($user['email']); ?></div>
                </div>
                <div class="info-item">
                    <div class="info-label"><span class="info-icon">📱</span>Phone</div>
                    <div class="info-value">
                        <?php echo $user['phone'] ? htmlspecialchars($user['phone']) : '<span style="color: var(--gray);">Not set</span>'; ?>
                    </div>
                </div>
                <div class="info-item">
                    <div class="info-label"><span class="info-icon">🎭</span>Role</div>
                    <div class="info-value"><?php echo ucfirst($user['role']); ?></div>
                </div>
            </div>

            <h3 style="margin: 2rem 0 1rem;">Shipping Address</h3>
            <div class="card" style="padding: 1rem; background: var(--light);">
                <?php if ($user['address']): ?>
                    <p style="margin: 0; white-space: pre-line;"><?php echo htmlspecialchars($user['address']); ?></p>
                <?php else: ?>
                    <p style="margin: 0; color: var(--gray);">📍 No address set yet</p>
                <?php endif; ?>
            </div>
        </div>

        <!-- Tab 2: Edit Profile -->
        <div id="tab-edit" class="tab-content">
            <h3 style="margin-bottom: 1.5rem;">Edit Basic Information</h3>
            <form method="POST">
                <?php echo getCsrfInput(); ?>

                <div class="form-group">
                    <label class="form-label">Full Name *</label>
                    <input type="text" name="name" class="form-control" required
                        value="<?php echo htmlspecialchars($user['name']); ?>" minlength="3">
                </div>

                <div class="form-group">
                    <label class="form-label">Email *</label>
                    <input type="email" name="email" class="form-control" required
                        value="<?php echo htmlspecialchars($user['email']); ?>">
                </div>

                <div class="form-group">
                    <label class="form-label">Phone Number</label>
                    <input type="tel" name="phone" class="form-control"
                        value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>" placeholder="08123456789">
                </div>

                <button type="submit" name="update_profile" class="btn btn-primary">
                    💾 Update Profile
                </button>
            </form>
        </div>

        <!-- Tab 3: Change Password -->
        <div id="tab-password" class="tab-content">
            <h3 style="margin-bottom: 1.5rem;">Change Password</h3>
            <form method="POST">
                <?php echo getCsrfInput(); ?>

                <div class="form-group">
                    <label class="form-label">Current Password *</label>
                    <input type="password" name="current_password" class="form-control" required>
                </div>

                <div class="form-group">
                    <label class="form-label">New Password *</label>
                    <input type="password" name="new_password" class="form-control" required minlength="6"
                        placeholder="Minimal 6 karakter">
                </div>

                <div class="form-group">
                    <label class="form-label">Confirm New Password *</label>
                    <input type="password" name="confirm_password" class="form-control" required minlength="6">
                </div>

                <button type="submit" name="change_password" class="btn btn-primary">
                    🔐 Change Password
                </button>
            </form>
        </div>

        <!-- Tab 4: Address -->
        <div id="tab-address" class="tab-content">
            <h3 style="margin-bottom: 1.5rem;">Shipping Address</h3>
            <p style="color: var(--gray); margin-bottom: 1.5rem;">
                💡 Alamat ini akan digunakan sebagai default saat checkout
            </p>
            <form method="POST">
                <?php echo getCsrfInput(); ?>

                <div class="form-group">
                    <label class="form-label">Full Address</label>
                    <textarea name="address" class="form-control" rows="5"
                        placeholder="Jl. Contoh No. 123, Kota, Provinsi, Kode Pos"><?php echo htmlspecialchars($user['address'] ?? ''); ?></textarea>
                </div>

                <button type="submit" name="update_address" class="btn btn-primary">
                    📍 Update Address
                </button>
            </form>
        </div>
    </div>
</div>

<script>
    function switchTab(tabName) {
        // Hide all tab contents
        const contents = document.querySelectorAll('.tab-content');
        contents.forEach(content => content.classList.remove('active'));

        // Remove active class from all tabs
        const tabs = document.querySelectorAll('.tab');
        tabs.forEach(tab => tab.classList.remove('active'));

        // Show selected tab content
        document.getElementById('tab-' + tabName).classList.add('active');

        // Add active class to clicked tab
        event.target.classList.add('active');
    }
</script>

<?php include 'includes/footer.php'; ?>