<?php
$pageTitle = "Contact Us";
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['send_msg'])) {
    setFlash('success', 'Thank you! Your message has been sent to our customer care team. We will respond within 2 hours.');
    header("Location: " . SITE_URL . "contact.php");
    exit;
}
?>

<div class="container my-4">
  <!-- Breadcrumb -->
  <nav aria-label="breadcrumb" class="mb-4">
    <ol class="breadcrumb">
      <li class="breadcrumb-item"><a href="<?= SITE_URL; ?>">Home</a></li>
      <li class="breadcrumb-item active" aria-current="page">Contact Us</li>
    </ol>
  </nav>

  <div class="row g-4">
    <div class="col-lg-6">
      <div class="card border-0 shadow-sm rounded-4 p-4 p-md-5 h-100">
        <h2 class="fw-bold mb-2 text-dark">Get in Touch with Pharmacist Support</h2>
        <p class="text-muted mb-4">Have a question about a medicine, dosage, or order delivery? Send us a message or call our 24/7 hotline.</p>

        <form action="" method="POST">
          <div class="mb-3">
            <label class="form-label small fw-bold">Your Name</label>
            <input type="text" name="name" class="form-control" required>
          </div>
          <div class="row g-3 mb-3">
            <div class="col-md-6">
              <label class="form-label small fw-bold">Email Address</label>
              <input type="email" name="email" class="form-control" required>
            </div>
            <div class="col-md-6">
              <label class="form-label small fw-bold">Phone Number</label>
              <input type="tel" name="phone" class="form-control">
            </div>
          </div>
          <div class="mb-4">
            <label class="form-label small fw-bold">Subject / Inquiry</label>
            <input type="text" name="subject" class="form-control" placeholder="e.g. Medicine Availability, Prescription Query" required>
          </div>
          <div class="mb-4">
            <label class="form-label small fw-bold">Message Details</label>
            <textarea name="message" class="form-control" rows="4" required></textarea>
          </div>
          <button type="submit" name="send_msg" class="btn btn-pharmacy btn-lg rounded-pill px-4">
            <i class="bi bi-send me-1"></i> Send Inquiry
          </button>
        </form>
      </div>
    </div>

    <div class="col-lg-6">
      <div class="card border-0 shadow-sm rounded-4 p-4 p-md-5 h-100 bg-teal text-white" style="background-color: var(--primary-color);">
        <h3 class="fw-bold text-white mb-4"><i class="bi bi-geo-alt me-2 text-emerald"></i> AFRIDI PHARMACY Headquarters</h3>
        
        <div class="d-flex align-items-start gap-3 mb-4">
          <i class="bi bi-building fs-3 text-teal-200"></i>
          <div>
            <h6 class="fw-bold text-white mb-1">Pharmacy Location</h6>
            <p class="mb-0 text-white-50">Main Healthcare Commercial Plaza, Sector F-8/3, Islamabad, Pakistan</p>
          </div>
        </div>

        <div class="d-flex align-items-start gap-3 mb-4">
          <i class="bi bi-telephone-inbound fs-3 text-teal-200"></i>
          <div>
            <h6 class="fw-bold text-white mb-1">24/7 Licensed Pharmacist Helpline</h6>
            <p class="mb-0 text-white-50">+92 300 1234567 / +92 51 9876543</p>
          </div>
        </div>

        <div class="d-flex align-items-start gap-3 mb-4">
          <i class="bi bi-envelope-check fs-3 text-teal-200"></i>
          <div>
            <h6 class="fw-bold text-white mb-1">Email Support</h6>
            <p class="mb-0 text-white-50">support@afridipharmacy.com / info@afridipharmacy.com</p>
          </div>
        </div>

        <div class="d-flex align-items-start gap-3 mb-4">
          <i class="bi bi-clock-history fs-3 text-teal-200"></i>
          <div>
            <h6 class="fw-bold text-white mb-1">Operating Hours</h6>
            <p class="mb-0 text-white-50">Retail Counter & Online Deliveries: Open 24/7 365 Days</p>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
