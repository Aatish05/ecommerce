<?php
require_once __DIR__ . '/../includes/functions.php';
require_admin();

$orders = db()->query(
    'SELECT o.*, u.name AS customer_name, u.email, order_products.items
     FROM orders o
     JOIN users u ON u.id = o.user_id
     LEFT JOIN (
        SELECT oi.order_id,
               GROUP_CONCAT(CONCAT(p.name, " (", seller.name, ") x", oi.quantity) ORDER BY p.name SEPARATOR ", ") AS items
        FROM order_items oi
        JOIN products p ON p.id = oi.product_id
        JOIN users seller ON seller.id = p.seller_id
        GROUP BY oi.order_id
     ) order_products ON order_products.order_id = o.id
     ORDER BY o.created_at DESC'
)->fetchAll();

$pageTitle = 'Manage Orders';
$active = 'admin';
$adminActive = 'orders';
include __DIR__ . '/../includes/header.php';
?>
<section class="container">
    <div class="row g-4">
        <div class="col-lg-3"><?php include __DIR__ . '/_sidebar.php'; ?></div>

        <div class="col-lg-9">
            <h1>Order history</h1>
            <p class="text-body-secondary">Admins can view all orders. Vendors are responsible for updating order status.</p>

            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <caption>Admin order history table</caption>
                    <thead>
                        <tr>
                            <th scope="col">Order</th>
                            <th scope="col">Customer</th>
                            <th scope="col">Products</th>
                            <th scope="col">Address</th>
                            <th scope="col">Total</th>
                            <th scope="col">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($orders as $order): ?>
                            <tr>
                                <th scope="row">
                                    #<?= (int) $order['id'] ?><br>
                                    <span class="small text-body-secondary">
                                        <?= e(date('M j, Y H:i', strtotime($order['created_at']))) ?>
                                    </span>
                                </th>

                                <td>
                                    <?= e($order['customer_name']) ?><br>
                                    <span class="small text-body-secondary"><?= e($order['email']) ?></span>
                                </td>

                                <td class="small"><?= e($order['items'] ?? 'No items') ?></td>

                                <td>
                                    <?= e($order['shipping_name']) ?><br>
                                    <span class="small"><?= e($order['shipping_address']) ?></span>
                                </td>

                                <td><?= money((float) $order['total_amount']) ?></td>

                                <td>
                                    <span class="badge text-bg-secondary">
                                        <?= e(ucfirst($order['status'])) ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>

                        <?php if (!$orders): ?>
                            <tr>
                                <td colspan="6" class="text-center text-body-secondary">No orders found.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</section>
<?php include __DIR__ . '/../includes/footer.php'; ?>