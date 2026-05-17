<?php $adminActive = $adminActive ?? ''; ?>
<aside class="admin-sidebar card shadow-sm mb-4">
    <div class="card-body">
        <h2 class="h5">Admin menu</h2>
        <nav aria-label="Admin navigation">
            <a class="<?= $adminActive === 'dashboard' ? 'active' : '' ?>" href="<?= url('admin/dashboard.php') ?>"><i class="bi bi-speedometer2" aria-hidden="true"></i> Dashboard</a>
            <a class="<?= $adminActive === 'products' ? 'active' : '' ?>" href="<?= url('admin/products.php') ?>"><i class="bi bi-box-seam" aria-hidden="true"></i> Products</a>
            <a class="<?= $adminActive === 'orders' ? 'active' : '' ?>" href="<?= url('admin/orders.php') ?>"><i class="bi bi-receipt" aria-hidden="true"></i> Orders</a>
            <a class="<?= $adminActive === 'reviews' ? 'active' : '' ?>" href="<?= url('admin/reviews.php') ?>"><i class="bi bi-star" aria-hidden="true"></i> Reviews</a>
            <a class="<?= $adminActive === 'messages' ? 'active' : '' ?>" href="<?= url('admin/messages.php') ?>"><i class="bi bi-envelope" aria-hidden="true"></i> Messages</a>
            <a class="<?= $adminActive === 'users' ? 'active' : '' ?>" href="<?= url('admin/users.php') ?>"><i class="bi bi-people" aria-hidden="true"></i> Users</a>
        </nav>
    </div>
</aside>
