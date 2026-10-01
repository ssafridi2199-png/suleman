<?php
$pageTitle = "Checkout Order";
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';

// Force login or guest email capture
requireLogin();

$db = getDBConnection();
$userId = $_SESSION['user_id'];
$currentUser = getCurrentUser();

// Fetch saved default address
$addrStmt = $db->prepare("SELECT * FROM addresses WHERE user_id = ? ORDER BY is_default DESC LIMIT 1");
$addrStmt->execute([$userId]);
$savedAddress = $addrStmt->fetch();

// Fetch Cart items
$cartStmt = $db->prepare("SELECT c.id AS cart_id, c.quantity, p.* 
                          FROM cart c 
                          JOIN products p ON c.product_id = p.id 
                          WHERE c.user_id = ?");
$cartStmt->execute([$userId]);
$cartItems = $cartStmt->fetchAll();

if (empty($cartItems)) {
    header("Location: " . SITE_URL . "cart.php");
    exit;
}

$subtotal = 0;
$hasRxProduct = false;

foreach ($cartItems as $item) {
    $unitPrice = getEffectivePrice($item['price'], $item['discount_price']);
    $subtotal += $unitPrice * $item['quantity'];
    if ($item['requires_prescription']) {
        $hasRxProduct = true;
    }
}

// Coupon discount handling
$discountAmount = 0;
$couponCode = '';
if (isset($_SESSION['applied_coupon'])) {
    $couponCode = $_SESSION['applied_coupon']['code'];
    $discountAmount = $_SESSION['applied_coupon']['discount'];
}

$deliveryFee = ($subtotal >= FREE_SHIPPING_THRESHOLD) ? 0.00 : DELIVERY_FEE;
$totalAmount = max(0, $subtotal + $deliveryFee - $discountAmount);

// Handle Coupon Form Submission via GET/POST
if (isset($_POST['apply_coupon'])) {
    $code = sanitize($_POST['coupon_code'] ?? '');
    $cpStmt = $db->prepare("SELECT * FROM coupons WHERE code = ? AND status = 'active' AND expiry_date >= CURDATE()");
    $cpStmt->execute([$code]);
    $coupon = $cpStmt->fetch();

    if ($coupon) {
        if ($subtotal < $coupon['min_order_amount']) {
            setFlash('danger', "Minimum order amount of " . formatCurrency($coupon['min_order_amount']) . " required for code '{$code}'.");
        } else {
            $disc = 0;
            if ($coupon['discount_type'] === 'percent') {
                $disc = ($subtotal * $coupon['discount_value']) / 100;
            } else {
                $disc = $coupon['discount_value'];
            }
            $_SESSION['applied_coupon'] = [
                'code' => $coupon['code'],
                'discount' => $disc
            ];
            setFlash('success', "Coupon '{$code}' applied successfully!");
            header("Location: " . SITE_URL . "checkout.php");
            exit;
        }
    } else {
        setFlash('danger', 'Invalid or expired coupon code.');
    }
}

// Handle Order Placement POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['place_order'])) {
    $name = sanitize($_POST['customer_name'] ?? '');
    $phone = sanitize($_POST['customer_phone'] ?? '');
    $email = sanitize($_POST['customer_email'] ?? '');
    $address = sanitize($_POST['shipping_address'] ?? '');
    $city = sanitize($_POST['city'] ?? '');
    $postalCode = sanitize($_POST['postal_code'] ?? '');
    $notes = sanitize($_POST['order_notes'] ?? '');
    $paymentMethod = sanitize($_POST['payment_method'] ?? 'cod');

    $rxFileName = null;

    // Prescription validation if required
    if ($hasRxProduct) {
        if (isset($_FILES['prescription_file']) && $_FILES['prescription_file']['error'] === UPLOAD_ERR_OK) {
            $fileTmp = $_FILES['prescription_file']['tmp_name'];
            $fileName = $_FILES['prescription_file']['name'];
            $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

            $allowed = ['jpg', 'jpeg', 'png', 'pdf'];
            if (!in_array($fileExt, $allowed)) {
                setFlash('danger', 'Invalid prescription file type. Only JPG, PNG, and PDF files are accepted.');
            } else {
                $rxFileName = 'rx_' . time() . '_' . rand(1000, 9999) . '.' . $fileExt;
                $targetPath = __DIR__ . '/uploads/prescriptions/' . $rxFileName;
                move_uploaded_file($fileTmp, $targetPath);
            }
        } else {
            setFlash('danger', 'A valid prescription file upload is required for prescription medicines in your order.');
        }
    }

    if (!isset($_SESSION['flash']) || $_SESSION['flash']['type'] !== 'danger') {
        try {
            $db->beginTransaction();

            $orderNum = 'ORD-' . date('Ymd') . '-' . rand(1000, 9999);

            // Insert into orders
            $insOrder = $db->prepare("INSERT INTO orders 
                (order_number, user_id, customer_name, customer_phone, customer_email, shipping_address, city, postal_code, order_notes, subtotal, delivery_fee, total_amount, payment_method, payment_status, order_status, has_prescription) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending', 'Pending', ?)");
            
            $insOrder->execute([
                $orderNum, $userId, $name, $phone, $email, $address, $city, $postalCode, $notes,
                $subtotal, $deliveryFee, $totalAmount, $paymentMethod, ($hasRxProduct ? 1 : 0)
            ]);

            $orderId = $db->lastInsertId();

            // Insert order items & reduce product stock
            foreach ($cartItems as $item) {
                $unitPrice = getEffectivePrice($item['price'], $item['discount_price']);
                $itemTotal = $unitPrice * $item['quantity'];

                $insItem = $db->prepare("INSERT INTO order_items (order_id, product_id, product_name, unit_price, quantity, total_price) VALUES (?, ?, ?, ?, ?, ?)");
                $insItem->execute([$orderId, $item['id'], $item['name'], $unitPrice, $item['quantity'], $itemTotal]);

                // Reduce Stock
                $updStock = $db->prepare("UPDATE products SET stock_quantity = stock_quantity - ? WHERE id = ?");
                $updStock->execute([$item['quantity'], $item['id']]);

                // Record Inventory Transaction
                $invLog = $db->prepare("INSERT INTO inventory (product_id, transaction_type, quantity, notes) VALUES (?, 'out', ?, ?)");
                $invLog->execute([$item['id'], $item['quantity'], "Customer Order #{$orderNum}"]);
            }

            // Insert Prescription Record if present
            if ($hasRxProduct && $rxFileName) {
                $insRx = $db->prepare("INSERT INTO prescriptions (user_id, order_id, prescription_file, notes, status) VALUES (?, ?, ?, ?, 'Pending')");
                $insRx->execute([$userId, $orderId, $rxFileName, "Uploaded during checkout for Order #{$orderNum}"]);
            }

            // Record Initial Payment entry
            $insPay = $db->prepare("INSERT INTO payments (order_id, payment_method, amount, status) VALUES (?, ?, ?, 'Pending')");
            $insPay->execute([$orderId, $paymentMethod, $totalAmount]);

            // Clear Cart
            $delCart = $db->prepare("DELETE FROM cart WHERE user_id = ?");
            $delCart->execute([$userId]);

            // Clear Applied Coupon
            unset($_SESSION['applied_coupon']);

            $db->commit();

            setFlash('success', "Order #{$orderNum} placed successfully! Our licensed pharmacist is reviewing your details.");
            header("Location: " . SITE_URL . "order-details.php?id=" . $orderId);
            exit;

        } catch (Exception $e) {
            $db->rollBack();
            setFlash('danger', "Failed to process order: " . $e->getMessage());
        }
    }
}
?>

<div class="container my-4">
  <h2 class="fw-bold mb-4"><i class="bi bi-shield-check text-teal"></i> Secure Pharmacy Checkout</h2>

  <form action="" method="POST" enctype="multipart/form-data">
    <div class="row g-4">
      
      <!-- CUSTOMER & DELIVERY ADDRESS FORM -->
      <div class="col-lg-7">
        
        <!-- Contact Details -->
        <div class="card border-0 shadow-sm rounded-4 p-4 mb-4">
          <h5 class="fw-bold mb-3"><i class="bi bi-person me-2 text-teal"></i> Customer Details</h5>
          
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label small fw-bold">Full Name</label>
              <input type="text" name="customer_name" class="form-control" value="<?= htmlspecialchars($currentUser['full_name']); ?>" required>
            </div>

            <div class="col-md-6">
              <label class="form-label small fw-bold">Phone Number</label>
              <input type="tel" name="customer_phone" class="form-control" value="<?= htmlspecialchars($savedAddress['phone'] ?? $currentUser['phone']); ?>" placeholder="+92 300 0000000" required>
            </div>

            <div class="col-12">
              <label class="form-label small fw-bold">Email Address</label>
              <input type="email" name="customer_email" class="form-control" value="<?= htmlspecialchars($currentUser['email']); ?>" required>
            </div>
          </div>
        </div>

        <!-- Shipping Address -->
        <div class="card border-0 shadow-sm rounded-4 p-4 mb-4">
          <h5 class="fw-bold mb-3"><i class="bi bi-geo-alt me-2 text-teal"></i> Delivery Address</h5>
          
          <div class="row g-3">
            <div class="col-12">
              <label class="form-label small fw-bold">Street Address / House / Sector</label>
              <textarea name="shipping_address" class="form-control" rows="2" placeholder="e.g. House # 45, Street 12, Sector F-8/3" required><?= htmlspecialchars($savedAddress['street_address'] ?? ''); ?></textarea>
            </div>

            <div class="col-md-6">
              <label class="form-label small fw-bold">City</label>
              <input type="text" name="city" class="form-control" value="<?= htmlspecialchars($savedAddress['city'] ?? 'Islamabad'); ?>" required>
            </div>

            <div class="col-md-6">
              <label class="form-label small fw-bold">Postal Code</label>
              <input type="text" name="postal_code" class="form-control" value="<?= htmlspecialchars($savedAddress['postal_code'] ?? '44000'); ?>" required>
            </div>

            <div class="col-12">
              <label class="form-label small fw-bold">Order Notes / Delivery Instructions (Optional)</label>
              <input type="text" name="order_notes" class="form-control" placeholder="e.g. Ring doorbell, deliver before 6 PM">
            </div>
          </div>
        </div>

        <!-- Prescription Upload Box (If Rx products in cart) -->
        <?php if ($hasRxProduct): ?>
          <div class="card border-warning shadow-sm rounded-4 p-4 mb-4 bg-warning-subtle">
            <h5 class="fw-bold text-dark mb-2">
              <i class="bi bi-file-earmark-medical me-2 text-danger"></i> Upload Doctor's Prescription
            </h5>
            <p class="small text-muted mb-3">
              One or more items in your cart require a valid prescription. Please upload a clear photo or PDF scan of your doctor's prescription.
            </p>
            
            <div class="mb-3">
              <input type="file" name="prescription_file" id="prescription_file" class="form-control" accept=".jpg,.jpeg,.png,.pdf" required>
              <div class="form-text">Accepted formats: JPG, PNG, PDF (Max size 5MB)</div>
            </div>

            <div id="rx_file_preview" class="d-none mt-2"></div>
          </div>
        <?php endif; ?>

        <!-- Payment Method Selection -->
        <div class="card border-0 shadow-sm rounded-4 p-4 mb-4">
          <h5 class="fw-bold mb-3"><i class="bi bi-credit-card me-2 text-teal"></i> Payment Method</h5>

          <div class="form-check p-3 border rounded-3 mb-2">
            <input class="form-check-input" type="radio" name="payment_method" id="pay_cod" value="cod" checked>
            <label class="form-check-label fw-bold d-flex justify-content-between w-100" for="pay_cod">
              <span>Cash on Delivery (COD)</span>
              <span class="badge bg-secondary">Pay at doorstep</span>
            </label>
          </div>

          <div class="form-check p-3 border rounded-3 mb-2">
            <input class="form-check-input" type="radio" name="payment_method" id="pay_card" value="card">
            <label class="form-check-label fw-bold d-flex justify-content-between w-100" for="pay_card">
              <span>Credit / Debit Card</span>
              <span class="badge bg-primary">Visa / Mastercard</span>
            </label>
          </div>

          <div class="form-check p-3 border rounded-3 mb-2">
            <input class="form-check-input" type="radio" name="payment_method" id="pay_easypaisa" value="easypaisa">
            <label class="form-check-label fw-bold d-flex justify-content-between w-100" for="pay_easypaisa">
              <span>Easypaisa Mobile Wallet</span>
              <span class="badge bg-success">Instant Mobile Pay</span>
            </label>
          </div>

          <div class="form-check p-3 border rounded-3 mb-2">
            <input class="form-check-input" type="radio" name="payment_method" id="pay_jazzcash" value="jazzcash">
            <label class="form-check-label fw-bold d-flex justify-content-between w-100" for="pay_jazzcash">
              <span>JazzCash Wallet</span>
              <span class="badge bg-danger">Instant Mobile Pay</span>
            </label>
          </div>
        </div>

      </div>

      <!-- ORDER SUMMARY & COUPON SIDEBAR -->
      <div class="col-lg-5">
        
        <!-- Coupon Code Box -->
        <div class="card border-0 shadow-sm rounded-4 p-4 mb-4">
          <h6 class="fw-bold mb-3"><i class="bi bi-ticket-perforated me-2 text-warning"></i> Have a Discount Coupon?</h6>
          <div class="input-group">
            <input type="text" name="coupon_code" class="form-control" placeholder="Enter coupon (e.g. HEALTH10)" value="<?= htmlspecialchars($couponCode); ?>">
            <button type="submit" name="apply_coupon" class="btn btn-outline-secondary">Apply</button>
          </div>
          <?php if ($discountAmount > 0): ?>
            <div class="text-success small fw-bold mt-2">✓ Coupon applied! Discount: <?= formatCurrency($discountAmount); ?></div>
          <?php endif; ?>
        </div>

        <!-- Order Items & Total Summary -->
        <div class="card border-0 shadow-sm rounded-4 p-4 sticky-top" style="top: 90px;">
          <h5 class="fw-bold mb-3">Order Items (<?= count($cartItems); ?>)</h5>
          
          <div class="cart-items-list mb-3" style="max-height: 280px; overflow-y: auto;">
            <?php foreach ($cartItems as $item): 
              $effectivePrice = getEffectivePrice($item['price'], $item['discount_price']);
            ?>
              <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                <div class="d-flex align-items-center gap-2">
                  <img src="<?= SITE_URL; ?>uploads/products/<?= htmlspecialchars($item['image'] ?? 'panadol-extra.jpg'); ?>" style="width: 45px; height: 45px; object-fit: contain;" class="rounded border">
                  <div>
                    <span class="fw-bold text-dark d-block small"><?= htmlspecialchars($item['name']); ?></span>
                    <small class="text-muted">Qty: <?= $item['quantity']; ?> x <?= formatCurrency($effectivePrice); ?></small>
                  </div>
                </div>
                <span class="fw-bold text-dark"><?= formatCurrency($effectivePrice * $item['quantity']); ?></span>
              </div>
            <?php endforeach; ?>
          </div>

          <div class="d-flex justify-content-between mb-2">
            <span class="text-muted">Subtotal</span>
            <span class="fw-bold"><?= formatCurrency($subtotal); ?></span>
          </div>

          <div class="d-flex justify-content-between mb-2">
            <span class="text-muted">Delivery Fee</span>
            <span class="fw-bold text-<?= $deliveryFee == 0 ? 'success' : 'dark'; ?>"><?= $deliveryFee == 0 ? 'FREE' : formatCurrency($deliveryFee); ?></span>
          </div>

          <?php if ($discountAmount > 0): ?>
            <div class="d-flex justify-content-between mb-2 text-success fw-bold">
              <span>Coupon Discount</span>
              <span>- <?= formatCurrency($discountAmount); ?></span>
            </div>
          <?php endif; ?>

          <hr class="my-3">

          <div class="d-flex justify-content-between mb-4">
            <span class="fs-5 fw-bold">Total Payable</span>
            <span class="fs-3 fw-extrabold text-teal" style="color: var(--primary-color);"><?= formatCurrency($totalAmount); ?></span>
          </div>

          <button type="submit" name="place_order" class="btn btn-pharmacy btn-lg rounded-pill shadow-sm w-100">
            <i class="bi bi-bag-check-fill me-2"></i> Confirm & Place Order
          </button>

          <div class="text-center mt-3 text-muted small">
            By placing order, you agree to Afridi Pharmacy terms and pharmacist verification process.
          </div>
        </div>

      </div>

    </div>
  </form>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
