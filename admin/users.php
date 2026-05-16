<?php
require_once __DIR__ . '/../includes/functions.php';
require_admin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf($_POST['csrf_token'] ?? null)) {
        set_flash('danger', 'Security check failed. Please try again.');
    } else {
        $userId = (int) ($_POST['user_id'] ?? 0);
        $role = $_POST['role'] === 'admin' ? 'admin' : 'user';
        if ($userId === (int) current_user()['id'] && $role !== 'admin') {
            set_flash('warning', 'You cannot remove your own admin role.');
        } else {
            $stmt = db()->prepare('UPDATE users SET role = ? WHERE id = ?');
            $stmt->execute([$role, $userId]);
            set_flash('success', 'User role updated.');
        }
    }
    redirect('admin/users.php');
}

$users = db()->query(
    'SELECT u.*, COUNT(o.id) AS order_count
     FROM users u
     LEFT JOIN orders o ON o.user_id = u.id
     GROUP BY u.id
     ORDER BY u.created_at DESC'
)->fetchAll();

$pageTitle = 'Manage Users';
$active = 'admin';
$adminActive = 'users';
include __DIR__ . '/../includes/header.php';
?>
<section class="container">
    <div class="row g-4">
        <div class="col-lg-3"><?php include __DIR__ . '/_sidebar.php'; ?></div>
        <div class="col-lg-9">
            <h1>Manage users</h1>
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <caption>User accounts and roles</caption>
                    <thead><tr><th scope="col">User</th><th scope="col">Email</th><th scope="col">Orders</th><th scope="col">Role</th></tr></thead>
                    <tbody>
                        <?php foreach ($users as $user): ?>
                            <tr>
                                <th scope="row"><?= e($user['name']) ?><br><span class="small text-body-secondary">@<?= e($user['username']) ?></span></th>
                                <td><?= e($user['email']) ?></td>
                                <td><?= (int) $user['order_count'] ?></td>
                                <td>
                                    <form class="d-flex gap-2" method="post">
                                        <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                                        <input type="hidden" name="user_id" value="<?= (int) $user['id'] ?>">
                                        <label class="visually-hidden" for="role-<?= (int) $user['id'] ?>">Role for <?= e($user['name']) ?></label>
                                        <select class="form-select form-select-sm" id="role-<?= (int) $user['id'] ?>" name="role">
                                            <option value="user" <?= $user['role'] === 'user' ? 'selected' : '' ?>>User</option>
                                            <option value="admin" <?= $user['role'] === 'admin' ? 'selected' : '' ?>>Admin</option>
                                        </select>
                                        <button class="btn btn-sm btn-outline-success" type="submit">Save</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</section>
<?php include __DIR__ . '/../includes/footer.php'; ?>
