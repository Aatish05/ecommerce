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
$reviewErrors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'review') {
    if (!is_logged_in()) {
        set_flash('warning', 'Please log in before writing a review.');
        redirect('login.php');
    }
    if (!verify_csrf($_POST['csrf_token'] ?? null)) {
        $reviewErrors['form'] = 'Security check failed. Please try again.';
    } else {
        [$saved, $reviewErrors] = save_review(
            (int) $product['id'],
            (int) current_user()['id'],
            (int) ($_POST['rating'] ?? 0),
            $_POST['title'] ?? '',
            $_POST['body'] ?? ''
        );
        if ($saved) {
            set_flash('success', 'Thank you. Your review was published.');
            redirect('product.php?id=' . (int) $product['id']);
        }
    }
}
$reviews = product_reviews((int) $product['id']);
$reviewSummary = review_summary((int) $product['id']);
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
            <img
            class="img-fluid product-detail-image rounded-4 w-100"
            src="<?= e($product['image_url']) ?>"
            alt="<?= e($product['name']) ?> product image"
        >
        </div>
        <div class="col-lg-6">
            <span class="badge text-bg-success"><?= e($product['category_name']) ?></span>
            <p class="text-body-secondary mt-3 mb-1"><?= e($product['brand']) ?></p>
            <h1><?= e($product['name']) ?></h1>
            <p class="mb-2"><strong>Seller:</strong> <?= e($product['seller_name']) ?></p>
            <p class="text-warning" aria-label="Average product rating">
                <?= number_format($reviewSummary['average_rating'], 1) ?>/5 stars from <?= $reviewSummary['review_count'] ?> review<?= $reviewSummary['review_count'] === 1 ? '' : 's' ?>
            </p>
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
<section class="container mt-5">
    <div class="row g-4">
        <div class="col-lg-7">
            <h2>Customer reviews</h2>
            <?php if (!$reviews): ?>
                <div class="alert alert-info">No reviews yet. Be the first customer to review this product.</div>
            <?php endif; ?>
            <?php foreach ($reviews as $review): ?>
                <article class="card shadow-sm mb-3">
                    <div class="card-body">
                        <div class="text-warning"><?= (int) $review['rating'] ?>/5 stars</div>
                        <h3 class="h5"><?= e($review['title']) ?></h3>
                        <p class="small text-body-secondary">By <?= e($review['reviewer_name']) ?> on <?= e(date('M j, Y', strtotime($review['created_at']))) ?></p>
                        <p class="mb-0"><?= e($review['body']) ?></p>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
        <div class="col-lg-5">
            <form class="card card-body shadow-sm needs-validation" method="post" novalidate>
                <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                <input type="hidden" name="action" value="review">
                <h2 class="h4">Write a review</h2>
                <?php if (isset($reviewErrors['form'])): ?><div class="alert alert-danger"><?= e($reviewErrors['form']) ?></div><?php endif; ?>
                <?php if (!is_logged_in()): ?>
                    <p><a href="<?= url('login.php') ?>">Log in</a> to write a product review.</p>
                <?php else: ?>
                    <div class="mb-3">
                        <label class="form-label form-required" for="rating">Rating</label>
                        <select class="form-select <?= isset($reviewErrors['rating']) ? 'is-invalid' : '' ?>" id="rating" name="rating" required>
                            <option value="">Choose rating</option>
                            <?php for ($rating = 5; $rating >= 1; $rating--): ?>
                                <option value="<?= $rating ?>" <?= (string) ($_POST['rating'] ?? '') === (string) $rating ? 'selected' : '' ?>><?= $rating ?> star<?= $rating === 1 ? '' : 's' ?></option>
                            <?php endfor; ?>
                        </select>
                        <div class="invalid-feedback"><?= e($reviewErrors['rating'] ?? 'Please choose a rating.') ?></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label form-required" for="title">Review title</label>
                        <input class="form-control <?= isset($reviewErrors['title']) ? 'is-invalid' : '' ?>" id="title" name="title" value="<?= e($_POST['title'] ?? '') ?>" required>
                        <div class="invalid-feedback"><?= e($reviewErrors['title'] ?? 'Please enter a short review title.') ?></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label form-required" for="body">Review</label>
                        <textarea class="form-control <?= isset($reviewErrors['body']) ? 'is-invalid' : '' ?>" id="body" name="body" rows="4" minlength="10" required><?= e($_POST['body'] ?? '') ?></textarea>
                        <div class="invalid-feedback"><?= e($reviewErrors['body'] ?? 'Review must be at least 10 characters.') ?></div>
                    </div>
                    <button class="btn btn-success" type="submit">Publish review</button>
                <?php endif; ?>
            </form>
        </div>
    </div>
</section>
<?php include __DIR__ . '/includes/footer.php'; ?>
