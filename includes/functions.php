<?php
// includes/functions.php
require_once __DIR__ . '/../config/database.php';

/**
 * បំប្លែងអក្សរដើម្បីការពារ XSS
 */
function e($value) {
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

/**
 * បង្កើត CSRF Token (Serverless & Session Compatible)
 */
function getCsrfToken() {
    if (empty($_SESSION['csrf_token'])) {
        if (!empty($_COOKIE['csrf_token'])) {
            $_SESSION['csrf_token'] = $_COOKIE['csrf_token'];
        } else {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
            if (!headers_sent()) {
                @setcookie('csrf_token', $_SESSION['csrf_token'], [
                    'expires'  => time() + 86400,
                    'path'     => '/',
                    'httponly' => true,
                    'samesite' => 'Lax'
                ]);
            }
        }
    }
    return $_SESSION['csrf_token'];
}

/**
 * ផ្ទៀងផ្ទាត់ CSRF Token (ផ្ទៀងផ្ទាត់ទាំង Session និង Cookie)
 */
function verifyCsrfToken($token) {
    if (empty($token)) {
        return false;
    }
    $sessionToken = $_SESSION['csrf_token'] ?? '';
    $cookieToken = $_COOKIE['csrf_token'] ?? '';
    if (!empty($sessionToken) && hash_equals($sessionToken, $token)) {
        return true;
    }
    if (!empty($cookieToken) && hash_equals($cookieToken, $token)) {
        return true;
    }
    return false;
}

/**
 * បង្ហាញតម្លៃជាលុយខ្មែរ (KHR)
 * ឧទាហរណ៍៖ 5000 = 5,000 ៛
 */
function formatPrice($price) {
    return number_format((float)$price, 0, '.', ',') . ' ៛';
}

/**
 * ពិនិត្យថា Admin បាន Login ឬនៅ (គាំទ្រទាំង Session និង Secure Signed Cookie លើ Serverless)
 */
function isAdminLoggedIn() {
    if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
        return true;
    }

    if (!empty($_COOKIE['admin_auth']) && defined('AUTH_SECRET')) {
        $parts = explode('.', $_COOKIE['admin_auth'], 2);
        if (count($parts) === 2) {
            list($payload, $sig) = $parts;
            if (hash_equals(hash_hmac('sha256', $payload, AUTH_SECRET), $sig)) {
                $data = json_decode(base64_decode($payload), true);
                if ($data && isset($data['exp']) && $data['exp'] > time()) {
                    $_SESSION['admin_logged_in'] = true;
                    $_SESSION['admin_id'] = $data['id'];
                    $_SESSION['admin_username'] = $data['user'];
                    $_SESSION['admin_name'] = $data['name'];
                    return true;
                }
            }
        }
    }

    return false;
}

/**
 * ការពារទំព័រ Admin
 */
function requireAdminAuth() {
    if (!isAdminLoggedIn()) {
        header('Location: login.php');
        exit;
    }
}

/**
 * Upload រូបភាពផលិតផល
 */
function handleImageUpload($file, $targetDir = '../uploads/') {

    if (!isset($file) || $file['error'] !== UPLOAD_ERR_OK) {
        return [
            'success' => false,
            'error' => 'សូមជ្រើសរើសរូបភាពត្រឹមត្រូវ'
        ];
    }

    // កំណត់ទំហំរូបភាពអតិបរមា 2MB
    $maxSize = 2 * 1024 * 1024;

    if ($file['size'] > $maxSize) {
        return [
            'success' => false,
            'error' => 'រូបភាពធំពេក (អនុញ្ញាតត្រឹមតែក្រោម 2MB)'
        ];
    }

    // ប្រភេទរូបភាពដែលអនុញ្ញាត
    $allowedExt = ['jpg', 'jpeg', 'png', 'webp'];

    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

    if (!in_array($ext, $allowedExt, true)) {
        return [
            'success' => false,
            'error' => 'អនុញ្ញាតតែប្រភេទរូបភាព JPG, PNG, WEBP ប៉ុណ្ណោះ'
        ];
    }

    // ពិនិត្យ MIME Type
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file($file['tmp_name']);

    $allowedMimes = [
        'image/jpeg',
        'image/png',
        'image/webp'
    ];

    if (!in_array($mime, $allowedMimes, true)) {
        return [
            'success' => false,
            'error' => 'File នេះមិនមែនជារូបភាពពិតប្រាកដទេ'
        ];
    }

    // បង្កើត Folder ប្រសិនបើមិនទាន់មាន
    if (!is_dir($targetDir)) {
        if (!mkdir($targetDir, 0755, true) && !is_dir($targetDir)) {
            return [
                'success' => false,
                'error' => 'មិនអាចបង្កើត Folder សម្រាប់រូបភាពបានទេ'
            ];
        }
    }

    // បង្កើតឈ្មោះ File ថ្មី
    $newFileName = 'prod_' . bin2hex(random_bytes(8)) . '.' . $ext;

    $destination = rtrim($targetDir, '/\\') . DIRECTORY_SEPARATOR . $newFileName;

    if (move_uploaded_file($file['tmp_name'], $destination)) {
        return [
            'success' => true,
            'filename' => $newFileName
        ];
    }

    return [
        'success' => false,
        'error' => 'បរាជ័យក្នុងការរក្សាទុករូបភាព'
    ];
}
