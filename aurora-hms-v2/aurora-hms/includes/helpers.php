<?php
/** Aurora Grand · Shared helpers (notifications, messaging, images) */
declare(strict_types=1);
require_once __DIR__ . '/../config/db.php';

/** Resolve a room/room-type image URL.
 *  Priority: uploads/rooms/<file>  →  assets/images/<file>  →  default.
 */
function image_url(?string $file): string {
    if (!$file) return base_url('assets/images/room-standard.jpg');
    $abs = __DIR__ . '/../uploads/rooms/' . $file;
    if (is_file($abs)) return base_url('uploads/rooms/' . rawurlencode($file));
    return base_url('assets/images/' . rawurlencode($file));
}

/** Handle a single file upload to /uploads/rooms/. Returns filename or null. */
function handle_image_upload(string $field): ?string {
    if (empty($_FILES[$field]) || ($_FILES[$field]['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
        return null;
    }
    $f = $_FILES[$field];
    if ($f['error'] !== UPLOAD_ERR_OK) throw new RuntimeException('Upload error: ' . $f['error']);
    if ($f['size'] > 5 * 1024 * 1024) throw new RuntimeException('Image must be ≤ 5 MB.');
    $mime = mime_content_type($f['tmp_name']) ?: '';
    $okMimes = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp', 'image/gif' => 'gif'];
    if (!isset($okMimes[$mime])) throw new RuntimeException('Only JPG/PNG/WEBP/GIF allowed.');
    $ext = $okMimes[$mime];
    $dir = __DIR__ . '/../uploads/rooms';
    if (!is_dir($dir)) @mkdir($dir, 0775, true);
    $name = 'room_' . date('Ymd_His') . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
    if (!move_uploaded_file($f['tmp_name'], $dir . '/' . $name)) {
        throw new RuntimeException('Could not save uploaded file.');
    }
    return $name;
}

/** Insert a notification row.
 *  Pass user_id for a targeted notification, or role for broadcast.
 */
function notify(array $opts): void {
    $opts += ['user_id' => null, 'role' => null, 'type' => 'system', 'title' => '', 'body' => '', 'link' => null];
    try {
        $s = db()->prepare('INSERT INTO notifications (user_id,role,type,title,body,link) VALUES (?,?,?,?,?,?)');
        $s->execute([$opts['user_id'], $opts['role'], $opts['type'], $opts['title'], $opts['body'], $opts['link']]);
    } catch (Throwable $e) { /* swallow — notifications must never break flow */ }
}

/** Unread notifications visible to a given user (own + role broadcasts). */
function unread_notifications(array $u): array {
    try {
        $s = db()->prepare('SELECT * FROM notifications
            WHERE is_read=0 AND (user_id=? OR (user_id IS NULL AND role=?))
            ORDER BY created_at DESC LIMIT 10');
        $s->execute([$u['id'], $u['role']]);
        return $s->fetchAll();
    } catch (Throwable $e) { return []; }
}

function unread_count(array $u): int {
    try {
        $s = db()->prepare('SELECT COUNT(*) FROM notifications
            WHERE is_read=0 AND (user_id=? OR (user_id IS NULL AND role=?))');
        $s->execute([$u['id'], $u['role']]);
        return (int)$s->fetchColumn();
    } catch (Throwable $e) { return 0; }
}

function mark_notification_read(int $id, array $u): void {
    try {
        db()->prepare('UPDATE notifications SET is_read=1
            WHERE id=? AND (user_id=? OR (user_id IS NULL AND role=?))')
            ->execute([$id, $u['id'], $u['role']]);
    } catch (Throwable $e) {}
}

/** Unread chat-message count for a given user.
 *  Admin/staff: count messages sent by customers/guests that are unread.
 *  Customer: count messages addressed to them in their threads (sent by admin/staff) that are unread.
 */
function unread_messages_count(array $u): int {
    try {
        if ($u['role'] === 'admin' || $u['role'] === 'staff') {
            return (int)db()->query("SELECT COUNT(*) FROM messages
                WHERE is_read=0 AND sender_role IN ('customer','guest')")->fetchColumn();
        }
        $s = db()->prepare("SELECT COUNT(*) FROM messages m
            JOIN message_threads t ON t.id=m.thread_id
            JOIN customers c ON c.id=t.customer_id
            WHERE c.user_id=? AND m.is_read=0 AND m.sender_role IN ('admin','staff')");
        $s->execute([$u['id']]);
        return (int)$s->fetchColumn();
    } catch (Throwable $e) { return 0; }
}
