<?php
$pageTitle = "Shopping Cart";
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';

$db = getDBConnection();
$userId = $_SESSION['user_id'] ?? null;
$sessionId = session_id();

// Fetch Cart items
if ($userId) {
    $stmt = $db->prepare("SELECT c.id AS cart_id, c.quantity, p.*, cat.name AS category_name 
                          FROM cart c 
                          JOIN products p ON c.product_id = p.id 
                          JOIN categories cat ON p.category_id = cat.id 
                          WHERE c.user_id = ?");
    $stmt->execute([$userId]);
} else {
    $stmt = $db->prepare("SELECT c.id AS cart_id, c.quantity, p.*, cat.name AS category_name 
                          FROM cart c 
                          JOIN products p ON c.product_id = p.id 
                          JOIN categories cat ON p.category_id = cat.id 
                          WHERE c.session_id = ?");
    $stmt->execute([$sessionId]);
}

$cartItems = $stmt->fetchAll();

$subtotal = 0;
$hasRxProduct = false;

foreach ($cartItems as $item) {
    $unitPrice = getEffectivePrice($item['price'], $item['discount_price']);
    $subtotal += $unitPrice * $item['quantity'];
    if ($item['requires_prescription']) {
        $hasRxProduct = true;
    }
}

$deliveryFee = ($subtotal >= FREE_SHIPPING_THRESHOLD || $subtotal == 0) ? 0.00 : DELIVERY_FEE;
$totalAmount = $subtotal + $deliveryFee;
?>

<div class="container my-4">
  <!-- Breadcrumb -->
  <nav aria-label="breadcrumb" class="mb-4">
    <ol class="breadcrumb">
      <li class="breadcrumb-item"><a href="<?= SITE_URL; ?>">Home</a></li>
      <li class="breadcrumb-item active" aria-current="page">Shopping Cart</li>
    </ol>
  </nav>

  <h2 class="fw-bold mb-4"><i class="bi bi-cart3 me-2 text-teal"></i> Your Pharmacy Cart</h2>

  <?php if (empty($cartItems)): ?>
    <div class="card border-0 shadow-sm rounded-4 text-center p-5">
      <div class="display-1 text-muted mb-3"><i class="bi bi-cart-x"></i></div>
      <h3 class="fw-bold">Your cart is currently empty</h3>
      <p class="text-muted fs-5 mb-4">You haven't added any medicines or health products to your cart yet.</p>
      <div>
        <a href="<?= SITE_URL; ?>products.php" class="btn btn-pharmacy btn-lg rounded-pill px-4">
          <i class="bi bi-bag-plus me-2"></i> Browse & Shop Medicines
        </a>
      </div>
    </div>
  <?php else: ?>

    <div class="row g-4">
      
      <!-- CART ITEMS TABLE -->
      <div class="col-lg-8">
        
        <?php if ($hasRxProduct): ?>
          <div class="alert alert-warning border-warning d-flex align-items-center gap-3 rounded-4 p-3 mb-4 shadow-sm">
            <i class="bi bi-file-earmark-medical-fill fs-2 text-danger"></i>
            <div>
              <h6 class="fw-bold text-dark mb-1">Prescription Required Items in Cart</h6>
              <p class="mb-0 small text-muted">Your order contains medicines that require pharmacist verification. You will be prompted to upload or select a prescription during checkout.</p>
            </div>
          </div>
        <?php endif; ?>

        <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
          <div class="table-responsive">
            <table class="table align-middle mb-0">
              <thead class="table-light">
                <tr>
                  <th scope="col" class="py-3 ps-4">Product</th>
                  <th scope="col" class="py-3">Price</th>
                  <th scope="col" class="py-3">Quantity</th>
                  <th scope="col" class="py-3">Total</th>
                  <th scope="col" class="py-3 text-end pe-4">Action</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($cartItems as $item): 
                  $effectivePrice = getEffectivePrice($item['price'], $item['discount_price']);
                  $itemTotal = $effectivePrice * $item['quantity'];
                ?>
                  <tr>
                    <td class="ps-4 py-3">
                      <div class="d-flex align-items-center gap-3">
                        <img src="<?= SITE_URL; ?>uploads/products/<?= htmlspecialchars($item['image'] ?? 'panadol-extra.jpg'); ?>" class="rounded-3 border" style="width: 60px; height: 60px; object-fit: contain;" alt="<?= htmlspecialchars($item['name']); ?>">
                        <div>
                          <a href="<?= SITE_URL; ?>product-details.php?id=<?= $item['id']; ?>" class="fw-bold text-dark text-decoration-none d-block mb-1">
                            <?= htmlspecialchars($item['name']); ?>
                          </a>
                          <div class="d-flex align-items-center gap-2">
                            <span class="badge bg-light text-muted border small"><?= htmlspecialchars($item['category_name']); ?></span>
                            <?php if ($item['requires_prescription']): ?>
                              <span class="badge bg-danger small">Rx</span>
                            <?php endif; ?>
                          </div>
                        </div>
                      </div>
                    </td>
                    <td>
                      <span class="fw-bold"><?= formatCurrency($effectivePrice); ?></span>
                      <?php if ($item['discount_price'] && $item['discount_price'] < $item['price']): ?>
                        <br><small class="text-muted text-decoration-line-through"><?= formatCurrency($item['price']); ?></small>
                      <?php endif; ?>
                    </td>
                    <td>
                      <div class="input-group input-group-sm" style="width: 110px;">
                        <button class="btn btn-outline-secondary btn-qty-minus" type="button">-</button>
                        <input type="text" class="form-control text-center font-weight-bold" data-cart-id="<?= $item['cart_id']; ?>" value="<?= $item['quantity']; ?>" readonly>
                        <button class="btn btn-outline-secondary btn-qty-plus" type="button">+</button>
                      </div>
                    </td>
                    <td>
                      <span class="fw-bold text-teal" style="color: var(--primary-color);"><?= formatCurrency($itemTotal); ?></span>
                    </td>
                    <td class="text-end pe-4">
                      <button type="button" class="btn btn-sm btn-outline-danger border-0 rounded-circle" onclick="triggerCartRemove(<?= $item['cart_id']; ?>)" title="Remove Item">
                        <i class="bi bi-trash fs-5"></i>
                      </button>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
          <div class="card-footer bg-white p-3 d-flex justify-content-between align-items-center">
            <a href="<?= SITE_URL; ?>products.php" class="btn btn-outline-secondary rounded-pill btn-sm">
              <i class="bi bi-arrow-left me-1"></i> Continue Shopping
            </a>
          </div>
        </div>
      </div>

      <!-- ORDER SUMMARY SIDEBAR -->
      <div class="col-lg-4">
        <div class="card border-0 shadow-sm rounded-4 p-4 sticky-top" style="top: 90px;">
          <h5 class="fw-bold mb-3">Order Summary</h5>
          
          <div class="d-flex justify-content-between mb-2">
            <span class="text-muted">Items Subtotal</span>
            <span class="fw-bold"><?= formatCurrency($subtotal); ?></span>
          </div>

          <div class="d-flex justify-content-between mb-2">
            <span class="text-muted">Estimated Delivery Fee</span>
            <?php if ($deliveryFee == 0): ?>
              <span class="text-success fw-bold">FREE</span>
            <?php else: ?>
              <span class="fw-bold"><?= formatCurrency($deliveryFee); ?></span>
            <?php endif; ?>
          </div>

          <?php if ($subtotal < FREE_SHIPPING_THRESHOLD): ?>
            <div class="alert alert-info py-2 px-3 small rounded-3 mb-3">
              <i class="bi bi-truck me-1"></i> Add <strong><?= formatCurrency(FREE_SHIPPING_THRESHOLD - $subtotal); ?></strong> more for FREE shipping!
            </div>
          <?php endif; ?>

          <hr class="my-3">

          <div class="d-flex justify-content-between mb-4">
            <span class="fs-5 fw-bold">Total Amount</span>
            <span class="fs-4 fw-extrabold text-teal" style="color: var(--primary-color);"><?= formatCurrency($totalAmount); ?></span>
          </div>

          <div class="d-grid gap-2">
            <a href="<?= SITE_URL; ?>checkout.php" class="btn btn-pharmacy btn-lg rounded-pill shadow-sm">
              Proceed to Checkout <i class="bi bi-arrow-right ms-1"></i>
            </a>
          </div>

          <div class="mt-4 pt-3 border-top text-center text-muted small">
            <i class="bi bi-shield-lock me-1 text-success"></i> Safe & Encrypted Checkout
          </div>
        </div>
      </div>

    </div>

  <?php endif; ?>
</div>

<script>
function triggerCartRemove(cartId) {
    if (confirm('Are you sure you want to remove this item from your cart?')) {
        const formData = new FormData();
        formData.append('action', 'remove_cart');
        formData.append('cart_id', cartId);
        fetch('cart-action.php', { method: 'POST', body: formData })
        .then(res => res.json())
        .then(data => { window.location.reload(); });
    }
}
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
