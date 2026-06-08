<?php require_once __DIR__ . '/config/db.php'; require_once __DIR__ . '/includes/security.php';
$pageTitle = 'About Us'; include __DIR__ . '/includes/header.php'; ?>
<div class="page-head"><div class="container"><h1>About Aurora Grand</h1><p>A legacy of luxury hospitality since 2010.</p></div></div>
<div class="container" style="padding-bottom:80px">
  <div class="detail-grid">
    <div><img src="<?= base_url('assets/images/lobby.jpg') ?>" alt="Lobby" style="border-radius:14px"></div>
    <div>
      <h2>Our Story</h2>
      <p class="muted" style="margin-top:14px">Aurora Grand Hotel was founded in 2010 with a singular vision — to redefine luxury hospitality in Bangladesh. Today we welcome guests from around the world with the same passion and dedication that started it all.</p>
      <p class="muted" style="margin-top:14px">Our 15 elegantly appointed rooms across 3 floors, 4 signature room categories, fine dining restaurant, rooftop infinity pool, and round-the-clock concierge service ensure every stay is unforgettable.</p>
      <div class="grid grid-2" style="margin-top:24px">
        <div class="kpi"><div class="label">Rooms</div><div class="value">15</div><i class="fa-solid fa-bed"></i></div>
        <div class="kpi"><div class="label">Staff</div><div class="value">30+</div><i class="fa-solid fa-users"></i></div>
        <div class="kpi"><div class="label">Happy Guests</div><div class="value">10K+</div><i class="fa-solid fa-face-smile"></i></div>
        <div class="kpi"><div class="label">Years</div><div class="value">15</div><i class="fa-solid fa-award"></i></div>
      </div>
    </div>
  </div>
</div>
<?php include __DIR__ . '/includes/footer.php'; ?>
