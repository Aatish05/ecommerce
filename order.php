<?php
require_once __DIR__ . '/includes/functions.php';
require_login();

$orderId = (int) ($_GET['id'] ?? 0);

$stmt = db()->prepare(
    'SELECT *
     FROM orders
     WHERE id = ? AND user_id = ?'
);
$stmt->execute([$orderId, current_user()['id']]);
$order = $stmt->fetch();

if (!$order) {
    set_flash('warning', 'Order not found.');
    redirect('orders.php');
}

$itemStmt = db()->prepare(
    'SELECT oi.*, p.name, p.image_url, p.brand, p.id AS product_id
     FROM order_items oi
     JOIN products p ON p.id = oi.product_id
     WHERE oi.order_id = ?
     ORDER BY p.name'
);
$itemStmt->execute([$orderId]);
$items = $itemStmt->fetchAll();

function order_detail_badge(string $status): string
{
    if ($status === 'completed') {
        return 'success';
    }

    if ($status === 'cancelled') {
        return 'danger';
    }

    if ($status === 'shipped') {
        return 'primary';
    }

    if ($status === 'processing') {
        return 'warning';
    }

    return 'secondary';
}

$status = $order['status'];
$badge = order_detail_badge($status);
$isCompleted = $status === 'completed';

$pageTitle = 'Order #' . $orderId;
$metaDescription = 'View detailed order information and completion status.';
include __DIR__ . '/includes/header.php';
?>
<section class="container">
    <a class="btn btn-outline-secondary mb-3" href="<?= url('orders.php') ?>">
        &larr; Back to orders
    </a>

    <div class="d-flex flex-column flex-md-row justify-content-between gap-3 align-items-md-center mb-4">
        <div>
            <h1>Order #<?= (int) $order['id'] ?></h1>
            <p class="text-body-secondary mb-0">
                Placed on <?= e(date('M j, Y H:i', strtotime($order['created_at']))) ?>
            </p>
        </div>

        <div class="text-md-end">
            <span class="badge text-bg-<?= e($badge) ?> fs-6">
                <?= e(ucfirst($status)) ?>
            </span>

            <?php if ($isCompleted): ?>
                <p class="text-success fw-semibold mb-0 mt-2">
                    This order has been completed.
                </p>
            <?php else: ?>
                <p class="text-body-secondary mb-0 mt-2">
                    This order is not completed yet.
                </p>
            <?php endif; ?>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h2 class="h4">Order items</h2>

                    <?php foreach ($items as $item): ?>
                        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center border-bottom py-3 gap-3">
                            <div class="d-flex align-items-center gap-3">
                                <img
                                    src="<?= e(url($item['image_url'])) ?>"
                                    alt="<?= e($item['name']) ?> thumbnail"
                                    width="90"
                                    height="90"
                                    class="rounded bg-light p-2"
                                    style="object-fit: cover;"
                                >

                                <div>
                                    <strong><?= e($item['name']) ?></strong><br>
                                    <span class="small text-body-secondary">
                                        Brand: <?= e($item['brand']) ?>
                                    </span><br>
                                    <span class="small text-body-secondary">
                                        Quantity: <?= (int) $item['quantity'] ?>
                                        x <?= money((float) $item['unit_price']) ?>
                                    </span>
                                </div>
                            </div>

                            <div class="text-md-end">
                                <strong>
                                    <?= money((float) $item['unit_price'] * (int) $item['quantity']) ?>
                                </strong>
                                <br>
                                <a
                                    class="small"
                                    href="<?= url('product.php?id=' . (int) $item['product_id']) ?>"
                                >
                                    View product
                                </a>
                            </div>
                        </div>
                    <?php endforeach; ?>

                    <div class="text-end fs-5 pt-3">
                        <strong>
                            Total: <?= money((float) $order['total_amount']) ?>
                        </strong>
                    </div>
                </div>
            </div>

            <div class="card shadow-sm mt-4">
                <div class="card-body">
                    <h2 class="h4">Order status explanation</h2>

                    <div class="row g-3 text-center">
                        <div class="col-6 col-md">
                            <div class="border rounded-3 p-3 <?= $status === 'pending' ? 'border-success bg-success-subtle' : '' ?>">
                                Pending
                            </div>
                        </div>

                        <div class="col-6 col-md">
                            <div class="border rounded-3 p-3 <?= $status === 'processing' ? 'border-success bg-success-subtle' : '' ?>">
                                Processing
                            </div>
                        </div>

                        <div class="col-6 col-md">
                            <div class="border rounded-3 p-3 <?= $status === 'shipped' ? 'border-success bg-success-subtle' : '' ?>">
                                Shipped
                            </div>
                        </div>

                        <div class="col-6 col-md">
                            <div class="border rounded-3 p-3 <?= $status === 'completed' ? 'border-success bg-success-subtle' : '' ?>">
                                Completed
                            </div>
                        </div>

                        <div class="col-6 col-md">
                            <div class="border rounded-3 p-3 <?= $status === 'cancelled' ? 'border-danger bg-danger-subtle' : '' ?>">
                                Cancelled
                            </div>
                        </div>
                    </div>

                    <p class="small text-body-secondary mt-3 mb-0">
                        Vendors update order status. Customers can check this page to know whether the order has been completed.
                    </p>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h2 class="h4">Delivery details</h2>

                    <p>
                        <strong>Status:</strong>
                        <span class="badge text-bg-<?= e($badge) ?>">
                            <?= e(ucfirst($status)) ?>
                        </span>
                    </p>

                    <p>
                        <strong>Completed:</strong>
                        <?= $isCompleted ? 'Yes' : 'No' ?>
                    </p>

                    <p>
                        <strong>Name:</strong><br>
                        <?= e($order['shipping_name']) ?>
                    </p>

                    <p>
                        <strong>Address:</strong><br>
                        <?= nl2br(e($order['shipping_address'])) ?>
                    </p>

                    <?php if (!empty($order['notes'])): ?>
                        <p>
                            <strong>Notes:</strong><br>
                            <?= nl2br(e($order['notes'])) ?>
                        </p>
                    <?php endif; ?>
                </div>
            </div>

            <div class="alert alert-light border mt-4">
                <h2 class="h6">Need help?</h2>
                <p class="small mb-0">
                    If your order status has not changed for a long time, contact DGShop support through the contact page.
                </p>
            </div>
        </div>
    </div>
</section>
<?php include __DIR__ . '/includes/footer.php'; ?>