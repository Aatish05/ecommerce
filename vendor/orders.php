<?php
require_once __DIR__ . '/../includes/functions.php';
require_vendor();

$sellerId = (int) current_user()['id'];
$stmt = db()->prepare(
    'SELECT o.id, o.status, o.created_at, u.name AS customer_name, u.email,
            GROUP_CONCAT(CONCAT(p.name, " x", oi.quantity) ORDER BY p.name SEPARATOR ", ") AS items,
            SUM(oi.quantity) AS units,
            SUM(oi.quantity * oi.unit_price) AS seller_total
     FROM orders o
     JOIN users u ON u.id = o.user_id
     JOIN order_items oi ON oi.order_id = o.id
     JOIN products p ON p.id = oi.product_id
     WHERE p.seller_id = ?
     GROUP BY o.id, o.status, o.created_at, u.name, u.email
     ORDER BY o.created_at DESC'
);
$stmt->execute([$sellerId]);
$orders = $stmt->fetchAll();

$pageTitle = 'Vendor Orders';
$active = 'vendor';
$vendorActive = 'orders';
include __DIR__ . '/../includes/header.php';
?>
<section class="container">
    <div class="row g-4">
        <div class="col-lg-3"><?php include __DIR__ . '/_sidebar.php'; ?></div>
        <div class="col-lg-9">
            <h1>My product order history</h1>
            <p class="text-body-secondary">These are orders that contain products from your vendor catalogue.</p>
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <caption>Vendor order history and revenue</caption>
                    <thead><tr><th scope="col">Order</th><th scope="col">Customer</th><th scope="col">Products</th><th scope="col">Units</th><th scope="col">Your revenue</th><th scope="col">Status</th></tr></thead>
                    <tbody>
                        <?php foreach ($orders as $order): ?>
                            <tr>
                                <th scope="row">#<?= (int) $order['id'] ?><br><span class="small text-body-secondary"><?= e(date('M j, Y H:i', strtotime($order['created_at']))) ?></span></th>
                                <td><?= e($order['customer_name']) ?><br><span class="small text-body-secondary"><?= e($order['email']) ?></span></td>
                                <td><?= e($order['items']) ?></td>
                                <td><?= (int) $order['units'] ?></td>
                                <td><?= money((float) $order['seller_total']) ?></td>
                                <td><span class="badge text-bg-secondary"><?= e($order['status']) ?></span></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</section>
<?php include __DIR__ . '/../includes/footer.php'; ?>
