
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
 * បង្កើត CSRF Token
 */
function getCsrfToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}

/**
 * ផ្ទៀងផ្ទាត់ CSRF Token
 */
function verifyCsrfToken($token) {
    if (empty($_SESSION['csrf_token']) || empty($token)) {
        return false;
    }

    return hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * បង្ហាញតម្លៃជាលុយខ្មែរ (KHR)
 * ឧទាហរណ៍៖ 5000 = 5,000 ៛
 */
function formatPrice($price) {
    return number_format((float)$price, 0, '.', ',') . ' ៛';
}

/**
 * ពិនិត្យថា Admin បាន Login ឬនៅ
 */
function isAdminLoggedIn() {
    return isset($_SESSION['admin_logged_in'])
        && $_SESSION['admin_logged_in'] === true;
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
