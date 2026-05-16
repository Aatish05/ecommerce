<?php
require_once __DIR__ . '/includes/functions.php';
$product = product_by_id((int) ($_GET['id'] ?? 0));
if (!$product) {
    http_response_code(404);
    $pageTitle = 'Product not found';
    include __DIR__ . '/includes/header.php';
    echo '<section class="container"><div class="alert alert-warning">Product not found.</div><a class="btn btn-success" href="' . e(url('products.php')) . '">Back to shop</a></section>';
    include __DIR__ . '/includes/footer.php';
    exit;
}
$pageTitle = $product['name'];
$metaDescription = substr($product['description'], 0, 155);
$active = 'products';
include __DIR__ . '/includes/header.php';
?>
<section class="container">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?= url('products.php') ?>">Shop</a></li>
            <li class="breadcrumb-item active" aria-current="page"><?= e($product['name']) ?></li>
        </ol>
    </nav>
    <div class="row g-5">
        <div class="col-lg-6">
            <img class="img-fluid product-image rounded-4 w-100" src="<?= e($product['image_url']) ?>" alt="<?= e($product['name']) ?> product image">
        </div>
        <div class="col-lg-6">
            <span class="badge text-bg-success"><?= e($product['category_name']) ?></span>
            <p class="text-body-secondary mt-3 mb-1"><?= e($product['brand']) ?></p>
            <h1><?= e($product['name']) ?></h1>
            <p class="lead"><?= e($product['description']) ?></p>
            <p class="display-6 text-success fw-bold"><?= money((float) $product['price']) ?></p>
            <p><strong>Stock:</strong> <?= (int) $product['stock'] ?> available</p>
            <form class="needs-validation" method="post" action="<?= url('cart.php') ?>" novalidate>
                <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                <input type="hidden" name="action" value="add">
                <input type="hidden" name="product_id" value="<?= (int) $product['id'] ?>">
                <div class="row g-2 align-items-end">
                    <div class="col-4">
                        <label class="form-label" for="quantity">Quantity</label>
                        <input class="form-control" type="number" id="quantity" name="quantity" min="1" max="<?= max(1, (int) $product['stock']) ?>" value="1" required>
                        <div class="invalid-feedback">Please choose a valid quantity.</div>
                    </div>
                    <div class="col-8">
                        <button class="btn btn-success btn-lg w-100" type="submit" <?= (int) $product['stock'] <= 0 ? 'disabled' : '' ?>>
                            <i class="bi bi-cart-plus" aria-hidden="true"></i> Add to cart
                        </button>
                    </div>
                </div>
            </form>
            <div class="alert alert-light border mt-4">
                <h2 class="h5">Ethical product note</h2>
                <p class="mb-0">We prioritise energy-efficient products, clear pricing, and honest descriptions to support ethical consumer decisions.</p>
            </div>
        </div>
    </div>
</section>
<?php include __DIR__ . '/includes/footer.php'; ?>
