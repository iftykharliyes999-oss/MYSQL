<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/security.php';
require_once __DIR__ . '/../includes/helpers.php';
require_role('admin','staff');
$pageTitle = 'Customer Messages';
$u = current_user();

if ($_SERVER['REQUEST_METHOD']==='POST') {
  csrf_check($_POST['csrf'] ?? null);
  $tid = (int)$_POST['thread_id'];
  $body = trim($_POST['body'] ?? '');
  if ($body !== '' && $tid) {
    db()->prepare("INSERT INTO messages (thread_id,sender_id,sender_role,body) VALUES (?,?,?,?)")
        ->execute([$tid, $u['id'], $u['role'], $body]);
    db()->prepare("UPDATE message_threads SET status='answered', last_activity=NOW() WHERE id=?")->execute([$tid]);
    // Notify customer
    $info = db()->prepare("SELECT t.subject, c.user_id FROM message_threads t LEFT JOIN customers c ON c.id=t.customer_id WHERE t.id=?");
    $info->execute([$tid]); $row = $info->fetch();
    if ($row && $row['user_id']) {
      notify([
        'user_id'=>(int)$row['user_id'],'type'=>'message',
        'title'=>'New reply from hotel',
        'body'=>$row['subject'],
        'link'=>'customer/messages.php?t='.$tid,
      ]);
    }
    flash('Reply sent.');
  }
  header('Location: ' . base_url('admin/messages.php?t='.$tid)); exit;
}

$threads = db()->query("
  SELECT t.*, u.name AS customer_name, u.email AS customer_email,
    (SELECT COUNT(*) FROM messages m WHERE m.thread_id=t.id AND m.is_read=0 AND m.sender_role IN ('customer','guest')) AS unread,
    (SELECT body FROM messages m WHERE m.thread_id=t.id ORDER BY m.created_at DESC LIMIT 1) AS last_msg
  FROM message_threads t
  LEFT JOIN customers c ON c.id=t.customer_id
  LEFT JOIN users u ON u.id=c.user_id
  ORDER BY t.last_activity DESC")->fetchAll();

$active = (int)($_GET['t'] ?? ($threads[0]['id'] ?? 0));
$activeThread = null; $messages = [];
if ($active) {
  $s = db()->prepare("SELECT t.*, u.name AS customer_name, u.email AS customer_email
    FROM message_threads t LEFT JOIN customers c ON c.id=t.customer_id LEFT JOIN users u ON u.id=c.user_id WHERE t.id=?");
  $s->execute([$active]); $activeThread = $s->fetch();
  if ($activeThread) {
    $ms = db()->prepare("SELECT m.*, u.name AS sender_name FROM messages m LEFT JOIN users u ON u.id=m.sender_id WHERE thread_id=? ORDER BY m.created_at ASC");
    $ms->execute([$active]); $messages = $ms->fetchAll();
    db()->prepare("UPDATE messages SET is_read=1 WHERE thread_id=? AND sender_role IN ('customer','guest')")->execute([$active]);
  }
}
include __DIR__ . '/../includes/dash-header.php';
?>
<style>
.chat-wrap{display:grid;grid-template-columns:320px 1fr;gap:18px;height:calc(100vh - 200px);min-height:560px}
.chat-list{background:#fff;border:1px solid var(--line);border-radius:14px;overflow:auto}
.chat-item{padding:14px 16px;border-bottom:1px solid #f1f5f9;display:block;color:inherit;text-decoration:none;position:relative}
.chat-item:hover{background:#f8fafc}
.chat-item.active{background:#fef9ec;border-left:3px solid #d4a857}
.chat-item .name{font-weight:600}
.chat-item .preview{color:#64748b;font-size:.82rem;margin-top:4px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.chat-item .badge{position:absolute;top:14px;right:14px;background:#dc2626;color:#fff;font-size:.7rem;font-weight:700;border-radius:999px;padding:2px 8px}
.chat-pane{background:#fff;border:1px solid var(--line);border-radius:14px;display:flex;flex-direction:column}
.chat-head{padding:16px 20px;border-bottom:1px solid var(--line);display:flex;justify-content:space-between;align-items:center}
.chat-body{flex:1;overflow:auto;padding:20px;background:#f8fafc}
.bubble{max-width:70%;padding:10px 14px;border-radius:14px;margin-bottom:10px;font-size:.92rem;line-height:1.4}
.bubble.in{background:#fff;border:1px solid var(--line);color:#0f172a}
.bubble.out{background:#0f172a;color:#fff;margin-left:auto;border-bottom-right-radius:4px}
.bubble small{display:block;opacity:.65;margin-top:4px;font-size:.7rem}
.chat-foot{padding:14px 16px;border-top:1px solid var(--line);display:flex;gap:8px}
.chat-foot textarea{flex:1;border:1px solid var(--line);border-radius:10px;padding:10px;resize:none;font:inherit}
@media(max-width:900px){.chat-wrap{grid-template-columns:1fr;height:auto}}
</style>

<div class="chat-wrap">
  <div class="chat-list">
    <?php if (!$threads): ?><div style="padding:30px;text-align:center;color:#94a3b8">No conversations yet</div><?php endif; ?>
    <?php foreach ($threads as $th): ?>
      <a class="chat-item <?= $th['id']==$active?'active':'' ?>" href="?t=<?= $th['id'] ?>">
        <div class="name"><?= e($th['customer_name'] ?: $th['guest_name'] ?: 'Guest') ?></div>
        <div class="preview"><strong><?= e($th['subject']) ?></strong> — <?= e(mb_substr((string)$th['last_msg'],0,40)) ?></div>
        <?php if ($th['unread']): ?><span class="badge"><?= $th['unread'] ?></span><?php endif; ?>
      </a>
    <?php endforeach; ?>
  </div>

  <div class="chat-pane">
    <?php if (!$activeThread): ?>
      <div style="margin:auto;color:#94a3b8;text-align:center;padding:40px">
        <i class="fa-solid fa-comments" style="font-size:3rem;opacity:.4"></i>
        <p style="margin-top:14px">Select a conversation to view messages</p>
      </div>
    <?php else: ?>
      <div class="chat-head">
        <div>
          <strong><?= e($activeThread['subject']) ?></strong>
          <div class="muted" style="font-size:.85rem;margin-top:2px">
            <?= e($activeThread['customer_name'] ?: $activeThread['guest_name'] ?: 'Guest') ?> · <?= e($activeThread['customer_email'] ?: $activeThread['guest_email'] ?: '—') ?>
          </div>
        </div>
        <span class="pill pill-<?= e($activeThread['status']) ?>"><?= e($activeThread['status']) ?></span>
      </div>
      <div class="chat-body" id="chatBody">
        <?php foreach ($messages as $m): $out = in_array($m['sender_role'],['admin','staff'],true); ?>
          <div class="bubble <?= $out?'out':'in' ?>">
            <?= nl2br(e($m['body'])) ?>
            <small><?= e($m['sender_name'] ?: ucfirst($m['sender_role'])) ?> · <?= e(date('M j, H:i', strtotime($m['created_at']))) ?></small>
          </div>
        <?php endforeach; ?>
      </div>
      <form class="chat-foot" method="post">
        <?= csrf_field() ?>
        <input type="hidden" name="thread_id" value="<?= $activeThread['id'] ?>">
        <textarea name="body" rows="2" placeholder="Type your reply…" required></textarea>
        <button class="btn btn-primary"><i class="fa-solid fa-paper-plane"></i> Send</button>
      </form>
    <?php endif; ?>
  </div>
</div>
<script>const cb=document.getElementById('chatBody'); if(cb) cb.scrollTop=cb.scrollHeight;</script>
<?php include __DIR__ . '/../includes/dash-footer.php'; ?>
