<?php
// product.php

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/header.php';

// Connect Database
$pdo = getDB();

// Get Product ID
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Get Product Information
$stmt = $pdo->prepare("
    SELECT
        p.*,
        c.name_kh AS cat_name_kh,
        c.name_en AS cat_name_en,
        c.slug AS cat_slug
    FROM products p
    JOIN categories c
        ON p.category_id = c.category_id
    WHERE p.product_id = :id
");

$stmt->execute([':id' => $id]);
$product = $stmt->fetch(PDO::FETCH_ASSOC);

// Check Product
if (!$product) {
    echo "
        <div class='container py-5 text-center'>
            <h3 class='text-muted'>
                រកមិនឃើញមុខទំនិញនេះទេ!
            </h3>

            <a href='index.php' class='btn btn-orange mt-3'>
                ត្រឡប់ទៅទំព័រដើម
            </a>
        </div>
    ";

    require_once __DIR__ . '/includes/footer.php';
    exit;
}

// Product Image
$img = $product['image'] ?? '';

if (strpos($img, 'http') === 0) {

    $imgPath = $img;

} elseif (
    !empty($img) &&
    file_exists(__DIR__ . '/uploads/' . $img)
) {

    $imgPath = 'uploads/' . $img;

} else {

    $imgPath = 'https://images.unsplash.com/photo-1546069901-ba9599a7e63c?w=600&auto=format&fit=crop&q=60';

}

// Format Price to Cambodian Riel
$price = number_format((float)$product['price'], 0, '.', ',') . ' ៛';

?>

<div class="container py-4">

    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-4">

        <ol class="breadcrumb">

            <li class="breadcrumb-item">
                <a href="index.php"
                   class="text-orange text-decoration-none">
                    ទំព័រដើម
                </a>
            </li>

            <li class="breadcrumb-item">

                <a href="index.php?category=<?= urlencode(strtolower($product['cat_name_en'])) ?>"
                   class="text-orange text-decoration-none">

                    <?= e($product['cat_name_kh']) ?>

                </a>

            </li>

            <li class="breadcrumb-item active">

                <?= e($product['name_kh']) ?>

            </li>

        </ol>

    </nav>


    <!-- Product Detail Card -->
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">

        <div class="row g-0">

            <!-- Product Image -->
            <div class="col-md-6 bg-light text-center p-4 d-flex align-items-center justify-content-center">

                <img
                    src="<?= e($imgPath) ?>"
                    alt="<?= e($product['name_kh']) ?>"
                    class="img-fluid rounded-4 object-fit-cover shadow-sm"
                    style="max-height: 420px; width: 100%;"
                >

            </div>


            <!-- Product Information -->
            <div class="col-md-6 p-4 p-lg-5 d-flex flex-column justify-content-between">

                <div>

                    <!-- Category -->
                    <span class="badge bg-orange text-white mb-2 px-3 py-1">

                        <?= e($product['cat_name_kh']) ?>

                    </span>


                    <!-- Khmer Product Name -->
                    <h2 class="fw-bold mb-3">

                        <?= e($product['name_kh']) ?>

                    </h2>


                    <!-- Price in Cambodian Riel -->
                    <div class="h3 text-orange fw-bold mb-3">

                        <?= $price ?>

                    </div>


                    <!-- Product Description -->
                    <p class="text-muted leading-relaxed mb-4">

                        <?= nl2br(e(
                            $product['description_kh']
                            ?: 'មុខម្ហូបនេះចម្អិនឡើងយ៉ាងយកចិត្តទុកដាក់ ដោយប្រើប្រាស់គ្រឿងផ្សំស្រស់ៗប្រចាំថ្ងៃ។'
                        )) ?>

                    </p>


                    <!-- Stock Status -->
                    <div class="mb-4">

                        <strong>
                            ស្ថានភាពក្នុងស្តុក៖
                        </strong>

                        <?php if ($product['is_available']): ?>

                            <span class="badge bg-success px-3 py-1">

                                <i class="bi bi-check-circle"></i>

                                មានក្នុងស្តុក

                            </span>

                        <?php else: ?>

                            <span class="badge bg-danger px-3 py-1">

                                <i class="bi bi-x-circle"></i>

                                អស់ស្តុកបណ្ដោះអាសន្ន

                            </span>

                        <?php endif; ?>

                    </div>

                </div>


                <!-- Order Section -->
                <div class="pt-3 border-top">

                    <?php if ($product['is_available']): ?>

                        <!-- Telegram Order Button -->
                        <button
                            type="button"
                            class="btn btn-telegram btn-lg w-100 btn-order-telegram mb-3"

                            data-id="<?= (int)$product['product_id'] ?>"

                            data-name="<?= e($product['name_kh']) ?>"

                            data-price="<?= (float)$product['price'] ?>"

                            data-currency="KHR"

                            data-category="<?= e($product['cat_name_kh']) ?>"

                            data-category-slug="<?= e($product['cat_slug']) ?>"

                            data-image="<?= e($imgPath) ?>"
                        >

                            <i class="bi bi-telegram me-2"></i>

                            កុម្ម៉ង់ទិញតាម Telegram ឥឡូវនេះ

                        </button>

                    <?php else: ?>

                        <button
                            class="btn btn-secondary btn-lg w-100 disabled mb-3"
                            disabled
                        >

                            មុខម្ហូបនេះអស់ស្តុកហើយ

                        </button>

                    <?php endif; ?>


                    <!-- Contact Phone -->
                    <div class="text-center">

                        <small class="text-muted d-block mb-1">

                            ឬទូរស័ព្ទកុម្ម៉ង់ផ្ទាល់មកកាន់លេខ៖

                        </small>


                        <div class="fw-bold">

                            <a
                                href="tel:<?= PHONE_1 ?>"
                                class="text-orange text-decoration-none me-3"
                            >

                                <i class="bi bi-telephone-fill"></i>

                                <?= PHONE_1_DISPLAY ?>

                            </a>


                            <a
                                href="tel:<?= PHONE_2 ?>"
                                class="text-orange text-decoration-none"
                            >

                                <i class="bi bi-telephone-fill"></i>

                                <?= PHONE_2_DISPLAY ?>

                            </a>

                        </div>

                    </div>

                </div>

            </div>

        </div>

    </div>

</div>


<?php

// Footer
require_once __DIR__ . '/includes/footer.php';

?>
