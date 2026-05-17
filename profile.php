<?php
require_once __DIR__ . '/includes/functions.php';
require_login();

$user = current_user();
$errors = [];

$stmt = db()->prepare('SELECT * FROM users WHERE id = ?');
$stmt->execute([$user['id']]);
$userDetails = $stmt->fetch();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf($_POST['csrf_token'] ?? null)) {
        $errors['form'] = 'Security check failed. Please try again.';
    }

    $name = trim($_POST['name'] ?? '');

    if ($name === '') {
        $errors['name'] = 'Please enter your name.';
    }

    if (!$errors) {
        $stmt = db()->prepare('UPDATE users SET name = ? WHERE id = ?');
        $stmt->execute([$name, $user['id']]);

        $_SESSION['user']['name'] = $name;

        set_flash('success', 'Profile updated.');
        redirect('profile.php');
    }
}

$pageTitle = 'Profile';
include __DIR__ . '/includes/header.php';
?>
<section class="container">
    <div class="row justify-content-center">
        <div class="col-lg-6">
            <h1>Profile</h1>

            <?php if (isset($errors['form'])): ?>
                <div class="alert alert-danger"><?= e($errors['form']) ?></div>
            <?php endif; ?>

            <div class="card shadow-sm mb-4">
                <div class="card-body text-center">
                    <?php if (!empty($userDetails['profile_image'])): ?>
                        <img
                            src="<?= e(url($userDetails['profile_image'])) ?>"
                            alt="Profile image"
                            class="rounded-circle border mb-3"
                            width="140"
                            height="140"
                            style="object-fit: cover;"
                        >
                    <?php else: ?>
                        <div
                            class="rounded-circle bg-secondary text-white d-inline-flex align-items-center justify-content-center mb-3"
                            style="width:140px;height:140px;"
                        >
                            No Image
                        </div>
                    <?php endif; ?>

                    <h2 class="h4 mb-1"><?= e($userDetails['name'] ?? $user['name']) ?></h2>
                    <p class="text-body-secondary mb-0"><?= e(ucfirst($userDetails['role'] ?? $user['role'])) ?> account</p>
                </div>
            </div>

            <form class="card card-body shadow-sm needs-validation" method="post" novalidate>
                <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">

                <div class="mb-3">
                    <label class="form-label form-required" for="name">Full name</label>
                    <input class="form-control <?= isset($errors['name']) ? 'is-invalid' : '' ?>" id="name" name="name" value="<?= e($_POST['name'] ?? $user['name']) ?>" required>
                    <div class="invalid-feedback"><?= e($errors['name'] ?? 'Please enter your name.') ?></div>
                </div>

                <div class="mb-3">
                    <label class="form-label" for="email">Email address</label>
                    <input class="form-control" id="email" value="<?= e($user['email']) ?>" disabled>
                </div>

                <button class="btn btn-success" type="submit">Save profile</button>
            </form>
        </div>
    </div>
</section>
<?php include __DIR__ . '/includes/footer.php'; ?>