<?php
$db = getDBConnection();
$productId = isset($_GET['id']) ? intval($_GET['id']) : 0;

$stmt = $db->prepare("SELECT p.*, c.name AS category_name, c.id AS cat_id, b.name AS brand_name 
                      FROM products p 
                      JOIN categories c ON p.category_id = c.id 
                      LEFT JOIN brands b ON p.brand_id = b.id 
                      WHERE p.id = ? AND p.status = 'active'");
$stmt->execute([$productId]);
$product = $stmt->fetch();

if (!$product) {
    header("Location: " . SITE_URL . "products.php");
    exit;
}

$pageTitle = $product['name'];
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';

// Fetch Related Products
$relStmt = $db->prepare("SELECT p.*, c.name AS category_name, b.name AS brand_name 
                         FROM products p 
                         JOIN categories c ON p.category_id = c.id 
                         LEFT JOIN brands b ON p.brand_id = b.id 
                         WHERE p.category_id = ? AND p.id != ? AND p.status = 'active' 
                         LIMIT 4");
$relStmt->execute([$product['category_id'], $product['id']]);
$relatedProducts = $relStmt->fetchAll();

// Fetch Reviews
$revStmt = $db->prepare("SELECT r.*, u.full_name FROM reviews r JOIN users u ON r.user_id = u.id WHERE r.product_id = ? AND r.status = 'approved' ORDER BY r.created_at DESC");
$revStmt->execute([$product['id']]);
$reviews = $revStmt->fetchAll();

// Handle Review Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_review'])) {
    if (!isLoggedIn()) {
        setFlash('warning', 'Please log in to leave a product review.');
    } else {
        $rating = intval($_POST['rating'] ?? 5);
        $reviewText = sanitize($_POST['review_text'] ?? '');
        $userId = $_SESSION['user_id'];

        if (!empty($reviewText)) {
            $insRev = $db->prepare("INSERT INTO reviews (product_id, user_id, rating, review_text, status) VALUES (?, ?, ?, ?, 'approved')");
            $insRev->execute([$product['id'], $userId, $rating, $reviewText]);
            setFlash('success', 'Thank you! Your product review has been submitted.');
            header("Location: " . SITE_URL . "product-details.php?id=" . $product['id']);
            exit;
        }
    }
}
?>

<div class="container my-4">
  <!-- Breadcrumb -->
  <nav aria-label="breadcrumb" class="mb-3">
    <ol class="breadcrumb">
      <li class="breadcrumb-item"><a href="<?= SITE_URL; ?>">Home</a></li>
      <li class="breadcrumb-item"><a href="<?= SITE_URL; ?>products.php">Products</a></li>
      <li class="breadcrumb-item"><a href="<?= SITE_URL; ?>products.php?category=<?= $product['cat_id']; ?>"><?= htmlspecialchars($product['category_name']); ?></a></li>
      <li class="breadcrumb-item active" aria-current="page"><?= htmlspecialchars($product['name']); ?></li>
    </ol>
  </nav>

  <!-- Product Detail Card -->
  <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-5">
    <div class="card-body p-4 p-md-5">
      <div class="row g-5 align-items-center">
        
        <!-- Product Image -->
        <div class="col-lg-5 text-center">
          <div class="bg-light p-4 rounded-4 position-relative border">
            <?php if ($product['requires_prescription']): ?>
              <span class="badge bg-danger position-absolute top-0 start-0 m-3 px-3 py-2 rounded-pill fs-6">
                <i class="bi bi-file-earmark-medical me-1"></i> Rx Required
              </span>
            <?php endif; ?>
            <img src="<?= SITE_URL; ?>uploads/products/<?= htmlspecialchars($product['image'] ?? 'panadol-extra.jpg'); ?>" class="img-fluid rounded-3 max-vh-50" alt="<?= htmlspecialchars($product['name']); ?>">
          </div>
        </div>

        <!-- Product Info -->
        <div class="col-lg-7">
          <div class="text-uppercase text-muted fw-bold small mb-2"><?= htmlspecialchars($product['category_name']); ?> • <?= htmlspecialchars($product['brand_name'] ?? 'Generic'); ?></div>
          <h1 class="fw-extrabold display-6 mb-3"><?= htmlspecialchars($product['name']); ?></h1>
          
          <div class="d-flex align-items-center gap-2 mb-3">
            <span class="badge bg-light text-dark border">SKU: <?= htmlspecialchars($product['sku']); ?></span>
            <?php if ($product['expiry_date']): ?>
              <span class="badge bg-info-subtle text-info border border-info-subtle"><i class="bi bi-calendar-event me-1"></i> Expiry: <?= date('M Y', strtotime($product['expiry_date'])); ?></span>
            <?php endif; ?>
          </div>

          <!-- Price Display -->
          <div class="d-flex align-items-baseline gap-3 my-4">
            <?php if ($product['discount_price'] && $product['discount_price'] < $product['price']): ?>
              <span class="display-5 fw-bold text-teal" style="color: var(--primary-color);"><?= formatCurrency($product['discount_price']); ?></span>
              <span class="fs-4 text-muted text-decoration-line-through"><?= formatCurrency($product['price']); ?></span>
              <span class="badge bg-warning text-dark fs-6 px-3 py-2 rounded-pill">Save <?= formatCurrency($product['price'] - $product['discount_price']); ?></span>
            <?php else: ?>
              <span class="display-5 fw-bold text-teal" style="color: var(--primary-color);"><?= formatCurrency($product['price']); ?></span>
            <?php endif; ?>
          </div>

          <!-- Stock Status -->
          <div class="mb-4">
            <?php if ($product['stock_quantity'] > 10): ?>
              <span class="badge bg-success-subtle text-success fs-6 px-3 py-2 border border-success-subtle rounded-pill">
                <i class="bi bi-check-circle-fill me-1"></i> In Stock (<?= $product['stock_quantity']; ?> available)
              </span>
            <?php elseif ($product['stock_quantity'] > 0): ?>
              <span class="badge bg-warning-subtle text-warning fs-6 px-3 py-2 border border-warning-subtle rounded-pill">
                <i class="bi bi-exclamation-triangle-fill me-1"></i> Low Stock (Only <?= $product['stock_quantity']; ?> left)
              </span>
            <?php else: ?>
              <span class="badge bg-danger-subtle text-danger fs-6 px-3 py-2 border border-danger-subtle rounded-pill">
                <i class="bi bi-x-circle-fill me-1"></i> Out of Stock
              </span>
            <?php endif; ?>
          </div>

          <!-- Prescription Warning Alert -->
          <?php if ($product['requires_prescription']): ?>
            <div class="alert alert-warning border-warning d-flex align-items-center gap-3 rounded-3 mb-4">
              <i class="bi bi-exclamation-triangle-fill fs-3 text-warning"></i>
              <div>
                <strong>Prescription Required Medicine</strong>
                <p class="mb-0 small text-muted">You will be required to upload a doctor's prescription during checkout before pharmacist approval.</p>
              </div>
            </div>
          <?php endif; ?>

          <!-- Quantity and Action Buttons -->
          <div class="d-flex flex-wrap align-items-center gap-3 my-4">
            <div class="input-group" style="width: 140px;">
              <button class="btn btn-outline-secondary" type="button" onclick="let q = document.getElementById('qty-<?= $product['id']; ?>'); if(q.value > 1) q.value--;">-</button>
              <input type="number" id="qty-<?= $product['id']; ?>" class="form-control text-center font-weight-bold" value="1" min="1" max="<?= $product['stock_quantity']; ?>">
              <button class="btn btn-outline-secondary" type="button" onclick="let q = document.getElementById('qty-<?= $product['id']; ?>'); if(parseInt(q.value) < <?= $product['stock_quantity']; ?>) q.value++;">+</button>
            </div>

            <button class="btn btn-pharmacy btn-lg px-4 rounded-pill btn-add-to-cart" data-product-id="<?= $product['id']; ?>" <?= $product['stock_quantity'] <= 0 ? 'disabled' : ''; ?>>
              <i class="bi bi-cart-plus me-2"></i> Add to Cart
            </button>
          </div>

        </div>
      </div>

      <!-- Description & Usage Tabs -->
      <div class="mt-5 pt-4 border-top">
        <ul class="nav nav-tabs nav-fill border-0 mb-4" id="productTab" role="tablist">
          <li class="nav-item" role="presentation">
            <button class="nav-link active fw-bold border-0 border-bottom border-3 border-teal" id="desc-tab" data-bs-toggle="tab" data-bs-target="#desc-pane" type="button">Product Description</button>
          </li>
          <li class="nav-item" role="presentation">
            <button class="nav-link fw-bold border-0" id="usage-tab" data-bs-toggle="tab" data-bs-target="#usage-pane" type="button">Dosage & Usage Instructions</button>
          </li>
          <li class="nav-item" role="presentation">
            <button class="nav-link fw-bold border-0" id="reviews-tab" data-bs-toggle="tab" data-bs-target="#reviews-pane" type="button">Customer Reviews (<?= count($reviews); ?>)</button>
          </li>
        </ul>

        <div class="tab-content" id="productTabContent">
          <div class="tab-pane fade show active p-3" id="desc-pane">
            <p class="fs-6 leading-relaxed"><?= nl2br(htmlspecialchars($product['description'])); ?></p>
          </div>

          <div class="tab-pane fade p-3" id="usage-pane">
            <?php if (!empty($product['usage_info'])): ?>
              <div class="alert alert-info border-info">
                <h6 class="fw-bold"><i class="bi bi-info-circle me-1"></i> Standard Dosage Guidelines</h6>
                <p class="mb-0"><?= nl2br(htmlspecialchars($product['usage_info'])); ?></p>
              </div>
            <?php else: ?>
              <p class="text-muted">Please consult your healthcare professional or licensed pharmacist for detailed dosage instructions.</p>
            <?php endif; ?>
          </div>

          <div class="tab-pane fade p-3" id="reviews-pane">
            <!-- Review Form -->
            <?php if (isLoggedIn()): ?>
              <div class="card border p-3 rounded-4 mb-4 bg-light">
                <h6 class="fw-bold mb-3">Leave a Review</h6>
                <form action="" method="POST">
                  <div class="row g-3">
                    <div class="col-md-3">
                      <label class="form-label small fw-bold">Rating</label>
                      <select name="rating" class="form-select">
                        <option value="5">5 - Excellent</option>
                        <option value="4">4 - Very Good</option>
                        <option value="3">3 - Average</option>
                        <option value="2">2 - Poor</option>
                        <option value="1">1 - Terrible</option>
                      </select>
                    </div>
                    <div class="col-md-9">
                      <label class="form-label small fw-bold">Your Comments</label>
                      <input type="text" name="review_text" class="form-control" placeholder="Share your experience with this medicine or product..." required>
                    </div>
                    <div class="col-12 text-end">
                      <button type="submit" name="submit_review" class="btn btn-pharmacy btn-sm rounded-pill px-4">Submit Review</button>
                    </div>
                  </div>
                </form>
              </div>
            <?php endif; ?>

            <!-- Reviews List -->
            <?php if (empty($reviews)): ?>
              <p class="text-muted">No reviews yet for this product. Be the first to write a review!</p>
            <?php else: ?>
              <div class="d-flex flex-column gap-3">
                <?php foreach ($reviews as $r): ?>
                  <div class="border-bottom pb-3">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                      <strong class="fw-bold"><?= htmlspecialchars($r['full_name']); ?></strong>
                      <small class="text-muted"><?= date('M d, Y', strtotime($r['created_at'])); ?></small>
                    </div>
                    <div class="text-warning mb-2">
                      <?php for($i=1; $i<=5; $i++): ?>
                        <i class="bi bi-star-fill<?= $i <= $r['rating'] ? '' : '-half text-muted'; ?>"></i>
                      <?php endfor; ?>
                    </div>
                    <p class="mb-0 text-secondary"><?= htmlspecialchars($r['review_text']); ?></p>
                  </div>
                <?php endforeach; ?>
              </div>
            <?php endif; ?>
          </div>
        </div>

      </div>

    </div>
  </div>

  <!-- Related Products -->
  <?php if (!empty($relatedProducts)): ?>
  <div class="mb-5">
    <h3 class="fw-bold mb-4">Related Products in <?= htmlspecialchars($product['category_name']); ?></h3>
    <div class="row g-4">
      <?php foreach ($relatedProducts as $rel): ?>
        <div class="col-md-3 col-sm-6">
          <div class="product-card">
            <div class="product-img-wrap">
              <a href="<?= SITE_URL; ?>product-details.php?id=<?= $rel['id']; ?>">
                <img src="<?= SITE_URL; ?>uploads/products/<?= htmlspecialchars($rel['image'] ?? 'panadol-extra.jpg'); ?>" class="img-fluid" alt="<?= htmlspecialchars($rel['name']); ?>">
              </a>
            </div>
            <div class="product-body">
              <a href="<?= SITE_URL; ?>product-details.php?id=<?= $rel['id']; ?>" class="product-title"><?= htmlspecialchars($rel['name']); ?></a>
              <div class="product-price-wrap">
                <span class="price-current"><?= formatCurrency($rel['discount_price'] ?? $rel['price']); ?></span>
              </div>
              <button class="btn btn-pharmacy btn-sm btn-add-to-cart w-100 mt-2" data-product-id="<?= $rel['id']; ?>">Add to Cart</button>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
  <?php endif; ?>

</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
