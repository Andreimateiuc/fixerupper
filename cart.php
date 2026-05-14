<?php
declare(strict_types=1);

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf_token();
    $action = $_POST['action'] ?? '';
    $productId = validate_int('product_id');

    if ($productId > 0 && isset($_SESSION['cart'][$productId])) {
        if ($action === 'remove') {
            unset($_SESSION['cart'][$productId]);
        }

        if ($action === 'update') {
            $quantity = max(1, validate_int('quantity', 1));
            $stmt = $pdo->prepare('SELECT stock FROM products WHERE id = :id LIMIT 1');
            $stmt->execute([':id' => $productId]);
            $product = $stmt->fetch();

            if ($product) {
                $_SESSION['cart'][$productId] = min($quantity, (int) $product['stock']);
            }
        }
    }

    redirect('cart.php');
}

$items = get_cart_items($pdo);
$total = cart_total($items);

$pageTitle = 'Shopping Cart';
require __DIR__ . '/header.php';
?>
<h1>Shopping Cart</h1>

<?php if (!$items): ?>
    <div class="notice">
        <p>Your cart is empty.</p>
        <a class="button-link" href="index.php">Browse products</a>
    </div>
<?php else: ?>
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Product</th>
                    <th>Price</th>
                    <th>Quantity</th>
                    <th>Subtotal</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($items as $item): ?>
                    <?php $product = $item['product']; ?>
                    <tr>
                        <td><?= e($product['name']) ?></td>
                        <td>&pound;<?= e(number_format((float) $product['price'], 2)) ?></td>
                        <td>
                            <form method="post" class="quantity-form">
                                <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                                <input type="hidden" name="product_id" value="<?= (int) $product['id'] ?>">
                                <input type="hidden" name="action" value="update">
                                <input type="number" name="quantity" min="1" max="<?= (int) $product['stock'] ?>" value="<?= (int) $item['quantity'] ?>">
                                <button type="submit">Update</button>
                            </form>
                        </td>
                        <td>&pound;<?= e(number_format((float) $item['subtotal'], 2)) ?></td>
                        <td>
                            <form method="post">
                                <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                                <input type="hidden" name="product_id" value="<?= (int) $product['id'] ?>">
                                <input type="hidden" name="action" value="remove">
                                <button class="secondary" type="submit">Remove</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <div class="cart-summary">
        <strong>Total: &pound;<?= e(number_format($total, 2)) ?></strong>
        <a class="button-link" href="checkout.php">Proceed to Checkout</a>
    </div>
<?php endif; ?>
<?php require __DIR__ . '/footer.php'; ?>
