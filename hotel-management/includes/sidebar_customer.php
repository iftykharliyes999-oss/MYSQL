<div class="sidebar bg-info text-white">
    <div class="p-3 border-bottom border-light">
        <h5 class="mb-0"><i class="fa-solid fa-user me-2"></i>My Account</h5>
        <small><?= htmlspecialchars($_SESSION['user_name']) ?></small>
    </div>
    <ul class="nav flex-column p-2">
        <li><a class="nav-link text-white" href="<?= BASE_URL ?>/customer/dashboard.php"><i class="fa-solid fa-gauge me-2"></i>Dashboard</a></li>
        <li><a class="nav-link text-white" href="<?= BASE_URL ?>/customer/book_room.php"><i class="fa-solid fa-bed me-2"></i>Book a Room</a></li>
        <li><a class="nav-link text-white" href="<?= BASE_URL ?>/customer/my_bookings.php"><i class="fa-solid fa-calendar-check me-2"></i>My Bookings</a></li>
        <li><a class="nav-link text-white" href="<?= BASE_URL ?>/customer/profile.php"><i class="fa-solid fa-user-pen me-2"></i>Profile</a></li>
        <li class="border-top mt-2 pt-2">
            <a class="nav-link text-warning" href="<?= BASE_URL ?>/public/logout.php"><i class="fa-solid fa-right-from-bracket me-2"></i>Logout</a>
        </li>
    </ul>
</div>
