    </main>
    <footer class="footer mt-5 py-5 bg-dark text-light">
        <div class="container">
            <div class="row g-4">
                <div class="col-md-4">
                    <h2 class="h5">DGShop Electronics</h2>
                    <p class="small mb-0">A student-built PHP/MySQL e-commerce system for sustainable electronic products.</p>
                </div>
                <div class="col-md-4">
                    <h2 class="h5">Quick links</h2>
                    <ul class="list-unstyled small">
                        <li><a class="link-light" href="<?= url('products.php') ?>">Shop products</a></li>
                        <li><a class="link-light" href="<?= url('contact.php') ?>">Contact support</a></li>
                        <li><a class="link-light" href="<?= url('terms.php') ?>">Terms and conditions</a></li>
                        <li><a class="link-light" href="<?= url('privacy.php') ?>">Privacy policy</a></li>
                    </ul>
                </div>
                <div class="col-md-4">
                    <h2 class="h5">Accessibility and ethics</h2>
                    <p class="small">Built with labelled forms, semantic HTML, keyboard navigation, strong contrast, hashed passwords, and GDPR-aware data handling.</p>
                </div>
            </div>
            <hr class="border-secondary">
            <p class="small mb-0">&copy; <?= date('Y') ?> DGShop Electronics. Educational group project.</p>
        </div>
    </footer>

    <div class="cookie-banner shadow-lg" id="cookieBanner" role="dialog" aria-live="polite" aria-label="Cookie notice">
        <div>
            <strong>Cookie notice</strong>
            <p class="mb-0 small">We use essential session cookies for login, cart, security, and consent preferences. See our privacy policy for details.</p>
        </div>
        <button class="btn btn-success btn-sm" type="button" id="acceptCookies">Accept</button>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?= url('assets/js/app.js') ?>"></script>
</body>
</html>
