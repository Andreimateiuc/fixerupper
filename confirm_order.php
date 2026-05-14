<?php
declare(strict_types=1);

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/functions.php';

require_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('checkout.php');
}

verify_csrf_token();
$items = get_cart_items($pdo);

if (!$items) {
    redirect('cart.php');
}

try {
    $pdo->beginTransaction();

    $orderStmt = $pdo->prepare('INSERT INTO orders (customer_id, status) VALUES (:customer_id, :status)');
    $orderStmt->execute([
        ':customer_id' => (int) current_user()['id'],
        ':status' => 'confirmed',
    ]);
    $orderId = (int) $pdo->lastInsertId();

    $itemStmt = $pdo->prepare('INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (:order_id, :product_id, :quantity, :price)');
    $stockStmt = $pdo->prepare('UPDATE products SET stock = stock - :quantity_to_subtract WHERE id = :product_id AND stock >= :quantity_required');

    foreach ($items as $item) {
        $product = $item['product'];
        $quantity = (int) $item['quantity'];

        $stockStmt->execute([
            ':quantity_to_subtract' => $quantity,
            ':quantity_required' => $quantity,
            ':product_id' => (int) $product['id'],
        ]);

        if ($stockStmt->rowCount() !== 1) {
            throw new RuntimeException('One or more items are no longer available in the requested quantity.');
        }

        $itemStmt->execute([
            ':order_id' => $orderId,
            ':product_id' => (int) $product['id'],
            ':quantity' => $quantity,
            ':price' => (float) $product['price'],
        ]);
    }

    $pdo->commit();
    unset($_SESSION['cart']);
    redirect('order_success.php?id=' . $orderId);
} catch (Throwable $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    http_response_code(400);
    $pageTitle = 'Order Error';
    require __DIR__ . '/header.php';
    ?>
    <section class="notice">
        <h1>Order could not be completed</h1>
        <p><?= e($e->getMessage()) ?></p>
        <a class="button-link" href="cart.php">Return to cart</a>
    </section>
    <?php require __DIR__ . '/footer.php'; ?>
    <?php
}
