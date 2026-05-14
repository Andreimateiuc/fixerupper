<?php
declare(strict_types=1);

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf_token();
    $productId = validate_int('product_id');
    $quantity = max(1, validate_int('quantity', 1));

    // SECURITY: Prepared statement prevents SQL Injection and verifies product exists.
    $stmt = $pdo->prepare('SELECT id, stock FROM products WHERE id = :id LIMIT 1');
    $stmt->execute([':id' => $productId]);
    $product = $stmt->fetch();

    if ($product && (int) $product['stock'] > 0) {
        $_SESSION['cart'][$productId] = min(
            (int) $product['stock'],
            (int) ($_SESSION['cart'][$productId] ?? 0) + $quantity
        );
    }

    redirect('cart.php');
}

$stmt = $pdo->prepare('SELECT id, name, description, price, image_url, stock FROM products ORDER BY id ASC');
$stmt->execute();
$products = $stmt->fetchAll();

$pageTitle = 'FixerUpper Products';
require __DIR__ . '/header.php';
?>
<section class="hero">
    <div>
        <p class="eyebrow">Hardware appliances</p>
        <h1>FixerUpper</h1>
        <p>Workshop-ready tools and appliances for repairs, renovation, and storage.</p>
    </div>
</section>

<section class="product-grid" aria-label="Products">
    <?php foreach ($products as $product): ?>
        <article class="product-card">
            <img src="<?= e($product['image_url']) ?>" alt="<?= e($product['name']) ?>">
            <div class="product-body">
                <h2><?= e($product['name']) ?></h2>
                <p><?= e($product['description']) ?></p>
                <div class="product-meta">
                    <strong>&pound;<?= e(number_format((float) $product['price'], 2)) ?></strong>
                    <span><?= (int) $product['stock'] ?> in stock</span>
                </div>
                <form method="post" class="inline-form">
                    <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                    <input type="hidden" name="product_id" value="<?= (int) $product['id'] ?>">
                    <input type="hidden" name="quantity" value="1">
                    <button type="submit" <?= (int) $product['stock'] < 1 ? 'disabled' : '' ?>>Add to Cart</button>
                </form>
            </div>
        </article>
    <?php endforeach; ?>
</section>
<?php require __DIR__ . '/footer.php'; ?>
