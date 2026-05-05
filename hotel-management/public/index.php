<?php
require_once __DIR__ . '/../config/config.php';
$pdo = (new Database())->connect();
$rooms = $pdo->query("SELECT * FROM rooms WHERE status='available' ORDER BY price LIMIT 6")->fetchAll();
$pageTitle = 'Welcome';
include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/navbar_public.php';
?>
<section class="hero">
  <div class="container">
    <h1>Welcome to <?= SITE_NAME ?></h1>
    <p>Experience luxury, comfort, and world-class hospitality.</p>
    <a href="<?= BASE_URL ?>/public/rooms.php" class="btn btn-warning btn-lg mt-3 px-4">
      <i class="fa-solid fa-bed me-2"></i>Browse Rooms
    </a>
  </div>
</section>

<section class="container py-5">
  <h2 class="text-center mb-4">Featured Rooms</h2>
  <div class="row g-4">
    <?php foreach ($rooms as $r): ?>
      <div class="col-md-4">
        <div class="card room-card h-100">
          <img src="<?= $r['image'] ? UPLOAD_URL . 'rooms/' . $r['image'] : 'https://images.unsplash.com/photo-1631049307264-da0ec9d70304?w=600' ?>" alt="Room">
          <div class="card-body">
            <h5 class="card-title">Room <?= htmlspecialchars($r['room_number']) ?> - <?= ucfirst($r['room_type']) ?></h5>
            <p class="text-muted small mb-2"><?= htmlspecialchars($r['description']) ?></p>
            <div class="d-flex justify-content-between align-items-center">
              <span class="h5 text-primary mb-0"><?= formatCurrency($r['price']) ?>/night</span>
              <a href="<?= BASE_URL ?>/public/login.php" class="btn btn-sm btn-primary">Book Now</a>
            </div>
          </div>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
</section>

<section class="bg-white py-5">
  <div class="container">
    <div class="row text-center g-4">
      <div class="col-md-3"><i class="fa-solid fa-wifi fa-3x text-primary mb-2"></i><h6>Free Wi-Fi</h6></div>
      <div class="col-md-3"><i class="fa-solid fa-utensils fa-3x text-primary mb-2"></i><h6>Restaurant</h6></div>
      <div class="col-md-3"><i class="fa-solid fa-swimmer fa-3x text-primary mb-2"></i><h6>Swimming Pool</h6></div>
      <div class="col-md-3"><i class="fa-solid fa-spa fa-3x text-primary mb-2"></i><h6>Spa & Wellness</h6></div>
    </div>
  </div>
</section>
<?php include __DIR__ . '/../includes/footer.php'; ?>
