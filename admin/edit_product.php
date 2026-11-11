
<?php
// admin/edit_product.php

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

requireAdminAuth();

$pdo = getDB();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$error = '';

// ==============================
// GET PRODUCT
// ==============================

$stmt = $pdo->prepare("
    SELECT *
    FROM products
    WHERE product_id = :id
");

$stmt->execute([':id' => $id]);

$product = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$product) {
    die("រកមិនឃើញផលិតផលនេះឡើយ!");
}

// ==============================
// GET CATEGORIES
// ==============================

$categories = $pdo->query("
    SELECT category_id, name_kh, name_en
    FROM categories
    ORDER BY display_order ASC
")->fetchAll(PDO::FETCH_ASSOC);

// ==============================
// FORM VALUES
// ==============================

$form = [
    'name_kh' => $product['name_kh'],
    'name_en' => $product['name_en'],
    'category_id' => $product['category_id'],
    'price' => $product['price'],
    'description_kh' => $product['description_kh'],
    'description_en' => $product['description_en'],
];

// ==============================
// HANDLE FORM SUBMIT
// ==============================

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Verify CSRF Token
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {

        $error = 'សុពលភាព Token មិនត្រឹមត្រូវ (CSRF Error)';

    } else {

        // Get Form Data
        $name_kh = trim($_POST['name_kh'] ?? '');
        $name_en = trim($_POST['name_en'] ?? '');

        $category_id = (int)($_POST['category_id'] ?? 0);

        // KHR Price - Integer only
        $price = trim($_POST['price'] ?? '');

        $description_kh = trim($_POST['description_kh'] ?? '');
        $description_en = trim($_POST['description_en'] ?? '');

        // Product Status
        $is_available = isset($_POST['is_available']);
        $is_featured = isset($_POST['is_featured']);

        // Keep old image by default
        $imageName = $product['image'];

        // Preserve submitted values if validation fails
        $form = [
            'name_kh' => $name_kh,
            'name_en' => $name_en,
            'category_id' => $category_id,
            'price' => $price,
            'description_kh' => $description_kh,
            'description_en' => $description_en,
        ];

        // ==============================
        // VALIDATE DATA
        // ==============================

        if (
            $name_kh === '' ||
            $name_en === '' ||
            $category_id <= 0 ||
            $price === '' ||
            !preg_match('/^\d+$/', $price)
        ) {

            $error = 'សូមបំពេញព័ត៌មានចាំបាច់ និងបញ្ចូលតម្លៃជាលេខរៀលត្រឹមត្រូវ!';

        } elseif (strlen(ltrim($price, '0')) > 12) {

            $error = 'តម្លៃផលិតផលធំពេក!';

        } else {

            // Normalize price
            $price = ltrim($price, '0');
            $price = $price === '' ? '0' : $price;

            // ==============================
            // CHECK CATEGORY
            // ==============================

            $checkCategory = $pdo->prepare("
                SELECT COUNT(*)
                FROM categories
                WHERE category_id = :cat
            ");

            $checkCategory->execute([
                ':cat' => $category_id
            ]);

            if ((int)$checkCategory->fetchColumn() === 0) {

                $error = 'ប្រភេទផលិតផលមិនត្រឹមត្រូវទេ!';

            } else {

                // ==============================
                // IMAGE UPLOAD
                // ==============================

                if (
                    isset($_FILES['image']) &&
                    $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE
                ) {

                    if ($_FILES['image']['error'] !== UPLOAD_ERR_OK) {

                        $error = 'មានបញ្ហាក្នុងការបង្ហោះរូបភាព!';

                    } else {

                        $uploadRes = handleImageUpload(
                            $_FILES['image'],
                            __DIR__ . '/../uploads/'
                        );

                        if ($uploadRes['success']) {

                            $imageName = $uploadRes['filename'];

                        } else {

                            $error = $uploadRes['error'];

                        }
                    }
                }

                // ==============================
                // UPDATE PRODUCT
                // ==============================

                if ($error === '') {

                    try {

                        $sql = "
                            UPDATE products

                            SET
                                category_id = :cat,
                                name_kh = :n_kh,
                                name_en = :n_en,
                                description_kh = :d_kh,
                                description_en = :d_en,
                                price = :price,
                                image = :img,
                                is_available = :available,
                                is_featured = :featured,
                                updated_at = CURRENT_TIMESTAMP

                            WHERE product_id = :id
                        ";

                        $updateStmt = $pdo->prepare($sql);

                        $updateStmt->bindValue(
                            ':cat',
                            $category_id,
                            PDO::PARAM_INT
                        );

                        $updateStmt->bindValue(
                            ':n_kh',
                            $name_kh,
                            PDO::PARAM_STR
                        );

                        $updateStmt->bindValue(
                            ':n_en',
                            $name_en,
                            PDO::PARAM_STR
                        );

                        $updateStmt->bindValue(
                            ':d_kh',
                            $description_kh,
                            PDO::PARAM_STR
                        );

                        $updateStmt->bindValue(
                            ':d_en',
                            $description_en,
                            PDO::PARAM_STR
                        );

                        // KHR: No decimal places
                        $updateStmt->bindValue(
                            ':price',
                            $price,
                            PDO::PARAM_STR
                        );

                        $updateStmt->bindValue(
                            ':img',
                            $imageName,
                            PDO::PARAM_STR
                        );

                        $updateStmt->bindValue(
                            ':available',
                            $is_available,
                            PDO::PARAM_BOOL
                        );

                        $updateStmt->bindValue(
                            ':featured',
                            $is_featured,
                            PDO::PARAM_BOOL
                        );

                        $updateStmt->bindValue(
                            ':id',
                            $id,
                            PDO::PARAM_INT
                        );

                        $updateStmt->execute();

                        // Delete old image after successful update
                        if (
                            $imageName !== $product['image'] &&
                            !empty($product['image']) &&
                            $product['image'] !== 'default-food.png'
                        ) {

                            $oldPath = __DIR__ . '/../uploads/' . basename($product['image']);

                            if (is_file($oldPath)) {
                                @unlink($oldPath);
                            }
                        }

                        // Redirect
                        header('Location: products.php');
                        exit;

                    } catch (PDOException $e) {

                        error_log('Edit Product Error: ' . $e->getMessage());

                        $error = 'មិនអាចកែប្រែផលិតផលបានទេ! សូមពិនិត្យ Database។';
                    }
                }
            }
        }
    }
}

// ==============================
// IMAGE DISPLAY
// ==============================

$curImg = $product['image'] ?? '';

if (
    !empty($curImg) &&
    preg_match('#^https?://#i', $curImg)
) {

    $curImgPath = $curImg;

} elseif (
    !empty($curImg) &&
    is_file(__DIR__ . '/../uploads/' . basename($curImg))
) {

    $curImgPath = '../uploads/' . basename($curImg);

} else {

    $curImgPath = '../uploads/default-food.png';

}

// CSRF Token
$csrfToken = getCsrfToken();

?>

<!DOCTYPE html>
<html lang="km">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>កែប្រែផលិតផល - Admin</title>

    <!-- Bootstrap -->
    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <!-- Bootstrap Icons -->
    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css"
        rel="stylesheet"
    >

    <!-- Khmer Font -->
    <link
        href="https://fonts.googleapis.com/css2?family=Kantumruy+Pro:wght@400;500;600;700&display=swap"
        rel="stylesheet"
    >

    <!-- Custom CSS -->
    <link rel="stylesheet" href="../assets/css/style.css">

    <link rel="stylesheet" href="../assets/css/admin.css">

    <style>

        body {
            font-family: 'Kantumruy Pro', sans-serif;
        }

        .currency-label {
            background: #fff3e8;
            color: #e87524;
            font-weight: 700;
            min-width: 55px;
            justify-content: center;
        }

        .price-input {
            font-weight: 600;
        }

        .price-note {
            color: #6c757d;
            font-size: 13px;
        }

        .product-preview {
            width: 85px;
            height: 85px;
            object-fit: cover;
        }

    </style>

</head>

<body class="bg-light">

<div class="container py-4" style="max-width: 850px;">

    <!-- HEADER -->
    <div class="mb-4">

        <a
            href="products.php"
            class="text-decoration-none text-muted small"
        >

            <i class="bi bi-arrow-left"></i>

            ត្រឡប់ទៅបញ្ជីផលិតផល

        </a>

        <h4 class="fw-bold mt-2">

            <i class="bi bi-pencil-square text-warning me-1"></i>

            កែប្រែ៖ <?= e($product['name_kh']) ?>

        </h4>

        <p class="text-muted small mb-0">

            កែប្រែព័ត៌មានផលិតផល និងតម្លៃជាប្រាក់រៀលខ្មែរ (KHR)

        </p>

    </div>


    <!-- ERROR MESSAGE -->
    <?php if ($error !== ''): ?>

        <div class="alert alert-danger" role="alert">

            <i class="bi bi-exclamation-triangle-fill me-1"></i>

            <?= e($error) ?>

        </div>

    <?php endif; ?>


    <!-- FORM CARD -->
    <div class="card border-0 shadow-sm rounded-4 p-4">

        <form
            method="POST"
            action="edit_product.php?id=<?= (int)$id ?>"
            enctype="multipart/form-data"
        >

            <!-- CSRF TOKEN -->
            <input
                type="hidden"
                name="csrf_token"
                value="<?= e($csrfToken) ?>"
            >


            <!-- PRODUCT NAME -->
            <div class="row g-3 mb-3">

                <div class="col-md-6">

                    <label class="form-label fw-semibold">

                        ឈ្មោះជាភាសាខ្មែរ

                        <span class="text-danger">*</span>

                    </label>

                    <input
                        type="text"
                        name="name_kh"
                        class="form-control"
                        required
                        value="<?= e($form['name_kh']) ?>"
                        placeholder="ឧ. បាយឆាសាច់ជ្រូក"
                    >

                </div>


                <div class="col-md-6">

                    <label class="form-label fw-semibold">

                        ឈ្មោះជាភាសាអង់គ្លេស (EN)

                        <span class="text-danger">*</span>

                    </label>

                    <input
                        type="text"
                        name="name_en"
                        class="form-control"
                        required
                        value="<?= e($form['name_en']) ?>"
                        placeholder="e.g. Pork Fried Rice"
                    >

                </div>

            </div>


            <!-- CATEGORY AND PRICE -->
            <div class="row g-3 mb-3">

                <!-- CATEGORY -->
                <div class="col-md-6">

                    <label class="form-label fw-semibold">

                        ប្រភេទ (Category)

                        <span class="text-danger">*</span>

                    </label>

                    <select
                        name="category_id"
                        class="form-select"
                        required
                    >

                        <option value="">

                            -- សូមជ្រើសរើសប្រភេទ --

                        </option>

                        <?php foreach ($categories as $c): ?>

                            <option
                                value="<?= (int)$c['category_id'] ?>"
                                <?= (string)$c['category_id'] === (string)$form['category_id'] ? 'selected' : '' ?>
                            >

                                <?= e($c['name_kh']) ?>

                                (<?= e($c['name_en']) ?>)

                            </option>

                        <?php endforeach; ?>

                    </select>

                </div>


                <!-- PRICE KHR -->
                <div class="col-md-6">

                    <label class="form-label fw-semibold">

                        តម្លៃជាប្រាក់រៀលខ្មែរ (KHR)

                        <span class="text-danger">*</span>

                    </label>

                    <div class="input-group">

                        <input
                            type="number"
                            step="1"
                            min="0"
                            name="price"
                            class="form-control price-input"
                            required
                            inputmode="numeric"
                            placeholder="ឧ. 10000"
                            value="<?= e($form['price']) ?>"
                        >

                        <span class="input-group-text currency-label">

                            ៛

                        </span>

                    </div>

                    <small class="price-note">

                        <i class="bi bi-info-circle"></i>

                        បញ្ចូលតម្លៃជាលេខរៀល ឧ. 10,000៛
                        សូមបញ្ចូល 10000 ដោយមិនដាក់សញ្ញាក្បៀស។

                    </small>

                </div>

            </div>


            <!-- DESCRIPTION -->
            <div class="row g-3 mb-3">

                <div class="col-md-6">

                    <label class="form-label fw-semibold">

                        ព័ត៌មានលម្អិត (ខ្មែរ)

                    </label>

                    <textarea
                        name="description_kh"
                        class="form-control"
                        rows="3"
                        placeholder="ពិពណ៌នាពីផលិតផល..."
                    ><?= e($form['description_kh']) ?></textarea>

                </div>


                <div class="col-md-6">

                    <label class="form-label fw-semibold">

                        Description (EN)

                    </label>

                    <textarea
                        name="description_en"
                        class="form-control"
                        rows="3"
                        placeholder="Describe the product..."
                    ><?= e($form['description_en']) ?></textarea>

                </div>

            </div>


            <!-- PRODUCT IMAGE -->
            <div class="row g-3 mb-4 align-items-center">

                <div class="col-md-8">

                    <label class="form-label fw-semibold">

                        <i class="bi bi-image me-1"></i>

                        ប្ដូររូបភាពថ្មី

                    </label>

                    <input
                        type="file"
                        name="image"
                        class="form-control"
                        accept="image/jpeg,image/png,image/webp"
                    >

                    <small class="text-muted">

                        JPG, PNG, WEBP (ទំហំក្រោម 2MB)

                    </small>

                    <div class="small text-success mt-2">

                        ប្រសិនបើមិនបញ្ចូលរូបភាពថ្មី
                        រូបភាពចាស់នឹងនៅដដែល។

                    </div>

                </div>


                <div class="col-md-4 text-center">

                    <label class="form-label fw-semibold d-block">

                        រូបភាពបច្ចុប្បន្ន

                    </label>

                    <img
                        src="<?= e($curImgPath) ?>"
                        class="rounded-3 border product-preview"
                        alt="Product Image"
                    >

                </div>

            </div>


            <!-- PRODUCT STATUS -->
            <div class="row g-3 mb-4">

                <div class="col-md-6">

                    <div class="form-check form-switch">

                        <input
                            class="form-check-input"
                            type="checkbox"
                            name="is_available"
                            id="is_available"
                            value="1"
                            <?= $product['is_available'] ? 'checked' : '' ?>
                        >

                        <label
                            class="form-check-label fw-semibold"
                            for="is_available"
                        >

                            មានក្នុងស្តុក (In Stock)

                        </label>

                    </div>

                </div>


                <div class="col-md-6">

                    <div class="form-check form-switch">

                        <input
                            class="form-check-input"
                            type="checkbox"
                            name="is_featured"
                            id="is_featured"
                            value="1"
                            <?= $product['is_featured'] ? 'checked' : '' ?>
                        >

                        <label
                            class="form-check-label fw-semibold"
                            for="is_featured"
                        >

                            មុខម្ហូបពិសេសប្រចាំហាង (Featured)

                        </label>

                    </div>

                </div>

            </div>


            <!-- BUTTONS -->
            <div class="d-flex gap-2">

                <button
                    type="submit"
                    class="btn btn-orange px-4 fw-bold"
                >

                    <i class="bi bi-save me-1"></i>

                    រក្សាទុកការកែប្រែ

                </button>


                <a
                    href="products.php"
                    class="btn btn-outline-secondary px-3"
                >

                    បោះបង់

                </a>

            </div>

        </form>

    </div>

</div>


<!-- BOOTSTRAP JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
