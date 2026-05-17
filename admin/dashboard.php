<?php
require_once __DIR__ . '/../includes/functions.php';
require_admin();

$stats = [
    'products' => (int) db()->query('SELECT COUNT(*) FROM products')->fetchColumn(),
    'orders' => (int) db()->query('SELECT COUNT(*) FROM orders')->fetchColumn(),
    'users' => (int) db()->query('SELECT COUNT(*) FROM users')->fetchColumn(),
    'vendors' => (int) db()->query('SELECT COUNT(*) FROM users WHERE role = "vendor"')->fetchColumn(),
    'messages' => (int) db()->query('SELECT COUNT(*) FROM contact_messages')->fetchColumn(),
    'reviews' => (int) db()->query('SELECT COUNT(*) FROM reviews')->fetchColumn(),
    'revenue' => (float) db()->query('SELECT COALESCE(SUM(total_amount), 0) FROM orders')->fetchColumn(),
];
$recentOrders = db()->query(
    'SELECT o.*, u.name AS customer_name
     FROM orders o
     JOIN users u ON u.id = o.user_id
     ORDER BY o.created_at DESC
     LIMIT 8'
)->fetchAll();
$topProducts = db()->query(
    'SELECT p.id, p.name, u.name AS seller_name,
            COALESCE(sales.units_sold, 0) AS units_sold,
            COALESCE(sales.revenue, 0) AS revenue
     FROM products p
     JOIN users u ON u.id = p.seller_id
     LEFT JOIN (
        SELECT product_id, SUM(quantity) AS units_sold, SUM(quantity * unit_price) AS revenue
        FROM order_items
        GROUP BY product_id
     ) sales ON sales.product_id = p.id
     ORDER BY revenue DESC
     LIMIT 5'
)->fetchAll();
$latestReviews = db()->query(
    'SELECT r.*, p.name AS product_name, u.name AS reviewer_name
     FROM reviews r
     JOIN products p ON p.id = r.product_id
     JOIN users u ON u.id = r.user_id
     ORDER BY r.created_at DESC
     LIMIT 4'
)->fetchAll();
$salesChart = sales_chart_data();

$pageTitle = 'Admin Dashboard';
$active = 'admin';
$adminActive = 'dashboard';
include __DIR__ . '/../includes/header.php';
?>
<section class="container">
    <div class="row g-4">
        <div class="col-lg-3"><?php include __DIR__ . '/_sidebar.php'; ?></div>
        <div class="col-lg-9">
            <div class="d-flex flex-column flex-md-row justify-content-between gap-3 align-items-md-center mb-3">
                <div>
                    <h1>Admin dashboard</h1>
                    <p class="text-body-secondary mb-0">Professional overview of revenue, orders, reviews, sellers, and product performance.</p>
                </div>
                <a class="btn btn-success" href="<?= url('admin/product-form.php') ?>">Add product</a>
            </div>
            <div class="row g-3 mb-4">
                <div class="col-md-6 col-xl-3"><div class="card text-bg-dark"><div class="card-body"><div class="fs-2 fw-bold"><?= money($stats['revenue']) ?></div><span>Total revenue</span></div></div></div>
                <div class="col-md-6 col-xl-3"><div class="card text-bg-primary"><div class="card-body"><div class="fs-2 fw-bold"><?= $stats['orders'] ?></div><span>Orders</span></div></div></div>
                <div class="col-md-6 col-xl-3"><div class="card text-bg-success"><div class="card-body"><div class="fs-2 fw-bold"><?= $stats['products'] ?></div><span>Products</span></div></div></div>
                <div class="col-md-6 col-xl-3"><div class="card text-bg-warning"><div class="card-body"><div class="fs-2 fw-bold"><?= $stats['vendors'] ?></div><span>Vendors</span></div></div></div>
            </div>

            <div class="row g-4">
                <div class="col-xl-8">
                    <div class="card shadow-sm h-100">
                        <div class="card-body">
                            <h2 class="h4">Sales graph</h2>
                            <p class="small text-body-secondary">Revenue grouped by order date.</p>
                            <canvas id="salesChart" height="130" aria-label="Revenue sales chart" role="img"></canvas>
                        </div>
                    </div>
                </div>
                <div class="col-xl-4">
                    <div class="card shadow-sm h-100">
                        <div class="card-body">
                            <h2 class="h4">Marketplace health</h2>
                            <div class="d-flex justify-content-between border-bottom py-2"><span>Customers</span><strong><?= $stats['users'] ?></strong></div>
                            <div class="d-flex justify-content-between border-bottom py-2"><span>Reviews</span><strong><?= $stats['reviews'] ?></strong></div>
                            <div class="d-flex justify-content-between border-bottom py-2"><span>Messages</span><strong><?= $stats['messages'] ?></strong></div>
                            <div class="d-flex justify-content-between py-2"><span>Average order</span><strong><?= money($stats['orders'] ? $stats['revenue'] / $stats['orders'] : 0) ?></strong></div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-8">
                    <div class="card shadow-sm">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h2 class="h4 mb-0">Order history</h2>
                                <a class="btn btn-outline-success btn-sm" href="<?= url('admin/orders.php') ?>">Manage orders</a>
                            </div>
                            <div class="table-responsive">
                                <table class="table align-middle">
                                    <thead><tr><th scope="col">Order</th><th scope="col">Customer</th><th scope="col">Status</th><th scope="col">Total</th><th scope="col">Date</th></tr></thead>
                                    <tbody>
                                        <?php foreach ($recentOrders as $order): ?>
                                            <tr>
                                                <th scope="row">#<?= (int) $order['id'] ?></th>
                                                <td><?= e($order['customer_name']) ?></td>
                                                <td><span class="badge text-bg-secondary"><?= e($order['status']) ?></span></td>
                                                <td><?= money((float) $order['total_amount']) ?></td>
                                                <td><?= e(date('M j', strtotime($order['created_at']))) ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-4">
                    <div class="card shadow-sm">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h2 class="h4 mb-0">Latest reviews</h2>
                                <a class="btn btn-outline-success btn-sm" href="<?= url('admin/reviews.php') ?>">View all</a>
                            </div>
                            <?php foreach ($latestReviews as $review): ?>
                                <article class="border-bottom pb-3 mb-3">
                                    <div class="text-warning" aria-label="<?= (int) $review['rating'] ?> star rating"><?= (int) $review['rating'] ?>/5 stars</div>
                                    <strong><?= e($review['title']) ?></strong>
                                    <p class="small mb-1"><?= e($review['product_name']) ?> by <?= e($review['reviewer_name']) ?></p>
                                    <p class="small text-body-secondary mb-0"><?= e(substr($review['body'], 0, 100)) ?></p>
                                </article>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
                <div class="col-12">
                    <div class="card shadow-sm">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h2 class="h4 mb-0">Product details and sales ranking</h2>
                                <a class="btn btn-outline-success btn-sm" href="<?= url('admin/products.php') ?>">All products</a>
                            </div>
                            <div class="table-responsive">
                                <table class="table align-middle">
                                    <thead><tr><th scope="col">Product</th><th scope="col">Seller</th><th scope="col">Units sold</th><th scope="col">Revenue</th><th scope="col">Details</th></tr></thead>
                                    <tbody>
                                        <?php foreach ($topProducts as $product): ?>
                                            <tr>
                                                <th scope="row"><?= e($product['name']) ?></th>
                                                <td><?= e($product['seller_name']) ?></td>
                                                <td><?= (int) $product['units_sold'] ?></td>
                                                <td><?= money((float) $product['revenue']) ?></td>
                                                <td><a class="btn btn-sm btn-outline-primary" href="<?= url('admin/product-details.php?id=' . (int) $product['id']) ?>">View details</a></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.7/dist/chart.umd.min.js"></script>
<script>
const salesData = <?= json_encode($salesChart, JSON_THROW_ON_ERROR) ?>;
const salesCanvas = document.getElementById('salesChart');
if (salesCanvas) {
    new Chart(salesCanvas, {
        type: 'line',
        data: {
            labels: salesData.labels,
            datasets: [{
                label: 'Revenue',
                data: salesData.values,
                borderColor: '#198754',
                backgroundColor: 'rgba(25, 135, 84, 0.15)',
                tension: 0.35,
                fill: true
            }]
        },
        options: {
            responsive: true,
            plugins: { legend: { display: false } },
            scales: { y: { beginAtZero: true } }
        }
    });
}
</script>
<?php include __DIR__ . '/../includes/footer.php'; ?>
