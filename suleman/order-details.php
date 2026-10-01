<?php
$pageTitle = "Order Details & Invoice";
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';

requireLogin();

$db = getDBConnection();
$userId = $_SESSION['user_id'];
$orderId = isset($_GET['id']) ? intval($_GET['id']) : 0;

$stmt = $db->prepare("SELECT * FROM orders WHERE id = ? AND (user_id = ? OR ? = 'admin')");
$stmt->execute([$orderId, $userId, $_SESSION['user_role']]);
$order = $stmt->fetch();

if (!$order) {
    setFlash('danger', 'Order not found or access denied.');
    header("Location: " . SITE_URL . "orders.php");
    exit;
}

// Fetch Order Items
$itemsStmt = $db->prepare("SELECT oi.*, p.image FROM order_items oi LEFT JOIN products p ON oi.product_id = p.id WHERE oi.order_id = ?");
$itemsStmt->execute([$orderId]);
$items = $itemsStmt->fetchAll();

// Fetch Prescription record if any
$rxStmt = $db->prepare("SELECT * FROM prescriptions WHERE order_id = ? LIMIT 1");
$rxStmt->execute([$orderId]);
$prescription = $rxStmt->fetch();

// Status step mapping for visual timeline progress
$statuses = ['Pending', 'Confirmed', 'Processing', 'Shipped', 'Delivered'];
$currentStatusIdx = array_search($order['order_status'], $statuses);
if ($currentStatusIdx === false) $currentStatusIdx = 0;
?>

<div class="container my-4">
  <!-- Breadcrumb -->
  <nav aria-label="breadcrumb" class="mb-4">
    <ol class="breadcrumb">
      <li class="breadcrumb-item"><a href="<?= SITE_URL; ?>">Home</a></li>
      <li class="breadcrumb-item"><a href="<?= SITE_URL; ?>orders.php">My Orders</a></li>
      <li class="breadcrumb-item active" aria-current="page"><?= htmlspecialchars($order['order_number']); ?></li>
    </ol>
  </nav>

  <!-- Header Card -->
  <div class="card border-0 shadow-sm rounded-4 p-4 mb-4">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
      <div>
        <span class="badge bg-teal mb-2" style="background-color: var(--primary-color);">Pharmacy Order Invoice</span>
        <h2 class="fw-extrabold mb-1"><?= htmlspecialchars($order['order_number']); ?></h2>
        <span class="text-muted small">Placed on <?= date('F d, Y \a\t h:i A', strtotime($order['created_at'])); ?></span>
      </div>
      <div class="text-md-end">
        <span class="d-block small text-muted">Order Status</span>
        <span class="fs-4 fw-bold text-teal" style="color: var(--primary-color);"><?= htmlspecialchars($order['order_status']); ?></span>
        <?php if ($order['tracking_number']): ?>
          <div class="small text-muted mt-1"><i class="bi bi-truck me-1"></i> Tracking #: <strong><?= htmlspecialchars($order['tracking_number']); ?></strong></div>
        <?php endif; ?>
      </div>
    </div>

    <!-- Visual Tracking Progress Bar (If not cancelled) -->
    <?php if ($order['order_status'] !== 'Cancelled'): ?>
      <div class="mt-4 pt-4 border-top">
        <h6 class="fw-bold mb-3"><i class="bi bi-signpost-split me-1 text-teal"></i> Delivery Progress Tracker</h6>
        <div class="progress rounded-pill mb-3" style="height: 12px;">
          <?php 
            $pct = (($currentStatusIdx + 1) / count($statuses)) * 100;
          ?>
          <div class="progress-bar bg-teal progress-bar-striped progress-bar-animated" role="progressbar" style="width: <?= $pct; ?>%; background-color: var(--primary-color);"></div>
        </div>
        
        <div class="d-flex justify-content-between text-center small fw-semibold">
          <?php foreach ($statuses as $idx => $st): ?>
            <div class="<?= $idx <= $currentStatusIdx ? 'text-teal fw-bold' : 'text-muted'; ?>">
              <i class="bi <?= $idx <= $currentStatusIdx ? 'bi-check-circle-fill' : 'bi-circle'; ?> d-block fs-5 mb-1"></i>
              <span><?= $st; ?></span>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
    <?php else: ?>
      <div class="alert alert-danger border-danger mt-3 mb-0">
        <i class="bi bi-x-circle-fill me-1"></i> This order has been cancelled.
      </div>
    <?php endif; ?>
  </div>

  <div class="row g-4">
    <!-- ORDER ITEMS TABLE -->
    <div class="col-lg-8">
      <div class="card border-0 shadow-sm rounded-4 p-4 mb-4">
        <h5 class="fw-bold mb-3"><i class="bi bi-prescription2 me-2 text-teal"></i> Prescribed / Ordered Medicines</h5>
        
        <div class="table-responsive">
          <table class="table align-middle mb-0">
            <thead class="table-light">
              <tr>
                <th>Medicine / Item</th>
                <th>Unit Price</th>
                <th>Qty</th>
                <th class="text-end">Total</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($items as $it): ?>
                <tr>
                  <td>
                    <div class="d-flex align-items-center gap-3">
                      <img src="<?= SITE_URL; ?>uploads/products/<?= htmlspecialchars($it['image'] ?? 'panadol-extra.jpg'); ?>" style="width: 50px; height: 50px; object-fit: contain;" class="rounded border">
                      <span class="fw-bold text-dark"><?= htmlspecialchars($it['product_name']); ?></span>
                    </div>
                  </td>
                  <td><?= formatCurrency($it['unit_price']); ?></td>
                  <td><?= $it['quantity']; ?></td>
                  <td class="text-end fw-bold"><?= formatCurrency($it['total_price']); ?></td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Prescription Review Details (If attached) -->
      <?php if ($prescription): ?>
        <div class="card border-0 shadow-sm rounded-4 p-4">
          <h5 class="fw-bold mb-3"><i class="bi bi-file-earmark-medical me-2 text-teal"></i> Associated Doctor's Prescription</h5>
          
          <div class="d-flex align-items-center justify-content-between p-3 bg-light rounded-3">
            <div class="d-flex align-items-center gap-3">
              <i class="bi bi-file-medical-fill fs-1 text-teal"></i>
              <div>
                <strong class="d-block text-dark"><?= htmlspecialchars($prescription['prescription_file']); ?></strong>
                <small class="text-muted">Uploaded: <?= date('M d, Y', strtotime($prescription['created_at'])); ?></small>
              </div>
            </div>
            <div>
              <?php 
                $pBadge = 'bg-warning text-dark';
                if ($prescription['status'] === 'Approved') $pBadge = 'bg-success';
                elseif ($prescription['status'] === 'Rejected') $pBadge = 'bg-danger';
              ?>
              <span class="badge <?= $pBadge; ?> px-3 py-2 rounded-pill">Pharmacist Status: <?= $prescription['status']; ?></span>
            </div>
          </div>

          <?php if (!empty($prescription['pharmacist_notes'])): ?>
            <div class="alert alert-info border-info mt-3 mb-0">
              <strong>Pharmacist Note:</strong> <?= htmlspecialchars($prescription['pharmacist_notes']); ?>
            </div>
          <?php endif; ?>
        </div>
      <?php endif; ?>

    </div>

    <!-- CUSTOMER & INVOICE SUMMARY SIDEBAR -->
    <div class="col-lg-4">
      <div class="card border-0 shadow-sm rounded-4 p-4 mb-4">
        <h5 class="fw-bold mb-3"><i class="bi bi-receipt me-2 text-teal"></i> Payment Summary</h5>

        <div class="d-flex justify-content-between mb-2">
          <span class="text-muted">Subtotal</span>
          <span class="fw-bold"><?= formatCurrency($order['subtotal']); ?></span>
        </div>

        <div class="d-flex justify-content-between mb-2">
          <span class="text-muted">Delivery Charge</span>
          <span class="fw-bold"><?= formatCurrency($order['delivery_fee']); ?></span>
        </div>

        <hr class="my-2">

        <div class="d-flex justify-content-between fs-5 fw-bold mb-3">
          <span>Total Paid / Payable</span>
          <span class="text-teal" style="color: var(--primary-color);"><?= formatCurrency($order['total_amount']); ?></span>
        </div>

        <div class="bg-light p-3 rounded-3 mb-3 small">
          <div>Payment Method: <strong><?= strtoupper($order['payment_method']); ?></strong></div>
          <div>Payment Status: <strong><?= ucfirst($order['payment_status']); ?></strong></div>
        </div>

        <button onclick="window.print()" class="btn btn-outline-secondary rounded-pill btn-sm w-100">
          <i class="bi bi-printer me-1"></i> Print Invoice
        </button>
      </div>

      <!-- Shipping Info -->
      <div class="card border-0 shadow-sm rounded-4 p-4">
        <h5 class="fw-bold mb-3"><i class="bi bi-geo-alt me-2 text-teal"></i> Delivery Information</h5>
        <div class="small">
          <strong class="d-block fs-6"><?= htmlspecialchars($order['customer_name']); ?></strong>
          <div class="text-muted mb-1"><i class="bi bi-telephone me-1"></i> <?= htmlspecialchars($order['customer_phone']); ?></div>
          <div class="text-muted mb-1"><i class="bi bi-envelope me-1"></i> <?= htmlspecialchars($order['customer_email']); ?></div>
          <hr class="my-2">
          <div class="text-dark">
            <?= nl2br(htmlspecialchars($order['shipping_address'])); ?>, <br>
            <strong><?= htmlspecialchars($order['city']); ?> - <?= htmlspecialchars($order['postal_code']); ?></strong>
          </div>
          <?php if (!empty($order['order_notes'])): ?>
            <div class="mt-2 text-muted fst-italic">Note: "<?= htmlspecialchars($order['order_notes']); ?>"</div>
          <?php endif; ?>
        </div>
      </div>
    </div>

  </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
