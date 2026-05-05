<?php
require_once __DIR__ . '/../config/config.php';
requireLogin();
$pdo = (new Database())->connect();
$bid=(int)($_GET['booking_id'] ?? 0);

$stmt=$pdo->prepare("SELECT b.*, u.name AS uname, u.email, u.phone, u.address,
    r.room_number, r.room_type, r.price
    FROM bookings b JOIN users u ON u.id=b.user_id JOIN rooms r ON r.id=b.room_id
    WHERE b.id=?");
$stmt->execute([$bid]); $b=$stmt->fetch();
if (!$b) die('Booking not found');

// Customers can only see their own invoices
if (currentUser()['role']==='customer' && $b['user_id'] != $_SESSION['user_id']) {
    http_response_code(403); die('Forbidden');
}

$pays=$pdo->prepare("SELECT * FROM payments WHERE booking_id=? ORDER BY paid_at");
$pays->execute([$bid]); $payments=$pays->fetchAll();
$paid = array_sum(array_map(fn($p)=>$p['payment_status']==='paid'||$p['payment_status']==='partial'?$p['amount']:0,$payments));
$due = $b['total_amount'] - $paid;

$pageTitle='Invoice #'.$bid;
include __DIR__ . '/../includes/header.php';
?>
<div class="container py-4">
  <div class="invoice-box">
    <div class="d-flex justify-content-between align-items-start mb-4">
      <div>
        <h2 class="text-primary mb-0"><?= SITE_NAME ?></h2>
        <small class="text-muted">123 Hotel Street, Dhaka · +880 1700-000000</small>
      </div>
      <div class="text-end">
        <h4 class="mb-0">INVOICE</h4>
        <small>#INV-<?= str_pad($b['id'],5,'0',STR_PAD_LEFT) ?></small><br>
        <small>Date: <?= formatDate($b['created_at']) ?></small>
      </div>
    </div>
    <hr>
    <div class="row mb-3">
      <div class="col-md-6">
        <strong>Bill To:</strong><br>
        <?= htmlspecialchars($b['uname']) ?><br>
        <?= htmlspecialchars($b['email']) ?><br>
        <?= htmlspecialchars($b['phone']) ?><br>
        <?= htmlspecialchars($b['address']) ?>
      </div>
      <div class="col-md-6 text-md-end">
        <strong>Booking #<?= $b['id'] ?></strong><br>
        Status: <?= statusBadge($b['status']) ?>
      </div>
    </div>
    <table class="table">
      <thead class="table-dark"><tr><th>Description</th><th>Check-In</th><th>Check-Out</th><th>Nights</th><th>Rate</th><th class="text-end">Amount</th></tr></thead>
      <tbody><tr>
        <td>Room <?= htmlspecialchars($b['room_number']) ?> (<?= ucfirst($b['room_type']) ?>)</td>
        <td><?= formatDate($b['check_in_date']) ?></td>
        <td><?= formatDate($b['check_out_date']) ?></td>
        <td><?= $b['total_nights'] ?></td>
        <td><?= formatCurrency($b['price']) ?></td>
        <td class="text-end"><strong><?= formatCurrency($b['total_amount']) ?></strong></td>
      </tr></tbody>
      <tfoot>
        <tr><td colspan="5" class="text-end">Subtotal:</td><td class="text-end"><?= formatCurrency($b['total_amount']) ?></td></tr>
        <tr><td colspan="5" class="text-end">Paid:</td><td class="text-end text-success"><?= formatCurrency($paid) ?></td></tr>
        <tr class="table-warning"><td colspan="5" class="text-end"><strong>Balance Due:</strong></td><td class="text-end"><strong><?= formatCurrency($due) ?></strong></td></tr>
      </tfoot>
    </table>

    <?php if ($payments): ?>
    <h6 class="mt-4">Payment History</h6>
    <table class="table table-sm">
      <thead><tr><th>Date</th><th>Method</th><th>Status</th><th>Reference</th><th class="text-end">Amount</th></tr></thead>
      <tbody><?php foreach ($payments as $p): ?>
        <tr>
          <td><?= formatDateTime($p['paid_at']) ?></td>
          <td class="text-uppercase"><?= $p['payment_method'] ?></td>
          <td><?= statusBadge($p['payment_status']) ?></td>
          <td><small><?= htmlspecialchars($p['transaction_ref']) ?></small></td>
          <td class="text-end"><?= formatCurrency($p['amount']) ?></td>
        </tr>
      <?php endforeach; ?></tbody>
    </table>
    <?php endif; ?>

    <p class="text-muted small mt-4">Thank you for choosing <?= SITE_NAME ?>. We look forward to your next stay!</p>
    <div class="text-end no-print">
      <button class="btn btn-primary" onclick="printInvoice()"><i class="fa fa-print"></i> Print / Save PDF</button>
      <a href="javascript:history.back()" class="btn btn-secondary">Back</a>
    </div>
  </div>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
