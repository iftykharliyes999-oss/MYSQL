</main>
<footer class="footer">
  <div class="container footer-grid">
    <div>
      <h4><i class="fa-solid fa-hotel"></i> <?= e(HOTEL_NAME) ?></h4>
      <p><?= e(HOTEL_TAGLINE) ?></p>
      <p class="muted"><?= e(HOTEL_ADDRESS) ?></p>
    </div>
    <div>
      <h5>Quick Links</h5>
      <a href="<?= base_url('index.php') ?>">Home</a>
      <a href="<?= base_url('rooms.php') ?>">Rooms</a>
      <a href="<?= base_url('about.php') ?>">About Us</a>
      <a href="<?= base_url('contact.php') ?>">Contact</a>
    </div>
    <div>
      <h5>Contact</h5>
      <p><i class="fa-solid fa-phone"></i> <?= e(HOTEL_PHONE) ?></p>
      <p><i class="fa-solid fa-envelope"></i> <?= e(HOTEL_EMAIL) ?></p>
      <div class="socials">
        <a href="#"><i class="fa-brands fa-facebook"></i></a>
        <a href="#"><i class="fa-brands fa-instagram"></i></a>
        <a href="#"><i class="fa-brands fa-twitter"></i></a>
      </div>
    </div>
  </div>
  <div class="footer-bottom">© <?= date('Y') ?> <?= e(HOTEL_NAME) ?>. All rights reserved.</div>
</footer>
</body></html>
