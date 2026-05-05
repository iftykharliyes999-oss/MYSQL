<?php
require_once __DIR__ . '/../config/config.php';
$pdo = (new Database())->connect();
$rooms = $pdo->query("SELECT * FROM rooms ORDER BY price")->fetchAll();
$pageTitle = 'Rooms';
include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/navbar_public.php';
?>
<div class="container py-5">
  <h2 class="mb-4">Our Rooms</h2>
  <div class="row g-4">
    <?php foreach ($rooms as $r): ?>
      <div class="col-md-4">
        <div class="card room-card h-100">
          <img src="<?= $r['image'] ? UPLOAD_URL.'rooms/'.$r['image'] : 'https://images.unsplash.com/photo-1631049307264-da0ec9d70304?w=600' ?>" alt="Room">
          <div class="card-body">
            <div class="d-flex justify-content-between">
              <h5>Room <?= htmlspecialchars($r['room_number']) ?></h5>
              <?= statusBadge($r['status']) ?>
            </div>
            <p class="text-muted text-capitalize mb-1"><?= $r['room_type'] ?> · Capacity <?= $r['capacity'] ?></p>
            <p class="small"><?= htmlspecialchars($r['description']) ?></p>
            <div class="d-flex justify-content-between align-items-center mt-3">
              <span class="h5 text-primary mb-0"><?= formatCurrency($r['price']) ?></span>
              <?php if ($r['status']==='available'): ?>
                <a href="<?= BASE_URL ?>/customer/book_room.php?room_id=<?= $r['id'] ?>" class="btn btn-sm btn-primary">Book</a>
              <?php else: ?>
                <button class="btn btn-sm btn-secondary" disabled>Unavailable</button>
              <?php endif; ?>
            </div>
          </div>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
