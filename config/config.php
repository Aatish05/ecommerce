<?php
declare(strict_types=1);

ini_set('display_errors', '0');
error_reporting(E_ALL);

$secureCookie = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/',
    'domain' => '',
    'secure' => $secureCookie,
    'httponly' => true,
    'samesite' => 'Lax',
]);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

define('APP_NAME', 'DGShop Electronics');
define('APP_BASE_URL', getenv('APP_BASE_URL') ?: '');
define('DB_HOST', getenv('DB_HOST') ?: '127.0.0.1');
define('DB_NAME', getenv('DB_NAME') ?: 'ecotech_ecommerce');
define('DB_USER', getenv('DB_USER') ?: 'root');
define('DB_PASS', getenv('DB_PASS') ?: '');

function db(): PDO
{
    static $pdo = null;

    if ($pdo instanceof PDO) {
        return $pdo;
    }

    try {
        $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4';
        $pdo = new PDO($dsn, DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);
    } catch (PDOException $exception) {
        render_database_setup_error($exception);
    }

    return $pdo;
}

function render_database_setup_error(PDOException $exception): void
{
    http_response_code(503);
    $message = htmlspecialchars($exception->getMessage(), ENT_QUOTES, 'UTF-8');
    echo '<!doctype html><html lang="en"><head><meta charset="utf-8">';
    echo '<meta name="viewport" content="width=device-width, initial-scale=1">';
    echo '<title>Database setup required | ' . APP_NAME . '</title>';
    echo '<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">';
    echo '</head><body class="bg-light"><main class="container py-5">';
    echo '<div class="card shadow-sm border-warning"><div class="card-body p-4">';
    echo '<h1 class="h3 text-warning">Database setup required</h1>';
    echo '<p>The website code is running, but it cannot connect to MySQL yet.</p>';
    echo '<ol>';
    echo '<li>Open XAMPP and start <strong>Apache</strong> and <strong>MySQL</strong>.</li>';
    echo '<li>Open <code>http://localhost/phpmyadmin</code>.</li>';
    echo '<li>Import <code>database/schema.sql</code> from this project.</li>';
    echo '<li>Check <code>config/config.php</code> if your MySQL user/password is not <code>root</code> with an empty password.</li>';
    echo '</ol>';
    echo '<p class="small text-muted mb-0"><strong>Technical detail:</strong> ' . $message . '</p>';
    echo '</div></div></main></body></html>';
    exit;
}

function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}

function verify_csrf(?string $token): bool
{
    return is_string($token)
        && isset($_SESSION['csrf_token'])
        && hash_equals($_SESSION['csrf_token'], $token);
}
