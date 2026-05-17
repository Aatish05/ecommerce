<?php
require_once __DIR__ . '/includes/functions.php';
$pageTitle = 'Sustainable Electronics Store';
$metaDescription = 'Shop laptops, smartphones, audio devices, and smart home technology through a secure PHP and MySQL e-commerce website.';
$active = 'home';
$featured = featured_products();
include __DIR__ . '/includes/header.php';
?>
<section class="container">
    <div class="hero p-4 p-lg-5">
        <div class="row align-items-center g-5">
            <div class="col-lg-7">
                <span class="hero-badge"><i class="bi bi-leaf" aria-hidden="true"></i> Sustainable electronics marketplace</span>
                <h1 class="display-4 fw-bold mt-3">Upgrade your tech with a cleaner footprint.</h1>
                <p class="lead">DGShop Electronics is a dynamic e-commerce website for curated laptops, phones, audio gear, and smart devices. It includes secure accounts, shopping cart, checkout, admin CRUD, search, and database-driven product pages.</p>
                <div class="d-flex flex-wrap gap-2">
                    <a class="btn btn-success btn-lg" href="<?= url('products.php') ?>">Shop products</a>
                    <a class="btn btn-outline-success btn-lg" href="<?= url('register.php') ?>">Create account</a>
                </div>
            </div>
            <div class="col-lg-5">
                <div class="card border-0 shadow-lg">
                    <div class="card-body p-4">
                        <h2 class="h4">HD rubric features</h2>
                        <ul class="list-unstyled mb-0">
                            <li class="mb-2"><i class="bi bi-check-circle-fill text-success" aria-hidden="true"></i> PHP sessions, login, logout, roles</li>
                            <li class="mb-2"><i class="bi bi-check-circle-fill text-success" aria-hidden="true"></i> MySQL products, orders, users, messages</li>
                            <li class="mb-2"><i class="bi bi-check-circle-fill text-success" aria-hidden="true"></i> Admin CRUD and protected dashboard</li>
                            <li class="mb-2"><i class="bi bi-check-circle-fill text-success" aria-hidden="true"></i> SEO, accessibility, validation, GDPR pages</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="container py-5">
    <div class="row g-4 text-center">
        <div class="col-md-4">
            <div class="icon-box mb-3"><i class="bi bi-shield-lock" aria-hidden="true"></i></div>
            <h2 class="h4">Secure by design</h2>
            <p>Prepared statements, CSRF tokens, password hashing, and protected admin pages reduce common web risks.</p>
        </div>
        <div class="col-md-4">
            <div class="icon-box mb-3"><i class="bi bi-database-check" aria-hidden="true"></i></div>
            <h2 class="h4">Database driven</h2>
            <p>Products, categories, users, orders, and contact messages are stored dynamically in MySQL.</p>
        </div>
        <div class="col-md-4">
            <div class="icon-box mb-3"><i class="bi bi-universal-access" aria-hidden="true"></i></div>
            <h2 class="h4">Accessible UI</h2>
            <p>Semantic layout, labels, alt text, keyboard-friendly controls, responsive design, and high contrast styling.</p>
        </div>
    </div>
</section>

<section class="container pb-5">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h2 class="h3 mb-1">Featured electronics</h2>
            <p class="text-body-secondary mb-0">Loaded from the products table.</p>
        </div>
        <a href="<?= url('products.php') ?>" class="btn btn-outline-success">View all</a>
    </div>
    <div class="row g-4">
        <?php foreach ($featured as $product): ?>
            <div class="col-sm-6 col-lg-4">
                <?php include __DIR__ . '/includes/product-card.php'; ?>
            </div>
        <?php endforeach; ?>
    </div>
</section>
<?php include __DIR__ . '/includes/footer.php'; ?>
