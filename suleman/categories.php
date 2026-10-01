<?php
$pageTitle = "Healthcare Categories";
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';

$db = getDBConnection();
$categories = $db->query("SELECT c.*, COUNT(p.id) AS product_count FROM categories c LEFT JOIN products p ON c.id = p.category_id AND p.status = 'active' WHERE c.status = 'active' GROUP BY c.id ORDER BY c.name ASC")->fetchAll();
?>

<div class="container my-4">
  <!-- Breadcrumb -->
  <nav aria-label="breadcrumb" class="mb-4">
    <ol class="breadcrumb">
      <li class="breadcrumb-item"><a href="<?= SITE_URL; ?>">Home</a></li>
      <li class="breadcrumb-item active" aria-current="page">Healthcare Categories</li>
    </ol>
  </nav>

  <div class="text-center mb-5">
    <h1 class="display-5 fw-extrabold text-dark mb-2">Browse All Healthcare Categories</h1>
    <p class="text-muted fs-5">Find prescription medicines, vitamins, baby supplies, diagnostic tools and emergency care</p>
  </div>

  <div class="row g-4 mb-5">
    <?php foreach ($categories as $cat): ?>
      <div class="col-lg-4 col-md-6">
        <div class="card border-0 shadow-sm rounded-4 h-100 p-4 transition-hover">
          <div class="d-flex align-items-center gap-3 mb-3">
            <div class="category-icon-box m-0" style="width: 64px; height: 64px; font-size: 2rem;">
              <i class="bi <?= htmlspecialchars($cat['icon_class']); ?>"></i>
            </div>
            <div>
              <h4 class="fw-bold mb-1"><?= htmlspecialchars($cat['name']); ?></h4>
              <span class="badge bg-teal-subtle text-teal border rounded-pill px-3 py-1"><?= $cat['product_count']; ?> Available Products</span>
            </div>
          </div>
          <p class="text-muted leading-relaxed flex-grow-1 mb-4"><?= htmlspecialchars($cat['description']); ?></p>
          <div>
            <a href="<?= SITE_URL; ?>products.php?category=<?= $cat['id']; ?>" class="btn btn-pharmacy w-100 rounded-pill">
              Explore <?= htmlspecialchars($cat['name']); ?> <i class="bi bi-arrow-right ms-1"></i>
            </a>
          </div>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
