<?php
require_once __DIR__ . '/includes/functions.php';

$errors = [];
$values = ['name' => current_user()['name'] ?? '', 'email' => current_user()['email'] ?? '', 'subject' => '', 'message' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf($_POST['csrf_token'] ?? null)) {
        $errors['form'] = 'Security check failed. Please try again.';
    }
    $values = [
        'name' => trim($_POST['name'] ?? ''),
        'email' => trim($_POST['email'] ?? ''),
        'subject' => trim($_POST['subject'] ?? ''),
        'message' => trim($_POST['message'] ?? ''),
    ];
    if (!$errors) {
        [$saved, $errors] = save_contact_message($values['name'], $values['email'], $values['subject'], $values['message']);
        if ($saved) {
            set_flash('success', 'Thank you. Your message has been sent to the DGShop team.');
            redirect('contact.php');
        }
    }
}

$pageTitle = 'Contact Us';
$metaDescription = 'Contact DGShop Electronics using a validated PHP contact form stored in MySQL.';
$active = 'contact';
include __DIR__ . '/includes/header.php';
?>
<section class="container">
    <div class="row g-5">
        <div class="col-lg-5">
            <h1>Contact DGShop</h1>
            <p class="lead">Use the contact form for support, product questions, accessibility feedback, or privacy requests.</p>
            <div class="card border-success">
                <div class="card-body">
                    <h2 class="h5">Support details</h2>
                    <p class="mb-1"><i class="bi bi-envelope" aria-hidden="true"></i> support@DGShop.test</p>
                    <p class="mb-1"><i class="bi bi-clock" aria-hidden="true"></i> Monday-Friday, 9:00-17:00</p>
                    <p class="mb-0"><i class="bi bi-geo-alt" aria-hidden="true"></i> Student Innovation Hub</p>
                </div>
            </div>
        </div>
        <div class="col-lg-7">
            <?php if (isset($errors['form'])): ?><div class="alert alert-danger"><?= e($errors['form']) ?></div><?php endif; ?>
            <form class="card card-body shadow-sm needs-validation" method="post" novalidate>
                <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label form-required" for="name">Name</label>
                        <input class="form-control <?= isset($errors['name']) ? 'is-invalid' : '' ?>" id="name" name="name" value="<?= e($values['name']) ?>" required>
                        <div class="invalid-feedback"><?= e($errors['name'] ?? 'Please enter your name.') ?></div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label form-required" for="email">Email</label>
                        <input class="form-control <?= isset($errors['email']) ? 'is-invalid' : '' ?>" type="email" id="email" name="email" value="<?= e($values['email']) ?>" required>
                        <div class="invalid-feedback"><?= e($errors['email'] ?? 'Please enter a valid email address.') ?></div>
                    </div>
                    <div class="col-12">
                        <label class="form-label form-required" for="subject">Subject</label>
                        <input class="form-control <?= isset($errors['subject']) ? 'is-invalid' : '' ?>" id="subject" name="subject" value="<?= e($values['subject']) ?>" required>
                        <div class="invalid-feedback"><?= e($errors['subject'] ?? 'Please enter a subject.') ?></div>
                    </div>
                    <div class="col-12">
                        <label class="form-label form-required" for="message">Message</label>
                        <textarea class="form-control <?= isset($errors['message']) ? 'is-invalid' : '' ?>" id="message" name="message" rows="6" minlength="10" required><?= e($values['message']) ?></textarea>
                        <div class="invalid-feedback"><?= e($errors['message'] ?? 'Please enter at least 10 characters.') ?></div>
                    </div>
                </div>
                <button class="btn btn-success mt-3" type="submit">Send message</button>
            </form>
        </div>
    </div>
</section>
<?php include __DIR__ . '/includes/footer.php'; ?>
