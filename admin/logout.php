<?php
// admin/logout.php
require_once __DIR__ . '/../config/database.php';

$_SESSION = [];

if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// លុប Serverless Auth Cookie
setcookie('admin_auth', '', [
    'expires'  => time() - 86400,
    'path'     => '/',
    'httponly' => true,
    'samesite' => 'Lax'
]);

session_destroy();
header('Location: login.php');
exit;
