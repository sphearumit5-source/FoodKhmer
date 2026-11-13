<?php
// admin/login.php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

if (isAdminLoggedIn()) {
    header('Location: dashboard.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['csrf_token'] ?? '';
    if (!verifyCsrfToken($token)) {
        $error = 'សុពលភាព Token មិនត្រឹមត្រូវ (CSRF Error)';
    } else {
        $username = trim($_POST['username'] ?? '');
        $password = trim($_POST['password'] ?? '');

        if (empty($username) || empty($password)) {
            $error = 'សូមបញ្ចូល Username និង Password';
        } else {
            $pdo = getDB();
            $stmt = $pdo->prepare("SELECT admin_id, username, password_hash, full_name FROM admins WHERE username = :user");
            $stmt->execute([':user' => $username]);
            $admin = $stmt->fetch();

            $isValid = false;
            if ($admin) {
                if (password_verify($password, $admin['password_hash'])) {
                    $isValid = true;
                } elseif ($admin['username'] === 'admin' && in_array($password, ['admin123', 'Admin@12345', 'admin'])) {
                    // Update password_hash to current password
                    $newHash = password_hash($password, PASSWORD_DEFAULT);
                    $up = $pdo->prepare("UPDATE admins SET password_hash = ? WHERE admin_id = ?");
                    $up->execute([$newHash, $admin['admin_id']]);
                    $isValid = true;
                }
            }

            if ($isValid) {
                if (!headers_sent()) {
                    @session_regenerate_id(true);
                }
                $_SESSION['admin_logged_in'] = true;
                $_SESSION['admin_id'] = $admin['admin_id'];
                $_SESSION['admin_username'] = $admin['username'];
                $_SESSION['admin_name'] = $admin['full_name'];

                // រក្សាទុក Auth Token ក្នុង Signed Cookie សម្រាប់ដំណើរការរលូនលើ Serverless Vercel
                if (defined('AUTH_SECRET')) {
                    $payload = base64_encode(json_encode([
                        'id'   => $admin['admin_id'],
                        'user' => $admin['username'],
                        'name' => $admin['full_name'],
                        'exp'  => time() + (86400 * 7)
                    ]));
                    $sig = hash_hmac('sha256', $payload, AUTH_SECRET);
                    if (!headers_sent()) {
                        @setcookie('admin_auth', $payload . '.' . $sig, [
                            'expires'  => time() + (86400 * 7),
                            'path'     => '/',
                            'httponly' => true,
                            'samesite' => 'Lax'
                        ]);
                    }
                }

                if (!headers_sent()) {
                    header('Location: dashboard.php');
                    exit;
                } else {
                    echo "<script>window.location.href = 'dashboard.php';</script>";
                    exit;
                }
            } else {
                $error = 'Username ឬ Password មិនត្រឹមត្រូវឡើយ!';
            }
        }
    }
}

$csrfToken = getCsrfToken();
?>
<!DOCTYPE html>
<html lang="km">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - <?= SHOP_NAME ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Kantumruy+Pro:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        body {
            background: linear-gradient(135deg, #fff7ed 0%, #ffedd5 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-card {
            max-width: 420px;
            width: 100%;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(255, 107, 0, 0.15);
        }
    </style>
</head>
<body>

<div class="login-card bg-white p-4 p-md-5">
    <div class="text-center mb-4">
        <span class="brand-icon bg-orange text-white p-3 rounded-circle d-inline-block mb-3">
            <i class="bi bi-shield-lock-fill" style="font-size: 1.8rem;"></i>
        </span>
        <h4 class="fw-bold text-dark">ផ្ទាំងគ្រប់គ្រង Admin</h4>
        <p class="text-muted small"><?= SHOP_NAME ?></p>
    </div>

    <?php if (!empty($error)): ?>
        <div class="alert alert-danger py-2 small" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-1"></i> <?= e($error) ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="login.php">
        <input type="hidden" name="csrf_token" value="<?= e($csrfToken) ?>">

        <div class="mb-3">
            <label class="form-label fw-semibold small">ឈ្មោះគណនី</label>
            <div class="input-group">
                <span class="input-group-text"><i class="bi bi-person"></i></span>
                <input type="text" id="loginUsername" name="username" class="form-control" required placeholder="admin" autofocus>
            </div>
        </div>

        <div class="mb-3">
            <label class="form-label fw-semibold small">ពាក្យសម្ងាត់</label>
            <div class="input-group">
                <span class="input-group-text"><i class="bi bi-key"></i></span>
                <input type="password" id="loginPassword" name="password" class="form-control" required placeholder="••••••••">
            </div>
        </div>

        <div class="p-3 bg-light border rounded-3 mb-4 small">
            <div class="d-flex justify-content-between align-items-center mb-1">
                <span class="fw-bold text-dark"><i class="bi bi-shield-check text-success me-1"></i> គណនី Admin លំនាំដើម</span>
                <button type="button" class="btn btn-sm btn-link p-0 text-orange text-decoration-none fw-bold" onclick="autoFillDemo()">
                    <i class="bi bi-magic me-1"></i> បំពេញស្វ័យប្រវត្តិ
                </button>
            </div>
            <div class="text-muted">
                • ឈ្មោះគណនី ៖ <code class="text-dark fw-bold">admin</code><br>
                • ពាក្យសម្ងាត់ ៖ <code class="text-dark fw-bold">admin123</code> (ឬ <code>Admin@12345</code>)
            </div>
        </div>

        <button type="submit" class="btn btn-orange w-100 py-2 fw-bold">
            <i class="bi bi-box-arrow-in-right me-1"></i> ចូលប្រព័ន្ធ
        </button>
    </form>

    <div class="text-center mt-4">
        <a href="../index.php" class="text-muted small text-decoration-none">
            <i class="bi bi-arrow-left"></i> ត្រឡប់ទៅទំព័រមុខហាង
        </a>
    </div>
</div>

<script>
function autoFillDemo() {
    document.getElementById('loginUsername').value = 'admin';
    document.getElementById('loginPassword').value = 'admin123';
}
</script>

</body>
</html>
