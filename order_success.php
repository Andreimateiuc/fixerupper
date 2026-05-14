<?php
declare(strict_types=1);

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/functions.php';

require_login();

$orderId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$orderId) {
    redirect('index.php');
}

// SECURITY: Prepared statements prevent SQL Injection and customer_id limits
// users to their own orders.
$stmt = $pdo->prepare('SELECT id, created_at, status FROM orders WHERE id = :id AND customer_id = :customer_id LIMIT 1');
$stmt->execute([
    ':id' => $orderId,
    ':customer_id' => (int) current_user()['id'],
]);
$order = $stmt->fetch();

if (!$order) {
    http_response_code(404);
    exit('Order not found.');
}

$itemStmt = $pdo->prepare(
    'SELECT p.name, oi.quantity, oi.price
     FROM order_items oi
     JOIN products p ON p.id = oi.product_id
     WHERE oi.order_id = :order_id'
);
$itemStmt->execute([':order_id' => $orderId]);
$items = $itemStmt->fetchAll();

$pageTitle = 'Order Success';
require __DIR__ . '/header.php';
?>
<section class="notice">
    <h1>Order Confirmed</h1>
    <p>Order #<?= (int) $order['id'] ?> was placed successfully.</p>
    <p>Status: <?= e($order['status']) ?> | Created: <?= e($order['created_at']) ?></p>
</section>

<div class="table-wrap">
    <table>
        <thead>
            <tr>
                <th>Product</th>
                <th>Quantity</th>
                <th>Price Paid</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($items as $item): ?>
                <tr>
                    <td><?= e($item['name']) ?></td>
                    <td><?= (int) $item['quantity'] ?></td>
                    <td>&pound;<?= e(number_format((float) $item['price'], 2)) ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<a class="button-link" href="index.php">Continue shopping</a>
<?php require __DIR__ . '/footer.php'; ?>
