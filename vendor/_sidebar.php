<?php $vendorActive = $vendorActive ?? ''; ?>
<aside class="admin-sidebar card shadow-sm mb-4">
    <div class="card-body">
        <h2 class="h5">Vendor menu</h2>
        <nav aria-label="Vendor navigation">
            <a class="<?= $vendorActive === 'dashboard' ? 'active' : '' ?>" href="<?= url('vendor/dashboard.php') ?>"><i class="bi bi-speedometer2" aria-hidden="true"></i> Dashboard</a>
            <a class="<?= $vendorActive === 'products' ? 'active' : '' ?>" href="<?= url('vendor/products.php') ?>"><i class="bi bi-box-seam" aria-hidden="true"></i> My products</a>
            <a class="<?= $vendorActive === 'orders' ? 'active' : '' ?>" href="<?= url('vendor/orders.php') ?>"><i class="bi bi-receipt" aria-hidden="true"></i> Product orders</a>
        </nav>
    </div>
</aside>
