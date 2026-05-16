<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/config.php';

function e(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function url(string $path = ''): string
{
    return rtrim(APP_BASE_URL, '/') . '/' . ltrim($path, '/');
}

function redirect(string $path): never
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

function register_user(string $name, string $username, string $email, string $password): array
{
    $errors = [];
    $name = trim($name);
    $username = trim($username);
    $email = strtolower(trim($email));

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

    if ($errors) {
        return [false, $errors];
    }

    $stmt = db()->prepare(
        'INSERT INTO users (name, username, email, password_hash, role) VALUES (?, ?, ?, ?, "user")'
    );
    $stmt->execute([$name, $username, $email, password_hash($password, PASSWORD_DEFAULT)]);

    return [true, []];
}

function categories(): array
{
    return db()->query('SELECT * FROM categories ORDER BY name')->fetchAll();
}

function featured_products(int $limit = 6): array
{
    $stmt = db()->prepare(
        'SELECT p.*, c.name AS category_name
         FROM products p
         JOIN categories c ON c.id = p.category_id
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
    $sql = 'SELECT p.*, c.name AS category_name
            FROM products p
            JOIN categories c ON c.id = p.category_id
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
    $sql = 'SELECT p.*, c.name AS category_name
            FROM products p
            JOIN categories c ON c.id = p.category_id
            WHERE p.id = ?';
    if (!$includeInactive) {
        $sql .= ' AND p.is_active = 1';
    }

    $stmt = db()->prepare($sql);
    $stmt->execute([$id]);
    $product = $stmt->fetch();
    return $product ?: null;
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
    return array_reduce(cart_items(), fn (float $sum, array $item): float => $sum + (float) $item['line_total'], 0.0);
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
        $total = array_reduce($items, fn (float $sum, array $item): float => $sum + (float) $item['line_total'], 0.0);
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
        $stockStmt = $pdo->prepare('UPDATE products SET stock = GREATEST(stock - ?, 0) WHERE id = ?');

        foreach ($items as $item) {
            $itemStmt->execute([$orderId, $item['id'], $item['quantity'], $item['price']]);
            $stockStmt->execute([$item['quantity'], $item['id']]);
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
    $stmt->execute([trim($name), strtolower(trim($email)), trim($subject), trim($message)]);
    return [true, []];
}
