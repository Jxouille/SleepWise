<?php
define('DB_HOST', '178.33.122.21');
define('DB_PORT', 3306);
define('DB_USER', 'axst62997');
define('DB_PASS', 'vN98OBrkug96JSeUmiFxuZGp');
define('DB_NAME', 'hangardb_axst62997');
define('APP_NAME', 'SleepWise');
define('APP_VERSION', '1.0');

function get_db() {
    $dsn = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=utf8mb4";
    try {
        $pdo = new PDO($dsn, DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
        return $pdo;
    } catch (PDOException $e) {
        die(json_encode(['error' => 'DB connection failed: ' . $e->getMessage()]));
    }
}
