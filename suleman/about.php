<?php
$pageTitle = "About Us";
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';
?>

<div class="container my-4">
  <!-- Breadcrumb -->
  <nav aria-label="breadcrumb" class="mb-4">
    <ol class="breadcrumb">
      <li class="breadcrumb-item"><a href="<?= SITE_URL; ?>">Home</a></li>
      <li class="breadcrumb-item active" aria-current="page">About Us</li>
    </ol>
  </nav>

  <div class="text-center max-w-3xl mx-auto mb-5">
    <span class="badge bg-teal-subtle text-teal border px-3 py-2 rounded-pill mb-2">ABOUT AFRIDI PHARMACY</span>
    <h1 class="display-4 fw-extrabold text-dark mb-3">Committed to Health, Quality & Genuine Care</h1>
    <p class="fs-5 text-muted">AFRIDI PHARMACY is a leading licensed retail pharmacy and digital health platform dedicated to delivering authentic pharmaceutical care directly to patients across Pakistan.</p>
  </div>

  <div class="row g-4 align-items-center mb-5">
    <div class="col-lg-6">
      <img src="<?= SITE_URL; ?>assets/images/hero-banner.jpg" class="img-fluid rounded-4 shadow-sm" alt="AFRIDI PHARMACY Licensed Team">
    </div>
    <div class="col-lg-6">
      <h3 class="fw-bold mb-3">Our Core Mission</h3>
      <p class="leading-relaxed text-muted mb-4">
        Founded by registered PMDC pharmacists, AFRIDI PHARMACY bridges the gap between traditional healthcare and modern digital convenience. We guarantee 100% genuine products sourced directly from authorized pharmaceutical manufacturers with controlled cold-chain storage.
      </p>
      
      <div class="row g-3">
        <div class="col-6">
          <div class="p-3 bg-light rounded-3 border">
            <h4 class="fw-bold text-teal mb-1">100%</h4>
            <span class="small text-muted">Authentic Products</span>
          </div>
        </div>
        <div class="col-6">
          <div class="p-3 bg-light rounded-3 border">
            <h4 class="fw-bold text-teal mb-1">24/7</h4>
            <span class="small text-muted">Pharmacist Consultation</span>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
