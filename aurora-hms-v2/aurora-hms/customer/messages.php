<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/security.php';
require_once __DIR__ . '/../includes/helpers.php';
require_role('customer');
$pageTitle = 'Messages';
$u = current_user();
$cust = db()->prepare('SELECT id FROM customers WHERE user_id=?'); $cust->execute([$u['id']]);
$cid = (int)$cust->fetchColumn();

if ($_SERVER['REQUEST_METHOD']==='POST') {
  csrf_check($_POST['csrf'] ?? null);
  $action = $_POST['action'] ?? '';
  if ($action === 'new') {
    $subject = trim($_POST['subject'] ?? ''); $body = trim($_POST['body'] ?? '');
    if ($subject && $body) {
      db()->prepare("INSERT INTO message_threads (customer_id,subject,status) VALUES (?,?,'open')")->execute([$cid, $subject]);
      $tid = (int)db()->lastInsertId();
      db()->prepare("INSERT INTO messages (thread_id,sender_id,sender_role,body) VALUES (?,?,'customer',?)")->execute([$tid, $u['id'], $body]);
      notify([
        'role'=>'admin','type'=>'message',
        'title'=>'New inquiry: '.$subject, 'body'=>$u['name'].' sent a new message.',
        'link'=>'admin/messages.php?t='.$tid,
      ]);
      flash('Message sent. Admin will reply soon.');
      header('Location: ' . base_url('customer/messages.php?t='.$tid)); exit;
    }
  } elseif ($action === 'reply') {
    $tid = (int)$_POST['thread_id']; $body = trim($_POST['body'] ?? '');
    if ($tid && $body) {
      db()->prepare("INSERT INTO messages (thread_id,sender_id,sender_role,body) VALUES (?,?,'customer',?)")->execute([$tid, $u['id'], $body]);
      db()->prepare("UPDATE message_threads SET status='open', last_activity=NOW() WHERE id=?")->execute([$tid]);
      notify(['role'=>'admin','type'=>'message','title'=>'New reply from '.$u['name'],'body'=>mb_substr($body,0,80),'link'=>'admin/messages.php?t='.$tid]);
      header('Location: ' . base_url('customer/messages.php?t='.$tid)); exit;
    }
  }
}

$threads = db()->prepare("SELECT t.*,
    (SELECT body FROM messages m WHERE m.thread_id=t.id ORDER BY m.created_at DESC LIMIT 1) AS last_msg,
    (SELECT COUNT(*) FROM messages m WHERE m.thread_id=t.id AND m.is_read=0 AND m.sender_role IN ('admin','staff')) AS unread
  FROM message_threads t WHERE customer_id=? ORDER BY last_activity DESC");
$threads->execute([$cid]); $threads = $threads->fetchAll();

$active = (int)($_GET['t'] ?? ($threads[0]['id'] ?? 0));
$activeThread = null; $messages = [];
if ($active) {
  $s = db()->prepare("SELECT * FROM message_threads WHERE id=? AND customer_id=?");
  $s->execute([$active, $cid]); $activeThread = $s->fetch();
  if ($activeThread) {
    $ms = db()->prepare("SELECT m.*, u.name AS sender_name FROM messages m LEFT JOIN users u ON u.id=m.sender_id WHERE thread_id=? ORDER BY m.created_at ASC");
    $ms->execute([$active]); $messages = $ms->fetchAll();
    db()->prepare("UPDATE messages SET is_read=1 WHERE thread_id=? AND sender_role IN ('admin','staff')")->execute([$active]);
  }
}
include __DIR__ . '/../includes/dash-header.php';
?>
<style>
.chat-wrap{display:grid;grid-template-columns:320px 1fr;gap:18px;height:calc(100vh - 200px);min-height:560px}
.chat-list{background:#fff;border:1px solid var(--line);border-radius:14px;overflow:auto}
.chat-list header{padding:14px 16px;border-bottom:1px solid var(--line);display:flex;justify-content:space-between;align-items:center}
.chat-item{padding:14px 16px;border-bottom:1px solid #f1f5f9;display:block;color:inherit;text-decoration:none;position:relative}
.chat-item:hover{background:#f8fafc} .chat-item.active{background:#fef9ec;border-left:3px solid #d4a857}
.chat-item .badge{position:absolute;top:14px;right:14px;background:#dc2626;color:#fff;font-size:.7rem;font-weight:700;border-radius:999px;padding:2px 8px}
.chat-pane{background:#fff;border:1px solid var(--line);border-radius:14px;display:flex;flex-direction:column}
.chat-head{padding:16px 20px;border-bottom:1px solid var(--line)}
.chat-body{flex:1;overflow:auto;padding:20px;background:#f8fafc}
.bubble{max-width:70%;padding:10px 14px;border-radius:14px;margin-bottom:10px;font-size:.92rem;line-height:1.4}
.bubble.in{background:#fff;border:1px solid var(--line);color:#0f172a}
.bubble.out{background:#0f172a;color:#fff;margin-left:auto;border-bottom-right-radius:4px}
.bubble small{display:block;opacity:.65;margin-top:4px;font-size:.7rem}
.chat-foot{padding:14px 16px;border-top:1px solid var(--line);display:flex;gap:8px}
.chat-foot textarea{flex:1;border:1px solid var(--line);border-radius:10px;padding:10px;resize:none;font:inherit}
.modal-bg{position:fixed;inset:0;background:rgba(15,23,42,.55);display:none;align-items:center;justify-content:center;z-index:60;padding:20px}
.modal-bg.open{display:flex}
.modal-box{background:#fff;border-radius:14px;max-width:520px;width:100%;padding:28px}
@media(max-width:900px){.chat-wrap{grid-template-columns:1fr;height:auto}}
</style>

<div class="chat-wrap">
  <div class="chat-list">
    <header>My Conversations <button class="btn btn-sm btn-primary" onclick="document.getElementById('newM').classList.add('open')"><i class="fa-solid fa-plus"></i> New</button></header>
    <?php if (!$threads): ?><div style="padding:30px;text-align:center;color:#94a3b8">No messages yet</div><?php endif; ?>
    <?php foreach ($threads as $th): ?>
      <a class="chat-item <?= $th['id']==$active?'active':'' ?>" href="?t=<?= $th['id'] ?>">
        <div style="font-weight:600"><?= e($th['subject']) ?></div>
        <div style="color:#64748b;font-size:.82rem;margin-top:4px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis"><?= e(mb_substr((string)$th['last_msg'],0,50)) ?></div>
        <?php if ($th['unread']): ?><span class="badge"><?= $th['unread'] ?></span><?php endif; ?>
      </a>
    <?php endforeach; ?>
  </div>
  <div class="chat-pane">
    <?php if (!$activeThread): ?>
      <div style="margin:auto;color:#94a3b8;text-align:center;padding:40px">
        <i class="fa-solid fa-comments" style="font-size:3rem;opacity:.4"></i>
        <p style="margin-top:14px">Start a new conversation with the hotel</p>
        <button class="btn btn-primary" style="margin-top:14px" onclick="document.getElementById('newM').classList.add('open')"><i class="fa-solid fa-plus"></i> New Message</button>
      </div>
    <?php else: ?>
      <div class="chat-head"><strong><?= e($activeThread['subject']) ?></strong> <span class="pill pill-<?= e($activeThread['status']) ?>" style="margin-left:8px"><?= e($activeThread['status']) ?></span></div>
      <div class="chat-body" id="chatBody">
        <?php foreach ($messages as $m): $out = $m['sender_role']==='customer'; ?>
          <div class="bubble <?= $out?'out':'in' ?>">
            <?= nl2br(e($m['body'])) ?>
            <small><?= e($m['sender_name'] ?: ucfirst($m['sender_role'])) ?> · <?= e(date('M j, H:i', strtotime($m['created_at']))) ?></small>
          </div>
        <?php endforeach; ?>
      </div>
      <form class="chat-foot" method="post">
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="reply"><input type="hidden" name="thread_id" value="<?= $activeThread['id'] ?>">
        <textarea name="body" rows="2" placeholder="Write a message…" required></textarea>
        <button class="btn btn-primary"><i class="fa-solid fa-paper-plane"></i></button>
      </form>
    <?php endif; ?>
  </div>
</div>

<div class="modal-bg" id="newM"><div class="modal-box">
  <h3 style="margin-bottom:14px">New Message to Hotel</h3>
  <form method="post"><?= csrf_field() ?><input type="hidden" name="action" value="new">
    <div class="form-group"><label>Subject *</label><input name="subject" required></div>
    <div class="form-group"><label>Message *</label><textarea name="body" rows="5" required></textarea></div>
    <div style="display:flex;gap:8px;justify-content:flex-end">
      <button type="button" class="btn btn-dark" onclick="document.getElementById('newM').classList.remove('open')">Cancel</button>
      <button class="btn btn-primary"><i class="fa-solid fa-paper-plane"></i> Send</button>
    </div>
  </form>
</div></div>
<script>const cb=document.getElementById('chatBody'); if(cb) cb.scrollTop=cb.scrollHeight;</script>
<?php include __DIR__ . '/../includes/dash-footer.php'; ?>
