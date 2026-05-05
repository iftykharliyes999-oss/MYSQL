<?php
require_once __DIR__ . '/../config/config.php';
requireRole('customer');
$pdo=(new Database())->connect();

$preselect = isset($_GET['room_id']) ? (int)$_GET['room_id'] : null;

if ($_SERVER['REQUEST_METHOD']==='POST') {
    csrf_verify();
    $room_id=(int)$_POST['room_id'];
    $ci=$_POST['check_in_date']; $co=$_POST['check_out_date'];
    $request=sanitize($_POST['special_request'] ?? '');

    if (strtotime($ci) < strtotime(date('Y-m-d'))) {
        flash('bk','Check-in date cannot be in the past.','danger');
    } elseif (strtotime($co) <= strtotime($ci)) {
        flash('bk','Check-out must be after check-in.','danger');
    } elseif (!isRoomAvailable($pdo,$room_id,$ci,$co)) {
        flash('bk','Sorry, this room is not available for the selected dates.','danger');
    } else {
        $room=$pdo->prepare("SELECT price FROM rooms WHERE id=?"); $room->execute([$room_id]);
        $price=$room->fetchColumn();
        $nights=calculateNights($ci,$co);
        $total=$nights*$price;
        $pdo->prepare("INSERT INTO bookings (user_id,room_id,check_in_date,check_out_date,total_nights,total_amount,special_request,status)
                       VALUES (?,?,?,?,?,?,?,'confirmed')")
            ->execute([$_SESSION['user_id'],$room_id,$ci,$co,$nights,$total,$request]);
        flash('bk','🎉 Booking confirmed! Total: '.formatCurrency($total),'success');
        redirect(BASE_URL . '/customer/my_bookings.php');
    }
}

$rooms=$pdo->query("SELECT * FROM rooms WHERE status='available' ORDER BY price")->fetchAll();
$pageTitle='Book a Room';
include __DIR__ . '/../includes/header.php';
?>
<div class="dashboard-wrapper">
<?php include __DIR__ . '/../includes/sidebar_customer.php'; ?>
<div class="main-content">
  <h3>Book a Room</h3>
  <?= flash('bk') ?>
  <div class="card p-4">
    <form method="POST">
      <?= csrf_field() ?>
      <div class="row g-3">
        <div class="col-md-12">
          <label>Choose Room</label>
          <select class="form-control" name="room_id" id="room_select" required>
            <?php foreach ($rooms as $r): ?>
              <option value="<?= $r['id'] ?>" data-price="<?= $r['price'] ?>" <?= $preselect==$r['id']?'selected':'' ?>>
                Room <?= htmlspecialchars($r['room_number']) ?> · <?= ucfirst($r['room_type']) ?> · <?= formatCurrency($r['price']) ?>/night
              </option>
            <?php endforeach; ?>
          </select>
          <input type="hidden" id="room_price" value="<?= $rooms[0]['price'] ?? 0 ?>">
        </div>
        <div class="col-md-6"><label>Check-In Date</label>
          <input type="date" class="form-control" name="check_in_date" id="check_in_date" min="<?= date('Y-m-d') ?>" required></div>
        <div class="col-md-6"><label>Check-Out Date</label>
          <input type="date" class="form-control" name="check_out_date" id="check_out_date" min="<?= date('Y-m-d',strtotime('+1 day')) ?>" required></div>
        <div class="col-md-12"><label>Special Request (optional)</label>
          <textarea class="form-control" name="special_request" rows="2"></textarea></div>
        <div class="col-md-12">
          <div class="alert alert-light border"><strong>Estimated Total: <span id="total_preview" class="text-primary">—</span></strong></div>
        </div>
      </div>
      <button class="btn btn-primary mt-3"><i class="fa fa-check"></i> Confirm Booking</button>
    </form>
  </div>
</div></div>
<script>
document.getElementById('room_select').addEventListener('change', function(){
  document.getElementById('room_price').value = this.options[this.selectedIndex].dataset.price;
});
</script>
<?php include __DIR__ . '/../includes/footer.php'; ?>
