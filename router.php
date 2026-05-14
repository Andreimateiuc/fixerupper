<?php
declare(strict_types=1);

// Development router for PHP's built-in server.
// SECURITY: Runtime files such as session storage must not be served publicly.
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?: '/';
if (str_starts_with($path, '/runtime/')) {
    http_response_code(403);
    echo 'Forbidden';
    return true;
}

$file = __DIR__ . $path;
if ($path !== '/' && is_file($file)) {
    return false;
}

require __DIR__ . '/index.php';
