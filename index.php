
<?php
// index.php

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/header.php';

$pdo = getDB();

// Selected Category
$selectedCategory = isset($_GET['category'])
    ? trim($_GET['category'])
    : 'all';

// Search Query
$searchQuery = isset($_GET['search'])
    ? trim($_GET['search'])
    : '';


// Get Categories
$catStmt = $pdo->query("
    SELECT category_id, name_kh, name_en, slug
    FROM categories
    ORDER BY display_order ASC
");

$categories = $catStmt->fetchAll(PDO::FETCH_ASSOC);


// Get Products
$sql = "
    SELECT
        p.*,
        c.name_kh AS cat_name_kh,
        c.name_en AS cat_name_en,
        c.slug AS cat_slug
    FROM products p
    JOIN categories c
        ON p.category_id = c.category_id
    WHERE 1=1
";

$params = [];


// Filter by Category
if ($selectedCategory !== 'all') {

    $sql .= " AND c.slug = :category_slug";

    $params[':category_slug'] = $selectedCategory;

}


// Search Products
if (!empty($searchQuery)) {

    $sql .= "
        AND (
            p.name_kh LIKE :q1
            OR p.name_en LIKE :q2
            OR p.description_kh LIKE :q3
        )
    ";

    $params[':q1'] = '%' . $searchQuery . '%';
    $params[':q2'] = '%' . $searchQuery . '%';
    $params[':q3'] = '%' . $searchQuery . '%';

}


// Sort Products
$sql .= "
    ORDER BY
        p.is_available DESC,
        p.is_featured DESC,
        p.product_id DESC
";

$stmt = $pdo->prepare($sql);

$stmt->execute($params);

$products = $stmt->fetchAll(PDO::FETCH_ASSOC);


// Category Icons
$catIcons = [
    'food'    => '🍲',
    'drinks'  => '🧋',
    'snacks'  => '🍟',
    'alcohol' => '🍺'
];

?>

<!-- ==================================
     HERO BANNER SECTION
=================================== -->

<section class="py-4">

    <div class="container">

        <div class="hero-wrapper">

            <div class="row align-items-center g-4">

                <div class="col-lg-8">

                    <span class="hero-tag">

                        <i class="bi bi-lightning-charge-fill text-warning"></i>

                        សេវាដឹកជូនដល់ផ្ទះរហ័សទាន់ចិត្ត

                    </span>


                    <h1 class="hero-title mb-3">

                        មុខម្ហូបឈ្ងុយឆ្ងាញ់ & ភេសជ្ជៈត្រជាក់ស្រួល

                    </h1>


                    <p class="hero-desc mb-4">

                        ធ្វើថ្មីៗស្រស់ៗរាល់ថ្ងៃ អនាម័យខ្ពស់ តម្លៃសមរម្យ។
                        ងាយស្រួលកុម្ម៉ង់ទិញបានភ្លាមៗតាម
                        <strong>Telegram</strong>
                        ឬទូរស័ព្ទមកហាងផ្ទាល់!

                    </p>


                    <div class="d-flex flex-wrap gap-3">

                        <!-- Telegram -->
                        <a
                            href="https://t.me/<?= e(ltrim(TELEGRAM_USERNAME, '@')) ?>"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="btn btn-telegram text-white"
                            style="border-radius:12px; font-weight:700;"
                        >

                            <i class="bi bi-telegram me-2"></i>

                            Telegram: @<?= e(ltrim(TELEGRAM_USERNAME, '@')) ?>

                        </a>


                        <!-- Phone 1 -->
                        <a
                            href="tel:<?= e(PHONE_1) ?>"
                            class="btn btn-orange"
                        >

                            <i class="bi bi-telephone-fill me-2"></i>

                            <?= e(PHONE_1_DISPLAY) ?>

                        </a>


                        <!-- Phone 2 -->
                        <a
                            href="tel:<?= e(PHONE_2) ?>"
                            class="btn btn-outline-dark"
                            style="border-radius:12px; font-weight:700;"
                        >

                            <i class="bi bi-telephone-fill me-2"></i>

                            <?= e(PHONE_2_DISPLAY) ?>

                        </a>

                    </div>

                </div>


                <!-- Hero Icon -->
                <div class="col-lg-4 text-center d-none d-lg-block">

                    <div style="
                        font-size: 7.5rem;
                        filter: drop-shadow(0 15px 25px rgba(255,107,0,0.3));
                        transform: rotate(-5deg);
                    ">

                        🍜

                    </div>

                </div>

            </div>

        </div>

    </div>

</section>



<!-- ==================================
     MENU & PRODUCTS SECTION
=================================== -->

<section class="py-4" id="menu">

    <div class="container">


        <!-- Search & Category Filter -->
        <div class="row g-3 align-items-center mb-4">


            <!-- Search -->
            <div class="col-lg-4">

                <form
                    method="GET"
                    action="index.php"
                    class="search-wrapper d-flex align-items-center"
                >

                    <input
                        type="hidden"
                        name="category"
                        value="<?= e($selectedCategory) ?>"
                    >

                    <i class="bi bi-search text-muted ms-2 me-2"></i>

                    <input
                        type="text"
                        name="search"
                        id="searchInput"
                        class="form-control border-0 shadow-none ps-0"
                        placeholder="ស្វែងរកមុខម្ហូប ឬភេសជ្ជៈ..."
                        value="<?= e($searchQuery) ?>"
                    >

                    <button
                        type="submit"
                        class="btn btn-orange btn-sm me-2"
                    >

                        ស្វែងរក

                    </button>

                </form>

            </div>


            <!-- Category Filter -->
            <div class="col-lg-8">

                <div class="category-filter-wrap justify-content-lg-end">

                    <!-- All Category -->
                    <a
                        href="index.php?category=all#menu"
                        class="category-pill <?= $selectedCategory === 'all' ? 'active' : '' ?>"
                    >

                        ✨ ទាំងអស់ (All)

                    </a>


                    <!-- Categories -->
                    <?php foreach ($categories as $cat): ?>

                        <?php

                        $icon = $catIcons[strtolower($cat['slug'])] ?? '🍴';

                        ?>

                        <a
                            href="index.php?category=<?= urlencode($cat['slug']) ?>#menu"
                            class="category-pill <?= $selectedCategory === $cat['slug'] ? 'active' : '' ?>"
                        >

                            <?= $icon ?>

                            <?= e($cat['name_kh']) ?>

                            (<?= e($cat['name_en']) ?>)

                        </a>

                    <?php endforeach; ?>

                </div>

            </div>

        </div>



        <!-- ==================================
             PRODUCTS GRID
        =================================== -->


        <?php if (empty($products)): ?>


            <!-- Empty Products -->
            <div class="text-center py-5 bg-white rounded-4 shadow-sm my-4 border">

                <div style="font-size: 3.5rem;">

                    🍽️

                </div>


                <h5 class="fw-bold text-dark mt-2">

                    មិនមានមុខម្ហូប ឬភេសជ្ជៈក្នុងប្រភេទនេះទេ!

                </h5>


                <p class="text-muted small">

                    សូមជ្រើសរើសប្រភេទផ្សេង
                    ឬចុចមើលមុខម្ហូបទាំងអស់ឡើងវិញ។

                </p>


                <a
                    href="index.php#menu"
                    class="btn btn-orange btn-sm mt-1"
                >

                    មើលមុខម្ហូបទាំងអស់

                </a>

            </div>


        <?php else: ?>


            <!-- Products -->
            <div
                class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-4 g-4"
                id="productsGrid"
            >


                <?php foreach ($products as $p): ?>


                    <?php

                    // Product Image
                    $img = $p['image'] ?? '';

                    if (strpos($img, 'http') === 0) {

                        $imgPath = $img;

                    } elseif (
                        !empty($img) &&
                        file_exists(__DIR__ . '/uploads/' . $img)
                    ) {

                        $imgPath = 'uploads/' . $img;

                    } else {

                        $imgPath = 'https://images.unsplash.com/photo-1546069901-ba9599a7e63c?w=500&auto=format&fit=crop&q=80';

                    }


                    // Cambodian Riel Price
                    $price = number_format(
                        (float)$p['price'],
                        0,
                        '.',
                        ','
                    ) . ' ៛';

                    ?>


                    <div
                        class="col product-card-wrapper"
                        data-name="<?= e(strtolower($p['name_kh'] . ' ' . $p['name_en'])) ?>"
                    >


                        <div class="product-card">


                            <!-- Product Image -->
                            <div class="product-img-box">

                                <img
                                    src="<?= e($imgPath) ?>"
                                    alt="<?= e($p['name_kh']) ?>"
                                    class="product-img"
                                    loading="lazy"
                                >


                                <!-- Category Badge -->
                                <span class="badge-cat">

                                    <?= e($p['cat_name_kh']) ?>

                                </span>


                                <!-- Stock Status -->
                                <?php if ($p['is_available']): ?>

                                    <span class="badge-stock bg-success text-white">

                                        <i class="bi bi-check-circle-fill me-1"></i>

                                        មានក្នុងស្តុក

                                    </span>

                                <?php else: ?>

                                    <span class="badge-stock bg-danger text-white">

                                        <i class="bi bi-x-circle-fill me-1"></i>

                                        អស់ស្តុក

                                    </span>

                                <?php endif; ?>


                                <!-- Featured Badge -->
                                <?php if ($p['is_featured']): ?>

                                    <span class="badge bg-warning text-dark position-absolute top-0 end-0 m-2">

                                        <i class="bi bi-star-fill"></i>

                                        ពិសេស

                                    </span>

                                <?php endif; ?>


                            </div>



                            <!-- Product Information -->
                            <div class="product-body">


                                <!-- Khmer Name -->
                                <h3 class="product-title">

                                    <?= e($p['name_kh']) ?>

                                </h3>


                                <!-- English Name -->
                                <div class="product-subtitle">

                                    <?= e($p['name_en']) ?>

                                </div>


                                <!-- Description -->
                                <p class="product-desc">

                                    <?= e(
                                        $p['description_kh']
                                        ?: 'ម្ហូបឆ្ងាញ់ អនាម័យខ្ពស់ ធ្វើថ្មីៗប្រចាំថ្ងៃ'
                                    ) ?>

                                </p>



                                <!-- Price & Order -->
                                <div class="d-flex justify-content-between align-items-center mt-auto pt-3 border-top">


                                    <!-- Price in KHR -->
                                    <div>

                                        <small
                                            class="text-muted d-block"
                                            style="font-size: 0.72rem;"
                                        >

                                            តម្លៃ / Price

                                        </small>


                                        <span class="product-price">

                                            <?= $price ?>

                                        </span>

                                    </div>



                                    <!-- Action Buttons -->
                                    <div class="d-flex gap-2">


                                        <!-- View Details -->
                                        <a
                                            href="product.php?id=<?= (int)$p['product_id'] ?>"
                                            class="btn btn-sm btn-outline-secondary"
                                            style="border-radius:10px;"
                                            title="មើលលម្អិត"
                                        >

                                            <i class="bi bi-eye"></i>

                                        </a>



                                        <?php if ($p['is_available']): ?>


                                            <!-- Telegram Order -->
                                            <button
                                                type="button"
                                                class="btn btn-sm btn-telegram btn-order-telegram"

                                                data-id="<?= (int)$p['product_id'] ?>"

                                                data-name="<?= e($p['name_kh']) ?>"

                                                data-price="<?= (float)$p['price'] ?>"

                                                data-currency="KHR"

                                                data-category="<?= e($p['cat_name_kh']) ?>"

                                                data-image="<?= e($imgPath) ?>"
                                            >

                                                <i class="bi bi-telegram"></i>

                                                Order

                                            </button>


                                        <?php else: ?>


                                            <!-- Out of Stock -->
                                            <button
                                                type="button"
                                                class="btn btn-sm btn-secondary disabled"
                                                style="border-radius:10px;"
                                                disabled
                                            >

                                                អស់ស្តុក

                                            </button>


                                        <?php endif; ?>


                                    </div>

                                </div>


                            </div>

                        </div>

                    </div>


                <?php endforeach; ?>


            </div>


        <?php endif; ?>


    </div>

</section>



<?php

// Footer
require_once __DIR__ . '/includes/footer.php';

?>
