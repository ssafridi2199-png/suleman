<?php
$adminTitle = "Dashboard Overview";
require_once __DIR__ . '/includes/admin-header.php';
?>

<div class="admin-wrapper">
  <?php require_once __DIR__ . '/includes/admin-sidebar.php'; ?>

  <main class="admin-main">
    
    <!-- Top Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
      <div>
        <h2 class="fw-bold mb-1">Pharmacist Admin Dashboard</h2>
        <p class="text-muted mb-0">Overview of pharmacy sales, order fulfillment, prescriptions & inventory</p>
      </div>
      <div>
        <a href="<?= SITE_URL; ?>admin/add-product.php" class="btn btn-pharmacy rounded-pill me-2"><i class="bi bi-plus-lg me-1"></i> Add Product</a>
        <a href="<?= SITE_URL; ?>admin/prescriptions.php" class="btn btn-outline-danger rounded-pill"><i class="bi bi-file-earmark-medical me-1"></i> Review Rx</a>
      </div>
    </div>

    <?php displayFlash(); ?>

    <?php
    $db = getDBConnection();

    // Stats calculations
    $totalSales = $db->query("SELECT SUM(total_amount) FROM orders WHERE order_status != 'Cancelled'")->fetchColumn() ?: 0;
    $totalOrders = $db->query("SELECT COUNT(*) FROM orders")->fetchColumn();
    $pendingOrders = $db->query("SELECT COUNT(*) FROM orders WHERE order_status = 'Pending'")->fetchColumn();
    $totalProducts = $db->query("SELECT COUNT(*) FROM products WHERE status = 'active'")->fetchColumn();
    $totalCustomers = $db->query("SELECT COUNT(*) FROM users WHERE role = 'customer'")->fetchColumn();
    $lowStockCount = $db->query("SELECT COUNT(*) FROM products WHERE stock_quantity <= 10 AND status = 'active'")->fetchColumn();
    $expiringCount = $db->query("SELECT COUNT(*) FROM products WHERE expiry_date <= DATE_ADD(CURDATE(), INTERVAL 60 DAY) AND expiry_date >= CURDATE()")->fetchColumn();
    $pendingRx = $db->query("SELECT COUNT(*) FROM prescriptions WHERE status = 'Pending'")->fetchColumn();

    // Recent 5 Orders
    $recentOrders = $db->query("SELECT * FROM orders ORDER BY created_at DESC LIMIT 5")->fetchAll();

    // Low stock items
    $lowStockItems = $db->query("SELECT p.*, c.name AS category_name FROM products p JOIN categories c ON p.category_id = c.id WHERE p.stock_quantity <= 10 AND p.status = 'active' ORDER BY p.stock_quantity ASC LIMIT 5")->fetchAll();
    ?>

    <!-- METRICS GRID CARDS -->
    <div class="row g-3 mb-4">
      <!-- Total Sales -->
      <div class="col-xl-3 col-md-6">
        <div class="stat-card">
          <div>
            <span class="text-muted small text-uppercase fw-bold">Total Sales</span>
            <h3 class="fw-bold mb-0 mt-1"><?= formatCurrency($totalSales); ?></h3>
          </div>
          <div class="stat-icon bg-success-subtle text-success">
            <i class="bi bi-cash-stack"></i>
          </div>
        </div>
      </div>

      <!-- Total Orders -->
      <div class="col-xl-3 col-md-6">
        <div class="stat-card">
          <div>
            <span class="text-muted small text-uppercase fw-bold">Total Orders</span>
            <h3 class="fw-bold mb-0 mt-1"><?= number_format($totalOrders); ?></h3>
            <small class="text-warning fw-bold"><?= $pendingOrders; ?> Pending</small>
          </div>
          <div class="stat-icon bg-primary-subtle text-primary">
            <i class="bi bi-bag-check"></i>
          </div>
        </div>
      </div>

      <!-- Total Products -->
      <div class="col-xl-3 col-md-6">
        <div class="stat-card">
          <div>
            <span class="text-muted small text-uppercase fw-bold">Active Products</span>
            <h3 class="fw-bold mb-0 mt-1"><?= number_format($totalProducts); ?></h3>
          </div>
          <div class="stat-icon bg-info-subtle text-info">
            <i class="bi bi-capsule"></i>
          </div>
        </div>
      </div>

      <!-- Customers -->
      <div class="col-xl-3 col-md-6">
        <div class="stat-card">
          <div>
            <span class="text-muted small text-uppercase fw-bold">Registered Patients</span>
            <h3 class="fw-bold mb-0 mt-1"><?= number_format($totalCustomers); ?></h3>
          </div>
          <div class="stat-icon bg-purple-subtle text-purple" style="background-color: #f3e8ff; color: #9333ea;">
            <i class="bi bi-people"></i>
          </div>
        </div>
      </div>

      <!-- Low Stock Alerts -->
      <div class="col-xl-4 col-md-6">
        <div class="stat-card border-warning">
          <div>
            <span class="text-muted small text-uppercase fw-bold">Low Stock Warning</span>
            <h3 class="fw-bold mb-0 mt-1 text-danger"><?= number_format($lowStockCount); ?> Items</h3>
            <small class="text-muted">Stock level &le; 10 units</small>
          </div>
          <div class="stat-icon bg-warning-subtle text-warning">
            <i class="bi bi-exclamation-triangle-fill"></i>
          </div>
        </div>
      </div>

      <!-- Expiring Medicines -->
      <div class="col-xl-4 col-md-6">
        <div class="stat-card border-danger">
          <div>
            <span class="text-muted small text-uppercase fw-bold">Expiring Medicines</span>
            <h3 class="fw-bold mb-0 mt-1 text-danger"><?= number_format($expiringCount); ?> Items</h3>
            <small class="text-muted">Expires within 60 days</small>
          </div>
          <div class="stat-icon bg-danger-subtle text-danger">
            <i class="bi bi-calendar-x"></i>
          </div>
        </div>
      </div>

      <!-- Pending Prescriptions -->
      <div class="col-xl-4 col-md-12">
        <div class="stat-card">
          <div>
            <span class="text-muted small text-uppercase fw-bold">Pending Prescriptions</span>
            <h3 class="fw-bold mb-0 mt-1 text-teal" style="color: var(--primary-color);"><?= number_format($pendingRx); ?> Rx Files</h3>
            <small class="text-muted">Awaiting pharmacist verification</small>
          </div>
          <div class="stat-icon bg-teal-subtle text-teal">
            <i class="bi bi-file-earmark-medical"></i>
          </div>
        </div>
      </div>
    </div>

    <div class="row g-4">
      <!-- RECENT ORDERS TABLE -->
      <div class="col-lg-7">
        <div class="card border-0 shadow-sm rounded-4 p-4 h-100">
          <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="fw-bold mb-0"><i class="bi bi-clock-history me-2 text-teal"></i> Recent Pharmacy Orders</h5>
            <a href="<?= SITE_URL; ?>admin/orders.php" class="btn btn-sm btn-link text-decoration-none">View All</a>
          </div>

          <div class="table-responsive">
            <table class="table align-middle table-hover mb-0">
              <thead class="table-light">
                <tr class="small">
                  <th>Order #</th>
                  <th>Customer</th>
                  <th>Total</th>
                  <th>Status</th>
                  <th class="text-end">Action</th>
                </tr>
              </thead>
              <tbody>
                <?php if (empty($recentOrders)): ?>
                  <tr><td colspan="5" class="text-center text-muted">No orders found.</td></tr>
                <?php else: ?>
                  <?php foreach ($recentOrders as $ord): 
                    $badge = 'bg-secondary';
                    if ($ord['order_status'] === 'Confirmed') $badge = 'bg-primary';
                    elseif ($ord['order_status'] === 'Processing') $badge = 'bg-info text-dark';
                    elseif ($ord['order_status'] === 'Shipped') $badge = 'bg-warning text-dark';
                    elseif ($ord['order_status'] === 'Delivered') $badge = 'bg-success';
                    elseif ($ord['order_status'] === 'Cancelled') $badge = 'bg-danger';
                  ?>
                    <tr>
                      <td class="fw-bold small"><?= htmlspecialchars($ord['order_number']); ?></td>
                      <td>
                        <span class="d-block fw-semibold small"><?= htmlspecialchars($ord['customer_name']); ?></span>
                        <small class="text-muted"><?= htmlspecialchars($ord['city']); ?></small>
                      </td>
                      <td class="fw-bold small"><?= formatCurrency($ord['total_amount']); ?></td>
                      <td><span class="badge <?= $badge; ?> rounded-pill small"><?= $ord['order_status']; ?></span></td>
                      <td class="text-end">
                        <a href="<?= SITE_URL; ?>admin/order-details.php?id=<?= $ord['id']; ?>" class="btn btn-sm btn-outline-secondary rounded-pill">Manage</a>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <!-- LOW STOCK ALERTS -->
      <div class="col-lg-5">
        <div class="card border-0 shadow-sm rounded-4 p-4 h-100">
          <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="fw-bold mb-0 text-danger"><i class="bi bi-exclamation-triangle-fill me-2"></i> Low Stock Alerts</h5>
            <a href="<?= SITE_URL; ?>admin/inventory.php" class="btn btn-sm btn-link text-decoration-none">Inventory Portal</a>
          </div>

          <div class="d-flex flex-column gap-3">
            <?php if (empty($lowStockItems)): ?>
              <p class="text-muted small text-center my-4">All medicine stock levels are healthy!</p>
            <?php else: ?>
              <?php foreach ($lowStockItems as $ls): ?>
                <div class="p-3 border rounded-3 d-flex justify-content-between align-items-center bg-light">
                  <div>
                    <strong class="d-block text-dark small"><?= htmlspecialchars($ls['name']); ?></strong>
                    <span class="text-muted small">Category: <?= htmlspecialchars($ls['category_name']); ?> • SKU: <?= htmlspecialchars($ls['sku']); ?></span>
                  </div>
                  <div class="text-end">
                    <span class="badge bg-danger px-3 py-2 rounded-pill"><?= $ls['stock_quantity']; ?> left</span>
                    <a href="<?= SITE_URL; ?>admin/edit-product.php?id=<?= $ls['id']; ?>" class="d-block small text-primary mt-1 text-decoration-none">+ Restock</a>
                  </div>
                </div>
              <?php endforeach; ?>
            <?php endif; ?>
          </div>
        </div>
      </div>

    </div>

  </main>
</div>

<?php require_once __DIR__ . '/includes/admin-footer.php'; ?>
