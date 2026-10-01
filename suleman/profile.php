<?php
$pageTitle = "My Profile & Address Manager";
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';

requireLogin();

$db = getDBConnection();
$userId = $_SESSION['user_id'];
$user = getCurrentUser();

// Update Profile Details POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $fullName = sanitize($_POST['full_name'] ?? '');
    $phone = sanitize($_POST['phone'] ?? '');

    if (!empty($fullName)) {
        $upd = $db->prepare("UPDATE users SET full_name = ?, phone = ? WHERE id = ?");
        $upd->execute([$fullName, $phone, $userId]);
        $_SESSION['user_name'] = $fullName;
        setFlash('success', 'Profile information updated successfully!');
        header("Location: " . SITE_URL . "profile.php");
        exit;
    }
}

// Update Address Details POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_address'])) {
    $street = sanitize($_POST['street_address'] ?? '');
    $city = sanitize($_POST['city'] ?? '');
    $postal = sanitize($_POST['postal_code'] ?? '');
    $phone = sanitize($_POST['phone'] ?? '');

    $chk = $db->prepare("SELECT id FROM addresses WHERE user_id = ?");
    $chk->execute([$userId]);
    $existing = $chk->fetch();

    if ($existing) {
        $upd = $db->prepare("UPDATE addresses SET full_name = ?, phone = ?, street_address = ?, city = ?, postal_code = ? WHERE user_id = ?");
        $upd->execute([$user['full_name'], $phone, $street, $city, $postal, $userId]);
    } else {
        $ins = $db->prepare("INSERT INTO addresses (user_id, full_name, phone, street_address, city, postal_code, is_default) VALUES (?, ?, ?, ?, ?, ?, 1)");
        $ins->execute([$userId, $user['full_name'], $phone, $street, $city, $postal]);
    }

    setFlash('success', 'Default shipping address saved successfully!');
    header("Location: " . SITE_URL . "profile.php");
    exit;
}

// Fetch saved address
$addrStmt = $db->prepare("SELECT * FROM addresses WHERE user_id = ? LIMIT 1");
$addrStmt->execute([$userId]);
$savedAddress = $addrStmt->fetch();
?>

<div class="container my-4">
  <h2 class="fw-bold mb-4"><i class="bi bi-person-gear text-teal"></i> My Account Settings</h2>

  <div class="row g-4">
    <!-- Profile Info Card -->
    <div class="col-lg-6">
      <div class="card border-0 shadow-sm rounded-4 p-4 h-100">
        <h4 class="fw-bold mb-3"><i class="bi bi-person me-2 text-teal"></i> Personal Details</h4>
        
        <form action="" method="POST">
          <div class="mb-3">
            <label class="form-label small fw-bold">Full Name</label>
            <input type="text" name="full_name" class="form-control" value="<?= htmlspecialchars($user['full_name']); ?>" required>
          </div>

          <div class="mb-3">
            <label class="form-label small fw-bold">Email Address</label>
            <input type="email" class="form-control bg-light" value="<?= htmlspecialchars($user['email']); ?>" readonly>
            <div class="form-text">Primary login email address cannot be changed directly.</div>
          </div>

          <div class="mb-4">
            <label class="form-label small fw-bold">Contact Phone Number</label>
            <input type="tel" name="phone" class="form-control" value="<?= htmlspecialchars($user['phone']); ?>">
          </div>

          <button type="submit" name="update_profile" class="btn btn-pharmacy rounded-pill px-4">Save Profile Changes</button>
        </form>
      </div>
    </div>

    <!-- Default Shipping Address Card -->
    <div class="col-lg-6">
      <div class="card border-0 shadow-sm rounded-4 p-4 h-100">
        <h4 class="fw-bold mb-3"><i class="bi bi-geo-alt me-2 text-teal"></i> Default Delivery Address</h4>
        
        <form action="" method="POST">
          <div class="mb-3">
            <label class="form-label small fw-bold">Recipient Phone</label>
            <input type="tel" name="phone" class="form-control" value="<?= htmlspecialchars($savedAddress['phone'] ?? $user['phone']); ?>" required>
          </div>

          <div class="mb-3">
            <label class="form-label small fw-bold">Street Address</label>
            <textarea name="street_address" class="form-control" rows="2" required><?= htmlspecialchars($savedAddress['street_address'] ?? ''); ?></textarea>
          </div>

          <div class="row g-3 mb-4">
            <div class="col-md-6">
              <label class="form-label small fw-bold">City</label>
              <input type="text" name="city" class="form-control" value="<?= htmlspecialchars($savedAddress['city'] ?? 'Islamabad'); ?>" required>
            </div>
            <div class="col-md-6">
              <label class="form-label small fw-bold">Postal Code</label>
              <input type="text" name="postal_code" class="form-control" value="<?= htmlspecialchars($savedAddress['postal_code'] ?? '44000'); ?>" required>
            </div>
          </div>

          <button type="submit" name="save_address" class="btn btn-pharmacy rounded-pill px-4">Save Address</button>
        </form>
      </div>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
