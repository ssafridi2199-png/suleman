<?php
$adminTitle = "Manage Orders";
require_once __DIR__ . '/includes/admin-header.php';

$db = getDBConnection();

// Update Order Status POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $orderId = intval($_POST['order_id']);
    $newStatus = sanitize($_POST['order_status']);
    $trackingNo = sanitize($_POST['tracking_number'] ?? '');

    $upd = $db->prepare("UPDATE orders SET order_status = ?, tracking_number = ? WHERE id = ?");
    $upd->execute([$newStatus, $trackingNo, $orderId]);

    setFlash('success', "Order #{$orderId} status updated to {$newStatus}!");
    header("Location: " . SITE_URL . "admin/orders.php");
    exit;
}

$statusFilter = isset($_GET['status']) ? sanitize($_GET['status']) : '';
$search = isset($_GET['search']) ? sanitize($_GET['search']) : '';

$where = ["1=1"];
$params = [];

if ($statusFilter !== '') {
    $where[] = "o.order_status = ?";
    $params[] = $statusFilter;
}

if ($search !== '') {
    $where[] = "(o.order_number LIKE ? OR o.customer_name LIKE ? OR o.customer_phone LIKE ?)";
    $params[] = "%{$search}%";
    $params[] = "%{$search}%";
    $params[] = "%{$search}%";
}

$whereSQL = implode(" AND ", $where);

$orders = $db->prepare("SELECT o.*, COUNT(oi.id) AS total_items FROM orders o LEFT JOIN order_items oi ON o.id = oi.order_id WHERE {$whereSQL} GROUP BY o.id ORDER BY o.created_at DESC");
$orders->execute($params);
$orderList = $orders->fetchAll();
?>

<div class="admin-wrapper">
  <?php require_once __DIR__ . '/includes/admin-sidebar.php'; ?>

  <main class="admin-main">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <div>
        <h2 class="fw-bold mb-1"><i class="bi bi-bag-check me-2 text-teal"></i> Pharmacy Orders Management</h2>
        <p class="text-muted mb-0">Review pending orders, update delivery status & tracking codes</p>
      </div>
    </div>

    <?php displayFlash(); ?>

    <!-- Filter Bar -->
    <div class="card border-0 shadow-sm rounded-4 p-3 mb-4">
      <form action="" method="GET" class="row g-3 align-items-center">
        <div class="col-md-5">
          <input type="text" name="search" class="form-control" placeholder="Search order #, customer name or phone..." value="<?= htmlspecialchars($search); ?>">
        </div>
        <div class="col-md-4">
          <select name="status" class="form-select" onchange="this.form.submit()">
            <option value="">All Statuses</option>
            <option value="Pending" <?= $statusFilter === 'Pending' ? 'selected' : ''; ?>>Pending</option>
            <option value="Confirmed" <?= $statusFilter === 'Confirmed' ? 'selected' : ''; ?>>Confirmed</option>
            <option value="Processing" <?= $statusFilter === 'Processing' ? 'selected' : ''; ?>>Processing</option>
            <option value="Shipped" <?= $statusFilter === 'Shipped' ? 'selected' : ''; ?>>Shipped</option>
            <option value="Delivered" <?= $statusFilter === 'Delivered' ? 'selected' : ''; ?>>Delivered</option>
            <option value="Cancelled" <?= $statusFilter === 'Cancelled' ? 'selected' : ''; ?>>Cancelled</option>
          </select>
        </div>
        <div class="col-md-3">
          <button type="submit" class="btn btn-pharmacy w-100 rounded-pill">Filter Orders</button>
        </div>
      </form>
    </div>

    <!-- Orders Table -->
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
      <div class="table-responsive">
        <table class="table align-middle table-hover mb-0">
          <thead class="table-light">
            <tr>
              <th class="ps-4">Order # & Date</th>
              <th>Customer Info</th>
              <th>Items & Total</th>
              <th>Payment</th>
              <th>Rx Status</th>
              <th>Order Status</th>
              <th class="text-end pe-4">Action</th>
            </tr>
          </thead>
          <tbody>
            <?php if (empty($orderList)): ?>
              <tr><td colspan="7" class="text-center text-muted py-4">No orders match filter criteria.</td></tr>
            <?php else: ?>
              <?php foreach ($orderList as $ord): 
                $badge = 'bg-secondary';
                if ($ord['order_status'] === 'Confirmed') $badge = 'bg-primary';
                elseif ($ord['order_status'] === 'Processing') $badge = 'bg-info text-dark';
                elseif ($ord['order_status'] === 'Shipped') $badge = 'bg-warning text-dark';
                elseif ($ord['order_status'] === 'Delivered') $badge = 'bg-success';
                elseif ($ord['order_status'] === 'Cancelled') $badge = 'bg-danger';
              ?>
                <tr>
                  <td class="ps-4">
                    <strong class="d-block text-dark small"><?= htmlspecialchars($ord['order_number']); ?></strong>
                    <small class="text-muted"><?= date('M d, Y, h:i A', strtotime($ord['created_at'])); ?></small>
                  </td>
                  <td>
                    <strong class="d-block text-dark small"><?= htmlspecialchars($ord['customer_name']); ?></strong>
                    <small class="text-muted"><?= htmlspecialchars($ord['customer_phone']); ?> • <?= htmlspecialchars($ord['city']); ?></small>
                  </td>
                  <td>
                    <span class="d-block fw-bold small text-teal" style="color: var(--primary-color);"><?= formatCurrency($ord['total_amount']); ?></span>
                    <small class="text-muted"><?= $ord['total_items']; ?> items</small>
                  </td>
                  <td>
                    <span class="badge bg-light text-dark border small"><?= strtoupper($ord['payment_method']); ?></span>
                  </td>
                  <td>
                    <?= $ord['has_prescription'] ? '<span class="badge bg-warning text-dark">Rx Uploaded</span>' : '<span class="badge bg-light text-muted border">None</span>'; ?>
                  </td>
                  <td>
                    <form action="" method="POST" class="d-flex align-items-center gap-1">
                      <input type="hidden" name="order_id" value="<?= $ord['id']; ?>">
                      <select name="order_status" class="form-select form-select-sm py-1 font-weight-bold" onchange="this.form.submit()">
                        <option value="Pending" <?= $ord['order_status'] === 'Pending' ? 'selected' : ''; ?>>Pending</option>
                        <option value="Confirmed" <?= $ord['order_status'] === 'Confirmed' ? 'selected' : ''; ?>>Confirmed</option>
                        <option value="Processing" <?= $ord['order_status'] === 'Processing' ? 'selected' : ''; ?>>Processing</option>
                        <option value="Shipped" <?= $ord['order_status'] === 'Shipped' ? 'selected' : ''; ?>>Shipped</option>
                        <option value="Delivered" <?= $ord['order_status'] === 'Delivered' ? 'selected' : ''; ?>>Delivered</option>
                        <option value="Cancelled" <?= $ord['order_status'] === 'Cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                      </select>
                      <input type="hidden" name="update_status" value="1">
                    </form>
                  </td>
                  <td class="text-end pe-4">
                    <a href="<?= SITE_URL; ?>admin/order-details.php?id=<?= $ord['id']; ?>" class="btn btn-sm btn-outline-primary rounded-pill">Details</a>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>

  </main>
</div>

<?php require_once __DIR__ . '/includes/admin-footer.php'; ?>
