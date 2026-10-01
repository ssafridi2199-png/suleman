<?php
$adminTitle = "Manage Order Details";
require_once __DIR__ . '/includes/admin-header.php';

$db = getDBConnection();
$orderId = isset($_GET['id']) ? intval($_GET['id']) : 0;

$stmt = $db->prepare("SELECT * FROM orders WHERE id = ?");
$stmt->execute([$orderId]);
$order = $stmt->fetch();

if (!$order) {
    setFlash('danger', 'Order not found.');
    header("Location: " . SITE_URL . "admin/orders.php");
    exit;
}

// Update Order POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_order'])) {
    $status = sanitize($_POST['order_status']);
    $payStatus = sanitize($_POST['payment_status']);
    $tracking = sanitize($_POST['tracking_number'] ?? '');

    $upd = $db->prepare("UPDATE orders SET order_status = ?, payment_status = ?, tracking_number = ? WHERE id = ?");
    $upd->execute([$status, $payStatus, $tracking, $orderId]);

    setFlash('success', 'Order updated successfully!');
    header("Location: " . SITE_URL . "admin/order-details.php?id=" . $orderId);
    exit;
}

// Fetch Items
$items = $db->prepare("SELECT oi.*, p.image FROM order_items oi LEFT JOIN products p ON oi.product_id = p.id WHERE oi.order_id = ?");
$items->execute([$orderId]);
$orderItems = $items->fetchAll();

// Fetch Prescription record if attached
$rx = $db->prepare("SELECT * FROM prescriptions WHERE order_id = ? LIMIT 1");
$rx->execute([$orderId]);
$prescription = $rx->fetch();
?>

<div class="admin-wrapper">
  <?php require_once __DIR__ . '/includes/admin-sidebar.php'; ?>

  <main class="admin-main">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <div>
        <h2 class="fw-bold mb-1"><i class="bi bi-file-text me-2 text-teal"></i> Admin Order Management: <?= htmlspecialchars($order['order_number']); ?></h2>
        <span class="text-muted small">Placed on <?= date('F d, Y \a\t h:i A', strtotime($order['created_at'])); ?></span>
      </div>
      <a href="<?= SITE_URL; ?>admin/orders.php" class="btn btn-outline-secondary rounded-pill">Back to Orders</a>
    </div>

    <?php displayFlash(); ?>

    <div class="row g-4">
      <!-- Order Status Update Form -->
      <div class="col-lg-8">
        <div class="card border-0 shadow-sm rounded-4 p-4 mb-4">
          <h5 class="fw-bold mb-3"><i class="bi bi-gear me-2 text-teal"></i> Update Order Status & Tracking</h5>
          
          <form action="" method="POST">
            <div class="row g-3">
              <div class="col-md-4">
                <label class="form-label small fw-bold">Fulfillment Status</label>
                <select name="order_status" class="form-select">
                  <option value="Pending" <?= $order['order_status'] === 'Pending' ? 'selected' : ''; ?>>Pending</option>
                  <option value="Confirmed" <?= $order['order_status'] === 'Confirmed' ? 'selected' : ''; ?>>Confirmed</option>
                  <option value="Processing" <?= $order['order_status'] === 'Processing' ? 'selected' : ''; ?>>Processing</option>
                  <option value="Shipped" <?= $order['order_status'] === 'Shipped' ? 'selected' : ''; ?>>Shipped</option>
                  <option value="Delivered" <?= $order['order_status'] === 'Delivered' ? 'selected' : ''; ?>>Delivered</option>
                  <option value="Cancelled" <?= $order['order_status'] === 'Cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                </select>
              </div>

              <div class="col-md-4">
                <label class="form-label small fw-bold">Payment Status</label>
                <select name="payment_status" class="form-select">
                  <option value="pending" <?= $order['payment_status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                  <option value="paid" <?= $order['payment_status'] === 'paid' ? 'selected' : ''; ?>>Paid</option>
                  <option value="failed" <?= $order['payment_status'] === 'failed' ? 'selected' : ''; ?>>Failed</option>
                </select>
              </div>

              <div class="col-md-4">
                <label class="form-label small fw-bold">Courier Tracking #</label>
                <input type="text" name="tracking_number" class="form-control" placeholder="TRK-987456" value="<?= htmlspecialchars($order['tracking_number'] ?? ''); ?>">
              </div>

              <div class="col-12 text-end">
                <button type="submit" name="update_order" class="btn btn-pharmacy rounded-pill px-4">Update Order</button>
              </div>
            </div>
          </form>
        </div>

        <!-- Ordered Items Table -->
        <div class="card border-0 shadow-sm rounded-4 p-4 mb-4">
          <h5 class="fw-bold mb-3">Order Items</h5>
          <div class="table-responsive">
            <table class="table align-middle">
              <thead class="table-light">
                <tr>
                  <th>Product</th>
                  <th>Price</th>
                  <th>Qty</th>
                  <th class="text-end">Total</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($orderItems as $it): ?>
                  <tr>
                    <td>
                      <div class="d-flex align-items-center gap-3">
                        <img src="<?= SITE_URL; ?>uploads/products/<?= htmlspecialchars($it['image'] ?? 'panadol-extra.jpg'); ?>" style="width: 45px; height: 45px; object-fit: contain;" class="rounded border">
                        <span class="fw-bold small text-dark"><?= htmlspecialchars($it['product_name']); ?></span>
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

        <!-- Attached Prescription Review -->
        <?php if ($prescription): ?>
          <div class="card border-0 shadow-sm rounded-4 p-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
              <h5 class="fw-bold mb-0 text-danger"><i class="bi bi-file-earmark-medical me-2"></i> Doctor's Prescription Attached</h5>
              <a href="<?= SITE_URL; ?>admin/prescriptions.php" class="btn btn-sm btn-outline-danger rounded-pill">Pharmacist Approval Portal</a>
            </div>

            <div class="d-flex align-items-center gap-3 bg-light p-3 rounded-3">
              <i class="bi bi-file-earmark-pdf fs-1 text-danger"></i>
              <div>
                <strong class="d-block text-dark"><?= htmlspecialchars($prescription['prescription_file']); ?></strong>
                <small class="text-muted">Status: <strong><?= $prescription['status']; ?></strong></small>
              </div>
            </div>
          </div>
        <?php endif; ?>

      </div>

      <!-- Customer Details Sidebar -->
      <div class="col-lg-4">
        <div class="card border-0 shadow-sm rounded-4 p-4 mb-4">
          <h5 class="fw-bold mb-3">Customer Information</h5>
          <strong class="d-block fs-6"><?= htmlspecialchars($order['customer_name']); ?></strong>
          <div class="text-muted small"><i class="bi bi-telephone me-1"></i> <?= htmlspecialchars($order['customer_phone']); ?></div>
          <div class="text-muted small"><i class="bi bi-envelope me-1"></i> <?= htmlspecialchars($order['customer_email']); ?></div>
          <hr>
          <h6 class="fw-bold small">Shipping Address</h6>
          <p class="small text-muted mb-0"><?= nl2br(htmlspecialchars($order['shipping_address'])); ?>, <?= htmlspecialchars($order['city']); ?></p>
        </div>

        <div class="card border-0 shadow-sm rounded-4 p-4">
          <h5 class="fw-bold mb-3">Financial Summary</h5>
          <div class="d-flex justify-content-between mb-2">
            <span class="text-muted">Subtotal</span>
            <span class="fw-bold"><?= formatCurrency($order['subtotal']); ?></span>
          </div>
          <div class="d-flex justify-content-between mb-2">
            <span class="text-muted">Delivery Charge</span>
            <span class="fw-bold"><?= formatCurrency($order['delivery_fee']); ?></span>
          </div>
          <hr>
          <div class="d-flex justify-content-between fs-5 fw-bold text-teal" style="color: var(--primary-color);">
            <span>Total Payable</span>
            <span><?= formatCurrency($order['total_amount']); ?></span>
          </div>
        </div>
      </div>

    </div>
  </main>
</div>

<?php require_once __DIR__ . '/includes/admin-footer.php'; ?>
