<?php
declare(strict_types=1);

require_once __DIR__ . '/auth.php';

$error = '';
$redirect = isset($_GET['redirect']) && $_GET['redirect'] === 'checkout.php' ? 'checkout.php' : 'index.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf_token();
    $redirect = isset($_POST['redirect']) && $_POST['redirect'] === 'checkout.php' ? 'checkout.php' : 'index.php';
    [$ok, $message] = register_customer(
        $pdo,
        (string) ($_POST['username'] ?? ''),
        (string) ($_POST['email'] ?? ''),
        (string) ($_POST['password'] ?? ''),
        (string) ($_POST['confirm_password'] ?? '')
    );

    if ($ok) {
        redirect($redirect);
    }
    $error = $message;
}

$pageTitle = 'Register';
require __DIR__ . '/header.php';
?>
<section class="auth-panel">
    <h1>Register</h1>
    <?php if ($error): ?>
        <p class="error"><?= e($error) ?></p>
    <?php endif; ?>
    <form method="post" class="stacked-form">
        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
        <input type="hidden" name="redirect" value="<?= e($redirect) ?>">

        <label for="username">Username</label>
        <input id="username" type="text" name="username" required minlength="3" maxlength="50" pattern="[A-Za-z0-9_]+">

        <label for="email">Email</label>
        <input id="email" type="email" name="email" required maxlength="120">

        <label for="password">Password</label>
        <input id="password" type="password" name="password" required minlength="8">

        <label for="confirm_password">Confirm Password</label>
        <input id="confirm_password" type="password" name="confirm_password" required minlength="8">

        <button type="submit">Create Account</button>
    </form>
    <p>Already registered? <a href="login.php?redirect=<?= e($redirect) ?>">Login</a></p>
</section>
<?php require __DIR__ . '/footer.php'; ?>
