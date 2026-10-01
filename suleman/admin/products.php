<?php
$adminTitle = "Manage Products";
require_once __DIR__ . '/includes/admin-header.php';
?>

<div class="admin-wrapper">
  <?php require_once __DIR__ . '/includes/admin-sidebar.php'; ?>

  <main class="admin-main">
    
    <div class="d-flex justify-content-between align-items-center mb-4">
      <div>
        <h2 class="fw-bold mb-1"><i class="bi bi-capsule me-2 text-teal"></i> Product Catalog Management</h2>
        <p class="text-muted mb-0">Add, edit, adjust stock, and update medicine prices</p>
      </div>
      <a href="<?= SITE_URL; ?>admin/add-product.php" class="btn btn-pharmacy rounded-pill">
        <i class="bi bi-plus-lg me-1"></i> Add New Product
      </a>
    </div>

    <?php displayFlash(); ?>

    <?php
    $db = getDBConnection();

    // Handle Delete Product POST
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_product_id'])) {
        $delId = intval($_POST['delete_product_id']);
        $delStmt = $db->prepare("DELETE FROM products WHERE id = ?");
        $delStmt->execute([$delId]);
        setFlash('success', 'Product deleted successfully!');
        header("Location: " . SITE_URL . "admin/products.php");
        exit;
    }

    $search = isset($_GET['search']) ? sanitize($_GET['search']) : '';
    $catId = isset($_GET['category']) ? intval($_GET['category']) : 0;

    $where = ["1=1"];
    $params = [];

    if ($search !== '') {
        $where[] = "(p.name LIKE ? OR p.sku LIKE ?)";
        $params[] = "%{$search}%";
        $params[] = "%{$search}%";
    }

    if ($catId > 0) {
        $where[] = "p.category_id = ?";
        $params[] = $catId;
    }

    $whereSQL = implode(" AND ", $where);

    $sql = "SELECT p.*, c.name AS category_name, b.name AS brand_name 
            FROM products p 
            JOIN categories c ON p.category_id = c.id 
            LEFT JOIN brands b ON p.brand_id = b.id 
            WHERE {$whereSQL} 
            ORDER BY p.id DESC";

    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    $products = $stmt->fetchAll();

    $categories = $db->query("SELECT * FROM categories ORDER BY name ASC")->fetchAll();
    ?>

    <!-- Filter Bar -->
    <div class="card border-0 shadow-sm rounded-4 p-3 mb-4">
      <form action="" method="GET" class="row g-3 align-items-center">
        <div class="col-md-5">
          <input type="text" name="search" class="form-control" placeholder="Search by product name or SKU..." value="<?= htmlspecialchars($search); ?>">
        </div>
        <div class="col-md-4">
          <select name="category" class="form-select" onchange="this.form.submit()">
            <option value="0">All Categories</option>
            <?php foreach ($categories as $c): ?>
              <option value="<?= $c['id']; ?>" <?= $catId == $c['id'] ? 'selected' : ''; ?>><?= htmlspecialchars($c['name']); ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="col-md-3">
          <button type="submit" class="btn btn-pharmacy w-100 rounded-pill"><i class="bi bi-search me-1"></i> Filter</button>
        </div>
      </form>
    </div>

    <!-- Products Data Table -->
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
      <div class="table-responsive">
        <table class="table align-middle table-hover mb-0">
          <thead class="table-light">
            <tr>
              <th class="ps-4">Product Info</th>
              <th>Category & Brand</th>
              <th>Price</th>
              <th>Stock</th>
              <th>Rx Flag</th>
              <th>Status</th>
              <th class="text-end pe-4">Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php if (empty($products)): ?>
              <tr><td colspan="7" class="text-center text-muted py-4">No products found matching filters.</td></tr>
            <?php else: ?>
              <?php foreach ($products as $p): ?>
                <tr>
                  <td class="ps-4">
                    <div class="d-flex align-items-center gap-3">
                      <img src="<?= SITE_URL; ?>uploads/products/<?= htmlspecialchars($p['image'] ?? 'panadol-extra.jpg'); ?>" style="width: 50px; height: 50px; object-fit: contain;" class="rounded border">
                      <div>
                        <strong class="d-block text-dark small"><?= htmlspecialchars($p['name']); ?></strong>
                        <small class="text-muted">SKU: <?= htmlspecialchars($p['sku']); ?></small>
                      </div>
                    </div>
                  </td>
                  <td>
                    <span class="d-block small font-weight-bold text-dark"><?= htmlspecialchars($p['category_name']); ?></span>
                    <small class="text-muted"><?= htmlspecialchars($p['brand_name'] ?? 'Generic'); ?></small>
                  </td>
                  <td>
                    <span class="fw-bold small"><?= formatCurrency($p['discount_price'] ?? $p['price']); ?></span>
                    <?php if ($p['discount_price']): ?>
                      <br><small class="text-muted text-decoration-line-through"><?= formatCurrency($p['price']); ?></small>
                    <?php endif; ?>
                  </td>
                  <td>
                    <?php if ($p['stock_quantity'] <= 10): ?>
                      <span class="badge bg-danger rounded-pill"><?= $p['stock_quantity']; ?> units</span>
                    <?php else: ?>
                      <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill"><?= $p['stock_quantity']; ?> units</span>
                    <?php endif; ?>
                  </td>
                  <td>
                    <?= $p['requires_prescription'] ? '<span class="badge bg-danger">Rx Required</span>' : '<span class="badge bg-light text-muted border">OTC</span>'; ?>
                  </td>
                  <td>
                    <?= $p['status'] === 'active' ? '<span class="badge bg-success">Active</span>' : '<span class="badge bg-secondary">Inactive</span>'; ?>
                  </td>
                  <td class="text-end pe-4">
                    <a href="<?= SITE_URL; ?>admin/edit-product.php?id=<?= $p['id']; ?>" class="btn btn-sm btn-outline-primary rounded-circle" title="Edit Product"><i class="bi bi-pencil"></i></a>
                    <form action="" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this product?');">
                      <input type="hidden" name="delete_product_id" value="<?= $p['id']; ?>">
                      <button type="submit" class="btn btn-sm btn-outline-danger rounded-circle" title="Delete Product"><i class="bi bi-trash"></i></button>
                    </form>
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
