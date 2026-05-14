<?php
declare(strict_types=1);

// Central database connection file.
// SECURITY: PDO is configured to throw exceptions and use real prepared statements.
// Every SQL query in this project uses parameterised prepared statements to defend
// against SQL Injection.

$dbHost = '127.0.0.1';
$dbName = 'fixerupper';
$dbUser = 'root';
$dbPass = '';

$dsn = "mysql:host={$dbHost};dbname={$dbName};charset=utf8mb4";

try {
    $pdo = new PDO($dsn, $dbUser, $dbPass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
} catch (PDOException $e) {
    // Do not expose database credentials or detailed SQL errors to users.
    http_response_code(500);
    exit('Database connection failed. Check db.php and your MySQL server.');
}
