<?php
$adminTitle = "Manage Brands";
require_once __DIR__ . '/includes/admin-header.php';

$db = getDBConnection();

// Add Brand POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_brand'])) {
    $name = sanitize($_POST['name'] ?? '');
    $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name)));

    if (!empty($name)) {
        $ins = $db->prepare("INSERT INTO brands (name, slug) VALUES (?, ?)");
        $ins->execute([$name, $slug]);
        setFlash('success', "Brand '{$name}' added!");
        header("Location: " . SITE_URL . "admin/brands.php");
        exit;
    }
}

// Delete Brand POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_brand_id'])) {
    $delId = intval($_POST['delete_brand_id']);
    $del = $db->prepare("DELETE FROM brands WHERE id = ?");
    $del->execute([$delId]);
    setFlash('success', "Brand deleted!");
    header("Location: " . SITE_URL . "admin/brands.php");
    exit;
}

$brands = $db->query("SELECT b.*, COUNT(p.id) AS product_count FROM brands b LEFT JOIN products p ON b.id = p.brand_id GROUP BY b.id ORDER BY b.name ASC")->fetchAll();
?>

<div class="admin-wrapper">
  <?php require_once __DIR__ . '/includes/admin-sidebar.php'; ?>

  <main class="admin-main">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <div>
        <h2 class="fw-bold mb-1"><i class="bi bi-tags me-2 text-teal"></i> Pharmaceutical Brands Management</h2>
      </div>
    </div>

    <?php displayFlash(); ?>

    <div class="row g-4">
      <!-- Add Brand Form -->
      <div class="col-lg-4">
        <div class="card border-0 shadow-sm rounded-4 p-4">
          <h5 class="fw-bold mb-3">Add Pharmaceutical Brand</h5>
          <form action="" method="POST">
            <div class="mb-4">
              <label class="form-label small fw-bold">Brand / Manufacturer Name *</label>
              <input type="text" name="name" class="form-control" placeholder="e.g. Novartis" required>
            </div>

            <button type="submit" name="add_brand" class="btn btn-pharmacy rounded-pill w-100">Save Brand</button>
          </form>
        </div>
      </div>

      <!-- Brands Table -->
      <div class="col-lg-8">
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
          <div class="table-responsive">
            <table class="table align-middle table-hover mb-0">
              <thead class="table-light">
                <tr>
                  <th class="ps-4">Brand Name</th>
                  <th>Slug</th>
                  <th>Total Products</th>
                  <th class="text-end pe-4">Action</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($brands as $b): ?>
                  <tr>
                    <td class="ps-4 fw-bold text-dark small"><?= htmlspecialchars($b['name']); ?></td>
                    <td class="small text-muted"><?= htmlspecialchars($b['slug']); ?></td>
                    <td><span class="badge bg-light text-dark border rounded-pill"><?= $b['product_count']; ?> products</span></td>
                    <td class="text-end pe-4">
                      <form action="" method="POST" class="d-inline" onsubmit="return confirm('Delete brand?');">
                        <input type="hidden" name="delete_brand_id" value="<?= $b['id']; ?>">
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
