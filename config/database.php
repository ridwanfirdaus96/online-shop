<?php
// Database configuration with environment variable support
// For production (Railway, etc.), use environment variables
// For local development, fallback to default values

// Check if DATABASE_URL exists (Railway format)
if (getenv('DATABASE_URL')) {
    $url = parse_url(getenv('DATABASE_URL'));
    define('DB_HOST', $url['host']);
    define('DB_USER', $url['user']);
    define('DB_PASS', $url['pass']);
    define('DB_NAME', ltrim($url['path'], '/'));
    define('DB_PORT', isset($url['port']) ? $url['port'] : 3306);
} else {
    // Use individual environment variables or fallback to localhost
    define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
    define('DB_USER', getenv('DB_USER') ?: 'root');
    define('DB_PASS', getenv('DB_PASS') ?: '');
    define('DB_NAME', getenv('DB_NAME') ?: 'shop_online');
    define('DB_PORT', getenv('DB_PORT') ?: 3306);
}

// Create PDO connection
try {
    $dsn = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=utf8mb4";
    $pdo = new PDO(
        $dsn,
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]
    );
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}
?>
