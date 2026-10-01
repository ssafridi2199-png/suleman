<?php
$adminTitle = "Pharmacy Settings";
require_once __DIR__ . '/includes/admin-header.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_settings'])) {
    setFlash('success', 'Pharmacy configuration settings saved successfully!');
    header("Location: " . SITE_URL . "admin/settings.php");
    exit;
}
?>

<div class="admin-wrapper">
  <?php require_once __DIR__ . '/includes/admin-sidebar.php'; ?>

  <main class="admin-main">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <div>
        <h2 class="fw-bold mb-1"><i class="bi bi-gear me-2 text-teal"></i> Store Configuration & Settings</h2>
        <p class="text-muted mb-0">Update pharmacy contact details, delivery charges and store defaults</p>
      </div>
    </div>

    <?php displayFlash(); ?>

    <div class="card border-0 shadow-sm rounded-4 p-4 p-md-5">
      <form action="" method="POST">
        <div class="row g-4">
          
          <div class="col-md-6">
            <label class="form-label fw-bold">Pharmacy Store Name</label>
            <input type="text" name="store_name" class="form-control" value="AFRIDI PHARMACY" required>
          </div>

          <div class="col-md-6">
            <label class="form-label fw-bold">PMDC License Registration #</label>
            <input type="text" name="license_no" class="form-control" value="PMDC-LIC-87459-ISB" required>
          </div>

          <div class="col-md-6">
            <label class="form-label fw-bold">Helpline Phone Number</label>
            <input type="text" name="helpline" class="form-control" value="+92 300 1234567" required>
          </div>

          <div class="col-md-6">
            <label class="form-label fw-bold">Support Email Address</label>
            <input type="email" name="email" class="form-control" value="support@afridipharmacy.com" required>
          </div>

          <div class="col-md-6">
            <label class="form-label fw-bold">Standard Delivery Fee (PKR)</label>
            <input type="number" step="0.01" name="delivery_fee" class="form-control" value="150.00" required>
          </div>

          <div class="col-md-6">
            <label class="form-label fw-bold">Free Shipping Threshold (PKR)</label>
            <input type="number" step="0.01" name="free_threshold" class="form-control" value="3000.00" required>
          </div>

          <div class="col-12">
            <label class="form-label fw-bold">Store Physical Address</label>
            <textarea name="address" class="form-control" rows="2">Main Healthcare Commercial Plaza, Sector F-8/3, Islamabad, Pakistan</textarea>
          </div>

          <div class="col-12">
            <button type="submit" name="save_settings" class="btn btn-pharmacy rounded-pill px-5">Save Configuration</button>
          </div>

        </div>
      </form>
    </div>
  </main>
</div>

<?php require_once __DIR__ . '/includes/admin-footer.php'; ?>
