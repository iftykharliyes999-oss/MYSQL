<div class="sidebar bg-success text-white">
    <div class="p-3 border-bottom border-light">
        <h5 class="mb-0"><i class="fa-solid fa-user-tie me-2"></i>Staff Panel</h5>
        <small><?= htmlspecialchars($_SESSION['user_name']) ?></small>
    </div>
    <ul class="nav flex-column p-2">
        <li><a class="nav-link text-white" href="<?= BASE_URL ?>/staff/dashboard.php"><i class="fa-solid fa-gauge me-2"></i>Dashboard</a></li>
        <li><a class="nav-link text-white" href="<?= BASE_URL ?>/staff/bookings.php"><i class="fa-solid fa-calendar-check me-2"></i>Bookings</a></li>
        <li><a class="nav-link text-white" href="<?= BASE_URL ?>/staff/checkin.php"><i class="fa-solid fa-right-to-bracket me-2"></i>Check-In/Out</a></li>
        <li><a class="nav-link text-white" href="<?= BASE_URL ?>/staff/housekeeping.php"><i class="fa-solid fa-broom me-2"></i>Housekeeping</a></li>
        <li class="border-top mt-2 pt-2">
            <a class="nav-link text-warning" href="<?= BASE_URL ?>/public/logout.php"><i class="fa-solid fa-right-from-bracket me-2"></i>Logout</a>
        </li>
    </ul>
</div>
