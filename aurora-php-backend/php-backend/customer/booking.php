<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/security.php';
require_role('customer');

$roomId = (int)($_GET['room'] ?? 0);
$room = db()->prepare('SELECT * FROM rooms WHERE id=:i'); $room->execute([':i'=>$roomId]);
$room = $room->fetch();
if (!$room) exit('Room not found.');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check($_POST['csrf'] ?? null);
    $checkIn  = $_POST['check_in']; $checkOut = $_POST['check_out'];
    $method   = $_POST['method'] ?? 'card';
    $nights   = max(1, (strtotime($checkOut) - strtotime($checkIn)) / 86400);
    $subtotal = $room['price'] * $nights;
    $total    = $subtotal * 1.20;

    $pdo = db(); $pdo->beginTransaction();
    try {
        $cust = $pdo->prepare('SELECT id FROM customers WHERE user_id=:u');
        $cust->execute([':u' => current_user()['id']]);
        $cid = (int)$cust->fetchColumn();
        $code = 'BK-' . random_int(1000,9999);

        $pdo->prepare('INSERT INTO bookings (code,customer_id,room_id,check_in,check_out,nights,total,status)
                       VALUES (:c,:cu,:r,:i,:o,:n,:t,"confirmed")')
            ->execute([':c'=>$code,':cu'=>$cid,':r'=>$roomId,':i'=>$checkIn,':o'=>$checkOut,':n'=>$nights,':t'=>$total]);
        $bid = (int)$pdo->lastInsertId();

        $pdo->prepare('INSERT INTO payments (booking_id,method,reference,amount) VALUES (:b,:m,:r,:a)')
            ->execute([':b'=>$bid,':m'=>$method,':r'=>strtoupper($method).'-'.random_int(10000,99999),':a'=>$total]);

        $pdo->prepare('INSERT INTO invoices (booking_id,subtotal,tax,service,total)
                       VALUES (:b,:s,:t,:sv,:tot)')
            ->execute([':b'=>$bid,':s'=>$subtotal,':t'=>$subtotal*0.15,':sv'=>$subtotal*0.05,':tot'=>$total]);

        $pdo->prepare('UPDATE rooms SET status="occupied" WHERE id=:i')->execute([':i'=>$roomId]);
        $pdo->commit();
        header('Location: my-bookings.php');
        exit;
    } catch (Throwable $e) { $pdo->rollBack(); exit('Booking failed.'); }
}
?>
<!doctype html><html><head><meta charset="utf-8"><title>Book room</title></head><body>
<h1>Book Room #<?= clean($room['number']) ?></h1>
<form method="post">
  <input type="hidden" name="csrf" value="<?= csrf_token() ?>">
  <p><label>Check-in <input type="date" name="check_in" required></label></p>
  <p><label>Check-out <input type="date" name="check_out" required></label></p>
  <p><label>Payment
    <select name="method"><option value="card">Card</option><option value="bkash">bKash</option></select>
  </label></p>
  <button>Confirm booking</button>
</form>
</body></html>