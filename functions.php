<?php
declare(strict_types=1);

// Shared security and utility functions.

const SESSION_TIMEOUT_SECONDS = 1800;

function is_https_request(): bool
{
    return (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] === '443');
}

// Local development fallback: this XAMPP setup may not allow PHP to write to
// C:\xampp\tmp. The included router.php blocks direct web access to /runtime.
// In production, configure the session path outside the public web root.
$sessionPath = __DIR__ . '/runtime/sessions';
if (!is_dir($sessionPath)) {
    mkdir($sessionPath, 0700, true);
}
session_save_path($sessionPath);

// SECURITY: Configure session cookies before session_start().
// HttpOnly prevents JavaScript reading the cookie during XSS attacks.
// SameSite=Strict reduces CSRF risk by not sending cookies on cross-site requests.
// Secure is enabled whenever the site is served over HTTPS. Use HTTPS in production.
session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/',
    'domain' => '',
    'secure' => is_https_request(),
    'httponly' => true,
    'samesite' => 'Strict',
]);

session_start();

// SECURITY: Basic session timeout. Old sessions are destroyed after inactivity.
if (isset($_SESSION['last_activity']) && time() - (int) $_SESSION['last_activity'] > SESSION_TIMEOUT_SECONDS) {
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
    }
    session_destroy();
    session_start();
}
$_SESSION['last_activity'] = time();

function e(?string $value): string
{
    // SECURITY: Escape all dynamic output to prevent Cross-Site Scripting (XSS).
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function redirect(string $path): never
{
    header("Location: {$path}");
    exit;
}

function current_user(): ?array
{
    return $_SESSION['user'] ?? null;
}

function require_login(): void
{
    if (!current_user()) {
        redirect('login.php?redirect=checkout.php');
    }
}

function csrf_token(): string
{
    // SECURITY: CSRF token for state-changing POST requests.
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verify_csrf_token(): void
{
    $token = $_POST['csrf_token'] ?? '';
    if (!is_string($token) || !hash_equals(csrf_token(), $token)) {
        http_response_code(400);
        exit('Invalid security token.');
    }
}

function validate_int(string $key, int $default = 0): int
{
    // SECURITY: Server-side input validation. Never trust browser-submitted values.
    $value = filter_input(INPUT_POST, $key, FILTER_VALIDATE_INT);
    return $value === false || $value === null ? $default : (int) $value;
}

function cart_count(): int
{
    $count = 0;
    foreach ($_SESSION['cart'] ?? [] as $quantity) {
        $count += (int) $quantity;
    }
    return $count;
}

function get_cart_items(PDO $pdo): array
{
    $cart = $_SESSION['cart'] ?? [];
    if (!$cart) {
        return [];
    }

    $ids = array_map('intval', array_keys($cart));
    $placeholders = implode(',', array_fill(0, count($ids), '?'));

    // SECURITY: Product IDs are still passed through a prepared statement.
    $stmt = $pdo->prepare("SELECT id, name, description, price, image_url, stock FROM products WHERE id IN ({$placeholders})");
    $stmt->execute($ids);
    $products = $stmt->fetchAll();

    $items = [];
    foreach ($products as $product) {
        $quantity = max(1, (int) ($cart[$product['id']] ?? 1));
        $quantity = min($quantity, (int) $product['stock']);
        $items[] = [
            'product' => $product,
            'quantity' => $quantity,
            'subtotal' => $quantity * (float) $product['price'],
        ];
    }

    return $items;
}

function cart_total(array $items): float
{
    $total = 0.0;
    foreach ($items as $item) {
        $total += (float) $item['subtotal'];
    }
    return $total;
}
