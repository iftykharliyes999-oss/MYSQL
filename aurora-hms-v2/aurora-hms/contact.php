<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/security.php';
require_once __DIR__ . '/includes/helpers.php';
$pageTitle = 'Contact';
$u = current_user();

if ($_SERVER['REQUEST_METHOD']==='POST') {
  csrf_check($_POST['csrf']??null);
  $name = trim($_POST['name'] ?? '');
  $email = clean_email($_POST['email'] ?? '');
  $subject = trim($_POST['subject'] ?? '');
  $body = trim($_POST['message'] ?? '');
  if ($name && $email && $subject && $body) {
    try {
      $cid = null;
      if ($u && $u['role']==='customer') {
        $s = db()->prepare('SELECT id FROM customers WHERE user_id=?'); $s->execute([$u['id']]);
        $cid = (int)$s->fetchColumn() ?: null;
      }
      db()->prepare("INSERT INTO message_threads (customer_id,guest_name,guest_email,subject,status) VALUES (?,?,?,?,'open')")
          ->execute([$cid, $cid ? null : $name, $cid ? null : $email, $subject]);
      $tid = (int)db()->lastInsertId();
      db()->prepare("INSERT INTO messages (thread_id,sender_id,sender_role,body) VALUES (?,?,?,?)")
          ->execute([$tid, $u['id'] ?? null, $cid ? 'customer' : 'guest', $body]);
      notify([
        'role'=>'admin','type'=>'message',
        'title'=>'New inquiry: '.$subject,
        'body'=>$name.' ('.$email.') sent a new contact-form message.',
        'link'=>'admin/messages.php?t='.$tid,
      ]);
      flash('Thank you! Your message has been sent — we will reply by email and in your account.');
    } catch (Throwable $e) {
      flash('Could not send message: ' . $e->getMessage(), 'error');
    }
  } else {
    flash('Please fill in all fields.', 'error');
  }
  header('Location: '.base_url('contact.php')); exit;
}
include __DIR__ . '/includes/header.php'; ?>
<div class="page-head"><div class="container"><h1>Contact Us</h1><p>We'd love to hear from you.</p></div></div>
<div class="container" style="padding-bottom:80px">
  <div class="detail-grid">
    <div class="form-card" style="margin:0;max-width:none">
      <h2>Send a Message</h2>
      <form method="post"><?= csrf_field() ?>
        <div class="form-row">
          <div class="form-group"><label>Name</label><input name="name" required value="<?= e($u['name'] ?? '') ?>"></div>
          <div class="form-group"><label>Email</label><input type="email" name="email" required value="<?= e($u['email'] ?? '') ?>"></div>
        </div>
        <div class="form-group"><label>Subject</label><input name="subject" required></div>
        <div class="form-group"><label>Message</label><textarea name="message" rows="5" required></textarea></div>
        <button class="btn btn-primary" style="width:100%;justify-content:center">Send Message</button>
      </form>
    </div>
    <div>
      <h2>Reach Us</h2>
      <div class="feature" style="text-align:left;margin-top:20px"><i class="fa-solid fa-location-dot"></i><h4><?= e(HOTEL_ADDRESS) ?></h4></div>
      <div class="feature" style="text-align:left;margin-top:14px"><i class="fa-solid fa-phone"></i><h4><?= e(HOTEL_PHONE) ?></h4></div>
      <div class="feature" style="text-align:left;margin-top:14px"><i class="fa-solid fa-envelope"></i><h4><?= e(HOTEL_EMAIL) ?></h4></div>
    </div>
  </div>
</div>
<?php include __DIR__ . '/includes/footer.php'; ?>
