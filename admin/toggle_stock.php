<?php
// admin/toggle_stock.php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

requireAdminAuth();

$isAjax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest')
    || (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false)
    || isset($_POST['ajax']);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    if ($isAjax) {
        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode(['success' => false, 'error' => 'Method Not Allowed']);
        exit;
    }
    header('Location: dashboard.php');
    exit;
}

$token = $_POST['csrf_token'] ?? '';
if (!verifyCsrfToken($token)) {
    if ($isAjax) {
        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode(['success' => false, 'error' => 'សុពលភាព Token មិនត្រឹមត្រូវ (CSRF Error)']);
        exit;
    }
    die('សុពលភាព Token មិនត្រឹមត្រូវ (CSRF Error)');
}

$productId = filter_input(INPUT_POST, 'product_id', FILTER_VALIDATE_INT);
if (!$productId || $productId <= 0) {
    if ($isAjax) {
        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode(['success' => false, 'error' => 'លេខសម្គាល់ទំនិញមិនត្រឹមត្រូវឡើយ']);
        exit;
    }
    header('Location: dashboard.php?msg=invalid');
    exit;
}

$pdo = getDB();

try {
    // ស្វែងរកស្ថានភាពស្តុកបច្ចុប្បន្ន
    $stmt = $pdo->prepare("SELECT is_available, name_kh FROM products WHERE product_id = :id");
    $stmt->execute([':id' => $productId]);
    $row = $stmt->fetch();

    if (!$row) {
        if ($isAjax) {
            header('Content-Type: application/json; charset=UTF-8');
            echo json_encode(['success' => false, 'error' => 'រកមិនឃើញទំនិញនេះឡើយ']);
            exit;
        }
        header('Location: dashboard.php?msg=not_found');
        exit;
    }

    $currentStatus = (bool)$row['is_available'];
    $newStatus = !$currentStatus;

    // កែប្រែស្ថានភាពស្តុក
    $up = $pdo->prepare("UPDATE products SET is_available = :avail, updated_at = CURRENT_TIMESTAMP WHERE product_id = :id");
    $up->bindValue(':avail', $newStatus, PDO::PARAM_BOOL);
    $up->bindValue(':id', $productId, PDO::PARAM_INT);
    $up->execute();

    // គណនាស្ថិតិស្តុកសរុបឡើងវិញ
    $inStockCount = (int)$pdo->query("SELECT COUNT(*) FROM products WHERE is_available = TRUE")->fetchColumn();
    $outStockCount = (int)$pdo->query("SELECT COUNT(*) FROM products WHERE is_available = FALSE")->fetchColumn();
    $totalCount = (int)$pdo->query("SELECT COUNT(*) FROM products")->fetchColumn();

    if ($isAjax) {
        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode([
            'success'            => true,
            'product_id'         => $productId,
            'name_kh'            => $row['name_kh'],
            'is_available'       => $newStatus,
            'status_text'        => $newStatus ? 'មានក្នុងស្តុក' : 'ដាច់ស្តុក',
            'badge_class'        => $newStatus ? 'bg-success' : 'bg-danger',
            'btn_class'          => $newStatus ? 'btn-outline-danger' : 'btn-outline-success',
            'btn_text'           => $newStatus ? 'ប្ដូរទៅដាច់ស្តុក' : 'ប្ដូរទៅមានស្តុក',
            'count_in_stock'     => $inStockCount,
            'count_out_of_stock' => $outStockCount,
            'count_total'        => $totalCount
        ]);
        exit;
    }

    header('Location: dashboard.php?msg=stock_updated');
    exit;

} catch (Throwable $e) {
    error_log('Toggle Stock Error: ' . $e->getMessage());
    if ($isAjax) {
        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode(['success' => false, 'error' => 'មានបញ្ហាក្នុងការកែប្រែស្ថានភាពស្តុក']);
        exit;
    }
    header('Location: dashboard.php?msg=error');
    exit;
}
