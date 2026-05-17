<?php
require_once __DIR__ . '/includes/functions.php';

if (is_logged_in()) {
    redirect('index.php');
}

$errors = [];
$values = ['name' => '', 'username' => '', 'email' => '', 'role' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf($_POST['csrf_token'] ?? null)) {
        $errors['form'] = 'Security check failed. Please try again.';
    }

    $values = [
        'name' => trim($_POST['name'] ?? ''),
        'username' => trim($_POST['username'] ?? ''),
        'email' => trim($_POST['email'] ?? ''),
        'role' => trim($_POST['role'] ?? ''),
    ];

    if (!$errors) {
        [$created, $errors] = register_user(
            $values['name'],
            $values['username'],
            $values['email'],
            $_POST['password'] ?? '',
            $values['role'],
            $_FILES['profile_image'] ?? []
        );

        if ($created) {
            $accountType = $values['role'] === 'vendor' ? 'vendor' : 'customer';
            set_flash('success', 'Registration successful as a ' . $accountType . '. Please log in.');
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
                    <p class="text-body-secondary">Register as a customer to shop, or as a vendor to sell electronic products.</p>

                    <?php if (isset($errors['form'])): ?>
                        <div class="alert alert-danger"><?= e($errors['form']) ?></div>
                    <?php endif; ?>

                    <form class="needs-validation" method="post" enctype="multipart/form-data" novalidate>
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

                        <fieldset class="mb-3">
                            <legend class="form-label form-required fs-6">Account type</legend>
                            <div class="row g-2">
                                <div class="col-md-6">
                                    <div class="form-check border rounded-3 p-3 h-100">
                                        <input class="form-check-input <?= isset($errors['role']) ? 'is-invalid' : '' ?>" type="radio" name="role" id="role_customer" value="user" <?= $values['role'] === 'user' ? 'checked' : '' ?> required>
                                        <label class="form-check-label fw-semibold" for="role_customer">Customer</label>
                                        <p class="small text-body-secondary mb-0">Browse products, checkout, and write reviews.</p>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-check border rounded-3 p-3 h-100">
                                        <input class="form-check-input <?= isset($errors['role']) ? 'is-invalid' : '' ?>" type="radio" name="role" id="role_vendor" value="vendor" <?= $values['role'] === 'vendor' ? 'checked' : '' ?> required>
                                        <label class="form-check-label fw-semibold" for="role_vendor">Vendor</label>
                                        <p class="small text-body-secondary mb-0">Add products and view your own sales revenue.</p>
                                    </div>
                                </div>
                            </div>

                            <?php if (isset($errors['role'])): ?>
                                <div class="text-danger small mt-2"><?= e($errors['role']) ?></div>
                            <?php endif; ?>
                        </fieldset>

                        <div class="mb-3">
                            <label class="form-label" for="profile_image">Profile image</label>
                            <input class="form-control <?= isset($errors['profile_image']) ? 'is-invalid' : '' ?>" type="file" id="profile_image" name="profile_image" accept="image/jpeg,image/png,image/webp">
                            <div class="form-text">Upload JPG, PNG, or WEBP. Maximum size 2MB.</div>
                            <div class="invalid-feedback"><?= e($errors['profile_image'] ?? 'Please upload a valid image.') ?></div>
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