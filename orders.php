<?php
require_once __DIR__ . '/includes/functions.php';
require_login();

$stmt = db()->prepare('SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC');
$stmt->execute([current_user()['id']]);
$orders = $stmt->fetchAll();

$pageTitle = 'My Orders';
$metaDescription = 'View your EcoTech Electronics order history.';
include __DIR__ . '/includes/header.php';
?>
<section class="container">
    <h1>My orders</h1>
    <?php if (!$orders): ?>
        <div class="alert alert-info">You have not placed any orders yet.</div>
        <a class="btn btn-success" href="<?= url('products.php') ?>">Start shopping</a>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <caption>Your order history</caption>
                <thead>
                    <tr>
                        <th scope="col">Order</th>
                        <th scope="col">Date</th>
                        <th scope="col">Status</th>
                        <th scope="col">Total</th>
                        <th scope="col">Details</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($orders as $order): ?>
                        <tr>
                            <th scope="row">#<?= (int) $order['id'] ?></th>
                            <td><?= e(date('M j, Y', strtotime($order['created_at']))) ?></td>
                            <td><span class="badge text-bg-secondary"><?= e(ucfirst($order['status'])) ?></span></td>
                            <td><?= money((float) $order['total_amount']) ?></td>
                            <td><a class="btn btn-sm btn-outline-success" href="<?= url('order.php?id=' . (int) $order['id']) ?>">View</a></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</section>
<?php include __DIR__ . '/includes/footer.php'; ?>
