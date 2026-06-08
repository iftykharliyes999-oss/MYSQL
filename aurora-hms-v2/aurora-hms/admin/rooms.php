<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/security.php';
require_once __DIR__ . '/../includes/helpers.php';
require_role('admin');
$pageTitle = 'Manage Rooms';

if ($_SERVER['REQUEST_METHOD']==='POST') {
  csrf_check($_POST['csrf'] ?? null);
  $action = $_POST['action'] ?? '';
  try {
    if ($action === 'add') {
      $img = handle_image_upload('image');
      $s = db()->prepare('INSERT INTO rooms (number,type_id,status,price,floor,image,description) VALUES (?,?,?,?,?,?,?)');
      $s->execute([$_POST['number'], (int)$_POST['type_id'], $_POST['status'], (float)$_POST['price'], (int)$_POST['floor'], $img, $_POST['description'] ?? null]);
      audit('room_add', 'rooms', (int)db()->lastInsertId()); flash('Room added.');
    } elseif ($action === 'update') {
      $img = handle_image_upload('image');
      if ($img) {
        db()->prepare('UPDATE rooms SET number=?,type_id=?,status=?,price=?,floor=?,description=?,image=? WHERE id=?')
            ->execute([$_POST['number'], (int)$_POST['type_id'], $_POST['status'], (float)$_POST['price'], (int)$_POST['floor'], $_POST['description'] ?? null, $img, (int)$_POST['id']]);
      } else {
        db()->prepare('UPDATE rooms SET number=?,type_id=?,status=?,price=?,floor=?,description=? WHERE id=?')
            ->execute([$_POST['number'], (int)$_POST['type_id'], $_POST['status'], (float)$_POST['price'], (int)$_POST['floor'], $_POST['description'] ?? null, (int)$_POST['id']]);
      }
      audit('room_update', 'rooms', (int)$_POST['id']); flash('Room updated.');
    } elseif ($action === 'delete') {
      db()->prepare('DELETE FROM rooms WHERE id=?')->execute([(int)$_POST['id']]);
      audit('room_delete', 'rooms', (int)$_POST['id']); flash('Room deleted.');
    }
  } catch (Throwable $e) { flash('Error: ' . $e->getMessage(), 'error'); }
  header('Location: ' . base_url('admin/rooms.php')); exit;
}

$rooms = db()->query('SELECT r.*, rt.name AS type_name, rt.image AS type_image FROM rooms r JOIN room_types rt ON rt.id=r.type_id ORDER BY r.number')->fetchAll();
$types = db()->query('SELECT * FROM room_types ORDER BY name')->fetchAll();
include __DIR__ . '/../includes/dash-header.php';
?>
<style>
.room-thumb{width:64px;height:48px;object-fit:cover;border-radius:6px;border:1px solid var(--line)}
.modal-bg{position:fixed;inset:0;background:rgba(15,23,42,.55);display:none;align-items:center;justify-content:center;z-index:60;padding:20px}
.modal-bg.open{display:flex}
.modal-box{background:#fff;border-radius:14px;max-width:620px;width:100%;max-height:92vh;overflow:auto;padding:28px;box-shadow:0 30px 80px rgba(0,0,0,.3)}
.modal-box h3{margin-bottom:18px;display:flex;justify-content:space-between;align-items:center}
.modal-box h3 button{background:none;border:none;font-size:1.4rem;cursor:pointer}
</style>

<div class="card" style="padding:20px;margin-bottom:24px;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:12px">
  <div><strong style="font-size:1.05rem">Manage Rooms (units)</strong><p class="muted" style="margin-top:4px">Tip: Public site shows <strong>Room Types</strong>. Edit category visuals at <a href="<?= base_url('admin/room-types.php') ?>" style="color:var(--gold-2,#b8862d)">Room Types →</a></p></div>
  <button class="btn btn-primary" onclick="openRoom()"><i class="fa-solid fa-plus"></i> Add New Room</button>
</div>

<div class="table-wrap">
  <table><thead><tr><th>Image</th><th>Number</th><th>Type</th><th>Floor</th><th>Price</th><th>Status</th><th>Actions</th></tr></thead><tbody>
  <?php foreach ($rooms as $r): ?>
    <tr>
      <td><img class="room-thumb" src="<?= e(image_url($r['image'] ?: $r['type_image'])) ?>" alt=""></td>
      <td><strong>#<?= e($r['number']) ?></strong></td><td><?= e($r['type_name']) ?></td><td><?= (int)$r['floor'] ?></td><td><?= money($r['price']) ?></td>
      <td><span class="pill pill-<?= e($r['status']) ?>"><?= e($r['status']) ?></span></td>
      <td style="white-space:nowrap">
        <button class="btn btn-sm btn-dark" onclick='openRoom(<?= json_encode($r, JSON_HEX_APOS|JSON_HEX_QUOT) ?>)'><i class="fa-solid fa-pen"></i></button>
        <form method="post" style="display:inline" onsubmit="return confirm('Delete room #<?= e($r['number']) ?>?')">
          <?= csrf_field() ?><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= $r['id'] ?>">
          <button class="btn btn-sm btn-danger"><i class="fa-solid fa-trash"></i></button>
        </form>
      </td>
    </tr>
  <?php endforeach; ?>
  </tbody></table>
</div>

<div class="modal-bg" id="roomModal">
  <div class="modal-box">
    <h3><span id="mTitle">Add Room</span> <button type="button" onclick="closeRoom()">&times;</button></h3>
    <form method="post" enctype="multipart/form-data" id="roomForm">
      <?= csrf_field() ?>
      <input type="hidden" name="action" id="mAction" value="add">
      <input type="hidden" name="id" id="mId">
      <div class="form-row">
        <div class="form-group"><label>Number *</label><input name="number" id="mNumber" required></div>
        <div class="form-group"><label>Floor *</label><input type="number" name="floor" id="mFloor" required></div>
      </div>
      <div class="form-row">
        <div class="form-group"><label>Type *</label><select name="type_id" id="mType"><?php foreach($types as $t): ?><option value="<?= $t['id'] ?>"><?= e($t['name']) ?></option><?php endforeach; ?></select></div>
        <div class="form-group"><label>Price (per night) *</label><input type="number" step="0.01" name="price" id="mPrice" required></div>
      </div>
      <div class="form-group"><label>Status</label>
        <select name="status" id="mStatus"><option>available</option><option>occupied</option><option>dirty</option><option>maintenance</option></select>
      </div>
      <div class="form-group"><label>Room Image (JPG/PNG/WEBP, ≤ 5 MB)</label>
        <input type="file" name="image" accept="image/*">
        <small class="muted" id="mImgHint"></small>
      </div>
      <div class="form-group"><label>Description</label><textarea name="description" id="mDesc" rows="3"></textarea></div>
      <button class="btn btn-primary" style="width:100%;justify-content:center"><i class="fa-solid fa-save"></i> Save Room</button>
    </form>
  </div>
</div>

<script>
function openRoom(r){
  document.getElementById('roomModal').classList.add('open');
  if (r) {
    document.getElementById('mTitle').textContent='Edit Room #'+r.number;
    document.getElementById('mAction').value='update';
    document.getElementById('mId').value=r.id;
    document.getElementById('mNumber').value=r.number;
    document.getElementById('mFloor').value=r.floor;
    document.getElementById('mType').value=r.type_id;
    document.getElementById('mPrice').value=r.price;
    document.getElementById('mStatus').value=r.status;
    document.getElementById('mDesc').value=r.description||'';
    document.getElementById('mImgHint').textContent = r.image ? 'Current: '+r.image+' (upload to replace)' : '';
  } else {
    document.getElementById('mTitle').textContent='Add Room';
    document.getElementById('mAction').value='add';
    document.getElementById('roomForm').reset();
    document.getElementById('mImgHint').textContent='';
  }
}
function closeRoom(){document.getElementById('roomModal').classList.remove('open')}
document.getElementById('roomModal').addEventListener('click',e=>{if(e.target.id==='roomModal')closeRoom()});
</script>
<?php include __DIR__ . '/../includes/dash-footer.php'; ?>
