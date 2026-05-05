<?php
require_once __DIR__ . '/../config/config.php';
requireRole(['admin','staff']);
$pdo = (new Database())->connect();

if ($_SERVER['REQUEST_METHOD']==='POST') {
    csrf_verify();
    $action = $_POST['action'] ?? '';
    if ($action==='create') {
        $bid=(int)$_POST['booking_id'];
        $amount=(float)$_POST['amount'];
        $method=$_POST['payment_method'];
        $status=$_POST['payment_status'];
        $ref=sanitize($_POST['transaction_ref'] ?? '');
        if (!in_array($method,['cash','card'])) { flash('p','Invalid payment method.','danger'); redirect(BASE_URL.'/admin/payments.php'); }
        $pdo->prepare("INSERT INTO payments (booking_id,amount,payment_method,payment_status,transaction_ref) VALUES (?,?,?,?,?)")
            ->execute([$bid,$amount,$method,$status,$ref]);
        flash('p','Payment recorded.','success');
    } elseif ($action==='update_status') {
        $pdo->prepare("UPDATE payments SET payment_status=? WHERE id=?")
            ->execute([$_POST['payment_status'],(int)$_POST['id']]);
        flash('p','Payment updated.','success');
    }
    redirect(BASE_URL . '/admin/payments.php');
}

$payments = $pdo->query("SELECT p.*, b.user_id, b.total_amount, u.name AS uname, r.room_number
    FROM payments p
    JOIN bookings b ON b.id=p.booking_id
    JOIN users u ON u.id=b.user_id
    JOIN rooms r ON r.id=b.room_id
    ORDER BY p.paid_at DESC")->fetchAll();

$dueBookings = $pdo->query("SELECT b.*, u.name AS uname, r.room_number,
    COALESCE((SELECT SUM(amount) FROM payments WHERE booking_id=b.id AND payment_status IN ('paid','partial')),0) AS paid_amt
    FROM bookings b JOIN users u ON u.id=b.user_id JOIN rooms r ON r.id=b.room_id
    HAVING paid_amt < b.total_amount
    ORDER BY b.created_at DESC")->fetchAll();

$pageTitle='Payments';
include __DIR__ . '/../includes/header.php';
?>
<div class="dashboard-wrapper">
<?php include __DIR__ . '/../includes/sidebar_admin.php'; ?>
<div class="main-content">
  <div class="d-flex justify-content-between mb-3">
    <h3>Payments & Billing</h3>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#newPay"><i class="fa fa-plus"></i> Record Payment</button>
  </div>
  <?= flash('p') ?>
  <div class="card mb-4"><div class="card-header"><strong>All Payments</strong></div>
  <div class="table-responsive">
    <table class="table align-middle mb-0">
      <thead><tr><th>#</th><th>Booking</th><th>Customer</th><th>Room</th><th>Amount</th><th>Method</th><th>Status</th><th>Ref</th><th>Date</th><th>Action</th></tr></thead>
      <tbody>
        <?php foreach ($payments as $p): ?>
        <tr>
          <td>#<?= $p['id'] ?></td>
          <td>#<?= $p['booking_id'] ?></td>
          <td><?= htmlspecialchars($p['uname']) ?></td>
          <td><?= htmlspecialchars($p['room_number']) ?></td>
          <td><?= formatCurrency($p['amount']) ?></td>
          <td><span class="badge bg-dark text-uppercase"><?= $p['payment_method'] ?></span></td>
          <td><?= statusBadge($p['payment_status']) ?></td>
          <td><small><?= htmlspecialchars($p['transaction_ref']) ?></small></td>
          <td><?= formatDateTime($p['paid_at']) ?></td>
          <td><a class="btn btn-sm btn-outline-primary" href="<?= BASE_URL ?>/admin/invoice.php?booking_id=<?= $p['booking_id'] ?>"><i class="fa fa-file-invoice"></i></a></td>
        </tr>
        <?php endforeach; ?>
        <?php if (!$payments): ?><tr><td colspan="10" class="text-center text-muted">No payments yet.</td></tr><?php endif; ?>
      </tbody>
    </table>
  </div></div>
</div></div>

<div class="modal fade" id="newPay"><div class="modal-dialog"><div class="modal-content">
  <form method="POST">
    <?= csrf_field() ?><input type="hidden" name="action" value="create">
    <div class="modal-header"><h5>Record Payment</h5><button class="btn-close" data-bs-dismiss="modal"></button></div>
    <div class="modal-body">
      <label>Booking (with outstanding balance)</label>
      <select class="form-control mb-2" name="booking_id" required>
        <?php foreach ($dueBookings as $b): $due=$b['total_amount']-$b['paid_amt']; ?>
          <option value="<?= $b['id'] ?>">
            #<?= $b['id'] ?> · <?= htmlspecialchars($b['uname']) ?> · Room <?= $b['room_number'] ?>
            · Due <?= formatCurrency($due) ?>
          </option>
        <?php endforeach; ?>
      </select>
      <label>Amount</label>
      <input class="form-control mb-2" type="number" step="0.01" name="amount" required>
      <label>Method</label>
      <select class="form-control mb-2" name="payment_method">
        <option value="cash">Cash</option><option value="card">Card</option>
      </select>
      <label>Status</label>
      <select class="form-control mb-2" name="payment_status">
        <option value="paid">Paid</option><option value="partial">Partial</option><option value="due">Due</option>
      </select>
      <label>Transaction Reference (optional)</label>
      <input class="form-control" name="transaction_ref" placeholder="e.g. TXN12345">
    </div>
    <div class="modal-footer"><button class="btn btn-primary">Save</button></div>
  </form>
</div></div></div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
