<?php
require_once __DIR__ . '/includes/functions.php';

if (is_logged_in()) {
    redirect('index.php');
}

$errors = [];
$values = ['name' => '', 'username' => '', 'email' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf($_POST['csrf_token'] ?? null)) {
        $errors['form'] = 'Security check failed. Please try again.';
    }

    $values = [
        'name' => trim($_POST['name'] ?? ''),
        'username' => trim($_POST['username'] ?? ''),
        'email' => trim($_POST['email'] ?? ''),
    ];

    if (!$errors) {
        [$created, $errors] = register_user($values['name'], $values['username'], $values['email'], $_POST['password'] ?? '');
        if ($created) {
            set_flash('success', 'Registration successful. Please log in.');
            redirect('login.php');
        }
    }
}

$pageTitle = 'Register';
$metaDescription = 'Create a secure EcoTech Electronics account.';
include __DIR__ . '/includes/header.php';
?>
<section class="container">
    <div class="row justify-content-center">
        <div class="col-lg-6">
            <div class="card shadow-sm">
                <div class="card-body p-4">
                    <h1 class="h2">Create account</h1>
                    <p class="text-body-secondary">Register to save orders and complete checkout.</p>
                    <?php if (isset($errors['form'])): ?><div class="alert alert-danger"><?= e($errors['form']) ?></div><?php endif; ?>
                    <form class="needs-validation" method="post" novalidate>
                        <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                        <div class="mb-3">
                            <label class="form-label form-required" for="name">Full name</label>
                            <input class="form-control <?= isset($errors['name']) ? 'is-invalid' : '' ?>" id="name" name="name" value="<?= e($values['name']) ?>" required>
                            <div class="invalid-feedback"><?= e($errors['name'] ?? 'Please enter your full name.') ?></div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label form-required" for="username">Username</label>
                            <input class="form-control <?= isset($errors['username']) ? 'is-invalid' : '' ?>" id="username" name="username" value="<?= e($values['username']) ?>" pattern="[A-Za-z0-9_]{3,30}" required>
                            <div class="invalid-feedback"><?= e($errors['username'] ?? 'Use 3-30 letters, numbers, or underscores.') ?></div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label form-required" for="email">Email address</label>
                            <input class="form-control <?= isset($errors['email']) ? 'is-invalid' : '' ?>" type="email" id="email" name="email" value="<?= e($values['email']) ?>" required>
                            <div class="invalid-feedback"><?= e($errors['email'] ?? 'Please enter a valid email address.') ?></div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label form-required" for="password">Password</label>
                            <input class="form-control <?= isset($errors['password']) ? 'is-invalid' : '' ?>" type="password" id="password" name="password" minlength="8" data-password-rules required>
                            <div class="invalid-feedback"><?= e($errors['password'] ?? 'Password must be at least 8 characters and include letters and numbers.') ?></div>
                        </div>
                        <button class="btn btn-success w-100" type="submit">Register</button>
                    </form>
                    <p class="mt-3 mb-0">Already registered? <a href="<?= url('login.php') ?>">Log in</a>.</p>
                </div>
            </div>
        </div>
    </div>
</section>
<?php include __DIR__ . '/includes/footer.php'; ?>
