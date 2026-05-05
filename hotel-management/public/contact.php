<?php
require_once __DIR__ . '/../config/config.php';
$pageTitle = 'Contact';
$sent = false;
if ($_SERVER['REQUEST_METHOD']==='POST') {
    csrf_verify();
    $sent = true; // For demo - real sending would use mail() or SMTP
    flash('contact', 'Thank you! We will get back to you soon.', 'success');
}
include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/navbar_public.php';
?>
<div class="container py-5">
  <h2>Contact Us</h2>
  <?= flash('contact') ?>
  <div class="row g-4 mt-2">
    <div class="col-md-6">
      <p><i class="fa-solid fa-location-dot text-primary me-2"></i>123 Hotel Street, Dhaka, Bangladesh</p>
      <p><i class="fa-solid fa-phone text-primary me-2"></i>+880 1700-000000</p>
      <p><i class="fa-solid fa-envelope text-primary me-2"></i>info@grandroyal.com</p>
    </div>
    <div class="col-md-6">
      <form method="POST" class="card p-4">
        <?= csrf_field() ?>
        <input class="form-control mb-2" name="name" placeholder="Your Name" required>
        <input class="form-control mb-2" type="email" name="email" placeholder="Email" required>
        <textarea class="form-control mb-2" name="message" rows="4" placeholder="Message" required></textarea>
        <button class="btn btn-primary">Send</button>
      </form>
    </div>
  </div>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
