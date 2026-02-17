<?php
// Script to verify and fix password hash
require_once 'config/database.php';

echo "<h2>Password Verification Tool</h2>";

// Check current admin password hash
$stmt = $pdo->prepare("SELECT id, name, email, password FROM users WHERE email = 'admin@shop.com'");
$stmt->execute();
$admin = $stmt->fetch();

if ($admin) {
    echo "<h3>Admin Account Found:</h3>";
    echo "ID: " . $admin['id'] . "<br>";
    echo "Name: " . $admin['name'] . "<br>";
    echo "Email: " . $admin['email'] . "<br>";
    echo "Password Hash: " . $admin['password'] . "<br><br>";
    
    // Test password verification
    $test_password = 'admin123';
    if (password_verify($test_password, $admin['password'])) {
        echo "<span style='color: green;'>✓ Password 'admin123' is CORRECT</span><br>";
    } else {
        echo "<span style='color: red;'>✗ Password 'admin123' is INCORRECT</span><br>";
        echo "<br><strong>Fixing password...</strong><br>";
        
        // Generate new hash
        $new_hash = password_hash($test_password, PASSWORD_DEFAULT);
        echo "New Hash: " . $new_hash . "<br>";
        
        // Update database
        $update_stmt = $pdo->prepare("UPDATE users SET password = ? WHERE email = 'admin@shop.com'");
        if ($update_stmt->execute([$new_hash])) {
            echo "<span style='color: green;'>✓ Password updated successfully!</span><br>";
            echo "<br>Now try to login with:<br>";
            echo "Email: admin@shop.com<br>";
            echo "Password: admin123<br>";
        } else {
            echo "<span style='color: red;'>✗ Failed to update password</span><br>";
        }
    }
} else {
    echo "<span style='color: red;'>✗ Admin account not found!</span><br>";
    echo "<br><strong>Creating admin account...</strong><br>";
    
    $password_hash = password_hash('admin123', PASSWORD_DEFAULT);
    $insert_stmt = $pdo->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)");
    
    if ($insert_stmt->execute(['Admin', 'admin@shop.com', $password_hash, 'admin'])) {
        echo "<span style='color: green;'>✓ Admin account created successfully!</span><br>";
        echo "<br>Login credentials:<br>";
        echo "Email: admin@shop.com<br>";
        echo "Password: admin123<br>";
    } else {
        echo "<span style='color: red;'>✗ Failed to create admin account</span><br>";
    }
}

echo "<br><hr>";
echo "<h3>All Users:</h3>";
$all_users = $pdo->query("SELECT id, name, email, role FROM users")->fetchAll();
foreach ($all_users as $user) {
    echo "- {$user['name']} ({$user['email']}) - Role: {$user['role']}<br>";
}
?>
