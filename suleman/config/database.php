<?php
// ========================================================
// AFRIDI PHARMACY - Database Configuration & Helpers
// Compatible with XAMPP MySQL (host: localhost, user: root)
// ========================================================

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'afridipharmacy');

define('SITE_NAME', 'AFRIDI PHARMACY');
define('SITE_URL', 'http://localhost/suleman/');
define('CURRENCY', 'PKR ');
define('DELIVERY_FEE', 150.00);
define('FREE_SHIPPING_THRESHOLD', 3000.00);

/**
 * Get PDO Database Connection
 * @return PDO
 */
function getDBConnection() {
    static $pdo = null;
    if ($pdo === null) {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ];
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            die("<div style='padding: 20px; font-family: sans-serif; background: #fee2e2; color: #991b1b; border: 1px solid #f87171; border-radius: 8px; margin: 40px auto; max-width: 600px;'>
                <h2>Database Connection Error</h2>
                <p>Unable to connect to MySQL database <strong>" . DB_NAME . "</strong> on <strong>" . DB_HOST . "</strong>.</p>
                <p>Please ensure XAMPP MySQL is running and you have imported <code>database/afridipharmacy.sql</code>.</p>
                <small>Error: " . htmlspecialchars($e->getMessage()) . "</small>
            </div>");
        }
    }
    return $pdo;
}

/**
 * Sanitize User Input
 */
function sanitize($data) {
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

/**
 * Format Currency
 */
function formatCurrency($amount) {
    return CURRENCY . number_format((float)$amount, 2);
}

/**
 * Flash Notification Helper
 */
function setFlash($type, $message) {
    $_SESSION['flash'] = [
        'type' => $type, // success, danger, warning, info
        'message' => $message
    ];
}

function displayFlash() {
    if (isset($_SESSION['flash'])) {
        $type = $_SESSION['flash']['type'];
        $msg = $_SESSION['flash']['message'];
        unset($_SESSION['flash']);
        echo "<div class='alert alert-{$type} alert-dismissible fade show shadow-sm' role='alert'>
            " . htmlspecialchars($msg) . "
            <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>
        </div>";
    }
}

/**
 * Generate CSRF Token
 */
function generateCSRFToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verifyCSRFToken($token) {
    if (isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token)) {
        return true;
    }
    return false;
}

/**
 * Get Cart Total Items Count for Current Session/User
 */
function getCartCount() {
    $db = getDBConnection();
    $userId = $_SESSION['user_id'] ?? null;
    $sessionId = session_id();

    if ($userId) {
        $stmt = $db->prepare("SELECT SUM(quantity) AS total FROM cart WHERE user_id = ?");
        $stmt->execute([$userId]);
    } else {
        $stmt = $db->prepare("SELECT SUM(quantity) AS total FROM cart WHERE session_id = ?");
        $stmt->execute([$sessionId]);
    }

    $res = $stmt->fetch();
    return intval($res['total'] ?? 0);
}

/**
 * Calculate Product Discounted Price
 */
function getEffectivePrice($price, $discount_price) {
    if ($discount_price !== null && $discount_price > 0 && $discount_price < $price) {
        return (float)$discount_price;
    }
    return (float)$price;
}
