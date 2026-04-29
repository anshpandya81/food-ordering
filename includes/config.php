<?php
// ============================================================
// config.php — Database Configuration & PDO Connection
// ============================================================
// Change DB_HOST, DB_USER, DB_PASS, DB_NAME to match your setup

define('DB_HOST', 'localhost');
define('DB_USER', 'root');          // XAMPP default
define('DB_PASS', '');              // XAMPP default (empty)
define('DB_NAME', 'food_ordering');

define('SITE_URL', 'http://localhost/food-ordering');
define('SITE_NAME', 'FoodieExpress');

// PDO connection using a function (call getDB() anywhere)
function getDB() {
    static $pdo = null; // reuse same connection

    if ($pdo === null) {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,  // throw exceptions on error
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,        // return arrays
                PDO::ATTR_EMULATE_PREPARES   => false,                   // real prepared statements
            ];
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            // Show friendly error (never expose raw DB errors in production)
            die(json_encode(['error' => 'Database connection failed: ' . $e->getMessage()]));
        }
    }
    return $pdo;
}

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Helper: Check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Helper: Check if admin is logged in
function isAdmin() {
    return isset($_SESSION['user_id']) && isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

// Helper: Redirect to URL
function redirect($url) {
    header("Location: $url");
    exit();
}

// Helper: Sanitize output to prevent XSS
function e($str) {
    return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
}

// Helper: Return JSON response (for API endpoints)
function jsonResponse($data, $statusCode = 200) {
    http_response_code($statusCode);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit();
}
?>
