<?php require_once __DIR__ . '/functions.php'; ?>
</main>
<footer class="site-footer">
  <img class="footer-logo" src="inktale_logo.png" alt="InkTale">
  <div class="footer-links">
    <span><strong>Contact us:</strong> <a href="tel:<?= h($CONTACT_PHONE); ?>"><?= h($CONTACT_PHONE); ?></a></span>
    <span><strong>Location:</strong> <a href="location.php">Open map page</a></span>
    <span><strong>Guest:</strong> <?= h($_COOKIE['inktale_guest'] ?? 'guest'); ?></span>
  </div>
  <div>InkTale bookstore experience — same look, powered by PHP & MySQL.</div>
</footer>
</body>
</html>
