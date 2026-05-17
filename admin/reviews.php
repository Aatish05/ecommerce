<?php
require_once __DIR__ . '/../includes/functions.php';
require_admin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf($_POST['csrf_token'] ?? null)) {
        set_flash('danger', 'Security check failed. Please try again.');
    } else {
        $reviewId = (int) ($_POST['review_id'] ?? 0);
        $action = $_POST['action'] ?? '';
        if ($action === 'toggle') {
            $stmt = db()->prepare('UPDATE reviews SET is_approved = IF(is_approved = 1, 0, 1) WHERE id = ?');
            $stmt->execute([$reviewId]);
            set_flash('success', 'Review visibility updated.');
        } elseif ($action === 'delete') {
            $stmt = db()->prepare('DELETE FROM reviews WHERE id = ?');
            $stmt->execute([$reviewId]);
            set_flash('success', 'Review deleted.');
        }
    }
    redirect('admin/reviews.php');
}

$reviews = db()->query(
    'SELECT r.*, p.name AS product_name, u.name AS reviewer_name, seller.name AS seller_name
     FROM reviews r
     JOIN products p ON p.id = r.product_id
     JOIN users u ON u.id = r.user_id
     JOIN users seller ON seller.id = p.seller_id
     ORDER BY r.created_at DESC'
)->fetchAll();

$pageTitle = 'Manage Reviews';
$active = 'admin';
$adminActive = 'reviews';
include __DIR__ . '/../includes/header.php';
?>
<section class="container">
    <div class="row g-4">
        <div class="col-lg-3"><?php include __DIR__ . '/_sidebar.php'; ?></div>
        <div class="col-lg-9">
            <h1>Product reviews</h1>
            <p class="text-body-secondary">Moderate customer feedback and monitor product reputation.</p>
            <div class="row g-3">
                <?php foreach ($reviews as $review): ?>
                    <div class="col-12">
                        <article class="card shadow-sm">
                            <div class="card-body">
                                <div class="d-flex flex-column flex-md-row justify-content-between gap-3">
                                    <div>
                                        <div class="text-warning"><?= (int) $review['rating'] ?>/5 stars</div>
                                        <h2 class="h5 mb-1"><?= e($review['title']) ?></h2>
                                        <p class="small text-body-secondary mb-2">
                                            <?= e($review['product_name']) ?> by <?= e($review['seller_name']) ?> -
                                            reviewed by <?= e($review['reviewer_name']) ?> on <?= e(date('M j, Y', strtotime($review['created_at']))) ?>
                                        </p>
                                    </div>
                                    <span class="badge text-bg-<?= (int) $review['is_approved'] ? 'success' : 'secondary' ?> align-self-md-start"><?= (int) $review['is_approved'] ? 'Approved' : 'Hidden' ?></span>
                                </div>
                                <p><?= e($review['body']) ?></p>
                                <div class="d-flex flex-wrap gap-2">
                                    <form method="post">
                                        <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                                        <input type="hidden" name="review_id" value="<?= (int) $review['id'] ?>">
                                        <input type="hidden" name="action" value="toggle">
                                        <button class="btn btn-outline-success btn-sm" type="submit"><?= (int) $review['is_approved'] ? 'Hide' : 'Approve' ?></button>
                                    </form>
                                    <form method="post">
                                        <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                                        <input type="hidden" name="review_id" value="<?= (int) $review['id'] ?>">
                                        <input type="hidden" name="action" value="delete">
                                        <button class="btn btn-outline-danger btn-sm" type="submit" data-confirm="Delete this review?">Delete</button>
                                    </form>
                                </div>
                            </div>
                        </article>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</section>
<?php include __DIR__ . '/../includes/footer.php'; ?>
