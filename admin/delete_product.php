<?php
// admin/delete_product.php
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

// ពិនិត្យ CSRF Token
if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
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
        echo json_encode(['success' => false, 'error' => 'លេខសម្គាល់ផលិតផលមិនត្រឹមត្រូវ']);
        exit;
    }
    header('Location: dashboard.php?msg=invalid');
    exit;
}

$pdo = getDB();

try {
    // រករូបភាព និងឈ្មោះផលិតផល
    $imgStmt = $pdo->prepare("SELECT name_kh, image FROM products WHERE product_id = :id");
    $imgStmt->execute([':id' => $productId]);
    $prod = $imgStmt->fetch();

    if (!$prod) {
        if ($isAjax) {
            header('Content-Type: application/json; charset=UTF-8');
            echo json_encode(['success' => false, 'error' => 'រកមិនឃើញផលិតផលនេះឡើយ']);
            exit;
        }
        header('Location: dashboard.php?msg=not_found');
        exit;
    }

    $imageName = $prod['image'] ?? '';
    $productName = $prod['name_kh'] ?? '';

    // លុបផលិតផលចេញពី Database
    $delStmt = $pdo->prepare("DELETE FROM products WHERE product_id = :id");
    $delStmt->execute([':id' => $productId]);

    // លុបរូបភាពចេញពី Folder uploads ប្រសិនបើជារូបដែលបាន Upload ក្នុង Server
    if (!empty($imageName) && $imageName !== 'default-food.png' && basename($imageName) === $imageName && strpos($imageName, 'http') !== 0) {
        $filePath = __DIR__ . '/../uploads/' . $imageName;
        if (is_file($filePath)) {
            @unlink($filePath);
        }
    }

    // គណនាចំនួនស្ថិតិស្តុកឡើងវិញ
    $totalCount = (int)$pdo->query("SELECT COUNT(*) FROM products")->fetchColumn();
    $inStockCount = (int)$pdo->query("SELECT COUNT(*) FROM products WHERE is_available = TRUE")->fetchColumn();
    $outStockCount = (int)$pdo->query("SELECT COUNT(*) FROM products WHERE is_available = FALSE")->fetchColumn();

    if ($isAjax) {
        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode([
            'success'            => true,
            'product_id'         => $productId,
            'product_name'       => $productName,
            'message'            => 'បានលុបទំនិញ "' . $productName . '" ដោយជោគជ័យ!',
            'count_total'        => $totalCount,
            'count_in_stock'     => $inStockCount,
            'count_out_of_stock' => $outStockCount
        ]);
        exit;
    }

    header('Location: dashboard.php?msg=deleted');
    exit;

} catch (Throwable $e) {
    error_log('Delete product error: ' . $e->getMessage());
    if ($isAjax) {
        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode(['success' => false, 'error' => 'មានបញ្ហាក្នុងការលុបទំនិញ']);
        exit;
    }
    header('Location: dashboard.php?msg=error');
    exit;
}
