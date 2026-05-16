<?php
require_once __DIR__ . '/../includes/functions.php';
require_admin();

$stats = [
    'products' => (int) db()->query('SELECT COUNT(*) FROM products')->fetchColumn(),
    'orders' => (int) db()->query('SELECT COUNT(*) FROM orders')->fetchColumn(),
    'users' => (int) db()->query('SELECT COUNT(*) FROM users')->fetchColumn(),
    'messages' => (int) db()->query('SELECT COUNT(*) FROM contact_messages')->fetchColumn(),
    'revenue' => (float) db()->query('SELECT COALESCE(SUM(total_amount), 0) FROM orders')->fetchColumn(),
];
$recentOrders = db()->query(
    'SELECT o.*, u.name AS customer_name
     FROM orders o
     JOIN users u ON u.id = o.user_id
     ORDER BY o.created_at DESC
     LIMIT 5'
)->fetchAll();

$pageTitle = 'Admin Dashboard';
$active = 'admin';
$adminActive = 'dashboard';
include __DIR__ . '/../includes/header.php';
?>
<section class="container">
    <div class="row g-4">
        <div class="col-lg-3"><?php include __DIR__ . '/_sidebar.php'; ?></div>
        <div class="col-lg-9">
            <h1>Admin dashboard</h1>
            <div class="row g-3 mb-4">
                <div class="col-md-6 col-xl-3"><div class="card text-bg-success"><div class="card-body"><div class="fs-2 fw-bold"><?= $stats['products'] ?></div><span>Products</span></div></div></div>
                <div class="col-md-6 col-xl-3"><div class="card text-bg-primary"><div class="card-body"><div class="fs-2 fw-bold"><?= $stats['orders'] ?></div><span>Orders</span></div></div></div>
                <div class="col-md-6 col-xl-3"><div class="card text-bg-warning"><div class="card-body"><div class="fs-2 fw-bold"><?= $stats['users'] ?></div><span>Users</span></div></div></div>
                <div class="col-md-6 col-xl-3"><div class="card text-bg-dark"><div class="card-body"><div class="fs-2 fw-bold"><?= money($stats['revenue']) ?></div><span>Revenue</span></div></div></div>
            </div>
            <div class="card shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h2 class="h4 mb-0">Recent orders</h2>
                        <a class="btn btn-outline-success btn-sm" href="<?= url('admin/orders.php') ?>">Manage orders</a>
                    </div>
                    <?php if (!$recentOrders): ?>
                        <p class="mb-0">No orders yet.</p>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table align-middle">
                                <thead><tr><th scope="col">Order</th><th scope="col">Customer</th><th scope="col">Status</th><th scope="col">Total</th></tr></thead>
                                <tbody>
                                    <?php foreach ($recentOrders as $order): ?>
                                        <tr>
                                            <th scope="row">#<?= (int) $order['id'] ?></th>
                                            <td><?= e($order['customer_name']) ?></td>
                                            <td><span class="badge text-bg-secondary"><?= e($order['status']) ?></span></td>
                                            <td><?= money((float) $order['total_amount']) ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</section>
<?php include __DIR__ . '/../includes/footer.php'; ?>
