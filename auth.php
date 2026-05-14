<?php
declare(strict_types=1);

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/functions.php';

function register_customer(PDO $pdo, string $username, string $email, string $password, string $confirmPassword): array
{
    // SECURITY: Validate and sanitise inputs server-side before storing or querying.
    $username = trim($username);
    $email = trim($email);

    if (!preg_match('/^[A-Za-z0-9_]{3,50}$/', $username)) {
        return [false, 'Username must be 3-50 characters and use only letters, numbers, or underscores.'];
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return [false, 'Enter a valid email address.'];
    }

    if (strlen($password) < 8) {
        return [false, 'Password must be at least 8 characters.'];
    }

    if ($password !== $confirmPassword) {
        return [false, 'Passwords do not match.'];
    }

    // SECURITY: bcrypt password hashing. Plain-text passwords are never stored.
    $passwordHash = password_hash($password, PASSWORD_BCRYPT);

    try {
        // SECURITY: Prepared statement prevents SQL Injection.
        $stmt = $pdo->prepare('INSERT INTO customers (username, email, password_hash) VALUES (:username, :email, :password_hash)');
        $stmt->execute([
            ':username' => $username,
            ':email' => $email,
            ':password_hash' => $passwordHash,
        ]);
    } catch (PDOException $e) {
        return [false, 'Username or email is already registered.'];
    }

    login_customer_by_id($pdo, (int) $pdo->lastInsertId());
    return [true, 'Registration successful.'];
}

function login_customer(PDO $pdo, string $email, string $password): array
{
    $email = trim($email);

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return [false, 'Enter a valid email address.'];
    }

    // SECURITY: Prepared statement prevents SQL Injection.
    $stmt = $pdo->prepare('SELECT id, username, email, password_hash FROM customers WHERE email = :email LIMIT 1');
    $stmt->execute([':email' => $email]);
    $customer = $stmt->fetch();

    // SECURITY: password_verify checks submitted password against bcrypt hash.
    if (!$customer || !password_verify($password, $customer['password_hash'])) {
        return [false, 'Invalid email or password.'];
    }

    login_customer_by_id($pdo, (int) $customer['id']);
    return [true, 'Login successful.'];
}

function login_customer_by_id(PDO $pdo, int $customerId): void
{
    // SECURITY: Regenerate the session ID after authentication to prevent
    // session fixation/hijacking attacks.
    session_regenerate_id(true);

    $stmt = $pdo->prepare('SELECT id, username, email FROM customers WHERE id = :id LIMIT 1');
    $stmt->execute([':id' => $customerId]);
    $customer = $stmt->fetch();

    $_SESSION['user'] = [
        'id' => (int) $customer['id'],
        'username' => $customer['username'],
        'email' => $customer['email'],
    ];
    $_SESSION['last_activity'] = time();
}
