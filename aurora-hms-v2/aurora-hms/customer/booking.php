<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/security.php';
require_once __DIR__ . '/../includes/helpers.php';
require_role('customer');
$pageTitle = 'Book a Room';
$u = current_user();
$cust = db()->prepare('SELECT id FROM customers WHERE user_id=?');
$cust->execute([$u['id']]); $cid = (int)$cust->fetchColumn();

$typeId = (int)($_GET['type'] ?? $_POST['type'] ?? 0);
$checkIn = $_GET['check_in'] ?? $_POST['check_in'] ?? date('Y-m-d');
$checkOut = $_GET['check_out'] ?? $_POST['check_out'] ?? date('Y-m-d', strtotime('+2 day'));
$guests = (int)($_GET['guests'] ?? $_POST['guests'] ?? 1);

$rt = null;
if ($typeId) { $s = db()->prepare('SELECT * FROM room_types WHERE id=?'); $s->execute([$typeId]); $rt = $s->fetch(); }
if (!$rt) { header('Location: ' . base_url('rooms.php')); exit; }

if (strtotime($checkOut) <= strtotime($checkIn)) {
  $checkOut = date('Y-m-d', strtotime($checkIn . ' +1 day'));
}
$nights = max(1, (int)((strtotime($checkOut) - strtotime($checkIn)) / 86400));
$subtotal = $nights * (float)$rt['base_price'];
$tax = $subtotal * TAX_RATE; $svc = $subtotal * SERVICE_RATE;
$total = $subtotal + $tax + $svc;

$error = '';
if ($_SERVER['REQUEST_METHOD']==='POST' && ($_POST['step'] ?? '') === 'confirm') {
  csrf_check($_POST['csrf']??null);
  // Find a room of this type not occupied AND not already booked during the requested window.
  $room = db()->prepare("
    SELECT r.id FROM rooms r
    WHERE r.type_id=? AND r.status IN ('available','dirty')
      AND r.id NOT IN (
        SELECT room_id FROM bookings
        WHERE deleted_at IS NULL
          AND status IN ('pending','confirmed','checked-in')
          AND NOT (check_out <= ? OR check_in >= ?)
      )
    ORDER BY r.id LIMIT 1");
  $room->execute([$typeId, $checkIn, $checkOut]);
  $rid = (int)$room->fetchColumn();
  if (!$rid) { $error = 'Sorry — no rooms of this type are available for those dates. Please pick different dates.'; }
  else {
    $method = $_POST['method'] ?? 'card';
    if (!in_array($method, ['card','bkash','cash'], true)) $method = 'card';
    $code = 'BK-' . strtoupper(bin2hex(random_bytes(3)));
    try {
      db()->beginTransaction();
      db()->prepare('INSERT INTO bookings (code,customer_id,room_id,check_in,check_out,nights,guests,total,status) VALUES (?,?,?,?,?,?,?,?,\'confirmed\')')
          ->execute([$code,$cid,$rid,$checkIn,$checkOut,$nights,$guests,$total]);
      $bid = (int)db()->lastInsertId();
      db()->prepare('INSERT INTO payments (booking_id,method,reference,amount,status) VALUES (?,?,?,?,\'paid\')')
          ->execute([$bid, $method, strtoupper($method.'-'.bin2hex(random_bytes(4))), $total]);
      db()->prepare('INSERT INTO invoices (booking_id,subtotal,tax,service,total) VALUES (?,?,?,?,?)')->execute([$bid,$subtotal,$tax,$svc,$total]);
      db()->commit();

      // Notify admin (broadcast to admin role) and the customer
      notify([
        'role'=>'admin','type'=>'booking',
        'title'=>"New booking $code",
        'body'=>$u['name']." booked ".$rt['name']." Room · ".money($total),
        'link'=>'admin/bookings.php',
      ]);
      notify([
        'user_id'=>$u['id'],'type'=>'booking',
        'title'=>"Booking confirmed: $code",
        'body'=>"Your ".$rt['name']." Room is booked for $nights night(s).",
        'link'=>'customer/my-bookings.php',
      ]);
      audit('booking_create', 'bookings', $bid);
      flash('Booking confirmed! Code: ' . $code);
      header('Location: ' . base_url('customer/invoice.php?id='.$bid)); exit;
    } catch (Throwable $e) { db()->rollBack(); $error = 'Booking failed: ' . $e->getMessage(); }
  }
}
include __DIR__ . '/../includes/dash-header.php'; ?>
<?php if ($error): ?><div class="flash flash-error"><?= e($error) ?></div><?php endif; ?>
<div class="detail-grid">
  <div>
    <div class="card"><div class="card-img" style="aspect-ratio:16/9"><img src="<?= e(image_url($rt['image'])) ?>" alt=""></div>
      <div class="card-body"><h2><?= e($rt['name']) ?> Room</h2><p class="muted" style="margin-top:8px"><?= e($rt['description']) ?></p>
        <table style="margin-top:18px;width:100%"><tbody>
          <tr><td><strong>Check-in</strong></td><td><?= e($checkIn) ?></td></tr>
          <tr><td><strong>Check-out</strong></td><td><?= e($checkOut) ?></td></tr>
          <tr><td><strong>Nights</strong></td><td><?= $nights ?></td></tr>
          <tr><td><strong>Guests</strong></td><td><?= $guests ?></td></tr>
        </tbody></table>
      </div>
    </div>
  </div>
  <div class="booking-card">
    <h3>Payment Summary</h3>
    <table style="margin:14px 0;width:100%">
      <tr><td>Subtotal (<?= $nights ?> × <?= money($rt['base_price']) ?>)</td><td style="text-align:right"><?= money($subtotal) ?></td></tr>
      <tr><td>Tax (<?= (int)(TAX_RATE*100) ?>%)</td><td style="text-align:right"><?= money($tax) ?></td></tr>
      <tr><td>Service (<?= (int)(SERVICE_RATE*100) ?>%)</td><td style="text-align:right"><?= money($svc) ?></td></tr>
      <tr style="border-top:2px solid var(--line);font-weight:700;font-size:1.1rem"><td style="padding-top:10px">Total</td><td style="text-align:right;padding-top:10px;color:var(--gold-2,#b8862d)"><?= money($total) ?></td></tr>
    </table>
    <form method="post">
      <?= csrf_field() ?>
      <input type="hidden" name="step" value="confirm">
      <input type="hidden" name="type" value="<?= (int)$typeId ?>">
      <input type="hidden" name="check_in" value="<?= e($checkIn) ?>">
      <input type="hidden" name="check_out" value="<?= e($checkOut) ?>">
      <input type="hidden" name="guests" value="<?= $guests ?>">
      <div class="form-group"><label>Payment Method</label>
        <select name="method" required>
          <option value="card">💳 Credit/Debit Card</option>
          <option value="bkash">📱 bKash</option>
          <option value="cash">💵 Pay at Hotel (Cash)</option>
        </select>
      </div>
      <button class="btn btn-primary" style="width:100%;justify-content:center"><i class="fa-solid fa-lock"></i> Confirm & Pay <?= money($total) ?></button>
    </form>
    <p class="muted" style="font-size:.8rem;margin-top:12px;text-align:center">Free cancellation up to 24h before check-in.</p>
  </div>
</div>
<?php include __DIR__ . '/../includes/dash-footer.php'; ?>
