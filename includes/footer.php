<?php
// ============================================================
// includes/footer.php — Common Footer
// ============================================================
?>
</main><!-- end .main-content -->

<!-- ── FOOTER ── -->
<footer class="footer">
    <div class="footer-container">
        <div class="footer-brand">
            <h3>🍕 <?= SITE_NAME ?></h3>
            <p>Delicious food delivered fast to your door. Order online and enjoy the best meals in town.</p>
            <div class="footer-social">
                <a href="#"><i class="fab fa-facebook"></i></a>
                <a href="#"><i class="fab fa-instagram"></i></a>
                <a href="#"><i class="fab fa-twitter"></i></a>
            </div>
        </div>
        <div class="footer-links">
            <h4>Quick Links</h4>
            <ul>
                <li><a href="<?= SITE_URL ?>/index.php">Home</a></li>
                <li><a href="<?= SITE_URL ?>/menu.php">Menu</a></li>
                <li><a href="<?= SITE_URL ?>/login.php">Login</a></li>
                <li><a href="<?= SITE_URL ?>/register.php">Register</a></li>
            </ul>
        </div>
        <div class="footer-contact">
            <h4>Contact</h4>
            <p><i class="fas fa-phone"></i> +1 (555) 123-4567</p>
            <p><i class="fas fa-envelope"></i> hello@foodieexpress.com</p>
            <p><i class="fas fa-map-marker-alt"></i> 123 Food Street, Flavor Town</p>
        </div>
    </div>
    <div class="footer-bottom">
        <p>&copy; <?= date('Y') ?> <?= SITE_NAME ?>. All rights reserved. | Built with ❤️ & PHP</p>
    </div>
</footer>

<script src="<?= SITE_URL ?>/js/script.js"></script>
</body>
</html>
