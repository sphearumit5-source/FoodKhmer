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

            if ($admin && password_verify($password, $admin['password_hash'])) {
                session_regenerate_id(true);
                $_SESSION['admin_logged_in'] = true;
                $_SESSION['admin_id'] = $admin['admin_id'];
                $_SESSION['admin_username'] = $admin['username'];
                $_SESSION['admin_name'] = $admin['full_name'];

                header('Location: dashboard.php');
                exit;
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
                <input type="text" name="username" class="form-control" required placeholder="admin" autofocus>
            </div>
        </div>

        <div class="mb-4">
            <label class="form-label fw-semibold small">ពាក្យសម្ងាត់</label>
            <div class="input-group">
                <span class="input-group-text"><i class="bi bi-key"></i></span>
                <input type="password" name="password" class="form-control" required placeholder="••••••••">
            </div>
            <small class="text-muted" style="font-size: 0.75rem;">គណនីលំនាំដើម៖ admin</small>
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

</body>
</html>
