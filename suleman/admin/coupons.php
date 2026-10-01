<?php
$adminTitle = "Manage Coupons";
require_once __DIR__ . '/includes/admin-header.php';

$db = getDBConnection();

// Add Coupon POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_coupon'])) {
    $code = strtoupper(sanitize($_POST['code'] ?? ''));
    $type = sanitize($_POST['discount_type'] ?? 'percent');
    $value = floatval($_POST['discount_value'] ?? 0);
    $minOrder = floatval($_POST['min_order_amount'] ?? 0);
    $expiry = $_POST['expiry_date'] ?? date('Y-12-31');

    if (!empty($code) && $value > 0) {
        $ins = $db->prepare("INSERT INTO coupons (code, discount_type, discount_value, min_order_amount, expiry_date, status) VALUES (?, ?, ?, ?, ?, 'active')");
        $ins->execute([$code, $type, $value, $minOrder, $expiry]);
        setFlash('success', "Coupon '{$code}' created!");
        header("Location: " . SITE_URL . "admin/coupons.php");
        exit;
    }
}

// Delete Coupon POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_coupon_id'])) {
    $delId = intval($_POST['delete_coupon_id']);
    $del = $db->prepare("DELETE FROM coupons WHERE id = ?");
    $del->execute([$delId]);
    setFlash('success', "Coupon deleted!");
    header("Location: " . SITE_URL . "admin/coupons.php");
    exit;
}

$coupons = $db->query("SELECT * FROM coupons ORDER BY created_at DESC")->fetchAll();
?>

<div class="admin-wrapper">
  <?php require_once __DIR__ . '/includes/admin-sidebar.php'; ?>

  <main class="admin-main">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <div>
        <h2 class="fw-bold mb-1"><i class="bi bi-ticket-perforated me-2 text-warning"></i> Discount Coupons Management</h2>
        <p class="text-muted mb-0">Create promo codes, set percentage or fixed discounts and minimum order limits</p>
      </div>
    </div>

    <?php displayFlash(); ?>

    <div class="row g-4">
      <!-- Add Coupon Form -->
      <div class="col-lg-4">
        <div class="card border-0 shadow-sm rounded-4 p-4">
          <h5 class="fw-bold mb-3">Create New Coupon</h5>
          <form action="" method="POST">
            <div class="mb-3">
              <label class="form-label small fw-bold">Coupon Code *</label>
              <input type="text" name="code" class="form-control text-uppercase fw-bold" placeholder="e.g. SUMMER15" required>
            </div>

            <div class="mb-3">
              <label class="form-label small fw-bold">Discount Type</label>
              <select name="discount_type" class="form-select">
                <option value="percent">Percentage (%)</option>
                <option value="fixed">Fixed Amount (PKR)</option>
              </select>
            </div>

            <div class="mb-3">
              <label class="form-label small fw-bold">Discount Value *</label>
              <input type="number" step="0.01" name="discount_value" class="form-control" placeholder="10" required>
            </div>

            <div class="mb-3">
              <label class="form-label small fw-bold">Minimum Order Amount (PKR)</label>
              <input type="number" step="0.01" name="min_order_amount" class="form-control" value="1000">
            </div>

            <div class="mb-4">
              <label class="form-label small fw-bold">Expiry Date *</label>
              <input type="date" name="expiry_date" class="form-control" value="<?= date('Y-12-31'); ?>" required>
            </div>

            <button type="submit" name="add_coupon" class="btn btn-pharmacy rounded-pill w-100">Create Promo Code</button>
          </form>
        </div>
      </div>

      <!-- Coupons List Table -->
      <div class="col-lg-8">
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
          <div class="table-responsive">
            <table class="table align-middle table-hover mb-0">
              <thead class="table-light">
                <tr>
                  <th class="ps-4">Code</th>
                  <th>Discount</th>
                  <th>Min Order</th>
                  <th>Expiry Date</th>
                  <th>Status</th>
                  <th class="text-end pe-4">Action</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($coupons as $cp): ?>
                  <tr>
                    <td class="ps-4"><span class="badge bg-warning text-dark fs-6 font-monospace px-3 py-1"><?= htmlspecialchars($cp['code']); ?></span></td>
                    <td class="fw-bold">
                      <?= $cp['discount_type'] === 'percent' ? $cp['discount_value'] . '%' : formatCurrency($cp['discount_value']); ?>
                    </td>
                    <td class="small text-muted"><?= formatCurrency($cp['min_order_amount']); ?></td>
                    <td class="small text-muted"><?= date('M d, Y', strtotime($cp['expiry_date'])); ?></td>
                    <td>
                      <span class="badge bg-<?= $cp['status'] === 'active' ? 'success' : 'secondary'; ?> rounded-pill"><?= ucfirst($cp['status']); ?></span>
                    </td>
                    <td class="text-end pe-4">
                      <form action="" method="POST" class="d-inline" onsubmit="return confirm('Delete coupon?');">
                        <input type="hidden" name="delete_coupon_id" value="<?= $cp['id']; ?>">
                        <button type="submit" class="btn btn-sm btn-outline-danger rounded-circle"><i class="bi bi-trash"></i></button>
                      </form>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>

  </main>
</div>

<?php require_once __DIR__ . '/includes/admin-footer.php'; ?>
