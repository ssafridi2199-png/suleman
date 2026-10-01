<?php
$pageTitle = "Shop All Medicines & Products";
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';

$db = getDBConnection();

// Read Filters
$search = isset($_GET['search']) ? sanitize($_GET['search']) : '';
$selectedCat = isset($_GET['category']) ? intval($_GET['category']) : 0;
$selectedBrand = isset($_GET['brand']) ? intval($_GET['brand']) : 0;
$rxFilter = isset($_GET['rx']) ? intval($_GET['rx']) : -1;
$onSale = isset($_GET['on_sale']) ? intval($_GET['on_sale']) : 0;
$sort = isset($_GET['sort']) ? sanitize($_GET['sort']) : 'newest';
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$limit = 12;
$offset = ($page - 1) * $limit;

// Build Dynamic Query
$whereClause = ["p.status = 'active'"];
$params = [];

if ($search !== '') {
    $whereClause[] = "(p.name LIKE ? OR p.description LIKE ? OR p.sku LIKE ? OR b.name LIKE ?)";
    $params[] = "%{$search}%";
    $params[] = "%{$search}%";
    $params[] = "%{$search}%";
    $params[] = "%{$search}%";
}

if ($selectedCat > 0) {
    $whereClause[] = "p.category_id = ?";
    $params[] = $selectedCat;
}

if ($selectedBrand > 0) {
    $whereClause[] = "p.brand_id = ?";
    $params[] = $selectedBrand;
}

if ($rxFilter === 1) {
    $whereClause[] = "p.requires_prescription = 1";
} elseif ($rxFilter === 0) {
    $whereClause[] = "p.requires_prescription = 0";
}

if ($onSale === 1) {
    $whereClause[] = "p.discount_price IS NOT NULL AND p.discount_price > 0";
}

$whereSQL = implode(" AND ", $whereClause);

// Determine Order By
$orderBy = "p.id DESC";
if ($sort === 'price_asc') {
    $orderBy = "COALESCE(p.discount_price, p.price) ASC";
} elseif ($sort === 'price_desc') {
    $orderBy = "COALESCE(p.discount_price, p.price) DESC";
} elseif ($sort === 'name_asc') {
    $orderBy = "p.name ASC";
}

// Count Total matching records
$countSql = "SELECT COUNT(*) FROM products p LEFT JOIN brands b ON p.brand_id = b.id WHERE {$whereSQL}";
$countStmt = $db->prepare($countSql);
$countStmt->execute($params);
$totalProducts = $countStmt->fetchColumn();
$totalPages = ceil($totalProducts / $limit);

// Fetch Products
$sql = "SELECT p.*, c.name AS category_name, b.name AS brand_name 
        FROM products p 
        JOIN categories c ON p.category_id = c.id 
        LEFT JOIN brands b ON p.brand_id = b.id 
        WHERE {$whereSQL} 
        ORDER BY {$orderBy} 
        LIMIT {$limit} OFFSET {$offset}";

$stmt = $db->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll();

// Fetch filter options
$allCats = $db->query("SELECT * FROM categories WHERE status='active' ORDER BY name ASC")->fetchAll();
$allBrands = $db->query("SELECT * FROM brands ORDER BY name ASC")->fetchAll();
?>

<div class="container my-4">
  <!-- Breadcrumb -->
  <nav aria-label="breadcrumb" class="mb-3">
    <ol class="breadcrumb">
      <li class="breadcrumb-item"><a href="<?= SITE_URL; ?>">Home</a></li>
      <li class="breadcrumb-item active" aria-current="page">Shop Products</li>
    </ol>
  </nav>

  <div class="row g-4">
    
    <!-- SIDEBAR FILTERS -->
    <div class="col-lg-3">
      <div class="card border-0 shadow-sm rounded-4 p-3 sticky-top" style="top: 90px;">
        <div class="d-flex justify-content-between align-items-center mb-3">
          <h5 class="fw-bold mb-0"><i class="bi bi-funnel me-1"></i> Filter Products</h5>
          <a href="<?= SITE_URL; ?>products.php" class="btn btn-sm btn-link text-decoration-none">Reset All</a>
        </div>

        <form action="<?= SITE_URL; ?>products.php" method="GET">
          <?php if ($search): ?>
            <input type="hidden" name="search" value="<?= htmlspecialchars($search); ?>">
          <?php endif; ?>

          <!-- Categories Filter -->
          <div class="mb-4">
            <label class="form-label fw-bold small text-uppercase text-muted">Category</label>
            <select name="category" class="form-select" onchange="this.form.submit()">
              <option value="0">All Categories</option>
              <?php foreach ($allCats as $cat): ?>
                <option value="<?= $cat['id']; ?>" <?= $selectedCat == $cat['id'] ? 'selected' : ''; ?>>
                  <?= htmlspecialchars($cat['name']); ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>

          <!-- Brand Filter -->
          <div class="mb-4">
            <label class="form-label fw-bold small text-uppercase text-muted">Brand</label>
            <select name="brand" class="form-select" onchange="this.form.submit()">
              <option value="0">All Brands</option>
              <?php foreach ($allBrands as $b): ?>
                <option value="<?= $b['id']; ?>" <?= $selectedBrand == $b['id'] ? 'selected' : ''; ?>>
                  <?= htmlspecialchars($b['name']); ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>

          <!-- Prescription Required Filter -->
          <div class="mb-4">
            <label class="form-label fw-bold small text-uppercase text-muted">Medicine Type</label>
            <div class="form-check">
              <input class="form-check-input" type="radio" name="rx" id="rx_all" value="-1" <?= $rxFilter === -1 ? 'checked' : ''; ?> onchange="this.form.submit()">
              <label class="form-check-label" for="rx_all">All Items</label>
            </div>
            <div class="form-check">
              <input class="form-check-input" type="radio" name="rx" id="rx_otc" value="0" <?= $rxFilter === 0 ? 'checked' : ''; ?> onchange="this.form.submit()">
              <label class="form-check-label" for="rx_otc">Over-The-Counter (OTC)</label>
            </div>
            <div class="form-check">
              <input class="form-check-input" type="radio" name="rx" id="rx_req" value="1" <?= $rxFilter === 1 ? 'checked' : ''; ?> onchange="this.form.submit()">
              <label class="form-check-label text-danger fw-semibold" for="rx_req">Rx Prescription Required</label>
            </div>
          </div>

          <!-- Discount Filter -->
          <div class="mb-4">
            <div class="form-check form-switch">
              <input class="form-check-input" type="checkbox" name="on_sale" id="on_sale" value="1" <?= $onSale === 1 ? 'checked' : ''; ?> onchange="this.form.submit()">
              <label class="form-check-label fw-semibold text-warning-emphasis" for="on_sale">Discounted Items Only</label>
            </div>
          </div>

          <!-- Sort By -->
          <div class="mb-3">
            <label class="form-label fw-bold small text-uppercase text-muted">Sort By</label>
            <select name="sort" class="form-select" onchange="this.form.submit()">
              <option value="newest" <?= $sort === 'newest' ? 'selected' : ''; ?>>Newest Arrivals</option>
              <option value="price_asc" <?= $sort === 'price_asc' ? 'selected' : ''; ?>>Price: Low to High</option>
              <option value="price_desc" <?= $sort === 'price_desc' ? 'selected' : ''; ?>>Price: High to Low</option>
              <option value="name_asc" <?= $sort === 'name_asc' ? 'selected' : ''; ?>>Product Name: A-Z</option>
            </select>
          </div>

          <button type="submit" class="btn btn-pharmacy w-100 rounded-pill">Apply Filters</button>
        </form>
      </div>
    </div>

    <!-- MAIN PRODUCT LIST -->
    <div class="col-lg-9">
      
      <!-- Header bar with total results -->
      <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center bg-white p-3 rounded-4 shadow-sm mb-4">
        <div>
          <h4 class="fw-bold mb-1">
            <?php if ($search): ?>
              Search Results for "<?= htmlspecialchars($search); ?>"
            <?php else: ?>
              Pharmacy Products Catalog
            <?php endif; ?>
          </h4>
          <span class="text-muted small">Showing <?= count($products); ?> of <?= $totalProducts; ?> items</span>
        </div>
      </div>

      <!-- Products Grid -->
      <?php if (empty($products)): ?>
        <div class="card border-0 shadow-sm rounded-4 text-center p-5">
          <div class="display-1 text-muted mb-3"><i class="bi bi-search"></i></div>
          <h4 class="fw-bold">No products found</h4>
          <p class="text-muted">We couldn't find any products matching your selected search parameters or filters.</p>
          <div>
            <a href="<?= SITE_URL; ?>products.php" class="btn btn-pharmacy rounded-pill">Clear All Filters</a>
          </div>
        </div>
      <?php else: ?>
        <div class="row g-4">
          <?php foreach ($products as $product): ?>
            <div class="col-md-4 col-sm-6">
              <div class="product-card">
                <div class="product-img-wrap">
                  <div class="product-badge-group">
                    <?php if ($product['requires_prescription']): ?>
                      <span class="badge-rx"><i class="bi bi-file-earmark-medical me-1"></i> Rx Required</span>
                    <?php endif; ?>
                    <?php if ($product['discount_price'] && $product['discount_price'] < $product['price']): 
                      $savePercent = round((($product['price'] - $product['discount_price']) / $product['price']) * 100);
                    ?>
                      <span class="badge-discount">-<?= $savePercent; ?>% OFF</span>
                    <?php endif; ?>
                  </div>
                  <a href="<?= SITE_URL; ?>product-details.php?id=<?= $product['id']; ?>">
                    <img src="<?= SITE_URL; ?>uploads/products/<?= htmlspecialchars($product['image'] ?? 'panadol-extra.jpg'); ?>" class="img-fluid" alt="<?= htmlspecialchars($product['name']); ?>">
                  </a>
                </div>
                <div class="product-body">
                  <div class="product-category-text"><?= htmlspecialchars($product['category_name']); ?></div>
                  <a href="<?= SITE_URL; ?>product-details.php?id=<?= $product['id']; ?>" class="product-title"><?= htmlspecialchars($product['name']); ?></a>
                  <div class="product-brand"><?= htmlspecialchars($product['brand_name'] ?? 'Generic'); ?> • SKU: <?= htmlspecialchars($product['sku']); ?></div>
                  
                  <div class="product-price-wrap">
                    <?php if ($product['discount_price'] && $product['discount_price'] < $product['price']): ?>
                      <span class="price-current"><?= formatCurrency($product['discount_price']); ?></span>
                      <span class="price-original"><?= formatCurrency($product['price']); ?></span>
                    <?php else: ?>
                      <span class="price-current"><?= formatCurrency($product['price']); ?></span>
                    <?php endif; ?>
                  </div>

                  <div class="mb-3">
                    <?php if ($product['stock_quantity'] > 10): ?>
                      <span class="stock-status-pill stock-in"><i class="bi bi-check-circle-fill"></i> In Stock (<?= $product['stock_quantity']; ?> units)</span>
                    <?php elseif ($product['stock_quantity'] > 0): ?>
                      <span class="stock-status-pill stock-low"><i class="bi bi-exclamation-circle-fill"></i> Low Stock (<?= $product['stock_quantity']; ?> left)</span>
                    <?php else: ?>
                      <span class="stock-status-pill stock-out"><i class="bi bi-x-circle-fill"></i> Out of Stock</span>
                    <?php endif; ?>
                  </div>

                  <div class="d-grid gap-2">
                    <button class="btn btn-pharmacy btn-add-to-cart w-100" data-product-id="<?= $product['id']; ?>" <?= $product['stock_quantity'] <= 0 ? 'disabled' : ''; ?>>
                      <i class="bi bi-cart-plus me-1"></i> Add to Cart
                    </button>
                    <a href="<?= SITE_URL; ?>product-details.php?id=<?= $product['id']; ?>" class="btn btn-outline-secondary btn-sm rounded-pill">View Details</a>
                  </div>
                </div>
              </div>
            </div>
          <?php endforeach; ?>
        </div>

        <!-- Pagination -->
        <?php if ($totalPages > 1): ?>
          <nav aria-label="Page navigation" class="mt-5">
            <ul class="pagination justify-content-center">
              <li class="page-item <?= $page <= 1 ? 'disabled' : ''; ?>">
                <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $page - 1])); ?>">Previous</a>
              </li>
              <?php for($i = 1; $i <= $totalPages; $i++): ?>
                <li class="page-item <?= $page == $i ? 'active' : ''; ?>">
                  <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $i])); ?>"><?= $i; ?></a>
                </li>
              <?php endfor; ?>
              <li class="page-item <?= $page >= $totalPages ? 'disabled' : ''; ?>">
                <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $page + 1])); ?>">Next</a>
              </li>
            </ul>
          </nav>
        <?php endif; ?>

      <?php endif; ?>

    </div>
  </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
