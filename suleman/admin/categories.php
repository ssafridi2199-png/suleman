<?php
$adminTitle = "Manage Categories";
require_once __DIR__ . '/includes/admin-header.php';

$db = getDBConnection();

// Add Category POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_category'])) {
    $name = sanitize($_POST['name'] ?? '');
    $description = sanitize($_POST['description'] ?? '');
    $iconClass = sanitize($_POST['icon_class'] ?? 'bi-capsule');
    $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name)));

    if (!empty($name)) {
        $ins = $db->prepare("INSERT INTO categories (name, slug, description, icon_class, status) VALUES (?, ?, ?, ?, 'active')");
        $ins->execute([$name, $slug, $description, $iconClass]);
        setFlash('success', "Category '{$name}' added!");
        header("Location: " . SITE_URL . "admin/categories.php");
        exit;
    }
}

// Delete Category POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_category_id'])) {
    $delId = intval($_POST['delete_category_id']);
    $del = $db->prepare("DELETE FROM categories WHERE id = ?");
    $del->execute([$delId]);
    setFlash('success', "Category deleted!");
    header("Location: " . SITE_URL . "admin/categories.php");
    exit;
}

$categories = $db->query("SELECT c.*, COUNT(p.id) AS product_count FROM categories c LEFT JOIN products p ON c.id = p.category_id GROUP BY c.id ORDER BY c.name ASC")->fetchAll();
?>

<div class="admin-wrapper">
  <?php require_once __DIR__ . '/includes/admin-sidebar.php'; ?>

  <main class="admin-main">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <div>
        <h2 class="fw-bold mb-1"><i class="bi bi-grid-3x3-gap me-2 text-teal"></i> Healthcare Categories Management</h2>
      </div>
    </div>

    <?php displayFlash(); ?>

    <div class="row g-4">
      <!-- Add Category Form -->
      <div class="col-lg-4">
        <div class="card border-0 shadow-sm rounded-4 p-4">
          <h5 class="fw-bold mb-3">Add New Category</h5>
          <form action="" method="POST">
            <div class="mb-3">
              <label class="form-label small fw-bold">Category Name *</label>
              <input type="text" name="name" class="form-control" placeholder="e.g. Eye Care" required>
            </div>

            <div class="mb-3">
              <label class="form-label small fw-bold">Bootstrap Icon Class</label>
              <input type="text" name="icon_class" class="form-control" value="bi-eye" placeholder="bi-eye">
            </div>

            <div class="mb-4">
              <label class="form-label small fw-bold">Description</label>
              <textarea name="description" class="form-control" rows="3" placeholder="Category summary..."></textarea>
            </div>

            <button type="submit" name="add_category" class="btn btn-pharmacy rounded-pill w-100">Save Category</button>
          </form>
        </div>
      </div>

      <!-- Categories Table -->
      <div class="col-lg-8">
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
          <div class="table-responsive">
            <table class="table align-middle table-hover mb-0">
              <thead class="table-light">
                <tr>
                  <th class="ps-4">Icon & Category</th>
                  <th>Description</th>
                  <th>Products</th>
                  <th class="text-end pe-4">Action</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($categories as $cat): ?>
                  <tr>
                    <td class="ps-4">
                      <div class="d-flex align-items-center gap-3">
                        <div class="category-icon-box m-0" style="width: 40px; height: 40px; font-size: 1.2rem;">
                          <i class="bi <?= htmlspecialchars($cat['icon_class']); ?>"></i>
                        </div>
                        <strong class="text-dark small"><?= htmlspecialchars($cat['name']); ?></strong>
                      </div>
                    </td>
                    <td class="small text-muted"><?= htmlspecialchars(substr($cat['description'] ?? '', 0, 50)); ?>...</td>
                    <td><span class="badge bg-teal-subtle text-teal border rounded-pill"><?= $cat['product_count']; ?> products</span></td>
                    <td class="text-end pe-4">
                      <form action="" method="POST" class="d-inline" onsubmit="return confirm('Delete category? Products in this category may be affected.');">
                        <input type="hidden" name="delete_category_id" value="<?= $cat['id']; ?>">
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
