<?php
/** Aurora Grand · MySQL connection (PDO, prepared statements) */
declare(strict_types=1);

define('DB_HOST', '127.0.0.1');
define('DB_NAME', 'aurora_hms');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHAR', 'utf8mb4');

define('HOTEL_NAME', 'Aurora Grand Hotel');
define('HOTEL_TAGLINE', 'Where luxury meets comfort');
define('HOTEL_ADDRESS', 'House 12, Banani, Dhaka, Bangladesh');
define('HOTEL_PHONE', '+880 1700-000000');
define('HOTEL_EMAIL', 'info@aurora.com');
define('CURRENCY', '৳');
define('TAX_RATE', 0.15);
define('SERVICE_RATE', 0.05);

function db(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHAR;
        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ]);
        } catch (PDOException $e) {
            http_response_code(500);
            exit('Database connection failed: ' . htmlspecialchars($e->getMessage()));
        }
    }
    return $pdo;
}

function base_url(string $path = ''): string {
    $script = $_SERVER['SCRIPT_NAME'] ?? '';
    $root = rtrim(str_replace('\\', '/', dirname($script)), '/');
    // Detect nested folder: any /admin /staff /customer /auth subfolder
    foreach (['/admin','/staff','/customer','/auth'] as $sub) {
        if (str_ends_with($root, $sub)) { $root = substr($root, 0, -strlen($sub)); }
    }
    return $root . '/' . ltrim($path, '/');
}
