<?php
$pageTitle = "My Orders";
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';

requireLogin();

$db = getDBConnection();
$userId = $_SESSION['user_id'];

$stmt = $db->prepare("SELECT o.*, COUNT(oi.id) AS item_count 
                      FROM orders o 
                      LEFT JOIN order_items oi ON o.id = oi.order_id 
                      WHERE o.user_id = ? 
                      GROUP BY o.id 
                      ORDER BY o.created_at DESC");
$stmt->execute([$userId]);
$orders = $stmt->fetchAll();
?>

<div class="container my-4">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <div>
      <h2 class="fw-bold mb-1"><i class="bi bi-bag-check text-teal"></i> My Orders History</h2>
      <p class="text-muted mb-0">Track live status of your medicine deliveries and past purchases</p>
    </div>
    <a href="<?= SITE_URL; ?>products.php" class="btn btn-pharmacy rounded-pill btn-sm"><i class="bi bi-cart-plus me-1"></i> New Order</a>
  </div>

  <?php if (empty($orders)): ?>
    <div class="card border-0 shadow-sm rounded-4 text-center p-5">
      <div class="display-1 text-muted mb-3"><i class="bi bi-box-seam"></i></div>
      <h4 class="fw-bold">No orders placed yet</h4>
      <p class="text-muted mb-4">You have not placed any medicine orders with Afridi Pharmacy yet.</p>
      <div>
        <a href="<?= SITE_URL; ?>products.php" class="btn btn-pharmacy rounded-pill">Shop Medicines Now</a>
      </div>
    </div>
  <?php else: ?>
    <div class="row g-4">
      <?php foreach ($orders as $ord): 
        $statusBadge = 'bg-secondary';
        if ($ord['order_status'] === 'Confirmed') $statusBadge = 'bg-primary';
        elseif ($ord['order_status'] === 'Processing') $statusBadge = 'bg-info text-dark';
        elseif ($ord['order_status'] === 'Shipped') $statusBadge = 'bg-warning text-dark';
        elseif ($ord['order_status'] === 'Delivered') $statusBadge = 'bg-success';
        elseif ($ord['order_status'] === 'Cancelled') $statusBadge = 'bg-danger';
      ?>
        <div class="col-lg-6">
          <div class="card border-0 shadow-sm rounded-4 p-4 h-100 position-relative">
            <div class="d-flex justify-content-between align-items-center mb-3">
              <div>
                <span class="fw-bold fs-5 text-teal" style="color: var(--primary-color);"><?= htmlspecialchars($ord['order_number']); ?></span>
                <span class="text-muted small ms-2">• <?= date('M d, Y, h:i A', strtotime($ord['created_at'])); ?></span>
              </div>
              <span class="badge <?= $statusBadge; ?> px-3 py-2 rounded-pill fs-6"><?= htmlspecialchars($ord['order_status']); ?></span>
            </div>

            <div class="row g-2 mb-3 small text-muted">
              <div class="col-6"><i class="bi bi-box me-1"></i> Items: <strong><?= $ord['item_count']; ?></strong></div>
              <div class="col-6"><i class="bi bi-credit-card me-1"></i> Method: <strong><?= strtoupper($ord['payment_method']); ?></strong></div>
              <div class="col-6"><i class="bi bi-geo-alt me-1"></i> City: <strong><?= htmlspecialchars($ord['city']); ?></strong></div>
              <div class="col-6">
                <i class="bi bi-file-earmark-medical me-1"></i> Prescription: 
                <strong><?= $ord['has_prescription'] ? '<span class="text-warning fw-bold">Uploaded</span>' : 'None Required'; ?></strong>
              </div>
            </div>

            <div class="d-flex justify-content-between align-items-center pt-3 border-top mt-auto">
              <div>
                <span class="text-muted small d-block">Total Amount:</span>
                <span class="fs-4 fw-bold text-dark"><?= formatCurrency($ord['total_amount']); ?></span>
              </div>
              <a href="<?= SITE_URL; ?>order-details.php?id=<?= $ord['id']; ?>" class="btn btn-outline-secondary rounded-pill btn-sm px-3">
                View Details & Invoice <i class="bi bi-arrow-right ms-1"></i>
              </a>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
