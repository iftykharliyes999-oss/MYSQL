<nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm sticky-top">
    <div class="container">
        <a class="navbar-brand fw-bold" href="<?= BASE_URL ?>/public/index.php">
            <i class="fa-solid fa-hotel me-2"></i><?= SITE_NAME ?>
        </a>
        <button class="navbar-toggler" data-bs-toggle="collapse" data-bs-target="#nav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="nav">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/public/index.php">Home</a></li>
                <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/public/rooms.php">Rooms</a></li>
                <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/public/about.php">About</a></li>
                <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/public/contact.php">Contact</a></li>
                <?php if (isLoggedIn()): ?>
                    <li class="nav-item"><a class="nav-link" href="<?= roleHomeUrl($_SESSION['user_role']) ?>">Dashboard</a></li>
                    <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/public/logout.php">Logout</a></li>
                <?php else: ?>
                    <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/public/login.php">Login</a></li>
                    <li class="nav-item"><a class="btn btn-warning ms-2 px-3" href="<?= BASE_URL ?>/public/register.php">Sign Up</a></li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>
