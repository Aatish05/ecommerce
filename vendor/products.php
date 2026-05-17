<?php
require_once __DIR__ . '/../includes/functions.php';
require_vendor();

$sellerId = (int) current_user()['id'];
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete') {
    if (!verify_csrf($_POST['csrf_token'] ?? null)) {
        set_flash('danger', 'Security check failed. Please try again.');
    } else {
        $productId = (int) ($_POST['product_id'] ?? 0);
        $stmt = db()->prepare('SELECT COUNT(*) FROM products WHERE id = ? AND seller_id = ?');
        $stmt->execute([$productId, $sellerId]);
        if ((int) $stmt->fetchColumn() === 0) {
            set_flash('danger', 'Product not found in your vendor account.');
        } else {
            $stmt = db()->prepare('SELECT COUNT(*) FROM order_items WHERE product_id = ?');
            $stmt->execute([$productId]);
            if ((int) $stmt->fetchColumn() > 0) {
                $stmt = db()->prepare('UPDATE products SET is_active = 0 WHERE id = ? AND seller_id = ?');
                $stmt->execute([$productId, $sellerId]);
                set_flash('success', 'Product has orders, so it was hidden instead of deleted.');
            } else {
                $stmt = db()->prepare('DELETE FROM products WHERE id = ? AND seller_id = ?');
                $stmt->execute([$productId, $sellerId]);
                set_flash('success', 'Product deleted.');
            }
        }
    }
    redirect('vendor/products.php');
}

$stmt = db()->prepare(
    'SELECT p.*, c.name AS category_name,
            COALESCE(sales.units_sold, 0) AS units_sold,
            COALESCE(sales.revenue, 0) AS revenue
     FROM products p
     JOIN categories c ON c.id = p.category_id
     LEFT JOIN (
        SELECT product_id, SUM(quantity) AS units_sold, SUM(quantity * unit_price) AS revenue
        FROM order_items
        GROUP BY product_id
     ) sales ON sales.product_id = p.id
     WHERE p.seller_id = ?
     ORDER BY p.created_at DESC'
);
$stmt->execute([$sellerId]);
$products = $stmt->fetchAll();

$pageTitle = 'Vendor Products';
$active = 'vendor';
$vendorActive = 'products';
include __DIR__ . '/../includes/header.php';
?>
<section class="container">
    <div class="row g-4">
        <div class="col-lg-3"><?php include __DIR__ . '/_sidebar.php'; ?></div>
        <div class="col-lg-9">
            <div class="d-flex flex-column flex-md-row justify-content-between gap-3 mb-3">
                <div>
                    <h1>My products</h1>
                    <p class="text-body-secondary mb-0">Add products, update stock, and track your product revenue.</p>
                </div>
                <a class="btn btn-success align-self-md-start" href="<?= url('vendor/product-form.php') ?>">Add product</a>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <caption>Your vendor product catalogue</caption>
                    <thead><tr><th scope="col">Product</th><th scope="col">Category</th><th scope="col">Price</th><th scope="col">Stock</th><th scope="col">Sold</th><th scope="col">Revenue</th><th scope="col">Actions</th></tr></thead>
                    <tbody>
                        <?php foreach ($products as $product): ?>
                            <tr>
                                <th scope="row"><?= e($product['name']) ?><br><span class="small text-body-secondary"><?= (int) $product['is_active'] ? 'Active' : 'Hidden' ?></span></th>
                                <td><?= e($product['category_name']) ?></td>
                                <td><?= money((float) $product['price']) ?></td>
                                <td><?= (int) $product['stock'] ?></td>
                                <td><?= (int) $product['units_sold'] ?></td>
                                <td><?= money((float) $product['revenue']) ?></td>
                                <td>
                                    <div class="d-flex flex-wrap gap-2">
                                        <a class="btn btn-sm btn-outline-primary" href="<?= url('vendor/product-form.php?id=' . (int) $product['id']) ?>">Edit</a>
                                        <form method="post">
                                            <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="product_id" value="<?= (int) $product['id'] ?>">
                                            <button class="btn btn-sm btn-outline-danger" type="submit" data-confirm="Delete this product?">Delete</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</section>
<?php include __DIR__ . '/../includes/footer.php'; ?>
