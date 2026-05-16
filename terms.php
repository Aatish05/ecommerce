<?php
require_once __DIR__ . '/includes/functions.php';
$pageTitle = 'Terms and Conditions';
$metaDescription = 'EcoTech Electronics terms and conditions for product browsing, accounts, orders, and ethical website use.';
include __DIR__ . '/includes/header.php';
?>
<section class="container">
    <div class="row justify-content-center">
        <div class="col-lg-9">
            <h1>Terms and conditions</h1>
            <p class="lead">These terms are written for the EcoTech Electronics student project demonstration.</p>
            <h2>Accounts</h2>
            <p>Users must provide accurate registration details and keep passwords private. Administrators must not share privileged accounts.</p>
            <h2>Orders</h2>
            <p>Orders placed in this demo are stored in the database to demonstrate checkout and order management. Payment processing is simulated.</p>
            <h2>Acceptable use</h2>
            <p>Users must not attempt to bypass authentication, inject malicious input, or access another user's data.</p>
            <h2>Product information</h2>
            <p>Product names, prices, stock levels, descriptions, and images are managed from the admin dashboard and should be kept accurate.</p>
            <h2>Privacy</h2>
            <p>Personal data is handled according to the privacy policy, with GDPR-aware consent and data minimisation principles.</p>
        </div>
    </div>
</section>
<?php include __DIR__ . '/includes/footer.php'; ?>
