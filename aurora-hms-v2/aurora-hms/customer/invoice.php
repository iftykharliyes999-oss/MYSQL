<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/security.php';
require_login();
$id = (int)($_GET['id'] ?? 0);
$u = current_user();

$q = db()->prepare('SELECT b.*, u.name AS guest, u.email, u.phone, r.number AS room_no, rt.name AS room_type, c.user_id FROM bookings b JOIN customers c ON c.id=b.customer_id JOIN users u ON u.id=c.user_id JOIN rooms r ON r.id=b.room_id JOIN room_types rt ON rt.id=r.type_id WHERE b.id=?');
$q->execute([$id]); $b = $q->fetch();
if (!$b) { header('Location: '.base_url('index.php')); exit; }
if ($u['role'] === 'customer' && (int)$b['user_id'] !== (int)$u['id']) { header('Location: '.base_url('unauthorized.php')); exit; }

$inv = db()->prepare('SELECT * FROM invoices WHERE booking_id=?'); $inv->execute([$id]); $inv = $inv->fetch();
if (!$inv) {
  $sub = (float)$b['total']; $tax = $sub * TAX_RATE; $svc = $sub * SERVICE_RATE;
  db()->prepare('INSERT INTO invoices (booking_id,subtotal,tax,service,total) VALUES (?,?,?,?,?)')->execute([$id,$sub,$tax,$svc,$sub+$tax+$svc]);
  $inv = ['subtotal'=>$sub,'tax'=>$tax,'service'=>$svc,'total'=>$sub+$tax+$svc,'issued_at'=>date('Y-m-d H:i:s')];
}
$pay = db()->prepare('SELECT * FROM payments WHERE booking_id=? LIMIT 1'); $pay->execute([$id]); $pay = $pay->fetch();
$pageTitle = 'Invoice ' . $b['code'];
?><!doctype html><html><head><meta charset="utf-8"><title><?= $pageTitle ?></title>
<link rel="stylesheet" href="<?= base_url('assets/css/style.css') ?>">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&family=Playfair+Display:wght@600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head><body>
<div class="invoice">
  <div class="inv-head">
    <div><h1><?= e(HOTEL_NAME) ?></h1><p class="muted"><?= e(HOTEL_ADDRESS) ?></p><p class="muted"><?= e(HOTEL_PHONE) ?> · <?= e(HOTEL_EMAIL) ?></p></div>
    <div style="text-align:right"><h2 style="color:var(--navy)">INVOICE</h2><p><strong>#<?= e($b['code']) ?></strong></p><p class="muted">Issued: <?= e(date('M d, Y', strtotime($inv['issued_at']))) ?></p></div>
  </div>
  <div style="display:grid;grid-template-columns:1fr 1fr;gap:30px;margin-bottom:30px">
    <div><h4>Billed To</h4><p style="margin-top:8px"><strong><?= e($b['guest']) ?></strong></p><p class="muted"><?= e($b['email']) ?></p><p class="muted"><?= e($b['phone']) ?></p></div>
    <div><h4>Booking Details</h4><p style="margin-top:8px"><strong>Room:</strong> <?= e($b['room_type']) ?> #<?= e($b['room_no']) ?></p><p><strong>Check-in:</strong> <?= e($b['check_in']) ?></p><p><strong>Check-out:</strong> <?= e($b['check_out']) ?></p><p><strong>Nights:</strong> <?= (int)$b['nights'] ?> · <strong>Guests:</strong> <?= (int)$b['guests'] ?></p></div>
  </div>
  <table><thead><tr><th>Description</th><th style="text-align:right">Amount</th></tr></thead><tbody>
    <tr><td><?= e($b['room_type']) ?> Room × <?= (int)$b['nights'] ?> night<?= $b['nights']>1?'s':'' ?></td><td style="text-align:right"><?= money($inv['subtotal']) ?></td></tr>
    <tr><td>Tax (<?= TAX_RATE*100 ?>%)</td><td style="text-align:right"><?= money($inv['tax']) ?></td></tr>
    <tr><td>Service Charge (<?= SERVICE_RATE*100 ?>%)</td><td style="text-align:right"><?= money($inv['service']) ?></td></tr>
  </tbody></table>
  <div class="inv-total"><div>Total Amount</div><div class="big"><?= money($inv['total']) ?></div></div>
  <?php if ($pay): ?><p style="margin-top:20px"><strong>Payment:</strong> <?= strtoupper(e($pay['method'])) ?> · Ref <code><?= e($pay['reference']) ?></code> · <span class="pill pill-<?= e($pay['status']) ?>"><?= e($pay['status']) ?></span></p><?php endif; ?>
  <p style="text-align:center;margin-top:40px;color:var(--slate-2);font-style:italic">Thank you for choosing <?= e(HOTEL_NAME) ?>. We look forward to welcoming you again.</p>
  <div style="margin-top:30px;text-align:center" class="no-print">
    <button onclick="window.print()" class="btn btn-primary"><i class="fa-solid fa-print"></i> Print Invoice</button>
    <a href="<?= base_url('customer/my-bookings.php') ?>" class="btn btn-outline">← Back</a>
  </div>
</div>
</body></html>
