<?php
declare(strict_types=1);

require_once __DIR__ . '/auth.php';

$error = '';
$redirect = isset($_GET['redirect']) && $_GET['redirect'] === 'checkout.php' ? 'checkout.php' : 'index.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf_token();
    $redirect = isset($_POST['redirect']) && $_POST['redirect'] === 'checkout.php' ? 'checkout.php' : 'index.php';
    [$ok, $message] = login_customer($pdo, (string) ($_POST['email'] ?? ''), (string) ($_POST['password'] ?? ''));

    if ($ok) {
        redirect($redirect);
    }
    $error = $message;
}

$pageTitle = 'Login';
require __DIR__ . '/header.php';
?>
<section class="auth-panel">
    <h1>Login</h1>
    <?php if ($error): ?>
        <p class="error"><?= e($error) ?></p>
    <?php endif; ?>
    <form method="post" class="stacked-form">
        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
        <input type="hidden" name="redirect" value="<?= e($redirect) ?>">
        <label for="email">Email</label>
        <input id="email" type="email" name="email" required maxlength="120">

        <label for="password">Password</label>
        <input id="password" type="password" name="password" required minlength="8">

        <button type="submit">Login</button>
    </form>
    <p>Need an account? <a href="register.php?redirect=<?= e($redirect) ?>">Register</a></p>
</section>
<?php require __DIR__ . '/footer.php'; ?>
