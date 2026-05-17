<?php
require_once __DIR__ . '/../includes/functions.php';
require_vendor();

$sellerId = (int) current_user()['id'];
$allowedStatuses = ['pending', 'processing', 'shipped', 'completed', 'cancelled'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf($_POST['csrf_token'] ?? null)) {
        set_flash('danger', 'Security check failed. Please try again.');
        redirect('vendor/orders.php');
    }

    $orderId = (int) ($_POST['order_id'] ?? 0);
    $status = $_POST['status'] ?? '';

    if (!in_array($status, $allowedStatuses, true)) {
        set_flash('danger', 'Invalid order status.');
        redirect('vendor/orders.php');
    }

    $stmt = db()->prepare(
        'SELECT COUNT(*)
         FROM orders o
         JOIN order_items oi ON oi.order_id = o.id
         JOIN products p ON p.id = oi.product_id
         WHERE o.id = ? AND p.seller_id = ?'
    );
    $stmt->execute([$orderId, $sellerId]);

    if ((int) $stmt->fetchColumn() === 0) {
        set_flash('danger', 'You can only update orders that contain your products.');
        redirect('vendor/orders.php');
    }

    $stmt = db()->prepare('UPDATE orders SET status = ? WHERE id = ?');
    $stmt->execute([$status, $orderId]);

    set_flash('success', 'Order status updated successfully.');
    redirect('vendor/orders.php');
}

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
            <p class="text-body-secondary">Update order status for orders that include your products.</p>

            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <caption>Vendor order history and revenue</caption>
                    <thead>
                        <tr>
                            <th scope="col">Order</th>
                            <th scope="col">Customer</th>
                            <th scope="col">Products</th>
                            <th scope="col">Units</th>
                            <th scope="col">Your revenue</th>
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

                                <td><?= e($order['items']) ?></td>

                                <td><?= (int) $order['units'] ?></td>

                                <td><?= money((float) $order['seller_total']) ?></td>

                                <td>
                                    <form class="d-flex gap-2" method="post">
                                        <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                                        <input type="hidden" name="order_id" value="<?= (int) $order['id'] ?>">

                                        <label class="visually-hidden" for="status-<?= (int) $order['id'] ?>">
                                            Status for order <?= (int) $order['id'] ?>
                                        </label>

                                        <select class="form-select form-select-sm" id="status-<?= (int) $order['id'] ?>" name="status">
                                            <?php foreach ($allowedStatuses as $status): ?>
                                                <option value="<?= e($status) ?>" <?= $order['status'] === $status ? 'selected' : '' ?>>
                                                    <?= e(ucfirst($status)) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>

                                        <button class="btn btn-sm btn-outline-success" type="submit">
                                            Save
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>

                        <?php if (!$orders): ?>
                            <tr>
                                <td colspan="6" class="text-center text-body-secondary">
                                    No orders found for your products.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</section>
<?php include __DIR__ . '/../includes/footer.php'; ?>