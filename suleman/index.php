<?php
$pageTitle = "AFRIDI PHARMACY - Your Health, Our Priority";
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';

$db = getDBConnection();

// Fetch Categories
$catStmt = $db->query("SELECT c.*, COUNT(p.id) AS product_count FROM categories c LEFT JOIN products p ON c.id = p.category_id AND p.status = 'active' GROUP BY c.id ORDER BY c.id ASC LIMIT 8");
$categories = $catStmt->fetchAll();

// Fetch Featured Products
$featStmt = $db->query("SELECT p.*, c.name AS category_name, b.name AS brand_name FROM products p JOIN categories c ON p.category_id = c.id LEFT JOIN brands b ON p.brand_id = b.id WHERE p.status = 'active' AND p.is_featured = 1 ORDER BY p.id DESC LIMIT 8");
$featuredProducts = $featStmt->fetchAll();

// Fetch Discount / Special Offer Products
$discStmt = $db->query("SELECT p.*, c.name AS category_name, b.name AS brand_name FROM products p JOIN categories c ON p.category_id = c.id LEFT JOIN brands b ON p.brand_id = b.id WHERE p.status = 'active' AND p.discount_price IS NOT NULL AND p.discount_price > 0 ORDER BY p.id DESC LIMIT 4");
$discountProducts = $discStmt->fetchAll();

// Fetch Latest Customer Reviews
$revStmt = $db->query("SELECT r.*, u.full_name, p.name AS product_name FROM reviews r JOIN users u ON r.user_id = u.id JOIN products p ON r.product_id = p.id WHERE r.status = 'approved' ORDER BY r.created_at DESC LIMIT 3");
$reviews = $revStmt->fetchAll();
?>

<div class="container my-4">

  <!-- HERO BANNER SECTION -->
  <div class="hero-pharmacy">
    <div class="row align-items-center g-4">
      <div class="col-lg-7">
        <span class="hero-badge">
          <i class="bi bi-patch-check-fill text-emerald me-1"></i> PMDC Certified Pharmacy • 100% Genuine Medicines
        </span>
        <h1 class="hero-title">Your Health, Our Top Priority</h1>
        <p class="hero-subtitle">
          Order authentic prescription medicines, daily vitamins, baby care essentials, and medical devices. Fast doorstep delivery by licensed pharmacists.
        </p>
        <div class="d-flex flex-wrap gap-3">
          <a href="<?= SITE_URL; ?>products.php" class="btn btn-pharmacy rounded-pill btn-lg px-4 py-3 shadow">
            <i class="bi bi-bag-plus me-2"></i> Shop Medicines Now
          </a>
          <a href="<?= SITE_URL; ?>prescriptions.php" class="btn btn-light rounded-pill btn-lg px-4 py-3 fw-bold text-teal shadow-sm">
            <i class="bi bi-file-earmark-medical me-2 text-danger"></i> Upload Prescription
          </a>
        </div>
      </div>
      <div class="col-lg-5 d-none d-lg-block">
        <div class="hero-img-wrap">
          <img src="<?= SITE_URL; ?>assets/images/hero-banner.jpg" class="img-fluid" alt="AFRIDI PHARMACY Licensed Pharmacist">
        </div>
      </div>
    </div>
  </div>

  <!-- TRUST FEATURE STRIP -->
  <div class="row g-3 mb-5">
    <div class="col-md-3 col-sm-6">
      <div class="feature-box">
        <div class="feature-icon"><i class="bi bi-shield-check"></i></div>
        <div>
          <h6 class="fw-bold mb-1">100% Authentic</h6>
          <span class="text-muted small">Sourced directly from manufacturers</span>
        </div>
      </div>
    </div>
    <div class="col-md-3 col-sm-6">
      <div class="feature-box">
        <div class="feature-icon"><i class="bi bi-truck"></i></div>
        <div>
          <h6 class="fw-bold mb-1">Express Delivery</h6>
          <span class="text-muted small">Fast & temperature controlled</span>
        </div>
      </div>
    </div>
    <div class="col-md-3 col-sm-6">
      <div class="feature-box">
        <div class="feature-icon"><i class="bi bi-person-badge"></i></div>
        <div>
          <h6 class="fw-bold mb-1">Pharmacist Review</h6>
          <span class="text-muted small">Every Rx prescription verified</span>
        </div>
      </div>
    </div>
    <div class="col-md-3 col-sm-6">
      <div class="feature-box">
        <div class="feature-icon"><i class="bi bi-headset"></i></div>
        <div>
          <h6 class="fw-bold mb-1">24/7 Consultation</h6>
          <span class="text-muted small">Helpline: +92 300 1234567</span>
        </div>
      </div>
    </div>
  </div>

  <!-- BROWSE CATEGORIES -->
  <div class="section-header">
    <div>
      <h2 class="section-title">Healthcare Categories</h2>
      <p class="section-subtitle">Explore by medical specialty or daily healthcare need</p>
    </div>
    <a href="<?= SITE_URL; ?>categories.php" class="btn btn-pharmacy-outline rounded-pill btn-sm">View All Categories <i class="bi bi-arrow-right"></i></a>
  </div>

  <div class="row g-3 mb-5">
    <?php foreach ($categories as $cat): ?>
      <div class="col-lg-3 col-md-4 col-6">
        <a href="<?= SITE_URL; ?>products.php?category=<?= $cat['id']; ?>" class="category-card">
          <div class="category-icon-box">
            <i class="bi <?= htmlspecialchars($cat['icon_class']); ?>"></i>
          </div>
          <div class="category-name"><?= htmlspecialchars($cat['name']); ?></div>
          <div class="category-count"><?= $cat['product_count']; ?> Products</div>
        </a>
      </div>
    <?php endforeach; ?>
  </div>

  <!-- FEATURED PRODUCTS -->
  <div class="section-header">
    <div>
      <h2 class="section-title">Popular & Essential Medicines</h2>
      <p class="section-subtitle">Top rated over-the-counter and prescription products</p>
    </div>
    <a href="<?= SITE_URL; ?>products.php" class="btn btn-pharmacy-outline rounded-pill btn-sm">Shop All Medicines <i class="bi bi-arrow-right"></i></a>
  </div>

  <div class="row g-4 mb-5">
    <?php foreach ($featuredProducts as $product): ?>
      <div class="col-xl-3 col-lg-4 col-md-6">
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
              <a href="<?= SITE_URL; ?>product-details.php?id=<?= $product['id']; ?>" class="btn btn-outline-secondary btn-sm rounded-pill">
                View Details
              </a>
            </div>
          </div>
        </div>
      </div>
    <?php endforeach; ?>
  </div>

  <!-- SPECIAL OFFERS BANNER -->
  <?php if (!empty($discountProducts)): ?>
  <div class="bg-gradient p-4 rounded-4 text-white mb-5 shadow-sm" style="background: linear-gradient(135deg, #0284c7 0%, #0f766e 100%);">
    <div class="row align-items-center">
      <div class="col-md-8">
        <span class="badge bg-warning text-dark font-weight-bold px-3 py-2 rounded-pill mb-2">LIMITED TIME PROMOTION</span>
        <h3 class="fw-bold mb-2">Save Big on Essential Health Supplements</h3>
        <p class="mb-0 text-white-50">Use coupon code <strong class="text-warning">HEALTH10</strong> at checkout for an extra 10% discount on orders over PKR 1,000!</p>
      </div>
      <div class="col-md-4 text-md-end mt-3 mt-md-0">
        <a href="<?= SITE_URL; ?>products.php?on_sale=1" class="btn btn-light rounded-pill px-4 py-2 fw-bold text-primary">Explore Offers <i class="bi bi-arrow-right"></i></a>
      </div>
    </div>
  </div>
  <?php endif; ?>

  <!-- WHY CHOOSE AFRIDI PHARMACY -->
  <div class="card border-0 shadow-sm rounded-4 mb-5 overflow-hidden">
    <div class="card-body p-5">
      <div class="text-center mb-4">
        <h2 class="fw-bold">Why Choose AFRIDI PHARMACY?</h2>
        <p class="text-muted">Pakistan's trusted healthcare partner for authentic medicines and medical supplies</p>
      </div>
      <div class="row g-4 text-center">
        <div class="col-md-4">
          <div class="p-3">
            <div class="feature-icon mx-auto mb-3" style="width: 70px; height: 70px; font-size: 2rem;"><i class="bi bi-shield-check"></i></div>
            <h5 class="fw-bold">100% Licensed & Authentic</h5>
            <p class="text-muted small">Every medicine is sourced directly from certified pharmaceutical distributors and stored under strict cold-chain compliance.</p>
          </div>
        </div>
        <div class="col-md-4">
          <div class="p-3">
            <div class="feature-icon mx-auto mb-3" style="width: 70px; height: 70px; font-size: 2rem;"><i class="bi bi-file-medical"></i></div>
            <h5 class="fw-bold">Pharmacist Prescription Review</h5>
            <p class="text-muted small">Our team of qualified pharmacists double-checks every prescription order to ensure accurate dosage and safety.</p>
          </div>
        </div>
        <div class="col-md-4">
          <div class="p-3">
            <div class="feature-icon mx-auto mb-3" style="width: 70px; height: 70px; font-size: 2rem;"><i class="bi bi-lightning-charge"></i></div>
            <h5 class="fw-bold">Doorstep Same-Day Delivery</h5>
            <p class="text-muted small">Get your urgent medication delivered safely in temperature-controlled packaging within hours across the city.</p>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- CUSTOMER REVIEWS -->
  <?php if (!empty($reviews)): ?>
  <div class="mb-5">
    <div class="section-header">
      <div>
        <h2 class="section-title">Verified Customer Reviews</h2>
        <p class="section-subtitle">Read what patients and customers say about our pharmacy service</p>
      </div>
    </div>
    <div class="row g-4">
      <?php foreach ($reviews as $rev): ?>
        <div class="col-md-4">
          <div class="card border-0 shadow-sm rounded-4 h-100 p-4">
            <div class="d-flex align-items-center gap-1 text-warning mb-3">
              <?php for($i=1; $i<=5; $i++): ?>
                <i class="bi bi-star-fill<?= $i <= $rev['rating'] ? '' : '-half text-muted'; ?>"></i>
              <?php endfor; ?>
            </div>
            <p class="text-dark flex-grow-1 font-italic">"<?= htmlspecialchars($rev['review_text']); ?>"</p>
            <div class="d-flex align-items-center gap-3 pt-3 border-top">
              <div class="bg-teal text-white rounded-circle fw-bold d-flex align-items-center justify-content-center" style="width: 42px; height: 42px; background-color: var(--primary-color);">
                <?= strtoupper(substr($rev['full_name'], 0, 1)); ?>
              </div>
              <div>
                <h6 class="mb-0 fw-bold"><?= htmlspecialchars($rev['full_name']); ?></h6>
                <small class="text-muted">Verified Buyer • <?= htmlspecialchars($rev['product_name']); ?></small>
              </div>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
  <?php endif; ?>

</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
