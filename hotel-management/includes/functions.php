<?php
/**
 * Helper Functions
 */

function sanitize($data) {
    return htmlspecialchars(strip_tags(trim($data)), ENT_QUOTES, 'UTF-8');
}

function redirect($url) {
    header("Location: " . $url);
    exit();
}

function flash($key, $msg = null, $type = 'info') {
    if ($msg !== null) {
        $_SESSION['flash'][$key] = ['msg' => $msg, 'type' => $type];
    } else {
        if (isset($_SESSION['flash'][$key])) {
            $f = $_SESSION['flash'][$key];
            unset($_SESSION['flash'][$key]);
            return '<div class="alert alert-' . $f['type'] . ' alert-dismissible fade show">' .
                   $f['msg'] . '<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>';
        }
    }
    return '';
}

function formatCurrency($amount) {
    return '৳ ' . number_format($amount, 2);
}

function formatDate($date) {
    return date('d M Y', strtotime($date));
}

function formatDateTime($datetime) {
    return date('d M Y, h:i A', strtotime($datetime));
}

function calculateNights($checkIn, $checkOut) {
    $d1 = new DateTime($checkIn);
    $d2 = new DateTime($checkOut);
    return $d1->diff($d2)->days;
}

/**
 * Check if a room is available between two dates.
 * Excludes a specific booking_id (useful for editing).
 */
function isRoomAvailable($pdo, $room_id, $check_in, $check_out, $exclude_booking_id = null) {
    $sql = "SELECT COUNT(*) FROM bookings
            WHERE room_id = :room_id
              AND status IN ('confirmed','checked_in','pending')
              AND NOT (check_out_date <= :check_in OR check_in_date >= :check_out)";
    $params = [':room_id' => $room_id, ':check_in' => $check_in, ':check_out' => $check_out];
    if ($exclude_booking_id) {
        $sql .= " AND id != :eid";
        $params[':eid'] = $exclude_booking_id;
    }
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchColumn() == 0;
}

function csrf_token() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_field() {
    return '<input type="hidden" name="csrf_token" value="' . csrf_token() . '">';
}

function csrf_verify() {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== ($_SESSION['csrf_token'] ?? '')) {
        die('CSRF token mismatch');
    }
}

function uploadImage($file, $folder = 'rooms') {
    if (!isset($file) || $file['error'] !== UPLOAD_ERR_OK) return null;
    $allowed = ['jpg','jpeg','png','gif','webp'];
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, $allowed)) return null;
    $name = uniqid() . '.' . $ext;
    $dest = UPLOAD_PATH . $folder . '/' . $name;
    if (!is_dir(dirname($dest))) mkdir(dirname($dest), 0775, true);
    if (move_uploaded_file($file['tmp_name'], $dest)) return $name;
    return null;
}

function statusBadge($status) {
    $map = [
        'available'   => 'success',
        'booked'      => 'warning',
        'maintenance' => 'danger',
        'pending'     => 'secondary',
        'confirmed'   => 'info',
        'checked_in'  => 'primary',
        'checked_out' => 'success',
        'cancelled'   => 'danger',
        'paid'        => 'success',
        'due'         => 'warning',
        'partial'     => 'info',
        'done'        => 'success',
        'in_progress' => 'info',
    ];
    $cls = $map[$status] ?? 'secondary';
    return '<span class="badge bg-' . $cls . '">' . ucfirst(str_replace('_',' ',$status)) . '</span>';
}
