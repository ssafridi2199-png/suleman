<?php
// ========================================================
// AFRIDI PHARMACY - Authentication & Session Middleware
// ========================================================

require_once __DIR__ . '/../config/database.php';

/**
 * Check if customer is logged in
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/**
 * Check if admin is logged in
 */
function isAdmin() {
    return isLoggedIn() && isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
}

/**
 * Require normal customer login guard
 */
function requireLogin() {
    if (!isLoggedIn()) {
        $_SESSION['redirect_url'] = $_SERVER['REQUEST_URI'];
        setFlash('warning', 'Please log in to your Afridi Pharmacy account to continue.');
        header('Location: ' . SITE_URL . 'login.php');
        exit;
    }
}

/**
 * Require Admin authentication guard
 */
function requireAdmin() {
    if (!isAdmin()) {
        setFlash('danger', 'Access denied. Administrator privileges required.');
        header('Location: ' . SITE_URL . 'login.php');
        exit;
    }
}

/**
 * Get current logged in user array
 */
function getCurrentUser() {
    if (!isLoggedIn()) return null;
    $db = getDBConnection();
    $stmt = $db->prepare("SELECT id, full_name, email, phone, role, status, created_at FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    return $stmt->fetch();
}

/**
 * Merge Guest Cart items into User Cart upon Login
 */
function syncGuestCartToUser($userId) {
    $db = getDBConnection();
    $sessionId = session_id();

    // Fetch guest items
    $stmt = $db->prepare("SELECT * FROM cart WHERE session_id = ? AND user_id IS NULL");
    $stmt->execute([$sessionId]);
    $guestItems = $stmt->fetchAll();

    foreach ($guestItems as $item) {
        // Check if user already has item in cart
        $checkStmt = $db->prepare("SELECT id, quantity FROM cart WHERE user_id = ? AND product_id = ?");
        $checkStmt->execute([$userId, $item['product_id']]);
        $existing = $checkStmt->fetch();

        if ($existing) {
            $newQty = $existing['quantity'] + $item['quantity'];
            $update = $db->prepare("UPDATE cart SET quantity = ?, user_id = ?, session_id = NULL WHERE id = ?");
            $update->execute([$newQty, $userId, $existing['id']]);
            // Delete guest row
            $del = $db->prepare("DELETE FROM cart WHERE id = ?");
            $del->execute([$item['id']]);
        } else {
            $update = $db->prepare("UPDATE cart SET user_id = ?, session_id = NULL WHERE id = ?");
            $update->execute([$userId, $item['id']]);
        }
    }
}
