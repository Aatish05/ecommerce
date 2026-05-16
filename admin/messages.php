<?php
require_once __DIR__ . '/../includes/functions.php';
require_admin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf($_POST['csrf_token'] ?? null)) {
        set_flash('danger', 'Security check failed. Please try again.');
    } else {
        $stmt = db()->prepare('UPDATE contact_messages SET is_read = 1 WHERE id = ?');
        $stmt->execute([(int) ($_POST['message_id'] ?? 0)]);
        set_flash('success', 'Message marked as read.');
    }
    redirect('admin/messages.php');
}

$messages = db()->query('SELECT * FROM contact_messages ORDER BY created_at DESC')->fetchAll();
$pageTitle = 'Contact Messages';
$active = 'admin';
$adminActive = 'messages';
include __DIR__ . '/../includes/header.php';
?>
<section class="container">
    <div class="row g-4">
        <div class="col-lg-3"><?php include __DIR__ . '/_sidebar.php'; ?></div>
        <div class="col-lg-9">
            <h1>Contact messages</h1>
            <?php if (!$messages): ?>
                <div class="alert alert-info">No messages yet.</div>
            <?php endif; ?>
            <div class="row g-3">
                <?php foreach ($messages as $message): ?>
                    <div class="col-12">
                        <article class="card shadow-sm <?= (int) $message['is_read'] ? '' : 'border-success' ?>">
                            <div class="card-body">
                                <div class="d-flex justify-content-between gap-3">
                                    <div>
                                        <h2 class="h5 mb-1"><?= e($message['subject']) ?></h2>
                                        <p class="small text-body-secondary mb-2"><?= e($message['name']) ?> &lt;<?= e($message['email']) ?>&gt; - <?= e(date('M j, Y H:i', strtotime($message['created_at']))) ?></p>
                                    </div>
                                    <span class="badge text-bg-<?= (int) $message['is_read'] ? 'secondary' : 'success' ?> align-self-start"><?= (int) $message['is_read'] ? 'Read' : 'New' ?></span>
                                </div>
                                <p><?= nl2br(e($message['message'])) ?></p>
                                <?php if (!(int) $message['is_read']): ?>
                                    <form method="post">
                                        <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                                        <input type="hidden" name="message_id" value="<?= (int) $message['id'] ?>">
                                        <button class="btn btn-outline-success btn-sm" type="submit">Mark as read</button>
                                    </form>
                                <?php endif; ?>
                            </div>
                        </article>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</section>
<?php include __DIR__ . '/../includes/footer.php'; ?>
