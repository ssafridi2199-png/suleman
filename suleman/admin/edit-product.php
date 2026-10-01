<?php
$adminTitle = "Edit Product";
require_once __DIR__ . '/includes/admin-header.php';

$db = getDBConnection();
$productId = isset($_GET['id']) ? intval($_GET['id']) : 0;

$stmt = $db->prepare("SELECT * FROM products WHERE id = ?");
$stmt->execute([$productId]);
$product = $stmt->fetch();

if (!$product) {
    setFlash('danger', 'Product not found.');
    header("Location: " . SITE_URL . "admin/products.php");
    exit;
}

$categories = $db->query("SELECT * FROM categories ORDER BY name ASC")->fetchAll();
$brands = $db->query("SELECT * FROM brands ORDER BY name ASC")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_product'])) {
    $name = sanitize($_POST['name'] ?? '');
    $categoryId = intval($_POST['category_id'] ?? 0);
    $brandId = !empty($_POST['brand_id']) ? intval($_POST['brand_id']) : null;
    $sku = sanitize($_POST['sku'] ?? '');
    $price = floatval($_POST['price'] ?? 0);
    $discountPrice = !empty($_POST['discount_price']) ? floatval($_POST['discount_price']) : null;
    $stockQuantity = intval($_POST['stock_quantity'] ?? 0);
    $requiresRx = isset($_POST['requires_prescription']) ? 1 : 0;
    $isFeatured = isset($_POST['is_featured']) ? 1 : 0;
    $status = sanitize($_POST['status'] ?? 'active');
    $expiryDate = !empty($_POST['expiry_date']) ? $_POST['expiry_date'] : null;
    $description = sanitize($_POST['description'] ?? '');
    $usageInfo = sanitize($_POST['usage_info'] ?? '');

    $imageName = $product['image'];

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
        setFlash('danger', 'Please fill in all required fields.');
    } else {
        $upd = $db->prepare("UPDATE products SET 
            category_id = ?, brand_id = ?, name = ?, sku = ?, description = ?, usage_info = ?, 
            price = ?, discount_price = ?, stock_quantity = ?, requires_prescription = ?, 
            image = ?, expiry_date = ?, is_featured = ?, status = ? 
            WHERE id = ?");

        $upd->execute([
            $categoryId, $brandId, $name, $sku, $description, $usageInfo,
            $price, $discountPrice, $stockQuantity, $requiresRx,
            $imageName, $expiryDate, $isFeatured, $status, $productId
        ]);

        // Record stock adjustment if quantity changed
        if ($stockQuantity != $product['stock_quantity']) {
            $diff = $stockQuantity - $product['stock_quantity'];
            $type = $diff > 0 ? 'in' : 'out';
            $inv = $db->prepare("INSERT INTO inventory (product_id, transaction_type, quantity, notes) VALUES (?, ?, ?, 'Admin stock adjustment')");
            $inv->execute([$productId, $type, abs($diff)]);
        }

        setFlash('success', "Product '{$name}' updated successfully!");
        header("Location: " . SITE_URL . "admin/products.php");
        exit;
    }
}
?>

<div class="admin-wrapper">
  <?php require_once __DIR__ . '/includes/admin-sidebar.php'; ?>

  <main class="admin-main">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <div>
        <h2 class="fw-bold mb-1"><i class="bi bi-pencil-square me-2 text-teal"></i> Edit Product: <?= htmlspecialchars($product['name']); ?></h2>
      </div>
      <a href="<?= SITE_URL; ?>admin/products.php" class="btn btn-outline-secondary rounded-pill">Back to Catalog</a>
    </div>

    <?php displayFlash(); ?>

    <div class="card border-0 shadow-sm rounded-4 p-4 p-md-5">
      <form action="" method="POST" enctype="multipart/form-data">
        <div class="row g-4">
          
          <div class="col-md-8">
            <label class="form-label fw-bold">Product Name *</label>
            <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($product['name']); ?>" required>
          </div>

          <div class="col-md-4">
            <label class="form-label fw-bold">SKU Code *</label>
            <input type="text" name="sku" class="form-control" value="<?= htmlspecialchars($product['sku']); ?>" required>
          </div>

          <div class="col-md-6">
            <label class="form-label fw-bold">Category *</label>
            <select name="category_id" class="form-select" required>
              <?php foreach ($categories as $c): ?>
                <option value="<?= $c['id']; ?>" <?= $product['category_id'] == $c['id'] ? 'selected' : ''; ?>><?= htmlspecialchars($c['name']); ?></option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="col-md-6">
            <label class="form-label fw-bold">Brand</label>
            <select name="brand_id" class="form-select">
              <option value="">Select Brand</option>
              <?php foreach ($brands as $b): ?>
                <option value="<?= $b['id']; ?>" <?= $product['brand_id'] == $b['id'] ? 'selected' : ''; ?>><?= htmlspecialchars($b['name']); ?></option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="col-md-4">
            <label class="form-label fw-bold">Price (PKR) *</label>
            <input type="number" step="0.01" name="price" class="form-control" value="<?= $product['price']; ?>" required>
          </div>

          <div class="col-md-4">
            <label class="form-label fw-bold">Discount Price (PKR)</label>
            <input type="number" step="0.01" name="discount_price" class="form-control" value="<?= $product['discount_price']; ?>">
          </div>

          <div class="col-md-4">
            <label class="form-label fw-bold">Stock Quantity *</label>
            <input type="number" name="stock_quantity" class="form-control" value="<?= $product['stock_quantity']; ?>" required>
          </div>

          <div class="col-md-4">
            <label class="form-label fw-bold">Status</label>
            <select name="status" class="form-select">
              <option value="active" <?= $product['status'] === 'active' ? 'selected' : ''; ?>>Active</option>
              <option value="inactive" <?= $product['status'] === 'inactive' ? 'selected' : ''; ?>>Inactive</option>
            </select>
          </div>

          <div class="col-md-4">
            <label class="form-label fw-bold">Expiry Date</label>
            <input type="date" name="expiry_date" class="form-control" value="<?= $product['expiry_date']; ?>">
          </div>

          <div class="col-md-4">
            <label class="form-label fw-bold">Replace Image</label>
            <input type="file" name="product_image" class="form-control" accept="image/*">
          </div>

          <div class="col-md-6">
            <div class="form-check form-switch p-3 border rounded-3 bg-light">
              <input class="form-check-input" type="checkbox" name="requires_prescription" id="requires_prescription" value="1" <?= $product['requires_prescription'] ? 'checked' : ''; ?>>
              <label class="form-check-label fw-bold text-danger" for="requires_prescription">Requires Doctor's Prescription (Rx)</label>
            </div>
          </div>

          <div class="col-md-6">
            <div class="form-check form-switch p-3 border rounded-3 bg-light">
              <input class="form-check-input" type="checkbox" name="is_featured" id="is_featured" value="1" <?= $product['is_featured'] ? 'checked' : ''; ?>>
              <label class="form-check-label fw-bold text-teal" for="is_featured">Featured Product</label>
            </div>
          </div>

          <div class="col-12">
            <label class="form-label fw-bold">Description</label>
            <textarea name="description" class="form-control" rows="3"><?= htmlspecialchars($product['description']); ?></textarea>
          </div>

          <div class="col-12">
            <label class="form-label fw-bold">Usage Instructions</label>
            <textarea name="usage_info" class="form-control" rows="2"><?= htmlspecialchars($product['usage_info']); ?></textarea>
          </div>

          <div class="col-12">
            <button type="submit" name="update_product" class="btn btn-pharmacy btn-lg rounded-pill px-5">
              <i class="bi bi-save me-1"></i> Update Product
            </button>
          </div>

        </div>
      </form>
    </div>
  </main>
</div>

<?php require_once __DIR__ . '/includes/admin-footer.php'; ?>
