<article class="card h-100 product-card">
    <img src="<?= e($product['image_url']) ?>" class="card-img-top product-image" alt="<?= e($product['name']) ?> product image">
    <div class="card-body d-flex flex-column">
        <span class="badge text-bg-light align-self-start mb-2"><?= e($product['category_name']) ?></span>
        <h3 class="h5 card-title"><?= e($product['name']) ?></h3>
        <p class="small text-body-secondary mb-2">Sold by <?= e($product['seller_name'] ?? 'EcoTech') ?></p>
        <?php $summary = strlen($product['description']) > 110 ? substr($product['description'], 0, 107) . '...' : $product['description']; ?>
        <p class="card-text text-body-secondary small flex-grow-1"><?= e($summary) ?></p>
        <div class="d-flex justify-content-between align-items-center">
            <strong class="fs-5 text-success"><?= money((float) $product['price']) ?></strong>
            <span class="small <?= (int) $product['stock'] > 0 ? 'text-success' : 'text-danger' ?>">
                <?= (int) $product['stock'] > 0 ? (int) $product['stock'] . ' in stock' : 'Out of stock' ?>
            </span>
        </div>
    </div>
    <div class="card-footer bg-transparent border-0 pt-0">
        <div class="d-grid gap-2">
            <a class="btn btn-outline-success" href="<?= url('product.php?id=' . (int) $product['id']) ?>">View details</a>
            <form method="post" action="<?= url('cart.php') ?>">
                <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                <input type="hidden" name="action" value="add">
                <input type="hidden" name="product_id" value="<?= (int) $product['id'] ?>">
                <button class="btn btn-success w-100" type="submit" <?= (int) $product['stock'] <= 0 ? 'disabled' : '' ?>>
                    <i class="bi bi-cart-plus" aria-hidden="true"></i> Add to cart
                </button>
            </form>
        </div>
    </div>
</article>
