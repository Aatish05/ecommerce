<?php
require_once __DIR__ . '/../includes/functions.php';
require_admin();

$allowedStatuses = ['pending', 'processing', 'shipped', 'completed', 'cancelled'];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf($_POST['csrf_token'] ?? null)) {
        set_flash('danger', 'Security check failed. Please try again.');
    } else {
        $status = $_POST['status'] ?? '';
        if (in_array($status, $allowedStatuses, true)) {
            $stmt = db()->prepare('UPDATE orders SET status = ? WHERE id = ?');
            $stmt->execute([$status, (int) ($_POST['order_id'] ?? 0)]);
            set_flash('success', 'Order status updated.');
        } else {
            set_flash('danger', 'Invalid order status.');
        }
    }
    redirect('admin/orders.php');
}

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
            <h1>Manage orders</h1>
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <caption>Admin order management table</caption>
                    <thead><tr><th scope="col">Order</th><th scope="col">Customer</th><th scope="col">Products</th><th scope="col">Address</th><th scope="col">Total</th><th scope="col">Status</th></tr></thead>
                    <tbody>
                        <?php foreach ($orders as $order): ?>
                            <tr>
                                <th scope="row">#<?= (int) $order['id'] ?><br><span class="small text-body-secondary"><?= e(date('M j, Y H:i', strtotime($order['created_at']))) ?></span></th>
                                <td><?= e($order['customer_name']) ?><br><span class="small text-body-secondary"><?= e($order['email']) ?></span></td>
                                <td class="small"><?= e($order['items']) ?></td>
                                <td><?= e($order['shipping_name']) ?><br><span class="small"><?= e($order['shipping_address']) ?></span></td>
                                <td><?= money((float) $order['total_amount']) ?></td>
                                <td>
                                    <form class="d-flex gap-2" method="post">
                                        <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                                        <input type="hidden" name="order_id" value="<?= (int) $order['id'] ?>">
                                        <label class="visually-hidden" for="status-<?= (int) $order['id'] ?>">Status for order <?= (int) $order['id'] ?></label>
                                        <select class="form-select form-select-sm" id="status-<?= (int) $order['id'] ?>" name="status">
                                            <?php foreach ($allowedStatuses as $status): ?>
                                                <option value="<?= e($status) ?>" <?= $order['status'] === $status ? 'selected' : '' ?>><?= e(ucfirst($status)) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                        <button class="btn btn-sm btn-outline-success" type="submit">Save</button>
                                    </form>
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
