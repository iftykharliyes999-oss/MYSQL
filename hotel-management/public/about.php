<?php
require_once __DIR__ . '/../config/config.php';
$pageTitle = 'About';
include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/navbar_public.php';
?>
<div class="container py-5">
  <h2>About <?= SITE_NAME ?></h2>
  <p class="lead">A 5-star experience in the heart of the city. We blend elegance with modern comfort.</p>
  <div class="row g-4 mt-3">
    <div class="col-md-6">
      <h4>Our Mission</h4>
      <p>To deliver exceptional hospitality experiences with personalized service and luxurious amenities.</p>
    </div>
    <div class="col-md-6">
      <h4>Our Services</h4>
      <ul>
        <li>24/7 Front Desk & Concierge</li>
        <li>Fine Dining Restaurant</li>
        <li>Swimming Pool & Spa</li>
        <li>Free High-Speed Wi-Fi</li>
        <li>Conference & Event Halls</li>
      </ul>
    </div>
  </div>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
