<?php require_once __DIR__ . '/config/db.php'; require_once __DIR__ . '/includes/security.php';
$pageTitle = 'Unauthorized'; include __DIR__ . '/includes/header.php'; ?>
<div class="container" style="padding:120px 24px;text-align:center">
  <i class="fa-solid fa-lock" style="font-size:4rem;color:var(--gold)"></i>
  <h1 style="margin-top:20px">Access Denied</h1>
  <p class="muted" style="margin-top:10px">You don't have permission to view this page.</p>
  <a href="<?= base_url('index.php') ?>" class="btn btn-primary" style="margin-top:24px">Back to Home</a>
</div>
<?php include __DIR__ . '/includes/footer.php'; ?>
