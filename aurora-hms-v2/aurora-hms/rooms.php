<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/security.php';
require_once __DIR__ . '/includes/helpers.php';
$pageTitle = 'Our Rooms';
$min = (int)($_GET['min'] ?? 0);
$max = (int)($_GET['max'] ?? 50000);
$type = (int)($_GET['type'] ?? 0);
$where = 'WHERE base_price BETWEEN ? AND ?';
$params = [$min, $max];
if ($type) { $where .= ' AND id = ?'; $params[] = $type; }
$types = db()->prepare("SELECT * FROM room_types $where ORDER BY base_price");
$types->execute($params);
$types = $types->fetchAll();
$allTypes = db()->query('SELECT id,name FROM room_types ORDER BY base_price')->fetchAll();
include __DIR__ . '/includes/header.php';
?>
<div class="page-head">
  <div class="container"><h1>Our Rooms & Suites</h1><p>Discover comfort and elegance across our signature room categories.</p></div>
</div>
<div class="container">
  <form class="filter-bar" method="get">
    <div class="form-group" style="margin:0"><label>Room Type</label>
      <select name="type"><option value="0">All types</option>
        <?php foreach ($allTypes as $t): ?><option value="<?= $t['id'] ?>" <?= $type==$t['id']?'selected':'' ?>><?= e($t['name']) ?></option><?php endforeach; ?>
      </select>
    </div>
    <div class="form-group" style="margin:0"><label>Min Price (<?= CURRENCY ?>)</label><input type="number" name="min" value="<?= $min ?>" min="0"></div>
    <div class="form-group" style="margin:0"><label>Max Price (<?= CURRENCY ?>)</label><input type="number" name="max" value="<?= $max ?>" min="0"></div>
    <button class="btn btn-dark"><i class="fa-solid fa-filter"></i> Filter</button>
  </form>
  <div class="grid grid-3" style="margin-bottom:80px">
    <?php foreach ($types as $rt): ?>
    <div class="card" id="type-<?= (int)$rt['id'] ?>">
      <div class="card-img"><img src="<?= e(image_url($rt['image'])) ?>" alt="<?= e($rt['name']) ?>" loading="lazy"></div>
      <div class="card-body">
        <h3><?= e($rt['name']) ?> Room</h3>
        <p class="muted"><?= e($rt['description']) ?></p>
        <p class="muted" style="margin-top:8px"><i class="fa-solid fa-user-group" style="color:var(--gold)"></i> Up to <?= (int)$rt['capacity'] ?> guests</p>
        <div class="card-meta">
          <span class="price"><?= money($rt['base_price']) ?><small>/night</small></span>
          <a href="<?= base_url('room.php?type=' . (int)$rt['id']) ?>" class="btn btn-primary btn-sm">Book Now</a>
        </div>
      </div>
    </div>
    <?php endforeach; if (!$types): ?><p class="muted">No rooms match your filters.</p><?php endif; ?>
  </div>
</div>
<?php include __DIR__ . '/includes/footer.php'; ?>
