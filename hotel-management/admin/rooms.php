<?php
require_once __DIR__ . '/../config/config.php';
requireRole('admin');
$pdo = (new Database())->connect();

if ($_SERVER['REQUEST_METHOD']==='POST') {
    csrf_verify();
    $action = $_POST['action'] ?? '';
    if ($action==='create') {
        $img = uploadImage($_FILES['image'] ?? null,'rooms');
        $pdo->prepare("INSERT INTO rooms (room_number,room_type,price,capacity,description,status,image) VALUES (?,?,?,?,?,?,?)")
            ->execute([sanitize($_POST['room_number']),$_POST['room_type'],(float)$_POST['price'],
                       (int)$_POST['capacity'],sanitize($_POST['description']),$_POST['status'],$img]);
        flash('r','Room added.','success');
    } elseif ($action==='update') {
        $id=(int)$_POST['id'];
        $img = uploadImage($_FILES['image'] ?? null,'rooms');
        if ($img) {
          $pdo->prepare("UPDATE rooms SET room_number=?,room_type=?,price=?,capacity=?,description=?,status=?,image=? WHERE id=?")
              ->execute([sanitize($_POST['room_number']),$_POST['room_type'],(float)$_POST['price'],
                         (int)$_POST['capacity'],sanitize($_POST['description']),$_POST['status'],$img,$id]);
        } else {
          $pdo->prepare("UPDATE rooms SET room_number=?,room_type=?,price=?,capacity=?,description=?,status=? WHERE id=?")
              ->execute([sanitize($_POST['room_number']),$_POST['room_type'],(float)$_POST['price'],
                         (int)$_POST['capacity'],sanitize($_POST['description']),$_POST['status'],$id]);
        }
        flash('r','Room updated.','success');
    } elseif ($action==='delete') {
        $pdo->prepare("DELETE FROM rooms WHERE id=?")->execute([(int)$_POST['id']]);
        flash('r','Room deleted.','success');
    }
    redirect(BASE_URL . '/admin/rooms.php');
}
$rooms=$pdo->query("SELECT * FROM rooms ORDER BY room_number")->fetchAll();
$pageTitle='Manage Rooms';
include __DIR__ . '/../includes/header.php';
?>
<div class="dashboard-wrapper">
<?php include __DIR__ . '/../includes/sidebar_admin.php'; ?>
<div class="main-content">
  <div class="d-flex justify-content-between mb-3">
    <h3>Rooms</h3>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#newRoom"><i class="fa fa-plus"></i> Add Room</button>
  </div>
  <?= flash('r') ?>
  <div class="card"><div class="table-responsive">
    <table class="table align-middle mb-0">
      <thead><tr><th>#</th><th>Room</th><th>Type</th><th>Capacity</th><th>Price</th><th>Status</th><th>Action</th></tr></thead>
      <tbody>
        <?php foreach ($rooms as $r): ?>
        <tr>
          <td>#<?= $r['id'] ?></td>
          <td><strong><?= htmlspecialchars($r['room_number']) ?></strong></td>
          <td class="text-capitalize"><?= $r['room_type'] ?></td>
          <td><?= $r['capacity'] ?></td>
          <td><?= formatCurrency($r['price']) ?></td>
          <td><?= statusBadge($r['status']) ?></td>
          <td>
            <button class="btn btn-sm btn-info text-white" data-bs-toggle="modal" data-bs-target="#er<?= $r['id'] ?>"><i class="fa fa-edit"></i></button>
            <form method="POST" class="d-inline" onsubmit="return confirmDelete()">
              <?= csrf_field() ?><input type="hidden" name="action" value="delete">
              <input type="hidden" name="id" value="<?= $r['id'] ?>">
              <button class="btn btn-sm btn-danger"><i class="fa fa-trash"></i></button>
            </form>
          </td>
        </tr>
        <div class="modal fade" id="er<?= $r['id'] ?>"><div class="modal-dialog"><div class="modal-content">
          <form method="POST" enctype="multipart/form-data">
            <?= csrf_field() ?><input type="hidden" name="action" value="update"><input type="hidden" name="id" value="<?= $r['id'] ?>">
            <div class="modal-header"><h5>Edit Room</h5><button class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
              <input class="form-control mb-2" name="room_number" value="<?= htmlspecialchars($r['room_number']) ?>" required>
              <select class="form-control mb-2" name="room_type">
                <?php foreach (['single','double','suite','deluxe'] as $t): ?>
                  <option <?= $r['room_type']==$t?'selected':'' ?>><?= $t ?></option>
                <?php endforeach; ?>
              </select>
              <input class="form-control mb-2" type="number" step="0.01" name="price" value="<?= $r['price'] ?>" required>
              <input class="form-control mb-2" type="number" name="capacity" value="<?= $r['capacity'] ?>">
              <textarea class="form-control mb-2" name="description"><?= htmlspecialchars($r['description']) ?></textarea>
              <select class="form-control mb-2" name="status">
                <?php foreach (['available','booked','maintenance'] as $s): ?>
                  <option <?= $r['status']==$s?'selected':'' ?>><?= $s ?></option>
                <?php endforeach; ?>
              </select>
              <input class="form-control" type="file" name="image" accept="image/*">
            </div>
            <div class="modal-footer"><button class="btn btn-primary">Save</button></div>
          </form>
        </div></div></div>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div></div>
</div></div>

<div class="modal fade" id="newRoom"><div class="modal-dialog"><div class="modal-content">
  <form method="POST" enctype="multipart/form-data">
    <?= csrf_field() ?><input type="hidden" name="action" value="create">
    <div class="modal-header"><h5>Add Room</h5><button class="btn-close" data-bs-dismiss="modal"></button></div>
    <div class="modal-body">
      <input class="form-control mb-2" name="room_number" placeholder="Room Number (e.g. 101)" required>
      <select class="form-control mb-2" name="room_type">
        <option value="single">Single</option><option value="double">Double</option>
        <option value="suite">Suite</option><option value="deluxe">Deluxe</option>
      </select>
      <input class="form-control mb-2" type="number" step="0.01" name="price" placeholder="Price per night" required>
      <input class="form-control mb-2" type="number" name="capacity" placeholder="Capacity" value="2">
      <textarea class="form-control mb-2" name="description" placeholder="Description"></textarea>
      <select class="form-control mb-2" name="status">
        <option value="available">Available</option><option value="maintenance">Maintenance</option>
      </select>
      <label class="small text-muted">Image (optional)</label>
      <input class="form-control" type="file" name="image" accept="image/*">
    </div>
    <div class="modal-footer"><button class="btn btn-primary">Create</button></div>
  </form>
</div></div></div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
