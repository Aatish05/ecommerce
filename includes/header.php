<?php
require_once __DIR__ . '/functions.php';
$pageTitle = $pageTitle ?? APP_NAME;
$metaDescription = $metaDescription ?? 'DGShop Electronics sells sustainable laptops, phones, audio gear, and smart devices with secure online ordering.';
$active = $active ?? '';
?>
<!doctype html>
<html lang="en" data-bs-theme="light">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="<?= e($metaDescription) ?>">
    <meta name="keywords" content="electronics ecommerce, sustainable technology, laptops, smartphones, smart home, PHP MySQL shop">
    <meta name="author" content="DGShop Electronics Student Project">
    <meta name="robots" content="index, follow">
    <title><?= e($pageTitle) ?> | <?= APP_NAME ?></title>
    <link rel="preconnect" href="https://cdn.jsdelivr.net">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="<?= url('assets/css/styles.css') ?>" rel="stylesheet">
</head>
<body>
<a class="visually-hidden-focusable skip-link" href="#main-content">Skip to main content</a>
<header>
    <nav class="navbar navbar-expand-lg sticky-top bg-body-tertiary border-bottom shadow-sm" aria-label="Main navigation">
        <div class="container">
            <a class="navbar-brand fw-bold text-success" href="<?= url('index.php') ?>">
                <i class="bi bi-cpu" aria-hidden="true"></i> DGShop
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav" aria-controls="mainNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="mainNav">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    <li class="nav-item"><a class="nav-link <?= $active === 'home' ? 'active' : '' ?>" href="<?= url('index.php') ?>">Home</a></li>
                    <li class="nav-item"><a class="nav-link <?= $active === 'products' ? 'active' : '' ?>" href="<?= url('products.php') ?>">Shop</a></li>
                    <li class="nav-item"><a class="nav-link <?= $active === 'contact' ? 'active' : '' ?>" href="<?= url('contact.php') ?>">Contact</a></li>
                    <li class="nav-item"><a class="nav-link <?= $active === 'privacy' ? 'active' : '' ?>" href="<?= url('privacy.php') ?>">Privacy</a></li>
                    <?php if (is_admin()): ?>
                        <li class="nav-item"><a class="nav-link <?= $active === 'admin' ? 'active' : '' ?>" href="<?= url('admin/dashboard.php') ?>">Admin</a></li>
                    <?php endif; ?>
                    <?php if (is_vendor()): ?>
                        <li class="nav-item"><a class="nav-link <?= $active === 'vendor' ? 'active' : '' ?>" href="<?= url('vendor/dashboard.php') ?>">Vendor panel</a></li>
                    <?php endif; ?>
                </ul>
                <div class="d-flex align-items-center gap-2">
                    <button class="btn btn-outline-secondary btn-sm" id="themeToggle" type="button" aria-label="Toggle dark and light mode">
                        <i class="bi bi-moon-stars" aria-hidden="true"></i>
                    </button>
                    <a class="btn btn-outline-success btn-sm" href="<?= url('cart.php') ?>" aria-label="Shopping cart with <?= cart_count() ?> items">
                        <i class="bi bi-cart3" aria-hidden="true"></i> Cart <span class="badge text-bg-success"><?= cart_count() ?></span>
                    </a>
                    <?php if (is_logged_in()): ?>
                        <div class="dropdown">
                            <button class="btn btn-success btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <?= e(current_user()['name']) ?>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><a class="dropdown-item" href="<?= url('orders.php') ?>">My orders</a></li>
                                <?php if (is_vendor()): ?>
                                    <li><a class="dropdown-item" href="<?= url('vendor/dashboard.php') ?>">Vendor dashboard</a></li>
                                <?php endif; ?>
                                <?php if (is_admin()): ?>
                                    <li><a class="dropdown-item" href="<?= url('admin/dashboard.php') ?>">Admin dashboard</a></li>
                                <?php endif; ?>
                                <li><a class="dropdown-item" href="<?= url('profile.php') ?>">Profile</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="<?= url('logout.php') ?>">Logout</a></li>
                            </ul>
                        </div>
                    <?php else: ?>
                        <a class="btn btn-outline-primary btn-sm" href="<?= url('login.php') ?>">Login</a>
                        <a class="btn btn-primary btn-sm" href="<?= url('register.php') ?>">Register</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>
</header>
<main id="main-content">
    <div class="container mt-4">
        <?php foreach (flash_messages() as $flash): ?>
            <div class="alert alert-<?= e($flash['type']) ?> alert-dismissible fade show" role="alert">
                <?= e($flash['message']) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endforeach; ?>
    </div>
