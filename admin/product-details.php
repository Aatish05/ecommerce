<?php
require_once __DIR__ . '/../includes/functions.php';
require_admin();

$product = product_by_id((int) ($_GET['id'] ?? 0), true);
if (!$product) {
    set_flash('warning', 'Product not found.');
    redirect('admin/products.php');
}

$stmt = db()->prepare(
    'SELECT COALESCE(SUM(oi.quantity), 0) AS units_sold,
            COALESCE(SUM(oi.quantity * oi.unit_price), 0) AS revenue,
            COUNT(DISTINCT oi.order_id) AS order_count
     FROM order_items oi
     WHERE oi.product_id = ?'
);
$stmt->execute([$product['id']]);
$sales = $stmt->fetch() ?: ['units_sold' => 0, 'revenue' => 0, 'order_count' => 0];

$stmt = db()->prepare(
    'SELECT o.id, o.status, o.created_at, u.name AS customer_name, oi.quantity, oi.unit_price
     FROM order_items oi
     JOIN orders o ON o.id = oi.order_id
     JOIN users u ON u.id = o.user_id
     WHERE oi.product_id = ?
     ORDER BY o.created_at DESC'
);
$stmt->execute([$product['id']]);
$orders = $stmt->fetchAll();
$reviews = product_reviews((int) $product['id'], false);
$summary = review_summary((int) $product['id']);

$pageTitle = 'Product Details';
$active = 'admin';
$adminActive = 'products';
include __DIR__ . '/../includes/header.php';
?>
<section class="container">
    <div class="row g-4">
        <div class="col-lg-3"><?php include __DIR__ . '/_sidebar.php'; ?></div>
        <div class="col-lg-9">
            <a class="btn btn-outline-secondary mb-3" href="<?= url('admin/products.php') ?>">&larr; Back to products</a>
            <div class="row g-4">
                <div class="col-lg-5">
                    <img class="img-fluid product-image rounded-4 w-100" src="<?= e($product['image_url']) ?>" alt="<?= e($product['name']) ?> product image">
                </div>
                <div class="col-lg-7">
                    <span class="badge text-bg-<?= (int) $product['is_active'] ? 'success' : 'secondary' ?>"><?= (int) $product['is_active'] ? 'Active' : 'Hidden' ?></span>
                    <h1 class="mt-2"><?= e($product['name']) ?></h1>
                    <p class="text-body-secondary"><?= e($product['brand']) ?> - <?= e($product['category_name']) ?></p>
                    <p><?= e($product['description']) ?></p>
                    <div class="row g-3">
                        <div class="col-sm-6"><div class="card"><div class="card-body"><span class="small text-body-secondary">Price</span><div class="fs-4 fw-bold"><?= money((float) $product['price']) ?></div></div></div></div>
                        <div class="col-sm-6"><div class="card"><div class="card-body"><span class="small text-body-secondary">Stock</span><div class="fs-4 fw-bold"><?= (int) $product['stock'] ?></div></div></div></div>
                        <div class="col-sm-6"><div class="card"><div class="card-body"><span class="small text-body-secondary">Units sold</span><div class="fs-4 fw-bold"><?= (int) $sales['units_sold'] ?></div></div></div></div>
                        <div class="col-sm-6"><div class="card"><div class="card-body"><span class="small text-body-secondary">Revenue</span><div class="fs-4 fw-bold"><?= money((float) $sales['revenue']) ?></div></div></div></div>
                    </div>
                    <p class="mt-3 mb-1"><strong>Seller:</strong> <?= e($product['seller_name']) ?></p>
                    <p><strong>Average review:</strong> <?= number_format($summary['average_rating'], 1) ?>/5 from <?= $summary['review_count'] ?> review<?= $summary['review_count'] === 1 ? '' : 's' ?></p>
                    <a class="btn btn-success" href="<?= url('admin/product-form.php?id=' . (int) $product['id']) ?>">Edit product</a>
                </div>
            </div>

            <div class="row g-4 mt-2">
                <div class="col-lg-7">
                    <div class="card shadow-sm">
                        <div class="card-body">
                            <h2 class="h4">Order history for this product</h2>
                            <div class="table-responsive">
                                <table class="table align-middle">
                                    <thead><tr><th scope="col">Order</th><th scope="col">Customer</th><th scope="col">Qty</th><th scope="col">Revenue</th><th scope="col">Status</th></tr></thead>
                                    <tbody>
                                        <?php foreach ($orders as $order): ?>
                                            <tr>
                                                <th scope="row">#<?= (int) $order['id'] ?></th>
                                                <td><?= e($order['customer_name']) ?></td>
                                                <td><?= (int) $order['quantity'] ?></td>
                                                <td><?= money((float) $order['quantity'] * (float) $order['unit_price']) ?></td>
                                                <td><span class="badge text-bg-secondary"><?= e($order['status']) ?></span></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-5">
                    <div class="card shadow-sm">
                        <div class="card-body">
                            <h2 class="h4">Reviews</h2>
                            <?php if (!$reviews): ?>
                                <p class="mb-0">No reviews yet.</p>
                            <?php endif; ?>
                            <?php foreach ($reviews as $review): ?>
                                <article class="border-bottom pb-3 mb-3">
                                    <div class="text-warning"><?= (int) $review['rating'] ?>/5 stars</div>
                                    <strong><?= e($review['title']) ?></strong>
                                    <p class="small text-body-secondary mb-1">By <?= e($review['reviewer_name']) ?></p>
                                    <p class="mb-1"><?= e($review['body']) ?></p>
                                    <span class="badge text-bg-<?= (int) $review['is_approved'] ? 'success' : 'secondary' ?>"><?= (int) $review['is_approved'] ? 'Approved' : 'Hidden' ?></span>
                                </article>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<?php include __DIR__ . '/../includes/footer.php'; ?>
