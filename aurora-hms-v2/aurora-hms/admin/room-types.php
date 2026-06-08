<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/security.php';
require_once __DIR__ . '/../includes/helpers.php';
require_role('admin');
$pageTitle = 'Room Types (Public Categories)';

if ($_SERVER['REQUEST_METHOD']==='POST') {
  csrf_check($_POST['csrf'] ?? null);
  $action = $_POST['action'] ?? '';
  try {
    if ($action === 'add') {
      $img = handle_image_upload('image');
      $s = db()->prepare('INSERT INTO room_types (name,base_price,capacity,description,image) VALUES (?,?,?,?,?)');
      $s->execute([$_POST['name'], (float)$_POST['base_price'], (int)$_POST['capacity'], $_POST['description'] ?? '', $img]);
      flash('Room type added.');
    } elseif ($action === 'update') {
      $img = handle_image_upload('image');
      if ($img) {
        db()->prepare('UPDATE room_types SET name=?,base_price=?,capacity=?,description=?,image=? WHERE id=?')
            ->execute([$_POST['name'], (float)$_POST['base_price'], (int)$_POST['capacity'], $_POST['description'] ?? '', $img, (int)$_POST['id']]);
      } else {
        db()->prepare('UPDATE room_types SET name=?,base_price=?,capacity=?,description=? WHERE id=?')
            ->execute([$_POST['name'], (float)$_POST['base_price'], (int)$_POST['capacity'], $_POST['description'] ?? '', (int)$_POST['id']]);
      }
      flash('Room type updated.');
    } elseif ($action === 'delete') {
      db()->prepare('DELETE FROM room_types WHERE id=?')->execute([(int)$_POST['id']]);
      flash('Room type deleted.');
    }
  } catch (Throwable $e) { flash('Error: ' . $e->getMessage(), 'error'); }
  header('Location: ' . base_url('admin/room-types.php')); exit;
}

$types = db()->query('SELECT * FROM room_types ORDER BY base_price')->fetchAll();
include __DIR__ . '/../includes/dash-header.php';
?>
<style>
.type-card{background:#fff;border:1px solid var(--line);border-radius:14px;overflow:hidden;display:flex;flex-direction:column}
.type-card img{width:100%;height:170px;object-fit:cover}
.type-card .body{padding:18px;display:flex;flex-direction:column;gap:8px;flex:1}
.type-card .row{display:flex;gap:8px;margin-top:auto}
.modal-bg{position:fixed;inset:0;background:rgba(15,23,42,.55);display:none;align-items:center;justify-content:center;z-index:60;padding:20px}
.modal-bg.open{display:flex}
.modal-box{background:#fff;border-radius:14px;max-width:620px;width:100%;max-height:92vh;overflow:auto;padding:28px}
.modal-box h3{margin-bottom:18px;display:flex;justify-content:space-between;align-items:center}
.modal-box h3 button{background:none;border:none;font-size:1.4rem;cursor:pointer}
</style>

<div class="card" style="padding:20px;margin-bottom:24px;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:12px">
  <div><strong style="font-size:1.05rem">Room Types</strong><p class="muted" style="margin-top:4px">These categories appear on the public site (<a href="<?= base_url('rooms.php') ?>" style="color:var(--gold-2,#b8862d)">/rooms.php</a>). Upload cover image to publish.</p></div>
  <button class="btn btn-primary" onclick="openType()"><i class="fa-solid fa-plus"></i> New Room Type</button>
</div>

<div class="grid grid-3">
  <?php foreach ($types as $t): ?>
    <div class="type-card">
      <img src="<?= e(image_url($t['image'])) ?>" alt="">
      <div class="body">
        <h3 style="margin:0"><?= e($t['name']) ?></h3>
        <p class="muted" style="font-size:.9rem"><?= e($t['description']) ?></p>
        <p style="font-weight:600;color:var(--gold-2,#b8862d)"><?= money($t['base_price']) ?> / night · <?= (int)$t['capacity'] ?> guests</p>
        <div class="row">
          <button class="btn btn-sm btn-dark" onclick='openType(<?= json_encode($t, JSON_HEX_APOS|JSON_HEX_QUOT) ?>)' style="flex:1"><i class="fa-solid fa-pen"></i> Edit</button>
          <form method="post" style="display:inline" onsubmit="return confirm('Delete <?= e($t['name']) ?> type?')">
            <?= csrf_field() ?><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= $t['id'] ?>">
            <button class="btn btn-sm btn-danger"><i class="fa-solid fa-trash"></i></button>
          </form>
        </div>
      </div>
    </div>
  <?php endforeach; ?>
</div>

<div class="modal-bg" id="typeModal"><div class="modal-box">
  <h3><span id="tTitle">New Room Type</span> <button type="button" onclick="closeType()">&times;</button></h3>
  <form method="post" enctype="multipart/form-data" id="typeForm">
    <?= csrf_field() ?>
    <input type="hidden" name="action" id="tAction" value="add"><input type="hidden" name="id" id="tId">
    <div class="form-row">
      <div class="form-group"><label>Name *</label><input name="name" id="tName" required></div>
      <div class="form-group"><label>Capacity *</label><input type="number" name="capacity" id="tCap" min="1" required></div>
    </div>
    <div class="form-group"><label>Base Price (<?= CURRENCY ?>/night) *</label><input type="number" step="0.01" name="base_price" id="tPrice" required></div>
    <div class="form-group"><label>Cover Image (JPG/PNG/WEBP, ≤ 5 MB) — appears on public site</label>
      <input type="file" name="image" accept="image/*">
      <small class="muted" id="tImgHint"></small>
    </div>
    <div class="form-group"><label>Description</label><textarea name="description" id="tDesc" rows="4"></textarea></div>
    <button class="btn btn-primary" style="width:100%;justify-content:center"><i class="fa-solid fa-save"></i> Save</button>
  </form>
</div></div>

<script>
function openType(t){
  document.getElementById('typeModal').classList.add('open');
  if (t){
    document.getElementById('tTitle').textContent='Edit '+t.name;
    document.getElementById('tAction').value='update';
    document.getElementById('tId').value=t.id;
    document.getElementById('tName').value=t.name;
    document.getElementById('tCap').value=t.capacity;
    document.getElementById('tPrice').value=t.base_price;
    document.getElementById('tDesc').value=t.description||'';
    document.getElementById('tImgHint').textContent=t.image?'Current: '+t.image+' (upload to replace)':'No image';
  } else {
    document.getElementById('typeForm').reset();
    document.getElementById('tAction').value='add';
    document.getElementById('tTitle').textContent='New Room Type';
    document.getElementById('tImgHint').textContent='';
  }
}
function closeType(){document.getElementById('typeModal').classList.remove('open')}
document.getElementById('typeModal').addEventListener('click',e=>{if(e.target.id==='typeModal')closeType()});
</script>
<?php include __DIR__ . '/../includes/dash-footer.php'; ?>
