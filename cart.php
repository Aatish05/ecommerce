<?php
require_once __DIR__ . '/includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf($_POST['csrf_token'] ?? null)) {
        set_flash('danger', 'Security check failed. Please try again.');
        redirect('cart.php');
    }

    $action = $_POST['action'] ?? '';
    $productId = (int) ($_POST['product_id'] ?? 0);
    $product = $productId ? product_by_id($productId) : null;

    if ($action === 'add' && $product) {
        $quantity = max(1, min((int) ($_POST['quantity'] ?? 1), (int) $product['stock']));
        $_SESSION['cart'][$productId] = min((int) $product['stock'], (int) ($_SESSION['cart'][$productId] ?? 0) + $quantity);
        set_flash('success', $product['name'] . ' was added to your cart.');
    } elseif ($action === 'update' && $product) {
        $quantity = max(0, min((int) ($_POST['quantity'] ?? 0), (int) $product['stock']));
        if ($quantity === 0) {
            unset($_SESSION['cart'][$productId]);
        } else {
            $_SESSION['cart'][$productId] = $quantity;
        }
        set_flash('success', 'Cart updated.');
    } elseif ($action === 'clear') {
        unset($_SESSION['cart']);
        set_flash('success', 'Cart cleared.');
    } else {
        set_flash('warning', 'Unable to update cart. Please choose a valid product.');
    }

    redirect('cart.php');
}

$pageTitle = 'Shopping Cart';
$metaDescription = 'Review selected electronics before secure checkout.';
$items = cart_items();
include __DIR__ . '/includes/header.php';
?>
<section class="container">
    <h1>Shopping cart</h1>
    <?php if (!$items): ?>
        <div class="alert alert-info">Your cart is empty.</div>
        <a class="btn btn-success" href="<?= url('products.php') ?>">Continue shopping</a>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table align-middle">
                <caption>Products currently in your shopping cart</caption>
                <thead>
                    <tr>
                        <th scope="col">Product</th>
                        <th scope="col">Price</th>
                        <th scope="col">Quantity</th>
                        <th scope="col">Line total</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($items as $item): ?>
                        <tr>
                            <td>
                                <div class="d-flex align-items-center gap-3">
                                    <img src="<?= e($item['image_url']) ?>" alt="<?= e($item['name']) ?> thumbnail" width="72" height="72" class="rounded object-fit-contain bg-light p-2">
                                    <div>
                                        <strong><?= e($item['name']) ?></strong><br>
                                        <span class="text-body-secondary small"><?= e($item['category_name']) ?></span>
                                    </div>
                                </div>
                            </td>
                            <td><?= money((float) $item['price']) ?></td>
                            <td>
                                <form class="d-flex gap-2" method="post">
                                    <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                                    <input type="hidden" name="action" value="update">
                                    <input type="hidden" name="product_id" value="<?= (int) $item['id'] ?>">
                                    <label class="visually-hidden" for="quantity-<?= (int) $item['id'] ?>">Quantity for <?= e($item['name']) ?></label>
                                    <input class="form-control" id="quantity-<?= (int) $item['id'] ?>" type="number" name="quantity" min="0" max="<?= (int) $item['stock'] ?>" value="<?= (int) $item['quantity'] ?>">
                                    <button class="btn btn-outline-success" type="submit">Update</button>
                                </form>
                            </td>
                            <td><?= money((float) $item['line_total']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
            <form method="post">
                <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                <input type="hidden" name="action" value="clear">
                <button class="btn btn-outline-danger" type="submit" data-confirm="Clear all items from your cart?">Clear cart</button>
            </form>
            <div class="text-md-end">
                <p class="fs-4 mb-2"><strong>Total:</strong> <?= money(cart_total()) ?></p>
                <a class="btn btn-success btn-lg" href="<?= url('checkout.php') ?>">Proceed to checkout</a>
            </div>
        </div>
    <?php endif; ?>
</section>
<?php include __DIR__ . '/includes/footer.php'; ?>
