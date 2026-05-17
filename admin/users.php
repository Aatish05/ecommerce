<?php
require_once __DIR__ . '/../includes/functions.php';
require_admin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf($_POST['csrf_token'] ?? null)) {
        set_flash('danger', 'Security check failed. Please try again.');
        redirect('admin/users.php');
    }

    $userId = (int) ($_POST['user_id'] ?? 0);
    $allowedRoles = ['admin', 'vendor', 'user'];
    $role = in_array($_POST['role'] ?? '', $allowedRoles, true) ? $_POST['role'] : 'user';
    $isBanned = isset($_POST['is_banned']) ? 1 : 0;

    if ($userId === (int) current_user()['id'] && $role !== 'admin') {
        set_flash('warning', 'You cannot remove your own admin role.');
        redirect('admin/users.php');
    }

    if ($userId === (int) current_user()['id'] && $isBanned === 1) {
        set_flash('warning', 'You cannot ban your own account.');
        redirect('admin/users.php');
    }

    $stmt = db()->prepare(
        'UPDATE users
         SET role = ?, is_banned = ?
         WHERE id = ?'
    );

    $stmt->execute([$role, $isBanned, $userId]);

    set_flash('success', 'User role and ban status updated.');
    redirect('admin/users.php');
}

$users = db()->query(
    'SELECT u.*, COALESCE(order_counts.order_count, 0) AS order_count
     FROM users u
     LEFT JOIN (
        SELECT user_id, COUNT(*) AS order_count
        FROM orders
        GROUP BY user_id
     ) order_counts ON order_counts.user_id = u.id
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
            <p class="text-body-secondary">
                Admins can update user roles and ban fake or suspicious customer/vendor accounts.
            </p>

            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <caption>User accounts, roles, and ban status</caption>

                    <thead>
                        <tr>
                            <th scope="col">User</th>
                            <th scope="col">Profile</th>
                            <th scope="col">Email</th>
                            <th scope="col">Orders</th>
                            <th scope="col">Role / Ban</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php foreach ($users as $user): ?>
                            <tr>
                                <th scope="row">
                                    <?= e($user['name']) ?><br>
                                    <span class="small text-body-secondary">
                                        @<?= e($user['username']) ?>
                                    </span>

                                    <?php if ((int) ($user['is_banned'] ?? 0) === 1): ?>
                                        <br>
                                        <span class="badge text-bg-danger mt-1">Banned</span>
                                    <?php endif; ?>
                                </th>

                                <td>
                                    <?php if (!empty($user['profile_image'])): ?>
                                        <img
                                            src="<?= e(url($user['profile_image'])) ?>"
                                            alt="<?= e($user['name']) ?> profile image"
                                            width="56"
                                            height="56"
                                            class="rounded-circle border"
                                            style="object-fit: cover;"
                                        >
                                    <?php else: ?>
                                        <div
                                            class="rounded-circle bg-secondary text-white d-flex align-items-center justify-content-center"
                                            style="width:56px;height:56px;"
                                        >
                                            N/A
                                        </div>
                                    <?php endif; ?>
                                </td>

                                <td><?= e($user['email']) ?></td>

                                <td><?= (int) $user['order_count'] ?></td>

                                <td>
                                    <form class="row g-2 align-items-center" method="post">
                                        <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                                        <input type="hidden" name="user_id" value="<?= (int) $user['id'] ?>">

                                        <div class="col-md-6">
                                            <label class="visually-hidden" for="role-<?= (int) $user['id'] ?>">
                                                Role for <?= e($user['name']) ?>
                                            </label>

                                            <select class="form-select form-select-sm" id="role-<?= (int) $user['id'] ?>" name="role">
                                                <option value="user" <?= $user['role'] === 'user' ? 'selected' : '' ?>>
                                                    User
                                                </option>
                                                <option value="vendor" <?= $user['role'] === 'vendor' ? 'selected' : '' ?>>
                                                    Vendor
                                                </option>
                                                <option value="admin" <?= $user['role'] === 'admin' ? 'selected' : '' ?>>
                                                    Admin
                                                </option>
                                            </select>
                                        </div>

                                        <div class="col-md-3">
                                            <div class="form-check">
                                                <input
                                                    class="form-check-input"
                                                    type="checkbox"
                                                    id="ban-<?= (int) $user['id'] ?>"
                                                    name="is_banned"
                                                    <?= (int) ($user['is_banned'] ?? 0) === 1 ? 'checked' : '' ?>
                                                    <?= (int) $user['id'] === (int) current_user()['id'] ? 'disabled' : '' ?>
                                                >
                                                <label class="form-check-label small" for="ban-<?= (int) $user['id'] ?>">
                                                    Ban
                                                </label>
                                            </div>
                                        </div>

                                        <div class="col-md-3">
                                            <button class="btn btn-sm btn-outline-success" type="submit">
                                                Save
                                            </button>
                                        </div>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>

                        <?php if (!$users): ?>
                            <tr>
                                <td colspan="5" class="text-center text-body-secondary">
                                    No users found.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</section>
<?php include __DIR__ . '/../includes/footer.php'; ?>