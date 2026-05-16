<?php
require_once __DIR__ . '/includes/functions.php';
require_login();

$orderId = (int) ($_GET['id'] ?? 0);
$stmt = db()->prepare('SELECT * FROM orders WHERE id = ? AND user_id = ?');
$stmt->execute([$orderId, current_user()['id']]);
$order = $stmt->fetch();

if (!$order) {
    set_flash('warning', 'Order not found.');
    redirect('orders.php');
}

$itemStmt = db()->prepare(
    'SELECT oi.*, p.name, p.image_url
     FROM order_items oi
     JOIN products p ON p.id = oi.product_id
     WHERE oi.order_id = ?'
);
$itemStmt->execute([$orderId]);
$items = $itemStmt->fetchAll();

$pageTitle = 'Order #' . $orderId;
include __DIR__ . '/includes/header.php';
?>
<section class="container">
    <a class="btn btn-outline-secondary mb-3" href="<?= url('orders.php') ?>">&larr; Back to orders</a>
    <h1>Order #<?= (int) $order['id'] ?></h1>
    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h2 class="h4">Items</h2>
                    <?php foreach ($items as $item): ?>
                        <div class="d-flex justify-content-between align-items-center border-bottom py-3">
                            <div class="d-flex align-items-center gap-3">
                                <img src="<?= e($item['image_url']) ?>" alt="<?= e($item['name']) ?> thumbnail" width="72" height="72" class="rounded bg-light p-2">
                                <div>
                                    <strong><?= e($item['name']) ?></strong><br>
                                    <span class="small text-body-secondary">Qty <?= (int) $item['quantity'] ?> at <?= money((float) $item['unit_price']) ?></span>
                                </div>
                            </div>
                            <strong><?= money((float) $item['unit_price'] * (int) $item['quantity']) ?></strong>
                        </div>
                    <?php endforeach; ?>
                    <div class="text-end fs-5 pt-3"><strong>Total: <?= money((float) $order['total_amount']) ?></strong></div>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h2 class="h4">Delivery</h2>
                    <p><strong>Status:</strong> <?= e(ucfirst($order['status'])) ?></p>
                    <p><strong>Name:</strong> <?= e($order['shipping_name']) ?></p>
                    <p><strong>Address:</strong><br><?= nl2br(e($order['shipping_address'])) ?></p>
                    <?php if ($order['notes']): ?>
                        <p><strong>Notes:</strong><br><?= nl2br(e($order['notes'])) ?></p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</section>
<?php include __DIR__ . '/includes/footer.php'; ?>
