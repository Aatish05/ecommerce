<?php
require_once __DIR__ . '/includes/functions.php';

if (is_logged_in()) {
    redirect('index.php');
}

$errors = [];
$email = trim($_POST['email'] ?? '');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf($_POST['csrf_token'] ?? null)) {
        $errors['form'] = 'Security check failed. Please try again.';
    } elseif (!validate_email($email)) {
        $errors['email'] = 'Please enter a valid email address.';
    } else {
        $user = find_user_by_email(strtolower($email));
        if (!$user || !password_verify($_POST['password'] ?? '', $user['password_hash'])) {
            $errors['form'] = 'Incorrect email or password.';
        } else {
            session_regenerate_id(true);
            $_SESSION['user'] = [
                'id' => (int) $user['id'],
                'name' => $user['name'],
                'username' => $user['username'],
                'email' => $user['email'],
                'role' => $user['role'],
            ];
            set_flash('success', 'Welcome back, ' . $user['name'] . '!');
            redirect($user['role'] === 'admin' ? 'admin/dashboard.php' : 'index.php');
        }
    }
}

$pageTitle = 'Login';
$metaDescription = 'Log in to EcoTech Electronics using secure PHP sessions.';
include __DIR__ . '/includes/header.php';
?>
<section class="container">
    <div class="row justify-content-center">
        <div class="col-lg-5">
            <div class="card shadow-sm">
                <div class="card-body p-4">
                    <h1 class="h2">Login</h1>
                    <p class="text-body-secondary">Access your account, orders, and admin tools.</p>
                    <?php if (isset($errors['form'])): ?><div class="alert alert-danger"><?= e($errors['form']) ?></div><?php endif; ?>
                    <form class="needs-validation" method="post" novalidate>
                        <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                        <div class="mb-3">
                            <label class="form-label form-required" for="email">Email address</label>
                            <input class="form-control <?= isset($errors['email']) ? 'is-invalid' : '' ?>" type="email" id="email" name="email" value="<?= e($email) ?>" required>
                            <div class="invalid-feedback"><?= e($errors['email'] ?? 'Please enter a valid email address.') ?></div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label form-required" for="password">Password</label>
                            <input class="form-control" type="password" id="password" name="password" required>
                            <div class="invalid-feedback">Please enter your password.</div>
                        </div>
                        <button class="btn btn-success w-100" type="submit">Login</button>
                    </form>
                    <p class="mt-3 mb-0">Need an account? <a href="<?= url('register.php') ?>">Register now</a>.</p>
                    <div class="alert alert-light border mt-3 small">
                        Demo accounts after importing the seed data: admin@example.com / password, user@example.com / password.
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<?php include __DIR__ . '/includes/footer.php'; ?>
