<?php
require_once __DIR__ . '/includes/functions.php';
require_login();

$items = cart_items();
if (!$items) {
    set_flash('info', 'Add products to your cart before checkout.');
    redirect('products.php');
}

$errors = [];
$values = [
    'shipping_name' => current_user()['name'],
    'shipping_address' => '',
    'notes' => '',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf($_POST['csrf_token'] ?? null)) {
        $errors['form'] = 'Security check failed. Please try again.';
    }

    $values['shipping_name'] = trim($_POST['shipping_name'] ?? '');
    $values['shipping_address'] = trim($_POST['shipping_address'] ?? '');
    $values['notes'] = trim($_POST['notes'] ?? '');

    if ($values['shipping_name'] === '') {
        $errors['shipping_name'] = 'Please enter the recipient name.';
    }
    if (strlen($values['shipping_address']) < 12) {
        $errors['shipping_address'] = 'Please enter a complete delivery address.';
    }

    foreach ($items as $item) {
        if ((int) $item['quantity'] > (int) $item['stock']) {
            $errors['stock'] = 'One or more products no longer has enough stock. Please update your cart.';
            break;
        }
    }

    if (!$errors) {
        $orderId = create_order((int) current_user()['id'], $items, $values['shipping_name'], $values['shipping_address'], $values['notes']);
        unset($_SESSION['cart']);
        set_flash('success', 'Order #' . $orderId . ' was placed successfully.');
        redirect('orders.php');
    }
}

$pageTitle = 'Checkout';
$metaDescription = 'Secure checkout for DGShop Electronics orders.';
include __DIR__ . '/includes/header.php';
?>
<section class="container">
    <h1>Checkout</h1>
    <?php if (isset($errors['form']) || isset($errors['stock'])): ?>
        <div class="alert alert-danger"><?= e($errors['form'] ?? $errors['stock']) ?></div>
    <?php endif; ?>
    <div class="row g-4">
        <div class="col-lg-7">
            <form class="card card-body shadow-sm needs-validation" method="post" novalidate>
                <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                <div class="mb-3">
                    <label class="form-label form-required" for="shipping_name">Recipient name</label>
                    <input class="form-control <?= isset($errors['shipping_name']) ? 'is-invalid' : '' ?>" id="shipping_name" name="shipping_name" value="<?= e($values['shipping_name']) ?>" required>
                    <div class="invalid-feedback"><?= e($errors['shipping_name'] ?? 'Please enter the recipient name.') ?></div>
                </div>
                <div class="mb-3">
                    <label class="form-label form-required" for="shipping_address">Delivery address</label>
                    <textarea class="form-control <?= isset($errors['shipping_address']) ? 'is-invalid' : '' ?>" id="shipping_address" name="shipping_address" rows="4" minlength="12" required><?= e($values['shipping_address']) ?></textarea>
                    <div class="invalid-feedback"><?= e($errors['shipping_address'] ?? 'Please enter a complete delivery address.') ?></div>
                </div>
                <div class="mb-3">
                    <label class="form-label" for="notes">Order notes</label>
                    <textarea class="form-control" id="notes" name="notes" rows="3"><?= e($values['notes']) ?></textarea>
                </div>
                <button class="btn btn-success btn-lg" type="submit">Place order</button>
            </form>
        </div>
        <div class="col-lg-5">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h2 class="h4">Order summary</h2>
                    <?php foreach ($items as $item): ?>
                        <div class="d-flex justify-content-between border-bottom py-2">
                            <span><?= e($item['name']) ?> x <?= (int) $item['quantity'] ?></span>
                            <strong><?= money((float) $item['line_total']) ?></strong>
                        </div>
                    <?php endforeach; ?>
                    <div class="d-flex justify-content-between pt-3 fs-5">
                        <strong>Total</strong>
                        <strong><?= money(cart_total()) ?></strong>
                    </div>
                    <p class="small text-body-secondary mt-3 mb-0">Payment integration is simulated for the student project demo; order data is still stored in MySQL.</p>
                </div>
            </div>
        </div>
    </div>
</section>
<?php include __DIR__ . '/includes/footer.php'; ?>
