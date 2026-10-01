<?php
$adminTitle = "Registered Customers";
require_once __DIR__ . '/includes/admin-header.php';

$db = getDBConnection();

$customers = $db->query("SELECT u.*, COUNT(o.id) AS total_orders, SUM(o.total_amount) AS total_spent 
                         FROM users u 
                         LEFT JOIN orders o ON u.id = o.user_id AND o.order_status != 'Cancelled' 
                         WHERE u.role = 'customer' 
                         GROUP BY u.id 
                         ORDER BY u.created_at DESC")->fetchAll();
?>

<div class="admin-wrapper">
  <?php require_once __DIR__ . '/includes/admin-sidebar.php'; ?>

  <main class="admin-main">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <div>
        <h2 class="fw-bold mb-1"><i class="bi bi-people me-2 text-teal"></i> Registered Customers & Patient Accounts</h2>
        <p class="text-muted mb-0">View registered patients, order statistics and contact details</p>
      </div>
    </div>

    <?php displayFlash(); ?>

    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
      <div class="table-responsive">
        <table class="table align-middle table-hover mb-0">
          <thead class="table-light">
            <tr>
              <th class="ps-4">Patient Name</th>
              <th>Email Address</th>
              <th>Phone</th>
              <th>Registered Date</th>
              <th>Total Orders</th>
              <th>Total Spent</th>
            </tr>
          </thead>
          <tbody>
            <?php if (empty($customers)): ?>
              <tr><td colspan="6" class="text-center text-muted py-4">No registered customers found.</td></tr>
            <?php else: ?>
              <?php foreach ($customers as $c): ?>
                <tr>
                  <td class="ps-4">
                    <strong class="d-block text-dark small"><?= htmlspecialchars($c['full_name']); ?></strong>
                  </td>
                  <td class="small text-muted"><?= htmlspecialchars($c['email']); ?></td>
                  <td class="small text-muted"><?= htmlspecialchars($c['phone'] ?? 'N/A'); ?></td>
                  <td class="small text-muted"><?= date('M d, Y', strtotime($c['created_at'])); ?></td>
                  <td><span class="badge bg-light text-dark border rounded-pill"><?= $c['total_orders']; ?> orders</span></td>
                  <td><span class="fw-bold text-teal" style="color: var(--primary-color);"><?= formatCurrency($c['total_spent'] ?? 0); ?></span></td>
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
