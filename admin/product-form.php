<?php
require_once __DIR__ . '/../includes/functions.php';
require_admin();

$id = (int) ($_GET['id'] ?? 0);
$editing = $id > 0;
$product = $editing ? product_by_id($id, true) : null;
if ($editing && !$product) {
    set_flash('warning', 'Product not found.');
    redirect('admin/products.php');
}

$errors = [];
$values = $product ?: [
    'name' => '',
    'brand' => '',
    'description' => '',
    'category_id' => '',
    'price' => '',
    'stock' => '',
    'image_url' => 'assets/img/laptop.svg',
    'is_active' => 1,
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf($_POST['csrf_token'] ?? null)) {
        $errors['form'] = 'Security check failed. Please try again.';
    }

    $values = [
        'name' => trim($_POST['name'] ?? ''),
        'brand' => trim($_POST['brand'] ?? ''),
        'description' => trim($_POST['description'] ?? ''),
        'category_id' => (int) ($_POST['category_id'] ?? 0),
        'price' => trim($_POST['price'] ?? ''),
        'stock' => trim($_POST['stock'] ?? ''),
        'image_url' => trim($_POST['image_url'] ?? ''),
        'is_active' => isset($_POST['is_active']) ? 1 : 0,
    ];

    if ($values['name'] === '') {
        $errors['name'] = 'Please enter a product name.';
    }
    if ($values['brand'] === '') {
        $errors['brand'] = 'Please enter a brand.';
    }
    if (strlen($values['description']) < 20) {
        $errors['description'] = 'Description must be at least 20 characters.';
    }
    if ($values['category_id'] <= 0) {
        $errors['category_id'] = 'Please choose a category.';
    }
    if (!is_numeric($values['price']) || (float) $values['price'] <= 0) {
        $errors['price'] = 'Please enter a valid price greater than zero.';
    }
    if (!ctype_digit((string) $values['stock'])) {
        $errors['stock'] = 'Please enter a whole number for stock.';
    }
    if ($values['image_url'] === '' || !preg_match('/^(assets\/img\/|https?:\/\/).+/', $values['image_url'])) {
        $errors['image_url'] = 'Use a local assets/img path or a valid image URL.';
    }

    if (!$errors) {
        if ($editing) {
            $stmt = db()->prepare(
                'UPDATE products
                 SET category_id = ?, name = ?, brand = ?, description = ?, price = ?, stock = ?, image_url = ?, is_active = ?
                 WHERE id = ?'
            );
            $stmt->execute([$values['category_id'], $values['name'], $values['brand'], $values['description'], $values['price'], $values['stock'], $values['image_url'], $values['is_active'], $id]);
            set_flash('success', 'Product updated.');
        } else {
            $stmt = db()->prepare(
                'INSERT INTO products (category_id, name, brand, description, price, stock, image_url, is_active)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?)'
            );
            $stmt->execute([$values['category_id'], $values['name'], $values['brand'], $values['description'], $values['price'], $values['stock'], $values['image_url'], $values['is_active']]);
            set_flash('success', 'Product created.');
        }
        redirect('admin/products.php');
    }
}

$pageTitle = $editing ? 'Edit Product' : 'Add Product';
$active = 'admin';
$adminActive = 'products';
$categories = categories();
include __DIR__ . '/../includes/header.php';
?>
<section class="container">
    <div class="row g-4">
        <div class="col-lg-3"><?php include __DIR__ . '/_sidebar.php'; ?></div>
        <div class="col-lg-9">
            <h1><?= $editing ? 'Edit product' : 'Add product' ?></h1>
            <?php if (isset($errors['form'])): ?><div class="alert alert-danger"><?= e($errors['form']) ?></div><?php endif; ?>
            <form class="card card-body shadow-sm needs-validation" method="post" novalidate>
                <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label form-required" for="name">Product name</label>
                        <input class="form-control <?= isset($errors['name']) ? 'is-invalid' : '' ?>" id="name" name="name" value="<?= e((string) $values['name']) ?>" required>
                        <div class="invalid-feedback"><?= e($errors['name'] ?? 'Please enter a product name.') ?></div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label form-required" for="brand">Brand</label>
                        <input class="form-control <?= isset($errors['brand']) ? 'is-invalid' : '' ?>" id="brand" name="brand" value="<?= e((string) $values['brand']) ?>" required>
                        <div class="invalid-feedback"><?= e($errors['brand'] ?? 'Please enter a brand.') ?></div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label form-required" for="category_id">Category</label>
                        <select class="form-select <?= isset($errors['category_id']) ? 'is-invalid' : '' ?>" id="category_id" name="category_id" required>
                            <option value="">Choose category</option>
                            <?php foreach ($categories as $category): ?>
                                <option value="<?= (int) $category['id'] ?>" <?= (string) $values['category_id'] === (string) $category['id'] ? 'selected' : '' ?>><?= e($category['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <div class="invalid-feedback"><?= e($errors['category_id'] ?? 'Please choose a category.') ?></div>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label form-required" for="price">Price</label>
                        <input class="form-control <?= isset($errors['price']) ? 'is-invalid' : '' ?>" type="number" step="0.01" min="0.01" id="price" name="price" value="<?= e((string) $values['price']) ?>" required>
                        <div class="invalid-feedback"><?= e($errors['price'] ?? 'Please enter a valid price.') ?></div>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label form-required" for="stock">Stock</label>
                        <input class="form-control <?= isset($errors['stock']) ? 'is-invalid' : '' ?>" type="number" min="0" id="stock" name="stock" value="<?= e((string) $values['stock']) ?>" required>
                        <div class="invalid-feedback"><?= e($errors['stock'] ?? 'Please enter a whole number.') ?></div>
                    </div>
                    <div class="col-12">
                        <label class="form-label form-required" for="image_url">Image path or URL</label>
                        <input class="form-control <?= isset($errors['image_url']) ? 'is-invalid' : '' ?>" id="image_url" name="image_url" value="<?= e((string) $values['image_url']) ?>" required>
                        <div class="invalid-feedback"><?= e($errors['image_url'] ?? 'Use assets/img/example.svg or a valid URL.') ?></div>
                    </div>
                    <div class="col-12">
                        <label class="form-label form-required" for="description">Description</label>
                        <textarea class="form-control <?= isset($errors['description']) ? 'is-invalid' : '' ?>" id="description" name="description" rows="5" minlength="20" required><?= e((string) $values['description']) ?></textarea>
                        <div class="invalid-feedback"><?= e($errors['description'] ?? 'Description must be at least 20 characters.') ?></div>
                    </div>
                    <div class="col-12">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" role="switch" id="is_active" name="is_active" <?= (int) $values['is_active'] ? 'checked' : '' ?>>
                            <label class="form-check-label" for="is_active">Visible in shop</label>
                        </div>
                    </div>
                </div>
                <div class="d-flex gap-2 mt-4">
                    <button class="btn btn-success" type="submit">Save product</button>
                    <a class="btn btn-outline-secondary" href="<?= url('admin/products.php') ?>">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</section>
<?php include __DIR__ . '/../includes/footer.php'; ?>
