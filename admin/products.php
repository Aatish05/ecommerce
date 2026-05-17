<?php
require_once __DIR__ . '/../includes/functions.php';
require_admin();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete') {
    if (!verify_csrf($_POST['csrf_token'] ?? null)) {
        set_flash('danger', 'Security check failed. Please try again.');
    } else {
        $productId = (int) ($_POST['product_id'] ?? 0);
        $stmt = db()->prepare('SELECT COUNT(*) FROM order_items WHERE product_id = ?');
        $stmt->execute([$productId]);
        $hasOrders = (int) $stmt->fetchColumn() > 0;

        if ($hasOrders) {
            $stmt = db()->prepare('UPDATE products SET is_active = 0 WHERE id = ?');
            $stmt->execute([$productId]);
            set_flash('success', 'Product is linked to orders, so it was hidden instead of permanently deleted.');
        } else {
            $stmt = db()->prepare('DELETE FROM products WHERE id = ?');
            $stmt->execute([$productId]);
            set_flash('success', 'Product deleted.');
        }
    }
    redirect('admin/products.php');
}

$q = trim($_GET['q'] ?? '');
$params = [];
$sql = 'SELECT p.*, c.name AS category_name, u.name AS seller_name
        FROM products p
        JOIN categories c ON c.id = p.category_id
        JOIN users u ON u.id = p.seller_id';
if ($q !== '') {
    $sql .= ' WHERE p.name LIKE ? OR p.brand LIKE ?';
    $params = ['%' . $q . '%', '%' . $q . '%'];
}
$sql .= ' ORDER BY p.created_at DESC';
$stmt = db()->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll();

$pageTitle = 'Manage Products';
$active = 'admin';
$adminActive = 'products';
include __DIR__ . '/../includes/header.php';
?>
<section class="container">
    <div class="row g-4">
        <div class="col-lg-3"><?php include __DIR__ . '/_sidebar.php'; ?></div>
        <div class="col-lg-9">
            <div class="d-flex flex-column flex-md-row justify-content-between gap-3 mb-3">
                <div>
                    <h1>Manage products</h1>
                    <p class="text-body-secondary mb-0">Create, read, update, and delete product records.</p>
                </div>
                <a class="btn btn-success align-self-md-start" href="<?= url('admin/product-form.php') ?>">Add product</a>
            </div>
            <form class="mb-3" method="get">
                <label class="form-label" for="q">Search admin products</label>
                <div class="input-group">
                    <input class="form-control" id="q" name="q" value="<?= e($q) ?>" placeholder="Search by name or brand">
                    <button class="btn btn-outline-success" type="submit">Search</button>
                </div>
            </form>
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <caption>Admin product management table</caption>
                    <thead><tr><th scope="col">Product</th><th scope="col">Seller</th><th scope="col">Category</th><th scope="col">Price</th><th scope="col">Stock</th><th scope="col">Status</th><th scope="col">Actions</th></tr></thead>
                    <tbody>
                        <?php foreach ($products as $product): ?>
                            <tr>
                                <th scope="row"><?= e($product['name']) ?><br><span class="small text-body-secondary"><?= e($product['brand']) ?></span></th>
                                <td><?= e($product['seller_name']) ?></td>
                                <td><?= e($product['category_name']) ?></td>
                                <td><?= money((float) $product['price']) ?></td>
                                <td><?= (int) $product['stock'] ?></td>
                                <td><span class="badge text-bg-<?= (int) $product['is_active'] ? 'success' : 'secondary' ?>"><?= (int) $product['is_active'] ? 'Active' : 'Hidden' ?></span></td>
                                <td>
                                    <div class="d-flex gap-2">
                                        <a class="btn btn-sm btn-outline-primary" href="<?= url('admin/product-form.php?id=' . (int) $product['id']) ?>">Edit</a>
                                        <a class="btn btn-sm btn-outline-secondary" href="<?= url('admin/product-details.php?id=' . (int) $product['id']) ?>">Details</a>
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
