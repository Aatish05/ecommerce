<?php
require_once __DIR__ . '/includes/functions.php';
$pageTitle = 'Privacy Policy';
$metaDescription = 'EcoTech Electronics privacy policy explaining GDPR, user consent, data protection, and ethical data handling.';
$active = 'privacy';
include __DIR__ . '/includes/header.php';
?>
<section class="container">
    <div class="row justify-content-center">
        <div class="col-lg-9">
            <h1>Privacy policy</h1>
            <p class="lead">This page explains how EcoTech Electronics collects, protects, and uses personal data for this educational e-commerce project.</p>
            <h2>Data we collect</h2>
            <p>We collect account details such as name, username, email address, hashed password, order information, shipping address, cart session data, and contact form messages.</p>
            <h2>GDPR and consent</h2>
            <p>The General Data Protection Regulation was approved in 2016 and has been enforced since May 25, 2018. Users should understand what data is collected and consent to essential processing needed for account login, checkout, and support.</p>
            <h2>Password and session security</h2>
            <p>Passwords are stored using PHP's <code>password_hash()</code> function, never in plain text. Login uses server-side sessions, regenerated session IDs, HTTP-only cookies, CSRF tokens, and protected admin routes.</p>
            <h2>User rights</h2>
            <p>Users can request access, correction, or deletion of personal data by contacting support. Admin users should only access messages and orders for legitimate support or fulfilment reasons.</p>
            <h2>Cookies</h2>
            <p>The website uses essential cookies for sessions, shopping cart state, security, dark/light mode, and cookie notice consent. It does not require third-party tracking cookies.</p>
            <h2>Ethical handling</h2>
            <p>Only necessary data is collected, database access is limited by role, and product information is presented honestly to avoid misleading customers.</p>
        </div>
    </div>
</section>
<?php include __DIR__ . '/includes/footer.php'; ?>
