<?php
$adminTitle = "Add New Product";
require_once __DIR__ . '/includes/admin-header.php';

$db = getDBConnection();

// Fetch Categories & Brands for dropdowns
$categories = $db->query("SELECT * FROM categories ORDER BY name ASC")->fetchAll();
$brands = $db->query("SELECT * FROM brands ORDER BY name ASC")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_product'])) {
    $name = sanitize($_POST['name'] ?? '');
    $categoryId = intval($_POST['category_id'] ?? 0);
    $brandId = !empty($_POST['brand_id']) ? intval($_POST['brand_id']) : null;
    $sku = sanitize($_POST['sku'] ?? '');
    $price = floatval($_POST['price'] ?? 0);
    $discountPrice = !empty($_POST['discount_price']) ? floatval($_POST['discount_price']) : null;
    $stockQuantity = intval($_POST['stock_quantity'] ?? 0);
    $requiresRx = isset($_POST['requires_prescription']) ? 1 : 0;
    $isFeatured = isset($_POST['is_featured']) ? 1 : 0;
    $expiryDate = !empty($_POST['expiry_date']) ? $_POST['expiry_date'] : null;
    $description = sanitize($_POST['description'] ?? '');
    $usageInfo = sanitize($_POST['usage_info'] ?? '');

    $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name)));

    $imageName = 'panadol-extra.jpg';

    // Handle Image Upload
    if (isset($_FILES['product_image']) && $_FILES['product_image']['error'] === UPLOAD_ERR_OK) {
        $fileTmp = $_FILES['product_image']['tmp_name'];
        $origName = $_FILES['product_image']['name'];
        $ext = strtolower(pathinfo($origName, PATHINFO_EXTENSION));

        if (in_array($ext, ['jpg', 'jpeg', 'png', 'webp'])) {
            $imageName = 'prod_' . time() . '_' . rand(100, 999) . '.' . $ext;
            move_uploaded_file($fileTmp, __DIR__ . '/../uploads/products/' . $imageName);
        }
    }

    if (empty($name) || $categoryId <= 0 || empty($sku) || $price <= 0) {
        setFlash('danger', 'Please fill in all required fields (Name, Category, SKU, Price).');
    } else {
        try {
            $ins = $db->prepare("INSERT INTO products 
                (category_id, brand_id, name, slug, sku, description, usage_info, price, discount_price, stock_quantity, requires_prescription, image, expiry_date, is_featured, status) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'active')");
            
            $ins->execute([
                $categoryId, $brandId, $name, $slug, $sku, $description, $usageInfo,
                $price, $discountPrice, $stockQuantity, $requiresRx, $imageName, $expiryDate, $isFeatured
            ]);

            $productId = $db->lastInsertId();

            // Record Initial Inventory Log
            $inv = $db->prepare("INSERT INTO inventory (product_id, transaction_type, quantity, notes) VALUES (?, 'in', ?, 'New product addition')");
            $inv->execute([$productId, $stockQuantity]);

            setFlash('success', "Product '{$name}' added successfully!");
            header("Location: " . SITE_URL . "admin/products.php");
            exit;
        } catch (Exception $e) {
            setFlash('danger', 'Error adding product: ' . $e->getMessage());
        }
    }
}
?>

<div class="admin-wrapper">
  <?php require_once __DIR__ . '/includes/admin-sidebar.php'; ?>

  <main class="admin-main">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <div>
        <h2 class="fw-bold mb-1"><i class="bi bi-plus-circle me-2 text-teal"></i> Add New Pharmacy Product</h2>
        <p class="text-muted mb-0">Enter medicine specs, prescription rules, price and stock</p>
      </div>
      <a href="<?= SITE_URL; ?>admin/products.php" class="btn btn-outline-secondary rounded-pill">Back to Catalog</a>
    </div>

    <?php displayFlash(); ?>

    <div class="card border-0 shadow-sm rounded-4 p-4 p-md-5">
      <form action="" method="POST" enctype="multipart/form-data">
        <div class="row g-4">
          
          <div class="col-md-8">
            <label class="form-label fw-bold">Product Name *</label>
            <input type="text" name="name" class="form-control" placeholder="e.g. Panadol Extra 500mg Tablets" required>
          </div>

          <div class="col-md-4">
            <label class="form-label fw-bold">SKU Code *</label>
            <input type="text" name="sku" class="form-control" placeholder="MED-PAN-001" required>
          </div>

          <div class="col-md-6">
            <label class="form-label fw-bold">Category *</label>
            <select name="category_id" class="form-select" required>
              <option value="">Select Category</option>
              <?php foreach ($categories as $c): ?>
                <option value="<?= $c['id']; ?>"><?= htmlspecialchars($c['name']); ?></option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="col-md-6">
            <label class="form-label fw-bold">Brand / Manufacturer</label>
            <select name="brand_id" class="form-select">
              <option value="">Select Brand (Optional)</option>
              <?php foreach ($brands as $b): ?>
                <option value="<?= $b['id']; ?>"><?= htmlspecialchars($b['name']); ?></option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="col-md-4">
            <label class="form-label fw-bold">Regular Price (PKR) *</label>
            <input type="number" step="0.01" name="price" class="form-control" placeholder="180.00" required>
          </div>

          <div class="col-md-4">
            <label class="form-label fw-bold">Discount Price (PKR)</label>
            <input type="number" step="0.01" name="discount_price" class="form-control" placeholder="160.00">
          </div>

          <div class="col-md-4">
            <label class="form-label fw-bold">Initial Stock Quantity *</label>
            <input type="number" name="stock_quantity" class="form-control" value="50" required>
          </div>

          <div class="col-md-6">
            <label class="form-label fw-bold">Expiry Date</label>
            <input type="date" name="expiry_date" class="form-control">
          </div>

          <div class="col-md-6">
            <label class="form-label fw-bold">Product Image</label>
            <input type="file" name="product_image" class="form-control" accept="image/*">
          </div>

          <div class="col-md-6">
            <div class="form-check form-switch p-3 border rounded-3 bg-light">
              <input class="form-check-input" type="checkbox" name="requires_prescription" id="requires_prescription" value="1">
              <label class="form-check-label fw-bold text-danger" for="requires_prescription">
                <i class="bi bi-file-earmark-medical me-1"></i> Requires Doctor's Prescription (Rx)
              </label>
            </div>
          </div>

          <div class="col-md-6">
            <div class="form-check form-switch p-3 border rounded-3 bg-light">
              <input class="form-check-input" type="checkbox" name="is_featured" id="is_featured" value="1" checked>
              <label class="form-check-label fw-bold text-teal" for="is_featured">
                Feature on Homepage
              </label>
            </div>
          </div>

          <div class="col-12">
            <label class="form-label fw-bold">Description & Indications</label>
            <textarea name="description" class="form-control" rows="3" placeholder="Detailed product summary, active ingredients..."></textarea>
          </div>

          <div class="col-12">
            <label class="form-label fw-bold">Usage Instructions & Dosage</label>
            <textarea name="usage_info" class="form-control" rows="2" placeholder="e.g. Take 1 tablet every 6 hours after meals..."></textarea>
          </div>

          <div class="col-12">
            <button type="submit" name="save_product" class="btn btn-pharmacy btn-lg rounded-pill px-5">
              <i class="bi bi-check-circle me-1"></i> Save & Add Product
            </button>
          </div>

        </div>
      </form>
    </div>
  </main>
</div>

<?php require_once __DIR__ . '/includes/admin-footer.php'; ?>
