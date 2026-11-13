
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
        $error = 'សុពលភាព Token មិនត្រឹមត្រូវ។ សូមព្យាយាមម្ដងទៀត។';
    } else {

        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';

        if ($username === '' || $password === '') {
            $error = 'សូមបញ្ចូល Username និង Password។';
        } else {
            try {
                $pdo = getDB();

                $stmt = $pdo->prepare("
                    SELECT admin_id, username, password_hash, full_name
                    FROM admins
                    WHERE username = :user
                    LIMIT 1
                ");

                $stmt->execute([':user' => $username]);
                $admin = $stmt->fetch(PDO::FETCH_ASSOC);

                // Verify password using secure password hash
                if ($admin && password_verify($password, $admin['password_hash'])) {

                    session_regenerate_id(true);

                    $_SESSION['admin_logged_in'] = true;
                    $_SESSION['admin_id'] = $admin['admin_id'];
                    $_SESSION['admin_username'] = $admin['username'];
                    $_SESSION['admin_name'] = $admin['full_name'];

                    // Signed authentication cookie
                    if (defined('AUTH_SECRET') && AUTH_SECRET !== '') {

                        $payload = base64_encode(json_encode([
                            'id'   => $admin['admin_id'],
                            'user' => $admin['username'],
                            'name' => $admin['full_name'],
                            'exp'  => time() + (86400 * 7)
                        ]));

                        $signature = hash_hmac(
                            'sha256',
                            $payload,
                            AUTH_SECRET
                        );

                        setcookie('admin_auth', $payload . '.' . $signature, [
                            'expires'  => time() + (86400 * 7),
                            'path'     => '/',
                            'secure'   => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
                            'httponly' => true,
                            'samesite' => 'Lax'
                        ]);
                    }

                    header('Location: dashboard.php');
                    exit;

                } else {
                    $error = 'Username ឬ Password មិនត្រឹមត្រូវឡើយ!';
                }

            } catch (PDOException $e) {
                error_log('Admin login error: ' . $e->getMessage());
                $error = 'មានបញ្ហាក្នុងការភ្ជាប់ Database។ សូមព្យាយាមម្ដងទៀត។';
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

    <title>Admin Login - <?= e(SHOP_NAME) ?></title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">

    <link href="https://fonts.googleapis.com/css2?family=Kantumruy+Pro:wght@400;500;600;700&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="../assets/css/style.css">

    <style>
        * {
            font-family: 'Kantumruy Pro', sans-serif;
        }

        body {
            background: linear-gradient(135deg, #fff7ed, #ffedd5);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .login-card {
            max-width: 420px;
            width: 100%;
            background: #fff;
            border-radius: 22px;
            padding: 40px;
            box-shadow: 0 15px 45px rgba(255, 107, 0, 0.13);
        }

        .brand-icon {
            width: 75px;
            height: 75px;
            background: #f97316;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            margin: 0 auto 20px;
            font-size: 32px;
        }

        .form-control {
            min-height: 48px;
        }

        .input-group-text {
            background: #fff7ed;
            color: #f97316;
        }

        .btn-login {
            background: #f97316;
            color: white;
            border: none;
            min-height: 48px;
            border-radius: 10px;
        }

        .btn-login:hover {
            background: #ea580c;
            color: white;
        }

        .password-toggle {
            cursor: pointer;
        }

        .back-link {
            color: #777;
            text-decoration: none;
        }

        .back-link:hover {
            color: #f97316;
        }
    </style>
</head>

<body>

<div class="login-card">

    <div class="text-center mb-4">

        <div class="brand-icon">
            <i class="bi bi-shield-lock-fill"></i>
        </div>

        <h4 class="fw-bold text-dark">
            ចូលប្រព័ន្ធគ្រប់គ្រង
        </h4>

        <p class="text-muted small">
            <?= e(SHOP_NAME) ?>
        </p>

        <p class="text-muted small">
            សូមបញ្ចូលព័ត៌មានគណនី Admin របស់អ្នក
        </p>

    </div>

    <?php if ($error !== ''): ?>

        <div class="alert alert-danger py-2 small">
            <i class="bi bi-exclamation-circle-fill me-1"></i>
            <?= e($error) ?>
        </div>

    <?php endif; ?>

    <form method="POST" action="login.php">

        <input
            type="hidden"
            name="csrf_token"
            value="<?= e($csrfToken) ?>"
        >

        <div class="mb-3">

            <label class="form-label fw-semibold">
                ឈ្មោះគណនី
            </label>

            <div class="input-group">

                <span class="input-group-text">
                    <i class="bi bi-person"></i>
                </span>

                <input
                    type="text"
                    name="username"
                    class="form-control"
                    placeholder="បញ្ចូល Username"
                    autocomplete="username"
                    required
                    autofocus
                >

            </div>

        </div>

        <div class="mb-4">

            <label class="form-label fw-semibold">
                ពាក្យសម្ងាត់
            </label>

            <div class="input-group">

                <span class="input-group-text">
                    <i class="bi bi-key"></i>
                </span>

                <input
                    type="password"
                    id="loginPassword"
                    name="password"
                    class="form-control"
                    placeholder="បញ្ចូល Password"
                    autocomplete="current-password"
                    required
                >

                <button
                    type="button"
                    class="input-group-text password-toggle"
                    onclick="togglePassword()"
                    aria-label="បង្ហាញ ឬលាក់ Password"
                >
                    <i class="bi bi-eye" id="eyeIcon"></i>
                </button>

            </div>

        </div>

        <button type="submit" class="btn btn-login w-100 fw-bold">

            <i class="bi bi-box-arrow-in-right me-2"></i>
            ចូលប្រព័ន្ធ

        </button>

    </form>

    <div class="text-center mt-4">

        <a href="../index.php" class="back-link small">
            <i class="bi bi-arrow-left me-1"></i>
            ត្រឡប់ទៅទំព័រមុខហាង
        </a>

    </div>

    <div class="text-center mt-3">
        <small class="text-muted">
            <i class="bi bi-shield-check text-success"></i>
            Secure Admin Access
        </small>
    </div>

</div>

<script>
function togglePassword() {

    const password = document.getElementById('loginPassword');
    const icon = document.getElementById('eyeIcon');

    if (password.type === 'password') {
        password.type = 'text';
        icon.classList.replace('bi-eye', 'bi-eye-slash');
    } else {
        password.type = 'password';
        icon.classList.replace('bi-eye-slash', 'bi-eye');
    }
}
</script>

</body>
</html>
