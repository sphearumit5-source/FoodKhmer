<?php
// admin/add_product.php

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

requireAdminAuth();

$pdo = getDB();

$error = '';
$success = '';

// ==============================
// GET CATEGORIES
// ==============================
$categories = $pdo->query("
    SELECT category_id, name_kh, name_en
    FROM categories
    ORDER BY display_order ASC
")->fetchAll(PDO::FETCH_ASSOC);

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

        // KHR: Keep price as string to avoid floating-point errors
        $price = trim($_POST['price'] ?? '');

        $description_kh = trim($_POST['description_kh'] ?? '');
        $description_en = trim($_POST['description_en'] ?? '');

        // Product Status
        $is_available = isset($_POST['is_available']);
        $is_featured = isset($_POST['is_featured']);

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

        } elseif (
            strlen(ltrim($price, '0')) > 12
        ) {

            $error = 'តម្លៃផលិតផលធំពេក!';

        } else {

            // Normalize price
            $price = ltrim($price, '0');
            $price = $price === '' ? '0' : $price;

            // Check Category Exists
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
                // DEFAULT IMAGE
                // ==============================

                $imageName = 'default-food.png';

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
                // INSERT PRODUCT
                // ==============================

                if ($error === '') {

                    try {

                        $sql = "
                            INSERT INTO products (
                                category_id,
                                name_kh,
                                name_en,
                                description_kh,
                                description_en,
                                price,
                                image,
                                is_available,
                                is_featured
                            )
                            VALUES (
                                :cat,
                                :n_kh,
                                :n_en,
                                :d_kh,
                                :d_en,
                                :price,
                                :img,
                                :available,
                                :featured
                            )
                        ";

                        $stmt = $pdo->prepare($sql);

                        $stmt->bindValue(':cat', $category_id, PDO::PARAM_INT);
                        $stmt->bindValue(':n_kh', $name_kh, PDO::PARAM_STR);
                        $stmt->bindValue(':n_en', $name_en, PDO::PARAM_STR);
                        $stmt->bindValue(':d_kh', $description_kh, PDO::PARAM_STR);
                        $stmt->bindValue(':d_en', $description_en, PDO::PARAM_STR);

                        // KHR price: no decimal places
                        $stmt->bindValue(':price', $price, PDO::PARAM_STR);

                        $stmt->bindValue(':img', $imageName, PDO::PARAM_STR);
                        $stmt->bindValue(':available', $is_available, PDO::PARAM_BOOL);
                        $stmt->bindValue(':featured', $is_featured, PDO::PARAM_BOOL);

                        $stmt->execute();

                        // Redirect after successful insert
                        header('Location: products.php');
                        exit;

                    } catch (PDOException $e) {

                        error_log('Add Product Error: ' . $e->getMessage());

                        $error = 'មិនអាចរក្សាទុកផលិតផលបានទេ! សូមពិនិត្យ Database។';
                    }
                }
            }
        }
    }
}

// ==============================
// CSRF TOKEN
// ==============================

$csrfToken = getCsrfToken();

?>

<!DOCTYPE html>
<html lang="km">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>បន្ថែមផលិតផលថ្មី - Admin</title>

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

            <i class="bi bi-plus-circle text-warning me-1"></i>

            បន្ថែមមុខម្ហូប ឬភេសជ្ជៈថ្មី

        </h4>

        <p class="text-muted small mb-0">

            បញ្ចូលព័ត៌មានផលិតផល និងតម្លៃជាប្រាក់រៀលខ្មែរ (KHR)

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
            action="add_product.php"
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
                        placeholder="ឧ. បាយឆាសាច់ជ្រូក"
                        value="<?= e($_POST['name_kh'] ?? '') ?>"
                    >

                </div>


                <div class="col-md-6">

                    <label class="form-label fw-semibold">

                        ឈ្មោះជាភាសាអង់គ្លេស

                        <span class="text-danger">*</span>

                    </label>

                    <input
                        type="text"
                        name="name_en"
                        class="form-control"
                        required
                        placeholder="e.g. Pork Fried Rice"
                        value="<?= e($_POST['name_en'] ?? '') ?>"
                    >

                </div>

            </div>


            <!-- CATEGORY AND PRICE -->
            <div class="row g-3 mb-3">

                <!-- CATEGORY -->
                <div class="col-md-6">

                    <label class="form-label fw-semibold">

                        ប្រភេទមុខម្ហូប/ភេសជ្ជៈ

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
                                <?= (($_POST['category_id'] ?? '') == $c['category_id']) ? 'selected' : '' ?>
                            >

                                <?= e($c['name_kh']) ?>

                            </option>

                        <?php endforeach; ?>

                    </select>

                </div>


                <!-- PRICE KHR -->
                <div class="col-md-6">

                    <label class="form-label fw-semibold">

                        តម្លៃជាប្រាក់រៀលខ្មែរ

                        <span class="text-danger">*</span>

                    </label>

                    <div class="input-group">

                        <input
                            type="number"
                            name="price"
                            class="form-control price-input"
                            required
                            min="0"
                            step="1"
                            inputmode="numeric"
                            placeholder="ឧ. 10000"
                            value="<?= e($_POST['price'] ?? '') ?>"
                        >

                        <span class="input-group-text currency-label">

                            ៛

                        </span>

                    </div>

                    <small class="price-note">

                        <i class="bi bi-info-circle"></i>

                        សូមបញ្ចូលជាលេខរៀល ឧ. 10,000៛
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
                        placeholder="ពិពណ៌នាពីគ្រឿងផ្សំ ឬរសជាតិ..."
                    ><?= e($_POST['description_kh'] ?? '') ?></textarea>

                </div>


                <div class="col-md-6">

                    <label class="form-label fw-semibold">

                        ការពិពណ៌នាជាភាសាអង់គ្លេស (បើមាន)

                    </label>

                    <textarea
                        name="description_en"
                        class="form-control"
                        rows="3"
                        placeholder="ព័ត៌មានលម្អិត ឬគ្រឿងផ្សំ..."
                    ><?= e($_POST['description_en'] ?? '') ?></textarea>

                </div>

            </div>


            <!-- PRODUCT IMAGE -->
            <div class="mb-4">

                <label class="form-label fw-semibold">

                    <i class="bi bi-image me-1"></i>

                    រូបភាពផលិតផល

                </label>

                <input
                    type="file"
                    name="image"
                    class="form-control"
                    accept="image/jpeg,image/png,image/webp"
                >

                <small class="text-muted">

                    អនុញ្ញាតតែ JPG, PNG, WEBP (ទំហំក្រោម 2MB)

                </small>

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
                            <?= isset($_POST['is_available']) || $_SERVER['REQUEST_METHOD'] !== 'POST' ? 'checked' : '' ?>
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
                            <?= isset($_POST['is_featured']) ? 'checked' : '' ?>
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

                    រក្សាទុក

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
