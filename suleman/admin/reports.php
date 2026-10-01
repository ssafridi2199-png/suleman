<?php
$adminTitle = "Sales & Inventory Reports";
require_once __DIR__ . '/includes/admin-header.php';

$db = getDBConnection();

// Analytics calculations
$totalRevenue = $db->query("SELECT SUM(total_amount) FROM orders WHERE order_status != 'Cancelled'")->fetchColumn() ?: 0;
$deliveredCount = $db->query("SELECT COUNT(*) FROM orders WHERE order_status = 'Delivered'")->fetchColumn();
$totalItemsSold = $db->query("SELECT SUM(oi.quantity) FROM order_items oi JOIN orders o ON oi.order_id = o.id WHERE o.order_status != 'Cancelled'")->fetchColumn() ?: 0;
$stockValuation = $db->query("SELECT SUM(price * stock_quantity) FROM products WHERE status = 'active'")->fetchColumn() ?: 0;

// Top 5 Best Selling Medicines
$topProducts = $db->query("SELECT oi.product_name, SUM(oi.quantity) AS total_qty, SUM(oi.total_price) AS total_sales FROM order_items oi JOIN orders o ON oi.order_id = o.id WHERE o.order_status != 'Cancelled' GROUP BY oi.product_id ORDER BY total_qty DESC LIMIT 5")->fetchAll();

// Monthly Sales Summary
$monthlySales = $db->query("SELECT DATE_FORMAT(created_at, '%Y-%m') AS month_year, COUNT(id) AS total_orders, SUM(total_amount) AS revenue FROM orders WHERE order_status != 'Cancelled' GROUP BY month_year ORDER BY month_year DESC LIMIT 6")->fetchAll();
?>

<div class="admin-wrapper">
  <?php require_once __DIR__ . '/includes/admin-sidebar.php'; ?>

  <main class="admin-main">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <div>
        <h2 class="fw-bold mb-1"><i class="bi bi-graph-up-arrow me-2 text-teal"></i> Pharmacy Sales & Stock Valuation Reports</h2>
        <p class="text-muted mb-0">Financial metrics, top selling products and revenue reports</p>
      </div>
      <button onclick="window.print()" class="btn btn-outline-secondary rounded-pill btn-sm"><i class="bi bi-printer me-1"></i> Print Financial Summary</button>
    </div>

    <!-- Summary Metrics -->
    <div class="row g-3 mb-4">
      <div class="col-md-3">
        <div class="stat-card">
          <div>
            <span class="text-muted small text-uppercase fw-bold">Total Gross Revenue</span>
            <h3 class="fw-bold mb-0 mt-1 text-teal" style="color: var(--primary-color);"><?= formatCurrency($totalRevenue); ?></h3>
          </div>
          <div class="stat-icon bg-success-subtle text-success"><i class="bi bi-currency-dollar"></i></div>
        </div>
      </div>

      <div class="col-md-3">
        <div class="stat-card">
          <div>
            <span class="text-muted small text-uppercase fw-bold">Delivered Orders</span>
            <h3 class="fw-bold mb-0 mt-1"><?= number_format($deliveredCount); ?></h3>
          </div>
          <div class="stat-icon bg-primary-subtle text-primary"><i class="bi bi-box-seam"></i></div>
        </div>
      </div>

      <div class="col-md-3">
        <div class="stat-card">
          <div>
            <span class="text-muted small text-uppercase fw-bold">Total Units Dispensed</span>
            <h3 class="fw-bold mb-0 mt-1"><?= number_format($totalItemsSold); ?> Units</h3>
          </div>
          <div class="stat-icon bg-info-subtle text-info"><i class="bi bi-capsule"></i></div>
        </div>
      </div>

      <div class="col-md-3">
        <div class="stat-card">
          <div>
            <span class="text-muted small text-uppercase fw-bold">Stock Asset Value</span>
            <h3 class="fw-bold mb-0 mt-1"><?= formatCurrency($stockValuation); ?></h3>
          </div>
          <div class="stat-icon bg-warning-subtle text-warning"><i class="bi bi-bank"></i></div>
        </div>
      </div>
    </div>

    <div class="row g-4">
      <!-- TOP SELLING PRODUCTS -->
      <div class="col-lg-6">
        <div class="card border-0 shadow-sm rounded-4 p-4 h-100">
          <h5 class="fw-bold mb-3"><i class="bi bi-trophy me-2 text-warning"></i> Top Selling Medicines</h5>
          
          <div class="table-responsive">
            <table class="table align-middle">
              <thead class="table-light">
                <tr class="small">
                  <th>Product Name</th>
                  <th>Units Sold</th>
                  <th class="text-end">Total Sales</th>
                </tr>
              </thead>
              <tbody>
                <?php if (empty($topProducts)): ?>
                  <tr><td colspan="3" class="text-center text-muted">No sales data available.</td></tr>
                <?php else: ?>
                  <?php foreach ($topProducts as $tp): ?>
                    <tr class="small">
                      <td class="fw-bold text-dark"><?= htmlspecialchars($tp['product_name']); ?></td>
                      <td><span class="badge bg-light text-dark border"><?= $tp['total_qty']; ?> units</span></td>
                      <td class="text-end fw-bold text-teal" style="color: var(--primary-color);"><?= formatCurrency($tp['total_sales']); ?></td>
                    </tr>
                  <?php endforeach; ?>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <!-- MONTHLY REVENUE REPORT -->
      <div class="col-lg-6">
        <div class="card border-0 shadow-sm rounded-4 p-4 h-100">
          <h5 class="fw-bold mb-3"><i class="bi bi-calendar3 me-2 text-teal"></i> Monthly Revenue Overview</h5>
          
          <div class="table-responsive">
            <table class="table align-middle">
              <thead class="table-light">
                <tr class="small">
                  <th>Month</th>
                  <th>Orders Count</th>
                  <th class="text-end">Monthly Revenue</th>
                </tr>
              </thead>
              <tbody>
                <?php if (empty($monthlySales)): ?>
                  <tr><td colspan="3" class="text-center text-muted">No monthly sales data available.</td></tr>
                <?php else: ?>
                  <?php foreach ($monthlySales as $ms): ?>
                    <tr class="small">
                      <td class="fw-bold text-dark"><?= date('F Y', strtotime($ms['month_year'] . '-01')); ?></td>
                      <td><span class="badge bg-light text-dark border"><?= $ms['total_orders']; ?> orders</span></td>
                      <td class="text-end fw-bold text-teal" style="color: var(--primary-color);"><?= formatCurrency($ms['revenue']); ?></td>
                    </tr>
                  <?php endforeach; ?>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>

  </main>
</div>

<?php require_once __DIR__ . '/includes/admin-footer.php'; ?>
