<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/security.php';
$pageTitle = 'Welcome';
$featured = [];
try {
    $featured = db()->query('SELECT * FROM room_types ORDER BY base_price ASC LIMIT 4')->fetchAll();
} catch (Throwable $e) { /* DB not ready yet */ }
include __DIR__ . '/includes/header.php';
?>
<section class="hero">
  <div class="container hero-content">
    <span class="eyebrow">Premium Hospitality · Est. 2010</span>
    <h1>Experience Luxury at <em>Aurora Grand</em> Hotel</h1>
    <p>Indulge in five-star comfort, world-class dining, and breathtaking city views. Your perfect stay begins here — every detail designed to delight.</p>
    <div class="hero-cta">
      <a href="<?= base_url('rooms.php') ?>" class="btn btn-primary"><i class="fa-solid fa-bed"></i> Explore Rooms</a>
      <a href="<?= base_url('about.php') ?>" class="btn btn-ghost" style="color:#fff;border-color:#fff"><i class="fa-solid fa-play"></i> Learn More</a>
    </div>
  </div>
</section>

<section class="section" style="background:#fff">
  <div class="container">
    <div class="section-title">
      <span class="eyebrow">Why Choose Us</span>
      <h2>Exceptional Experience, Every Stay</h2>
      <p>From the moment you arrive, our team is dedicated to ensuring your stay is nothing short of extraordinary.</p>
    </div>
    <div class="grid grid-4">
      <div class="feature"><i class="fa-solid fa-bed"></i><h4>Luxurious Rooms</h4><p class="muted">Spacious, elegantly designed rooms with premium amenities.</p></div>
      <div class="feature"><i class="fa-solid fa-utensils"></i><h4>Fine Dining</h4><p class="muted">Award-winning restaurants serving world cuisine.</p></div>
      <div class="feature"><i class="fa-solid fa-person-swimming"></i><h4>Infinity Pool</h4><p class="muted">Rooftop pool with panoramic skyline views.</p></div>
      <div class="feature"><i class="fa-solid fa-bell-concierge"></i><h4>24/7 Concierge</h4><p class="muted">Personalized service whenever you need it.</p></div>
    </div>
  </div>
</section>

<section class="section">
  <div class="container">
    <div class="section-title">
      <span class="eyebrow">Our Accommodations</span>
      <h2>Choose Your Perfect Room</h2>
    </div>
    <div class="grid grid-3">
      <?php foreach ($featured as $rt): ?>
      <div class="card">
        <div class="card-img"><img src="<?= base_url('assets/images/' . ($rt['image'] ?: 'room-standard.jpg')) ?>" alt="<?= e($rt['name']) ?>" loading="lazy"></div>
        <div class="card-body">
          <h3><?= e($rt['name']) ?> Room</h3>
          <p class="muted"><?= e(mb_strimwidth($rt['description'] ?: '', 0, 90, '…')) ?></p>
          <div class="card-meta">
            <span class="price"><?= money($rt['base_price']) ?><small>/night</small></span>
            <a href="<?= base_url('rooms.php#type-' . (int)$rt['id']) ?>" class="btn btn-dark btn-sm">View</a>
          </div>
        </div>
      </div>
      <?php endforeach; if (!$featured): ?>
        <p class="muted">Run <code>database.sql</code> in MySQL to load rooms.</p>
      <?php endif; ?>
    </div>
    <div style="text-align:center;margin-top:40px">
      <a href="<?= base_url('rooms.php') ?>" class="btn btn-primary">View All Rooms <i class="fa-solid fa-arrow-right"></i></a>
    </div>
  </div>
</section>

<section class="section" style="background:#fff">
  <div class="container">
    <div class="grid grid-3">
      <div><img src="<?= base_url('assets/images/lobby.jpg') ?>" alt="Lobby" loading="lazy" style="border-radius:14px;aspect-ratio:4/3;object-fit:cover"></div>
      <div><img src="<?= base_url('assets/images/pool.jpg') ?>" alt="Pool" loading="lazy" style="border-radius:14px;aspect-ratio:4/3;object-fit:cover"></div>
      <div><img src="<?= base_url('assets/images/dining.jpg') ?>" alt="Dining" loading="lazy" style="border-radius:14px;aspect-ratio:4/3;object-fit:cover"></div>
    </div>
  </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
