<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/config.php';

function e(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function url(string $path = ''): string
{
    if (APP_BASE_URL !== '') {
        return rtrim(APP_BASE_URL, '/') . '/' . ltrim($path, '/');
    }

    $documentRoot = realpath($_SERVER['DOCUMENT_ROOT'] ?? '') ?: '';
    $projectRoot = realpath(dirname(__DIR__)) ?: '';
    $basePath = '';

    if ($documentRoot !== '' && substr($projectRoot, 0, strlen($documentRoot)) === $documentRoot) {
        $basePath = str_replace('\\', '/', substr($projectRoot, strlen($documentRoot)));
    }

    return rtrim($basePath, '/') . '/' . ltrim($path, '/');
}

function redirect(string $path): void
{
    header('Location: ' . url($path));
    exit;
}

function set_flash(string $type, string $message): void
{
    $_SESSION['flash'][] = ['type' => $type, 'message' => $message];
}

function flash_messages(): array
{
    $messages = $_SESSION['flash'] ?? [];
    unset($_SESSION['flash']);
    return $messages;
}

function current_user(): ?array
{
    return $_SESSION['user'] ?? null;
}

function is_logged_in(): bool
{
    return current_user() !== null;
}

function is_admin(): bool
{
    return (current_user()['role'] ?? '') === 'admin';
}

function is_vendor(): bool
{
    return (current_user()['role'] ?? '') === 'vendor';
}

function can_sell_products(): bool
{
    return is_admin() || is_vendor();
}

function require_login(): void
{
    if (!is_logged_in()) {
        set_flash('warning', 'Please log in to access that page.');
        redirect('login.php');
    }
}

function require_admin(): void
{
    require_login();
    if (!is_admin()) {
        set_flash('danger', 'You do not have permission to access the admin area.');
        redirect('index.php');
    }
}

function require_vendor(): void
{
    require_login();
    if (!is_vendor()) {
        set_flash('danger', 'You do not have permission to access the vendor area.');
        redirect('index.php');
    }
}

function validate_email(string $email): bool
{
    return (bool) filter_var($email, FILTER_VALIDATE_EMAIL);
}

function password_rules_message(string $password): ?string
{
    if (strlen($password) < 8) {
        return 'Password must be at least 8 characters long.';
    }
    if (!preg_match('/[A-Za-z]/', $password) || !preg_match('/[0-9]/', $password)) {
        return 'Password must include at least one letter and one number.';
    }
    return null;
}

function find_user_by_email(string $email): ?array
{
    $stmt = db()->prepare('SELECT * FROM users WHERE email = ? LIMIT 1');
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    return $user ?: null;
}

function find_user_by_username(string $username): ?array
{
    $stmt = db()->prepare('SELECT * FROM users WHERE username = ? LIMIT 1');
    $stmt->execute([$username]);
    $user = $stmt->fetch();
    return $user ?: null;
}

function upload_image(array $file, string $folder): array
{
    if (empty($file['name'])) {
        return [true, null, null];
    }

    if ($file['error'] !== UPLOAD_ERR_OK) {
        return [false, null, 'Image upload failed. Please try again.'];
    }

    $allowedTypes = ['image/jpeg', 'image/png', 'image/webp'];
    $maxSize = 2 * 1024 * 1024;

    if ($file['size'] > $maxSize) {
        return [false, null, 'Image must be smaller than 2MB.'];
    }

    $imageInfo = getimagesize($file['tmp_name']);
    $mimeType = $imageInfo['mime'] ?? '';

    if (!in_array($mimeType, $allowedTypes, true)) {
        return [false, null, 'Only JPG, PNG, or WEBP images are allowed.'];
    }

    if ($mimeType === 'image/jpeg') {
        $extension = 'jpg';
    } elseif ($mimeType === 'image/png') {
        $extension = 'png';
    } elseif ($mimeType === 'image/webp') {
        $extension = 'webp';
    } else {
        $extension = 'jpg';
    }

    $fileName = uniqid('img_', true) . '.' . $extension;
    $uploadDir = __DIR__ . '/../uploads/' . $folder . '/';

    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    $targetPath = $uploadDir . $fileName;
    $publicPath = 'uploads/' . $folder . '/' . $fileName;

    if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
        return [false, null, 'Could not save uploaded image.'];
    }

    return [true, $publicPath, null];
}

function register_user(string $name, string $username, string $email, string $password, string $role, array $profileImage = []): array
{
    $errors = [];
    $name = trim($name);
    $username = trim($username);
    $email = strtolower(trim($email));
    $role = trim($role);
    $profileImagePath = null;

    if ($name === '') {
        $errors['name'] = 'Please enter your full name.';
    }

    if (!preg_match('/^[A-Za-z0-9_]{3,30}$/', $username)) {
        $errors['username'] = 'Username must be 3-30 characters and use only letters, numbers, or underscores.';
    } elseif (find_user_by_username($username)) {
        $errors['username'] = 'That username is already taken.';
    }

    if (!validate_email($email)) {
        $errors['email'] = 'Please enter a valid email address.';
    } elseif (find_user_by_email($email)) {
        $errors['email'] = 'An account with this email already exists.';
    }

    $passwordMessage = password_rules_message($password);
    if ($passwordMessage !== null) {
        $errors['password'] = $passwordMessage;
    }

    if (!in_array($role, ['user', 'vendor'], true)) {
        $errors['role'] = 'Please choose whether you are registering as a customer or vendor.';
    }

    if (!empty($profileImage['name'])) {
        [$uploaded, $profileImagePath, $uploadError] = upload_image($profileImage, 'users');

        if (!$uploaded) {
            $errors['profile_image'] = $uploadError;
        }
    }

    if ($errors) {
        return [false, $errors];
    }

    $stmt = db()->prepare(
        'INSERT INTO users (name, username, email, password_hash, role, profile_image)
         VALUES (?, ?, ?, ?, ?, ?)'
    );

    $stmt->execute([
        $name,
        $username,
        $email,
        password_hash($password, PASSWORD_DEFAULT),
        $role,
        $profileImagePath
    ]);

    return [true, []];
}

function categories(): array
{
    return db()->query('SELECT * FROM categories ORDER BY name')->fetchAll();
}

function sellers(): array
{
    return db()->query('SELECT id, name, email FROM users WHERE role IN ("admin", "vendor") ORDER BY name')->fetchAll();
}

function featured_products(int $limit = 6): array
{
    $stmt = db()->prepare(
        'SELECT p.*, c.name AS category_name, u.name AS seller_name
         FROM products p
         JOIN categories c ON c.id = p.category_id
         JOIN users u ON u.id = p.seller_id
         WHERE p.is_active = 1
         ORDER BY p.created_at DESC
         LIMIT ?'
    );
    $stmt->bindValue(1, $limit, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll();
}

function products(array $filters = []): array
{
    $sql = 'SELECT p.*, c.name AS category_name, u.name AS seller_name
            FROM products p
            JOIN categories c ON c.id = p.category_id
            JOIN users u ON u.id = p.seller_id
            WHERE p.is_active = 1';
    $params = [];

    if (!empty($filters['q'])) {
        $sql .= ' AND (p.name LIKE ? OR p.description LIKE ? OR p.brand LIKE ?)';
        $term = '%' . $filters['q'] . '%';
        array_push($params, $term, $term, $term);
    }

    if (!empty($filters['category_id'])) {
        $sql .= ' AND p.category_id = ?';
        $params[] = (int) $filters['category_id'];
    }

    if (!empty($filters['max_price'])) {
        $sql .= ' AND p.price <= ?';
        $params[] = (float) $filters['max_price'];
    }

    $sorts = [
        'price_asc' => 'p.price ASC',
        'price_desc' => 'p.price DESC',
        'newest' => 'p.created_at DESC',
        'name' => 'p.name ASC',
    ];

    $sql .= ' ORDER BY ' . ($sorts[$filters['sort'] ?? 'newest'] ?? $sorts['newest']);

    $stmt = db()->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

function product_by_id(int $id, bool $includeInactive = false): ?array
{
    $sql = 'SELECT p.*, c.name AS category_name, u.name AS seller_name
            FROM products p
            JOIN categories c ON c.id = p.category_id
            JOIN users u ON u.id = p.seller_id
            WHERE p.id = ?';

    if (!$includeInactive) {
        $sql .= ' AND p.is_active = 1';
    }

    $stmt = db()->prepare($sql);
    $stmt->execute([$id]);
    $product = $stmt->fetch();

    return $product ?: null;
}

function product_reviews(int $productId, bool $approvedOnly = true): array
{
    $sql = 'SELECT r.*, u.name AS reviewer_name
            FROM reviews r
            JOIN users u ON u.id = r.user_id
            WHERE r.product_id = ?';

    if ($approvedOnly) {
        $sql .= ' AND r.is_approved = 1';
    }

    $sql .= ' ORDER BY r.created_at DESC';

    $stmt = db()->prepare($sql);
    $stmt->execute([$productId]);

    return $stmt->fetchAll();
}

function review_summary(int $productId): array
{
    $stmt = db()->prepare('SELECT COUNT(*) AS review_count, COALESCE(AVG(rating), 0) AS average_rating FROM reviews WHERE product_id = ? AND is_approved = 1');
    $stmt->execute([$productId]);
    $summary = $stmt->fetch() ?: ['review_count' => 0, 'average_rating' => 0];

    return [
        'review_count' => (int) $summary['review_count'],
        'average_rating' => (float) $summary['average_rating'],
    ];
}

function save_review(int $productId, int $userId, int $rating, string $title, string $body): array
{
    $errors = [];

    if ($rating < 1 || $rating > 5) {
        $errors['rating'] = 'Please choose a rating from 1 to 5.';
    }

    if (trim($title) === '') {
        $errors['title'] = 'Please enter a short review title.';
    }

    if (strlen(trim($body)) < 10) {
        $errors['body'] = 'Review must be at least 10 characters.';
    }

    if (!product_by_id($productId)) {
        $errors['product'] = 'Product not found.';
    }

    if ($errors) {
        return [false, $errors];
    }

    $stmt = db()->prepare(
        'INSERT INTO reviews (product_id, user_id, rating, title, body, is_approved)
         VALUES (?, ?, ?, ?, ?, 1)'
    );

    $stmt->execute([$productId, $userId, $rating, trim($title), trim($body)]);

    return [true, []];
}

function sales_chart_data(?int $sellerId = null): array
{
    if ($sellerId !== null) {
        $stmt = db()->prepare(
            'SELECT DATE(o.created_at) AS sale_date, COALESCE(SUM(oi.quantity * oi.unit_price), 0) AS revenue
             FROM orders o
             JOIN order_items oi ON oi.order_id = o.id
             JOIN products p ON p.id = oi.product_id
             WHERE p.seller_id = ?
             GROUP BY DATE(o.created_at)
             ORDER BY sale_date ASC
             LIMIT 14'
        );

        $stmt->execute([$sellerId]);
    } else {
        $stmt = db()->query(
            'SELECT DATE(created_at) AS sale_date, COALESCE(SUM(total_amount), 0) AS revenue
             FROM orders
             GROUP BY DATE(created_at)
             ORDER BY sale_date ASC
             LIMIT 14'
        );
    }

    $rows = $stmt->fetchAll();

    return [
        'labels' => array_map(function (array $row): string {
            return date('M j', strtotime($row['sale_date']));
        }, $rows),
        'values' => array_map(function (array $row): float {
            return (float) $row['revenue'];
        }, $rows),
    ];
}

function vendor_revenue(int $sellerId): float
{
    $stmt = db()->prepare(
        'SELECT COALESCE(SUM(oi.quantity * oi.unit_price), 0)
         FROM order_items oi
         JOIN products p ON p.id = oi.product_id
         WHERE p.seller_id = ?'
    );

    $stmt->execute([$sellerId]);

    return (float) $stmt->fetchColumn();
}

function cart(): array
{
    return $_SESSION['cart'] ?? [];
}

function cart_count(): int
{
    return array_sum(array_map('intval', cart()));
}

function cart_items(): array
{
    $items = [];

    foreach (cart() as $productId => $quantity) {
        $product = product_by_id((int) $productId);

        if ($product) {
            $product['quantity'] = (int) $quantity;
            $product['line_total'] = (float) $product['price'] * (int) $quantity;
            $items[] = $product;
        }
    }

    return $items;
}

function cart_total(): float
{
    return array_reduce(cart_items(), function (float $sum, array $item): float {
        return $sum + (float) $item['line_total'];
    }, 0.0);
}

function money(float $amount): string
{
    return '$' . number_format($amount, 2);
}

function create_order(int $userId, array $items, string $shippingName, string $shippingAddress, string $notes = ''): int
{
    $pdo = db();
    $pdo->beginTransaction();

    try {
        $total = array_reduce($items, function (float $sum, array $item): float {
            return $sum + (float) $item['line_total'];
        }, 0.0);

        $stmt = $pdo->prepare(
            'INSERT INTO orders (user_id, total_amount, shipping_name, shipping_address, notes, status)
             VALUES (?, ?, ?, ?, ?, "pending")'
        );

        $stmt->execute([$userId, $total, $shippingName, $shippingAddress, $notes]);

        $orderId = (int) $pdo->lastInsertId();

        $itemStmt = $pdo->prepare(
            'INSERT INTO order_items (order_id, product_id, quantity, unit_price)
             VALUES (?, ?, ?, ?)'
        );

        $stockStmt = $pdo->prepare('UPDATE products SET stock = CASE WHEN stock >= ? THEN stock - ? ELSE 0 END WHERE id = ?');

        foreach ($items as $item) {
            $itemStmt->execute([$orderId, $item['id'], $item['quantity'], $item['price']]);
            $stockStmt->execute([$item['quantity'], $item['quantity'], $item['id']]);
        }

        $pdo->commit();

        return $orderId;
    } catch (Throwable $exception) {
        $pdo->rollBack();
        throw $exception;
    }
}

function save_contact_message(string $name, string $email, string $subject, string $message): array
{
    $errors = [];

    if (trim($name) === '') {
        $errors['name'] = 'Please enter your name.';
    }

    if (!validate_email($email)) {
        $errors['email'] = 'Please enter a valid email address.';
    }

    if (trim($subject) === '') {
        $errors['subject'] = 'Please enter a subject.';
    }

    if (strlen(trim($message)) < 10) {
        $errors['message'] = 'Message must be at least 10 characters.';
    }

    if ($errors) {
        return [false, $errors];
    }

    $stmt = db()->prepare('INSERT INTO contact_messages (name, email, subject, message) VALUES (?, ?, ?, ?)');

    $stmt->execute([
        trim($name),
        strtolower(trim($email)),
        trim($subject),
        trim($message)
    ]);

    return [true, []];
}