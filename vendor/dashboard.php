<?php
require_once __DIR__ . '/../includes/functions.php';
require_vendor();

$sellerId = (int) current_user()['id'];
$stmt = db()->prepare('SELECT COUNT(*) FROM products WHERE seller_id = ?');
$stmt->execute([$sellerId]);
$productCount = (int) $stmt->fetchColumn();

$stmt = db()->prepare(
    'SELECT COUNT(DISTINCT o.id)
     FROM orders o
     JOIN order_items oi ON oi.order_id = o.id
     JOIN products p ON p.id = oi.product_id
     WHERE p.seller_id = ?'
);
$stmt->execute([$sellerId]);
$orderCount = (int) $stmt->fetchColumn();

$stmt = db()->prepare(
    'SELECT COUNT(*)
     FROM reviews r
     JOIN products p ON p.id = r.product_id
     WHERE p.seller_id = ?'
);
$stmt->execute([$sellerId]);
$reviewCount = (int) $stmt->fetchColumn();

$stmt = db()->prepare(
    'SELECT o.id, o.status, o.created_at, u.name AS customer_name,
            SUM(oi.quantity * oi.unit_price) AS seller_total
     FROM orders o
     JOIN users u ON u.id = o.user_id
     JOIN order_items oi ON oi.order_id = o.id
     JOIN products p ON p.id = oi.product_id
     WHERE p.seller_id = ?
     GROUP BY o.id, o.status, o.created_at, u.name
     ORDER BY o.created_at DESC
     LIMIT 6'
);
$stmt->execute([$sellerId]);
$orders = $stmt->fetchAll();

$stmt = db()->prepare(
    'SELECT p.id, p.name, p.stock, p.price,
            COALESCE(sales.units_sold, 0) AS units_sold,
            COALESCE(sales.revenue, 0) AS revenue
     FROM products p
     LEFT JOIN (
        SELECT product_id, SUM(quantity) AS units_sold, SUM(quantity * unit_price) AS revenue
        FROM order_items
        GROUP BY product_id
     ) sales ON sales.product_id = p.id
     WHERE p.seller_id = ?
     ORDER BY revenue DESC
     LIMIT 5'
);
$stmt->execute([$sellerId]);
$topProducts = $stmt->fetchAll();
$salesChart = sales_chart_data($sellerId);

$pageTitle = 'Vendor Dashboard';
$active = 'vendor';
$vendorActive = 'dashboard';
include __DIR__ . '/../includes/header.php';
?>
<section class="container">
    <div class="row g-4">
        <div class="col-lg-3"><?php include __DIR__ . '/_sidebar.php'; ?></div>
        <div class="col-lg-9">
            <div class="d-flex flex-column flex-md-row justify-content-between gap-3 align-items-md-center mb-3">
                <div>
                    <h1>Vendor dashboard</h1>
                    <p class="text-body-secondary mb-0">Track your product revenue, order history, reviews, and stock performance.</p>
                </div>
                <a class="btn btn-success" href="<?= url('vendor/product-form.php') ?>">Add product</a>
            </div>
            <div class="row g-3 mb-4">
                <div class="col-md-6 col-xl-3"><div class="card text-bg-dark"><div class="card-body"><div class="fs-2 fw-bold"><?= money(vendor_revenue($sellerId)) ?></div><span>Your revenue</span></div></div></div>
                <div class="col-md-6 col-xl-3"><div class="card text-bg-primary"><div class="card-body"><div class="fs-2 fw-bold"><?= $orderCount ?></div><span>Orders</span></div></div></div>
                <div class="col-md-6 col-xl-3"><div class="card text-bg-success"><div class="card-body"><div class="fs-2 fw-bold"><?= $productCount ?></div><span>Products</span></div></div></div>
                <div class="col-md-6 col-xl-3"><div class="card text-bg-warning"><div class="card-body"><div class="fs-2 fw-bold"><?= $reviewCount ?></div><span>Reviews</span></div></div></div>
            </div>
            <div class="row g-4">
                <div class="col-xl-8">
                    <div class="card shadow-sm h-100">
                        <div class="card-body">
                            <h2 class="h4">Your sales graph</h2>
                            <canvas id="vendorSalesChart" height="130" aria-label="Vendor revenue sales chart" role="img"></canvas>
                        </div>
                    </div>
                </div>
                <div class="col-xl-4">
                    <div class="card shadow-sm h-100">
                        <div class="card-body">
                            <h2 class="h4">Top products</h2>
                            <?php foreach ($topProducts as $product): ?>
                                <div class="d-flex justify-content-between border-bottom py-2">
                                    <div>
                                        <strong><?= e($product['name']) ?></strong><br>
                                        <span class="small text-body-secondary"><?= (int) $product['units_sold'] ?> sold, <?= (int) $product['stock'] ?> in stock</span>
                                    </div>
                                    <strong><?= money((float) $product['revenue']) ?></strong>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
                <div class="col-12">
                    <div class="card shadow-sm">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h2 class="h4 mb-0">Recent order history</h2>
                                <a class="btn btn-outline-success btn-sm" href="<?= url('vendor/orders.php') ?>">View all</a>
                            </div>
                            <div class="table-responsive">
                                <table class="table align-middle">
                                    <thead><tr><th scope="col">Order</th><th scope="col">Customer</th><th scope="col">Your revenue</th><th scope="col">Status</th><th scope="col">Date</th></tr></thead>
                                    <tbody>
                                        <?php foreach ($orders as $order): ?>
                                            <tr>
                                                <th scope="row">#<?= (int) $order['id'] ?></th>
                                                <td><?= e($order['customer_name']) ?></td>
                                                <td><?= money((float) $order['seller_total']) ?></td>
                                                <td><span class="badge text-bg-secondary"><?= e($order['status']) ?></span></td>
                                                <td><?= e(date('M j, Y', strtotime($order['created_at']))) ?></td>
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
const vendorSalesData = <?= json_encode($salesChart, JSON_THROW_ON_ERROR) ?>;
const vendorSalesCanvas = document.getElementById('vendorSalesChart');
if (vendorSalesCanvas) {
    new Chart(vendorSalesCanvas, {
        type: 'bar',
        data: {
            labels: vendorSalesData.labels,
            datasets: [{
                label: 'Revenue',
                data: vendorSalesData.values,
                backgroundColor: 'rgba(25, 135, 84, 0.75)'
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
