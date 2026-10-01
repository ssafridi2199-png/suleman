<?php
// ========================================================
// AFRIDI PHARMACY - AJAX Cart Action Controller
// ========================================================

header('Content-Type: application/json');
require_once __DIR__ . '/includes/auth.php';

$response = ['status' => 'error', 'message' => 'Invalid request'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $db = getDBConnection();
    $userId = $_SESSION['user_id'] ?? null;
    $sessionId = session_id();

    if ($action === 'add_to_cart') {
        $productId = intval($_POST['product_id'] ?? 0);
        $quantity = intval($_POST['quantity'] ?? 1);

        if ($productId <= 0 || $quantity <= 0) {
            echo json_encode(['status' => 'error', 'message' => 'Invalid product or quantity']);
            exit;
        }

        // Verify product stock
        $pStmt = $db->prepare("SELECT id, name, stock_quantity, status FROM products WHERE id = ?");
        $pStmt->execute([$productId]);
        $product = $pStmt->fetch();

        if (!$product || $product['status'] !== 'active') {
            echo json_encode(['status' => 'error', 'message' => 'Product is not available']);
            exit;
        }

        if ($product['stock_quantity'] < $quantity) {
            echo json_encode(['status' => 'error', 'message' => "Only {$product['stock_quantity']} units in stock for {$product['name']}"]);
            exit;
        }

        // Check if item already exists in cart
        if ($userId) {
            $stmt = $db->prepare("SELECT id, quantity FROM cart WHERE user_id = ? AND product_id = ?");
            $stmt->execute([$userId, $productId]);
        } else {
            $stmt = $db->prepare("SELECT id, quantity FROM cart WHERE session_id = ? AND product_id = ?");
            $stmt->execute([$sessionId, $productId]);
        }

        $existing = $stmt->fetch();

        if ($existing) {
            $newQty = $existing['quantity'] + $quantity;
            if ($newQty > $product['stock_quantity']) {
                echo json_encode(['status' => 'error', 'message' => "Cannot exceed available stock level ({$product['stock_quantity']})"]);
                exit;
            }
            $upd = $db->prepare("UPDATE cart SET quantity = ? WHERE id = ?");
            $upd->execute([$newQty, $existing['id']]);
        } else {
            if ($userId) {
                $ins = $db->prepare("INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, ?)");
                $ins->execute([$userId, $productId, $quantity]);
            } else {
                $ins = $db->prepare("INSERT INTO cart (session_id, product_id, quantity) VALUES (?, ?, ?)");
                $ins->execute([$sessionId, $productId, $quantity]);
            }
        }

        $newCartCount = getCartCount();
        echo json_encode([
            'status' => 'success',
            'message' => "{$product['name']} added to your cart successfully!",
            'cart_count' => $newCartCount
        ]);
        exit;
    }

    if ($action === 'update_cart') {
        $cartId = intval($_POST['cart_id'] ?? 0);
        $quantity = intval($_POST['quantity'] ?? 1);

        if ($cartId <= 0 || $quantity <= 0) {
            echo json_encode(['status' => 'error', 'message' => 'Invalid parameters']);
            exit;
        }

        // Check stock
        $cStmt = $db->prepare("SELECT c.id, c.product_id, p.stock_quantity, p.name FROM cart c JOIN products p ON c.product_id = p.id WHERE c.id = ?");
        $cStmt->execute([$cartId]);
        $item = $cStmt->fetch();

        if (!$item) {
            echo json_encode(['status' => 'error', 'message' => 'Cart item not found']);
            exit;
        }

        if ($quantity > $item['stock_quantity']) {
            echo json_encode(['status' => 'error', 'message' => "Stock limit reached ({$item['stock_quantity']} max)"]);
            exit;
        }

        $upd = $db->prepare("UPDATE cart SET quantity = ? WHERE id = ?");
        $upd->execute([$quantity, $cartId]);

        echo json_encode(['status' => 'success', 'message' => 'Cart updated']);
        exit;
    }

    if ($action === 'remove_cart') {
        $cartId = intval($_POST['cart_id'] ?? 0);
        $del = $db->prepare("DELETE FROM cart WHERE id = ?");
        $del->execute([$cartId]);
        echo json_encode(['status' => 'success', 'message' => 'Item removed']);
        exit;
    }
}

echo json_encode($response);
