<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/security.php';
require_once __DIR__ . '/includes/helpers.php';
$typeId = (int)($_GET['type'] ?? 0);
$rt = null;
if ($typeId) {
  $s = db()->prepare('SELECT * FROM room_types WHERE id=?'); $s->execute([$typeId]); $rt = $s->fetch();
}
if (!$rt) { header('Location: ' . base_url('rooms.php')); exit; }
$pageTitle = $rt['name'] . ' Room';
$am = db()->prepare('SELECT a.* FROM amenities a JOIN room_amenities ra ON ra.amenity_id=a.id WHERE ra.room_type_id=?');
$am->execute([$typeId]);
$amenities = $am->fetchAll();
include __DIR__ . '/includes/header.php';
?>
<div class="container" style="padding:60px 24px">
  <div class="detail-grid">
    <div>
      <div class="detail-img"><img src="<?= e(image_url($rt['image'])) ?>" alt="<?= e($rt['name']) ?>"></div>
      <h1 style="margin-top:30px"><?= e($rt['name']) ?> Room</h1>
      <p class="muted" style="margin-top:10px;font-size:1.05rem"><?= e($rt['description']) ?></p>
      <h3 style="margin-top:30px;font-size:1.2rem">Amenities</h3>
      <div class="grid grid-4" style="margin-top:14px">
        <?php foreach ($amenities as $a): ?>
          <div class="feature" style="padding:18px"><i class="fa-solid <?= e($a['icon'] ?: 'fa-check') ?>"></i><h4 style="font-size:.95rem"><?= e($a['name']) ?></h4></div>
        <?php endforeach; ?>
      </div>
    </div>
    <div class="booking-card">
      <h3>Book This Room</h3>
      <p class="price" style="margin:10px 0 20px"><?= money($rt['base_price']) ?> <small>/ night</small></p>
      <form method="get" action="<?= base_url('customer/booking.php') ?>">
        <input type="hidden" name="type" value="<?= (int)$rt['id'] ?>">
        <div class="form-group"><label>Check-in</label><input type="date" name="check_in" required min="<?= date('Y-m-d') ?>" value="<?= date('Y-m-d') ?>"></div>
        <div class="form-group"><label>Check-out</label><input type="date" name="check_out" required min="<?= date('Y-m-d', strtotime('+1 day')) ?>" value="<?= date('Y-m-d', strtotime('+2 day')) ?>"></div>
        <div class="form-group"><label>Guests</label><select name="guests"><?php for($i=1;$i<=$rt['capacity'];$i++) echo "<option>$i</option>"; ?></select></div>
        <button class="btn btn-primary" style="width:100%;justify-content:center"><i class="fa-solid fa-calendar-check"></i> Continue to Booking</button>
      </form>
      <p class="muted" style="font-size:.82rem;margin-top:14px;text-align:center">Sign-in required to complete booking</p>
    </div>
  </div>
</div>
<?php include __DIR__ . '/includes/footer.php'; ?>
