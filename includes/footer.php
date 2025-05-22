</main>

<!-- Add to Cart Modal -->
<div class="modal fade" id="addToCartModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="addToCartModalLabel">Item Added</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p id="addToCartMessage"></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Continue Shopping</button>
                <a href="cart.php" class="btn btn-primary">View Cart</a>
            </div>
        </div>
    </div>
</div>

<footer class="bg-dark text-white py-4 mt-5">
    <div class="container">
        <div class="row">
            <div class="col-md-4">
                <h5><?= SITE_NAME ?></h5>
                <p>Delicious food delivered to your doorstep.</p>
            </div>
            <div class="col-md-4">
                <h5>Quick Links</h5>
                <ul class="list-unstyled">
                    <li><a href="<?= BASE_URL ?>" class="text-white">Home</a></li>
                    <li><a href="<?= BASE_URL ?>/menu.php" class="text-white">Menu</a></li>
                    <li><a href="<?= BASE_URL ?>/order_tracking.php" class="text-white">Track Order</a></li>
                </ul>
            </div>
            <div class="col-md-4">
                <h5>Contact Us</h5>
                <address>
                    <p><i class="bi bi-geo-alt"></i> Xawa taako, Wadajir</p>
                    <p><i class="bi bi-telephone"></i> (123) 456-7890</p>
                    <p><i class="bi bi-envelope"></i> info@foodexpress.com</p>
                </address>
            </div>
        </div>
        <div class="text-center mt-3">
            <p>&copy; <?= date('Y') ?> <?= SITE_NAME ?>. All rights reserved.</p>
        </div>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<?php if (isset($pageScripts)): ?>
    <?php foreach ($pageScripts as $script): ?>
        <script src="<?= BASE_URL ?>/assets/js/<?= $script ?>"></script>
    <?php endforeach; ?>
<?php endif; ?>
</body>

</html>