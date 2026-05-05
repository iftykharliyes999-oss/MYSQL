<div class="sidebar bg-dark text-white">
    <div class="p-3 border-bottom border-secondary">
        <h5 class="mb-0"><i class="fa-solid fa-user-shield me-2"></i>Admin Panel</h5>
        <small class="text-muted"><?= htmlspecialchars($_SESSION['user_name']) ?></small>
    </div>
    <ul class="nav flex-column p-2">
        <li><a class="nav-link text-white" href="<?= BASE_URL ?>/admin/dashboard.php"><i class="fa-solid fa-gauge me-2"></i>Dashboard</a></li>
        <li><a class="nav-link text-white" href="<?= BASE_URL ?>/admin/users.php"><i class="fa-solid fa-users me-2"></i>Users</a></li>
        <li><a class="nav-link text-white" href="<?= BASE_URL ?>/admin/rooms.php"><i class="fa-solid fa-bed me-2"></i>Rooms</a></li>
        <li><a class="nav-link text-white" href="<?= BASE_URL ?>/admin/bookings.php"><i class="fa-solid fa-calendar-check me-2"></i>Bookings</a></li>
        <li><a class="nav-link text-white" href="<?= BASE_URL ?>/admin/payments.php"><i class="fa-solid fa-credit-card me-2"></i>Payments</a></li>
        <li><a class="nav-link text-white" href="<?= BASE_URL ?>/admin/housekeeping.php"><i class="fa-solid fa-broom me-2"></i>Housekeeping</a></li>
        <li><a class="nav-link text-white" href="<?= BASE_URL ?>/admin/reports.php"><i class="fa-solid fa-chart-line me-2"></i>Reports</a></li>
        <li class="border-top border-secondary mt-2 pt-2">
            <a class="nav-link text-warning" href="<?= BASE_URL ?>/public/logout.php"><i class="fa-solid fa-right-from-bracket me-2"></i>Logout</a>
        </li>
    </ul>
</div>
