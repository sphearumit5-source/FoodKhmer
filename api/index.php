<?php
// api/index.php - Front controller for Vercel Serverless PHP

$uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
$path = trim($uri, '/');

// Base directory (project root)
$rootDir = dirname(__DIR__);

// Default to index.php if root
if ($path === '' || $path === 'index.php') {
    chdir($rootDir);
    require $rootDir . '/index.php';
    exit;
}

// Handle admin root load
if ($path === 'admin' || $path === 'admin/') {
    chdir($rootDir . '/admin');
    require $rootDir . '/admin/dashboard.php';
    exit;
}

$target = $rootDir . '/' . $path;

// 1. Direct file match
if (is_file($target)) {
    if (pathinfo($target, PATHINFO_EXTENSION) === 'php') {
        chdir(dirname($target));
        require $target;
        exit;
    }

    // Static asset fallback
    $mimeTypes = [
        'css'   => 'text/css',
        'js'    => 'application/javascript',
        'png'   => 'image/png',
        'jpg'   => 'image/jpeg',
        'jpeg'  => 'image/jpeg',
        'gif'   => 'image/gif',
        'svg'   => 'image/svg+xml',
        'webp'  => 'image/webp',
        'ico'   => 'image/x-icon',
        'woff'  => 'font/woff',
        'woff2' => 'font/woff2',
        'ttf'   => 'font/ttf',
    ];
    $ext = strtolower(pathinfo($target, PATHINFO_EXTENSION));
    $mime = $mimeTypes[$ext] ?? 'application/octet-stream';
    header("Content-Type: $mime");
    readfile($target);
    exit;
}

// 2. Try with .php appended (e.g. /product -> /product.php)
if (is_file($target . '.php')) {
    chdir(dirname($target . '.php'));
    require $target . '.php';
    exit;
}

// 3. Directory index fallback
if (is_dir($target)) {
    if (is_file($target . '/index.php')) {
        chdir($target);
        require $target . '/index.php';
        exit;
    }
    if (is_file($target . '/dashboard.php')) {
        chdir($target);
        require $target . '/dashboard.php';
        exit;
    }
    if (is_file($target . '/login.php')) {
        chdir($target);
        require $target . '/login.php';
        exit;
    }
}

// 4. 404 Not Found
http_response_code(404);
echo "404 Not Found";
