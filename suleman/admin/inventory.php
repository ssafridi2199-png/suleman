<?php
$adminTitle = "Inventory & Expiry Control";
require_once __DIR__ . '/includes/admin-header.php';

$db = getDBConnection();

// Manual Stock Adjustment POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['adjust_stock'])) {
    $productId = intval($_POST['product_id']);
    $type = sanitize($_POST['type']); // in, out, adjustment
    $qty = intval($_POST['quantity']);
    $notes = sanitize($_POST['notes'] ?? 'Manual Admin Adjustment');

    if ($productId > 0 && $qty > 0) {
        if ($type === 'in') {
            $upd = $db->prepare("UPDATE products SET stock_quantity = stock_quantity + ? WHERE id = ?");
            $upd->execute([$qty, $productId]);
        } elseif ($type === 'out') {
            $upd = $db->prepare("UPDATE products SET stock_quantity = GREATEST(0, stock_quantity - ?) WHERE id = ?");
            $upd->execute([$qty, $productId]);
        }

        $inv = $db->prepare("INSERT INTO inventory (product_id, transaction_type, quantity, notes) VALUES (?, ?, ?, ?)");
        $inv->execute([$productId, $type, $qty, $notes]);

        setFlash('success', 'Stock level adjusted successfully!');
        header("Location: " . SITE_URL . "admin/inventory.php");
        exit;
    }
}

$filter = isset($_GET['filter']) ? sanitize($_GET['filter']) : 'all';

$where = ["p.status = 'active'"];
if ($filter === 'low') {
    $where[] = "p.stock_quantity <= 10 AND p.stock_quantity > 0";
} elseif ($filter === 'out') {
    $where[] = "p.stock_quantity = 0";
} elseif ($filter === 'expiring') {
    $where[] = "p.expiry_date <= DATE_ADD(CURDATE(), INTERVAL 60 DAY) AND p.expiry_date >= CURDATE()";
}

$whereSQL = implode(" AND ", $where);

$inventoryItems = $db->query("SELECT p.*, c.name AS category_name, b.name AS brand_name 
                              FROM products p 
                              JOIN categories c ON p.category_id = c.id 
                              LEFT JOIN brands b ON p.brand_id = b.id 
                              WHERE {$whereSQL} 
                              ORDER BY p.stock_quantity ASC")->fetchAll();

// Inventory log history
$logs = $db->query("SELECT i.*, p.name AS product_name, p.sku FROM inventory i JOIN products p ON i.product_id = p.id ORDER BY i.created_at DESC LIMIT 15")->fetchAll();
?>

<div class="admin-wrapper">
  <?php require_once __DIR__ . '/includes/admin-sidebar.php'; ?>

  <main class="admin-main">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <div>
        <h2 class="fw-bold mb-1"><i class="bi bi-boxes me-2 text-teal"></i> Pharmacy Inventory & Expiry Control</h2>
        <p class="text-muted mb-0">Track real-time stock levels, low-stock alerts and expiration dates</p>
      </div>
      <button class="btn btn-pharmacy rounded-pill" data-bs-toggle="modal" data-bs-target="#stockModal">
        <i class="bi bi-plus-slash-minus me-1"></i> Adjust Stock Level
      </button>
    </div>

    <?php displayFlash(); ?>

    <!-- Filter Pills -->
    <div class="d-flex gap-2 mb-4">
      <a href="?filter=all" class="btn btn-sm rounded-pill <?= $filter === 'all' ? 'btn-teal text-white' : 'btn-outline-secondary'; ?>" style="<?= $filter === 'all' ? 'background-color: var(--primary-color); color: #fff;' : ''; ?>">All Stock Items</a>
      <a href="?filter=low" class="btn btn-sm rounded-pill <?= $filter === 'low' ? 'btn-warning text-dark' : 'btn-outline-warning'; ?>">Low Stock (&le; 10)</a>
      <a href="?filter=out" class="btn btn-sm rounded-pill <?= $filter === 'out' ? 'btn-danger text-white' : 'btn-outline-danger'; ?>">Out of Stock (0)</a>
      <a href="?filter=expiring" class="btn btn-sm rounded-pill <?= $filter === 'expiring' ? 'btn-danger text-white' : 'btn-outline-danger'; ?>">Expiring Soon (&le; 60 Days)</a>
    </div>

    <div class="row g-4 mb-5">
      <div class="col-lg-12">
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
          <div class="table-responsive">
            <table class="table align-middle table-hover mb-0">
              <thead class="table-light">
                <tr>
                  <th class="ps-4">Product Name & SKU</th>
                  <th>Category</th>
                  <th>Stock Quantity</th>
                  <th>Expiry Date</th>
                  <th>Stock Alert</th>
                  <th class="text-end pe-4">Quick Adjust</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($inventoryItems as $item): ?>
                  <tr>
                    <td class="ps-4">
                      <strong class="d-block text-dark small"><?= htmlspecialchars($item['name']); ?></strong>
                      <small class="text-muted">SKU: <?= htmlspecialchars($item['sku']); ?></small>
                    </td>
                    <td class="small text-muted"><?= htmlspecialchars($item['category_name']); ?></td>
                    <td><span class="fw-bold fs-6"><?= $item['stock_quantity']; ?> units</span></td>
                    <td>
                      <?php if ($item['expiry_date']): ?>
                        <?php 
                          $isExpiring = (strtotime($item['expiry_date']) <= strtotime('+60 days'));
                        ?>
                        <span class="small <?= $isExpiring ? 'text-danger fw-bold' : 'text-muted'; ?>">
                          <i class="bi bi-calendar-event me-1"></i> <?= date('M d, Y', strtotime($item['expiry_date'])); ?>
                          <?= $isExpiring ? ' (Expiring)' : ''; ?>
                        </span>
                      <?php else: ?>
                        <span class="text-muted small">N/A</span>
                      <?php endif; ?>
                    </td>
                    <td>
                      <?php if ($item['stock_quantity'] == 0): ?>
                        <span class="badge bg-danger rounded-pill">Out of Stock</span>
                      <?php elseif ($item['stock_quantity'] <= 10): ?>
                        <span class="badge bg-warning text-dark rounded-pill">Low Stock Warning</span>
                      <?php else: ?>
                        <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill">Optimal Stock</span>
                      <?php endif; ?>
                    </td>
                    <td class="text-end pe-4">
                      <a href="<?= SITE_URL; ?>admin/edit-product.php?id=<?= $item['id']; ?>" class="btn btn-sm btn-outline-secondary rounded-pill">Edit Product</a>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>

    <!-- Inventory Logs Table -->
    <h4 class="fw-bold mb-3"><i class="bi bi-journal-text me-2 text-teal"></i> Recent Stock Movement Logs</h4>
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
      <div class="table-responsive">
        <table class="table align-middle table-hover mb-0">
          <thead class="table-light">
            <tr class="small">
              <th class="ps-4">Timestamp</th>
              <th>Product</th>
              <th>Movement Type</th>
              <th>Quantity</th>
              <th>Reason / Notes</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($logs as $l): ?>
              <tr class="small">
                <td class="ps-4 text-muted"><?= date('M d, Y h:i A', strtotime($l['created_at'])); ?></td>
                <td><strong class="text-dark"><?= htmlspecialchars($l['product_name']); ?></strong> (<?= htmlspecialchars($l['sku']); ?>)</td>
                <td>
                  <?php if ($l['transaction_type'] === 'in'): ?>
                    <span class="badge bg-success">Stock Added (+IN)</span>
                  <?php else: ?>
                    <span class="badge bg-danger">Stock Reduced (-OUT)</span>
                  <?php endif; ?>
                </td>
                <td class="fw-bold"><?= $l['quantity']; ?> units</td>
                <td class="text-muted"><?= htmlspecialchars($l['notes'] ?? 'N/A'); ?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>

  </main>
</div>

<!-- Stock Adjustment Modal -->
<div class="modal fade" id="stockModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content rounded-4 border-0">
      <div class="modal-header bg-teal text-white" style="background-color: var(--primary-color);">
        <h5 class="modal-title fw-bold text-white"><i class="bi bi-box-seam me-2"></i> Adjust Product Stock</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form action="" method="POST">
        <div class="modal-body p-4">
          <div class="mb-3">
            <label class="form-label fw-bold small">Select Medicine / Product</label>
            <select name="product_id" class="form-select" required>
              <option value="">Select Item</option>
              <?php foreach ($inventoryItems as $p): ?>
                <option value="<?= $p['id']; ?>"><?= htmlspecialchars($p['name']); ?> (Current: <?= $p['stock_quantity']; ?>)</option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="mb-3">
            <label class="form-label fw-bold small">Transaction Type</label>
            <select name="type" class="form-select" required>
              <option value="in">Add Stock (+ IN)</option>
              <option value="out">Remove / Dispense Stock (- OUT)</option>
            </select>
          </div>

          <div class="mb-3">
            <label class="form-label fw-bold small">Quantity Units</label>
            <input type="number" name="quantity" class="form-control" value="10" min="1" required>
          </div>

          <div class="mb-3">
            <label class="form-label fw-bold small">Adjustment Reason / Notes</label>
            <input type="text" name="notes" class="form-control" placeholder="e.g. Received new distributor batch # 4512">
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-light rounded-pill" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" name="adjust_stock" class="btn btn-pharmacy rounded-pill px-4">Update Inventory</button>
        </div>
      </form>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/includes/admin-footer.php'; ?>
