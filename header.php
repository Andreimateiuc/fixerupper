<?php
require_once __DIR__ . '/functions.php';
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($pageTitle ?? 'FixerUpper') ?></title>
    <link rel="stylesheet" href="assets/css/styles.css">
</head>
<body>
<header class="site-header">
    <a class="brand" href="index.php">FixerUpper</a>
    <nav class="nav">
        <a href="index.php">Products</a>
        <a href="cart.php">Cart (<?= cart_count() ?>)</a>
        <?php if (current_user()): ?>
            <span class="welcome">Hi, <?= e(current_user()['username']) ?></span>
            <a href="logout.php">Logout</a>
        <?php else: ?>
            <a href="login.php">Login</a>
            <a href="register.php">Register</a>
        <?php endif; ?>
    </nav>
</header>
<main class="container">
