<?php
require_once __DIR__ . '/includes/functions.php';
$pageTitle = 'Shop Electronics';
$metaDescription = 'Search and filter electronics by category, price, and product name.';
$active = 'products';
$filters = [
    'q' => trim($_GET['q'] ?? ''),
    'category_id' => $_GET['category_id'] ?? '',
    'max_price' => $_GET['max_price'] ?? '',
    'sort' => $_GET['sort'] ?? 'newest',
];
$products = products($filters);
$categories = categories();
include __DIR__ . '/includes/header.php';
?>
<section class="container">
    <div class="d-flex flex-column flex-lg-row justify-content-between gap-3 align-items-lg-end mb-4">
        <div>
            <h1>Shop electronics</h1>
            <p class="text-body-secondary mb-0">Use search and filters to find database-driven product pages.</p>
        </div>
        <span class="badge text-bg-success fs-6"><?= count($products) ?> result<?= count($products) === 1 ? '' : 's' ?></span>
    </div>

    <form class="card card-body shadow-sm mb-4 needs-validation" method="get" novalidate>
        <div class="row g-3 align-items-end">
            <div class="col-md-4">
                <label class="form-label" for="q">Search products</label>
                <input class="form-control" type="search" name="q" id="q" value="<?= e($filters['q']) ?>" placeholder="Laptop, phone, headphones">
            </div>
            <div class="col-md-3">
                <label class="form-label" for="category_id">Category</label>
                <select class="form-select" name="category_id" id="category_id">
                    <option value="">All categories</option>
                    <?php foreach ($categories as $category): ?>
                        <option value="<?= (int) $category['id'] ?>" <?= (string) $filters['category_id'] === (string) $category['id'] ? 'selected' : '' ?>>
                            <?= e($category['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label" for="max_price">Max price</label>
                <input class="form-control" type="number" min="0" step="0.01" name="max_price" id="max_price" value="<?= e((string) $filters['max_price']) ?>">
            </div>
            <div class="col-md-2">
                <label class="form-label" for="sort">Sort by</label>
                <select class="form-select" name="sort" id="sort">
                    <option value="newest" <?= $filters['sort'] === 'newest' ? 'selected' : '' ?>>Newest</option>
                    <option value="price_asc" <?= $filters['sort'] === 'price_asc' ? 'selected' : '' ?>>Price low-high</option>
                    <option value="price_desc" <?= $filters['sort'] === 'price_desc' ? 'selected' : '' ?>>Price high-low</option>
                    <option value="name" <?= $filters['sort'] === 'name' ? 'selected' : '' ?>>Name</option>
                </select>
            </div>
            <div class="col-md-1 d-grid">
                <button class="btn btn-success" type="submit">Filter</button>
            </div>
        </div>
    </form>

    <?php if (!$products): ?>
        <div class="alert alert-info">No products match your filters. Try a different search term or category.</div>
    <?php endif; ?>

    <div class="row g-4">
        <?php foreach ($products as $product): ?>
            <div class="col-sm-6 col-lg-4">
                <?php include __DIR__ . '/includes/product-card.php'; ?>
            </div>
        <?php endforeach; ?>
    </div>
</section>
<?php include __DIR__ . '/includes/footer.php'; ?>
