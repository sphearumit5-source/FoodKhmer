
<?php
// admin/add_product.php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

requireAdminAuth();

$pdo = getDB();
$error = '';

$categories = $pdo->query("
    SELECT category_id, name_kh, name_en
    FROM categories
    ORDER BY display_order ASC
")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        $error = 'សុពលភាព Token មិនត្រឹមត្រូវ (CSRF Error)';
    } else {

        $name_kh = trim($_POST['name_kh'] ?? '');
        $name_en = trim($_POST['name_en'] ?? '');
        $category_id = (int)($_POST['category_id'] ?? 0);

        // តម្លៃជារៀល (KHR) ចំនួនគត់
        $priceInput = trim($_POST['price'] ?? '');

        $description_kh = trim($_POST['description_kh'] ?? '');
        $description_en = trim($_POST['description_en'] ?? '');

        $is_available = isset($_POST['is_available']);
        $is_featured = isset($_POST['is_featured']);

        if (
            $name_kh === '' ||
            $name_en === '' ||
            $category_id <= 0 ||
            $priceInput === '' ||
            !ctype_digit($priceInput)
        ) {
            $error = 'សូមបំពេញព័ត៌មានឱ្យបានត្រឹមត្រូវ និងបញ្ចូលតម្លៃជាលេខរៀល!';
        } elseif ((float)$priceInput < 0) {
            $error = 'តម្លៃមិនអាចតិចជាងសូន្យបានទេ!';
        } else {

            $price = (int)$priceInput;

            // ពិនិត្យថាប្រភេទនេះមានក្នុង Database ឬអត់
            $checkCategory = $pdo->prepare("
                SELECT COUNT(*)
                FROM categories
                WHERE category_id = :cat
            ");
            $checkCategory->execute([':cat' => $category_id]);

            if ((int)$checkCategory->fetchColumn() === 0) {
                $error = 'សូមជ្រើសរើសប្រភេទមុខម្ហូបឱ្យបានត្រឹមត្រូវ!';
            } else {

                $imageName = 'default-food.png';

                if (
                    isset($_FILES['image']) &&
                    $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE
                ) {
                    if ($_FILES['image']['error'] !== UPLOAD_ERR_OK) {
                        $error = 'មានបញ្ហាក្នុងការផ្ទុករូបភាព!';
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

                if ($error === '') {

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

                    try {
                        $stmt = $pdo->prepare($sql);

                        $stmt->bindValue(':cat', $category_id, PDO::PARAM_INT);
                        $stmt->bindValue(':n_kh', $name_kh, PDO::PARAM_STR);
                        $stmt->bindValue(':n_en', $name_en, PDO::PARAM_STR);
                        $stmt->bindValue(':d_kh', $description_kh, PDO::PARAM_STR);
                        $stmt->bindValue(':d_en', $description_en, PDO::PARAM_STR);
                        $stmt->bindValue(':price', $price, PDO::PARAM_INT);
                        $stmt->bindValue(':img', $imageName, PDO::PARAM_STR);
                        $stmt->bindValue(':available', $is_available, PDO::PARAM_BOOL);
                        $stmt->bindValue(':featured', $is_featured, PDO::PARAM_BOOL);

                        $stmt->execute();

                        header('Location: products.php');
                        exit;
                    } catch (PDOException $e) {
                        $error = 'មានបញ្ហាក្នុងការរក្សាទុកទិន្នន័យ៖ ' . $e->getMessage();
                    }
                }
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

    <title>បន្ថែមមុខម្ហូបថ្មី - Admin</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">

    <link href="https://fonts.googleapis.com/css2?family=Kantumruy+Pro:wght@400;500;600;700&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
</head>

<body class="bg-light">

<div class="container py-4" style="max-width: 850px;">

    <div class="mb-4">
        <a href="products.php" class="text-decoration-none text-muted small">
            <i class="bi bi-arrow-left"></i>
            ត្រឡប់ទៅបញ្ជីផលិតផល
        </a>

        <h4 class="fw-bold mt-1">
            បន្ថែមមុខម្ហូប ឬភេសជ្ជៈថ្មី
        </h4>
    </div>

    <?php if (!empty($error)): ?>
        <div class="alert alert-danger" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-1"></i>
            <?= e($error) ?>
        </div>
    <?php endif; ?>

    <div class="card border-0 shadow-sm rounded-4 p-4">

        <form method="POST" action="add_product.php" enctype="multipart/form-data">

            <input
                type="hidden"
                name="csrf_token"
                value="<?= e($csrfToken) ?>"
            >

            <div class="row g-3 mb-3">

                <div class="col-md-6">
                    <label class="form-label fw-semibold">
                        ឈ្មោះជាភាសាខ្មែរ <span class="text-danger">*</span>
                    </label>

                    <input
                        type="text"
                        name="name_kh"
                        class="form-control"
                        required
                        placeholder="ឧ. បាយឆាសាច់ជ្រូក"
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
                        placeholder="ឧ. Pork Fried Rice"
                    >
                </div>

            </div>

            <div class="row g-3 mb-3">

                <div class="col-md-6">
                    <label class="form-label fw-semibold">
                        ប្រភេទមុខម្ហូប/ភេសជ្ជៈ
                        <span class="text-danger">*</span>
                    </label>

                    <select name="category_id" class="form-select" required>

                        <option value="">
                            -- សូមជ្រើសរើសប្រភេទ --
                        </option>

                        <?php foreach ($categories as $c): ?>

                            <option value="<?= e($c['category_id']) ?>">
                                <?= e($c['name_kh']) ?>
                            </option>

                        <?php endforeach; ?>

                    </select>
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold">
                        តម្លៃជារៀល (KHR)
                        <span class="text-danger">*</span>
                    </label>

                    <div class="input-group">
                        <span class="input-group-text">៛</span>

                        <input
                            type="number"
                            step="1"
                            min="0"
                            name="price"
                            class="form-control"
                            required
                            placeholder="ឧ. 5000"
                        >
                    </div>

                    <small class="text-muted">
                        សូមបញ្ចូលតម្លៃជាលេខរៀល ឧ. 5000 ឬ 10000
                    </small>
                </div>

            </div>

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
                    ></textarea>
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
                    ></textarea>
                </div>

            </div>

            <div class="mb-4">

                <label class="form-label fw-semibold">
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

            <div class="row g-3 mb-4">

                <div class="col-md-6">
                    <div class="form-check form-switch">

                        <input
                            class="form-check-input"
                            type="checkbox"
                            name="is_available"
                            id="is_available"
                            value="1"
                            checked
                        >

                        <label class="form-check-label fw-semibold" for="is_available">
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
                        >

                        <label class="form-check-label fw-semibold" for="is_featured">
                            មុខម្ហូបពិសេសប្រចាំហាង (Featured)
                        </label>

                    </div>
                </div>

            </div>

            <div class="d-flex gap-2">

                <button type="submit" class="btn btn-orange px-4 fw-bold">
                    <i class="bi bi-save me-1"></i>
                    រក្សាទុក
                </button>

                <a href="products.php" class="btn btn-outline-secondary px-3">
                    បោះបង់
                </a>

            </div>

        </form>

    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
