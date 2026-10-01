<?php
$activePage = basename($_SERVER['PHP_SELF']);
$db = getDBConnection();

// Pending count badges for admin
$pendingOrdersCount = $db->query("SELECT COUNT(*) FROM orders WHERE order_status = 'Pending'")->fetchColumn();
$pendingRxCount = $db->query("SELECT COUNT(*) FROM prescriptions WHERE status = 'Pending'")->fetchColumn();
$lowStockCount = $db->query("SELECT COUNT(*) FROM products WHERE stock_quantity <= 10 AND status = 'active'")->fetchColumn();
?>

<!-- Admin Sidebar -->
<aside class="admin-sidebar">
  <div class="admin-brand">
    <i class="bi bi-crosshair2 text-teal" style="color: #2dd4bf;"></i>
    <span>AFRIDI <span class="fw-normal text-muted">ADMIN</span></span>
  </div>

  <ul class="admin-nav">
    <li class="admin-nav-item <?= $activePage === 'index.php' ? 'active' : ''; ?>">
      <a href="<?= SITE_URL; ?>admin/index.php"><i class="bi bi-speedometer2"></i> Dashboard</a>
    </li>
    
    <li class="admin-nav-item <?= in_array($activePage, ['products.php', 'add-product.php', 'edit-product.php']) ? 'active' : ''; ?>">
      <a href="<?= SITE_URL; ?>admin/products.php"><i class="bi bi-capsule"></i> Products</a>
    </li>

    <li class="admin-nav-item <?= $activePage === 'categories.php' ? 'active' : ''; ?>">
      <a href="<?= SITE_URL; ?>admin/categories.php"><i class="bi bi-grid-3x3-gap"></i> Categories</a>
    </li>

    <li class="admin-nav-item <?= $activePage === 'brands.php' ? 'active' : ''; ?>">
      <a href="<?= SITE_URL; ?>admin/brands.php"><i class="bi bi-tags"></i> Brands</a>
    </li>

    <li class="admin-nav-item <?= in_array($activePage, ['orders.php', 'order-details.php']) ? 'active' : ''; ?>">
      <a href="<?= SITE_URL; ?>admin/orders.php">
        <i class="bi bi-bag-check"></i> Orders
        <?php if ($pendingOrdersCount > 0): ?>
          <span class="badge bg-warning text-dark ms-auto"><?= $pendingOrdersCount; ?></span>
        <?php endif; ?>
      </a>
    </li>

    <li class="admin-nav-item <?= $activePage === 'prescriptions.php' ? 'active' : ''; ?>">
      <a href="<?= SITE_URL; ?>admin/prescriptions.php">
        <i class="bi bi-file-earmark-medical"></i> Prescriptions
        <?php if ($pendingRxCount > 0): ?>
          <span class="badge bg-danger ms-auto"><?= $pendingRxCount; ?></span>
        <?php endif; ?>
      </a>
    </li>

    <li class="admin-nav-item <?= $activePage === 'inventory.php' ? 'active' : ''; ?>">
      <a href="<?= SITE_URL; ?>admin/inventory.php">
        <i class="bi bi-boxes"></i> Inventory
        <?php if ($lowStockCount > 0): ?>
          <span class="badge bg-danger ms-auto"><?= $lowStockCount; ?> Low</span>
        <?php endif; ?>
      </a>
    </li>

    <li class="admin-nav-item <?= $activePage === 'customers.php' ? 'active' : ''; ?>">
      <a href="<?= SITE_URL; ?>admin/customers.php"><i class="bi bi-people"></i> Customers</a>
    </li>

    <li class="admin-nav-item <?= $activePage === 'coupons.php' ? 'active' : ''; ?>">
      <a href="<?= SITE_URL; ?>admin/coupons.php"><i class="bi bi-ticket-perforated"></i> Coupons</a>
    </li>

    <li class="admin-nav-item <?= $activePage === 'reports.php' ? 'active' : ''; ?>">
      <a href="<?= SITE_URL; ?>admin/reports.php"><i class="bi bi-graph-up-arrow"></i> Reports</a>
    </li>

    <li class="admin-nav-item <?= $activePage === 'settings.php' ? 'active' : ''; ?>">
      <a href="<?= SITE_URL; ?>admin/settings.php"><i class="bi bi-gear"></i> Settings</a>
    </li>
  </ul>

  <div class="p-3 border-top border-secondary">
    <div class="d-flex align-items-center gap-2 mb-2">
      <i class="bi bi-person-circle fs-4 text-teal"></i>
      <div>
        <strong class="d-block text-white small"><?= htmlspecialchars($adminUser['full_name']); ?></strong>
        <span class="text-muted small">Pharmacist Admin</span>
      </div>
    </div>
    <a href="<?= SITE_URL; ?>logout.php" class="btn btn-sm btn-outline-danger w-100 rounded-pill mt-2"><i class="bi bi-box-arrow-right me-1"></i> Logout</a>
    <a href="<?= SITE_URL; ?>index.php" class="btn btn-sm btn-outline-light w-100 rounded-pill mt-2" target="_blank"><i class="bi bi-eye me-1"></i> View Website</a>
  </div>
</aside>
