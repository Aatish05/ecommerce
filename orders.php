<?php
require_once __DIR__ . '/includes/functions.php';
require_login();

$stmt = db()->prepare(
    'SELECT o.*,
            COUNT(oi.id) AS item_count,
            GROUP_CONCAT(CONCAT(p.name, " x", oi.quantity) ORDER BY p.name SEPARATOR ", ") AS product_summary
     FROM orders o
     LEFT JOIN order_items oi ON oi.order_id = o.id
     LEFT JOIN products p ON p.id = oi.product_id
     WHERE o.user_id = ?
     GROUP BY o.id
     ORDER BY o.created_at DESC'
);
$stmt->execute([current_user()['id']]);
$orders = $stmt->fetchAll();

function customer_status_badge(string $status): string
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

$pageTitle = 'My Orders';
$metaDescription = 'View your DGShop Electronics order history and order completion status.';
include __DIR__ . '/includes/header.php';
?>
<section class="container">
    <div class="d-flex flex-column flex-md-row justify-content-between gap-3 align-items-md-center mb-4">
        <div>
            <h1>My orders</h1>
            <p class="text-body-secondary mb-0">
                View your order history, products purchased, total amount, and completion status.
            </p>
        </div>

        <a class="btn btn-success" href="<?= url('products.php') ?>">
            Continue shopping
        </a>
    </div>

    <?php if (!$orders): ?>
        <div class="alert alert-info">
            You have not placed any orders yet.
        </div>
    <?php else: ?>
        <div class="row g-4">
            <?php foreach ($orders as $order): ?>
                <?php
                    $status = $order['status'];
                    $badge = customer_status_badge($status);
                    $isCompleted = $status === 'completed';
                ?>

                <div class="col-12">
                    <div class="card shadow-sm border-<?= e($badge) ?>">
                        <div class="card-body">
                            <div class="row g-3 align-items-center">
                                <div class="col-lg-2">
                                    <h2 class="h5 mb-1">
                                        Order #<?= (int) $order['id'] ?>
                                    </h2>
                                    <p class="small text-body-secondary mb-0">
                                        <?= e(date('M j, Y H:i', strtotime($order['created_at']))) ?>
                                    </p>
                                </div>

                                <div class="col-lg-4">
                                    <strong>Products</strong>
                                    <p class="small text-body-secondary mb-0">
                                        <?= e($order['product_summary'] ?? 'No product details available') ?>
                                    </p>
                                </div>

                                <div class="col-lg-2">
                                    <strong>Items</strong>
                                    <p class="mb-0">
                                        <?= (int) $order['item_count'] ?>
                                    </p>
                                </div>

                                <div class="col-lg-2">
                                    <strong>Total</strong>
                                    <p class="mb-0 text-success fw-bold">
                                        <?= money((float) $order['total_amount']) ?>
                                    </p>
                                </div>

                                <div class="col-lg-2 text-lg-end">
                                    <span class="badge text-bg-<?= e($badge) ?> mb-2">
                                        <?= e(ucfirst($status)) ?>
                                    </span>

                                    <?php if ($isCompleted): ?>
                                        <div class="small text-success">
                                            Order completed
                                        </div>
                                    <?php else: ?>
                                        <div class="small text-body-secondary">
                                            Not completed yet
                                        </div>
                                    <?php endif; ?>

                                    <a
                                        class="btn btn-sm btn-outline-success mt-2"
                                        href="<?= url('order.php?id=' . (int) $order['id']) ?>"
                                    >
                                        View details
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>
<?php include __DIR__ . '/includes/footer.php'; ?>