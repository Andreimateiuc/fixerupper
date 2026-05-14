<?php
declare(strict_types=1);

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/functions.php';

$items = get_cart_items($pdo);
if (!$items) {
    redirect('cart.php');
}

if (!current_user()) {
    $pageTitle = 'Checkout Login Required';
    require __DIR__ . '/header.php';
    ?>
    <section class="notice">
        <h1>Checkout</h1>
        <p>You need to login or register before confirming this order.</p>
        <div class="actions">
            <a class="button-link" href="login.php?redirect=checkout.php">Login</a>
            <a class="button-link secondary-link" href="register.php?redirect=checkout.php">Register</a>
        </div>
    </section>
    <?php
    require __DIR__ . '/footer.php';
    exit;
}

$pageTitle = 'Confirm Order';
require __DIR__ . '/header.php';
?>
<h1>Confirm Order</h1>
<div class="table-wrap">
    <table>
        <thead>
            <tr>
                <th>Product</th>
                <th>Quantity</th>
                <th>Price</th>
                <th>Subtotal</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($items as $item): ?>
                <?php $product = $item['product']; ?>
                <tr>
                    <td><?= e($product['name']) ?></td>
                    <td><?= (int) $item['quantity'] ?></td>
                    <td>&pound;<?= e(number_format((float) $product['price'], 2)) ?></td>
                    <td>&pound;<?= e(number_format((float) $item['subtotal'], 2)) ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<div class="cart-summary">
    <strong>Total: &pound;<?= e(number_format(cart_total($items), 2)) ?></strong>
    <form method="post" action="confirm_order.php">
        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
        <button type="submit">Confirm Order</button>
    </form>
</div>
<?php require __DIR__ . '/footer.php'; ?>
