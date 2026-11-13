<?php
// admin/delete_product.php

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

requireAdminAuth();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: products.php');
    exit;
}

// ពិនិត្យ CSRF Token
if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
    die('សុពលភាព Token មិនត្រឹមត្រូវ (CSRF Error)');
}

$productId = filter_input(INPUT_POST, 'product_id', FILTER_VALIDATE_INT);

if (!$productId || $productId <= 0) {
    header('Location: products.php?msg=invalid');
    exit;
}

$pdo = getDB();

try {

    // ចាប់ផ្ដើម Transaction
    $pdo->beginTransaction();

    // រករូបភាពផលិតផល
    $imgStmt = $pdo->prepare("
        SELECT image
        FROM products
        WHERE product_id = :id
        FOR UPDATE
    ");

    $imgStmt->execute([':id' => $productId]);

    $imageName = $imgStmt->fetchColumn();

    // ពិនិត្យថាផលិតផលមានក្នុង Database ឬអត់
    if ($imageName === false) {
        $pdo->rollBack();

        header('Location: products.php?msg=not_found');
        exit;
    }

    // លុបផលិតផលចេញពី Database
    $delStmt = $pdo->prepare("
        DELETE FROM products
        WHERE product_id = :id
    ");

    $delStmt->execute([':id' => $productId]);

    // បញ្ចប់ Transaction
    $pdo->commit();

    // លុបរូបភាពចេញពី Folder uploads
    // លុបតែរូបភាពដែលជាឈ្មោះឯកសារសុវត្ថិភាព
    if (
        !empty($imageName) &&
        $imageName !== 'default-food.png' &&
        basename($imageName) === $imageName
    ) {

        $filePath = __DIR__ . '/../uploads/' . $imageName;

        if (is_file($filePath)) {
            @unlink($filePath);
        }
    }

    header('Location: products.php?msg=deleted');
    exit;

} catch (Throwable $e) {

    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    error_log('Delete product error: ' . $e->getMessage());

    header('Location: products.php?msg=error');
    exit;
}
