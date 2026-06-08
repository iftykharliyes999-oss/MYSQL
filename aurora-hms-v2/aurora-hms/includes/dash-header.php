<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/security.php';
require_once __DIR__ . '/helpers.php';
$u = current_user();
$flash = take_flash();
$notifs = $u ? unread_notifications($u) : [];
$notifCount = $u ? unread_count($u) : 0;
$msgCount = $u ? unread_messages_count($u) : 0;
?><!doctype html>
<html lang="en"><head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($pageTitle ?? 'Dashboard') ?> · <?= e(HOTEL_NAME) ?></title>
<link rel="stylesheet" href="<?= base_url('assets/css/style.css') ?>">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Playfair+Display:wght@500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<style>
.bell-wrap{position:relative;display:inline-block;margin-right:14px}
.bell-btn{background:#fff;border:1px solid var(--line);width:42px;height:42px;border-radius:50%;cursor:pointer;font-size:1.05rem;color:#334;position:relative}
.bell-btn:hover{background:#f8fafc}
.bell-badge{position:absolute;top:-4px;right:-4px;background:#dc2626;color:#fff;font-size:.68rem;font-weight:700;border-radius:999px;padding:2px 6px;min-width:18px;text-align:center}
.bell-pop{position:absolute;right:0;top:50px;width:340px;background:#fff;border:1px solid var(--line);border-radius:12px;box-shadow:0 10px 40px rgba(0,0,0,.12);z-index:50;display:none}
.bell-wrap.open .bell-pop{display:block}
.bell-pop header{padding:14px 16px;border-bottom:1px solid var(--line);font-weight:600;display:flex;justify-content:space-between;align-items:center}
.bell-pop .item{padding:12px 16px;border-bottom:1px solid #f1f5f9;display:block;color:inherit;text-decoration:none}
.bell-pop .item:hover{background:#f8fafc}
.bell-pop .item small{display:block;color:#64748b;font-size:.78rem;margin-top:2px}
.bell-pop .empty{padding:24px;text-align:center;color:#94a3b8}
.dash-top-right{display:flex;align-items:center;gap:8px}
</style>
</head><body class="has-sidebar">
<?php include __DIR__ . '/sidebar.php'; ?>
<div class="main-wrap">
  <header class="dash-top">
    <h1><?= e($pageTitle ?? 'Dashboard') ?></h1>
    <div class="dash-top-right">
      <?php if ($u): ?>
        <div class="bell-wrap" id="bellWrap">
          <button class="bell-btn" onclick="document.getElementById('bellWrap').classList.toggle('open')" title="Notifications">
            <i class="fa-solid fa-bell"></i>
            <?php if ($notifCount): ?><span class="bell-badge"><?= $notifCount ?></span><?php endif; ?>
          </button>
          <div class="bell-pop">
            <header>Notifications <a href="<?= base_url($u['role'].'/notifications.php') ?>" style="font-size:.8rem;color:var(--gold-2,#b8862d)">View all</a></header>
            <?php if (!$notifs): ?><div class="empty">No new notifications</div>
            <?php else: foreach ($notifs as $n): ?>
              <a class="item" href="<?= base_url(($n['link'] ?: $u['role'].'/notifications.php')) ?>">
                <strong><?= e($n['title']) ?></strong>
                <small><?= e($n['body']) ?> · <?= e(date('M j, H:i', strtotime($n['created_at']))) ?></small>
              </a>
            <?php endforeach; endif; ?>
          </div>
        </div>
        <a href="<?= base_url($u['role'].'/messages.php') ?>" class="bell-btn" style="display:inline-flex;align-items:center;justify-content:center;text-decoration:none" title="Messages">
          <i class="fa-solid fa-comments"></i>
          <?php if ($msgCount): ?><span class="bell-badge"><?= $msgCount ?></span><?php endif; ?>
        </a>
      <?php endif; ?>
      <div class="user-chip"><i class="fa-solid fa-user-circle"></i> <?= e($u['name']) ?> <span class="role-badge"><?= e($u['role']) ?></span></div>
    </div>
  </header>
  <?php if ($flash): ?><div class="flash flash-<?= e($flash['t']) ?>"><?= e($flash['m']) ?></div><?php endif; ?>
  <div class="dash-body">
