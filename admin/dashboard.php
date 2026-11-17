<?php
// admin/dashboard.php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

requireAdminAuth();

$pdo = getDB();

// ទាញយកប្រភេទមុខម្ហូបទាំងអស់
$categories = $pdo->query("SELECT category_id, name_kh, name_en, slug FROM categories ORDER BY display_order ASC")->fetchAll();

// ទាញយកមុខម្ហូប និងភេសជ្ជៈទាំងអស់
$stmt = $pdo->query("
    SELECT p.*, c.name_kh AS cat_name, c.slug AS cat_slug 
    FROM products p
    JOIN categories c ON p.category_id = c.category_id
    ORDER BY p.is_available DESC, p.product_id DESC
");
$products = $stmt->fetchAll();

// គណនាស្ថិតិ
$totalProducts = count($products);
$inStockCount = 0;
$outOfStockCount = 0;
$featuredCount = 0;

foreach ($products as $p) {
    if (!empty($p['is_available'])) {
        $inStockCount++;
    } else {
        $outOfStockCount++;
    }
    if (!empty($p['is_featured'])) {
        $featuredCount++;
    }
}

$csrfToken = getCsrfToken();
?>
<!DOCTYPE html>
<html lang="km">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ផ្ទាំងគ្រប់គ្រង & គ្រប់គ្រងស្តុក - <?= e(SHOP_NAME) ?></title>
    
    <!-- Bootstrap 5 & Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Kantumruy+Pro:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
    
    <style>
        * {
            font-family: 'Kantumruy Pro', sans-serif;
        }
        body {
            background-color: #f8fafc;
            min-height: 100vh;
        }
        .admin-navbar {
            background: #ffffff;
            border-bottom: 1px solid #e2e8f0;
            box-shadow: 0 2px 10px rgba(0,0,0,0.03);
        }
        .stat-card {
            border: 0;
            border-radius: 18px;
            transition: all 0.25s ease;
            box-shadow: 0 4px 15px rgba(0,0,0,0.04);
            overflow: hidden;
            background: #ffffff;
        }
        .stat-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.08);
        }
        .stat-icon {
            width: 52px;
            height: 52px;
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
        }
        .product-img-thumb {
            width: 58px;
            height: 58px;
            object-fit: cover;
            border-radius: 12px;
            border: 1px solid #e2e8f0;
        }
        .btn-stock-toggle {
            font-size: 0.82rem;
            font-weight: 700;
            padding: 6px 14px;
            border-radius: 30px;
            cursor: pointer;
            transition: all 0.2s ease;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            border: none;
        }
        .btn-stock-toggle:hover {
            opacity: 0.88;
            transform: scale(1.02);
        }
        .btn-stock-in {
            background: #10b981;
            color: #ffffff;
        }
        .btn-stock-out {
            background: #ef4444;
            color: #ffffff;
        }
        .filter-btn.active {
            background: #f97316 !important;
            color: white !important;
            border-color: #f97316 !important;
        }
        .toast-container {
            position: fixed;
            bottom: 24px;
            right: 24px;
            z-index: 1080;
        }
        .table-responsive {
            border-radius: 16px;
        }
        @media (max-width: 768px) {
            .stat-card {
                margin-bottom: 12px;
            }
        }
    </style>
</head>
<body>

<!-- Admin Header Navigation -->
<nav class="navbar navbar-expand-lg admin-navbar sticky-top py-2 py-md-3">
    <div class="container-fluid px-3 px-lg-4">
        <a class="navbar-brand d-flex align-items-center gap-2" href="dashboard.php">
            <span class="brand-icon bg-orange text-white p-2 rounded-3 d-inline-flex">
                <i class="bi bi-shield-lock-fill"></i>
            </span>
            <div>
                <span class="fw-bold text-dark d-block" style="font-size: 1.05rem;"><?= e(SHOP_NAME) ?></span>
                <span class="text-muted small" style="font-size: 0.78rem;">ផ្ទាំងគ្រប់គ្រង & គ្រប់គ្រងស្តុក</span>
            </div>
        </a>

        <button class="navbar-toggler border-0 shadow-none" type="button" data-bs-toggle="collapse" data-bs-target="#adminNav">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="adminNav">
            <ul class="navbar-nav ms-auto align-items-lg-center gap-2 mt-3 mt-lg-0">
                <li class="nav-item">
                    <span class="badge bg-light text-dark border px-3 py-2 rounded-pill small me-lg-2">
                        <i class="bi bi-person-circle text-orange me-1"></i>
                        <?= e($_SESSION['admin_name'] ?? 'Admin') ?>
                    </span>
                </li>
                <li class="nav-item">
                    <a class="btn btn-sm btn-orange text-white fw-bold px-3 py-2 rounded-pill" href="add_product.php">
                        <i class="bi bi-plus-circle me-1"></i> បន្ថែមមុខម្ហូបថ្មី
                    </a>
                </li>
                <li class="nav-item">
                    <a class="btn btn-sm btn-outline-secondary px-3 py-2 rounded-pill" href="../index.php" target="_blank">
                        <i class="bi bi-box-arrow-up-right me-1"></i> មើលមុខហាង
                    </a>
                </li>
                <li class="nav-item">
                    <a class="btn btn-sm btn-outline-danger px-3 py-2 rounded-pill" href="logout.php">
                        <i class="bi bi-box-arrow-right me-1"></i> ចាកចេញ
                    </a>
                </li>
            </ul>
        </div>
    </div>
</nav>

<!-- Main Admin Content -->
<div class="container-fluid px-3 px-lg-4 py-4">

    <!-- Flash Messages -->
    <?php if (isset($_GET['msg'])): ?>
        <?php if ($_GET['msg'] === 'saved'): ?>
            <div class="alert alert-success alert-dismissible fade show rounded-4 shadow-sm" role="alert">
                <i class="bi bi-check-circle-fill me-2 fs-5 align-middle"></i>
                <strong>ជោគជ័យ!</strong> បានរក្សាទុកព័ត៌មានមុខម្ហូបដោយជោគជ័យ។
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php elseif ($_GET['msg'] === 'deleted'): ?>
            <div class="alert alert-success alert-dismissible fade show rounded-4 shadow-sm" role="alert">
                <i class="bi bi-trash-fill me-2 fs-5 align-middle"></i>
                <strong>ជោគជ័យ!</strong> បានលុបមុខម្ហូបចេញពីប្រព័ន្ធរួចរាល់ហើយ។
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php elseif ($_GET['msg'] === 'stock_updated'): ?>
            <div class="alert alert-info alert-dismissible fade show rounded-4 shadow-sm" role="alert">
                <i class="bi bi-arrow-repeat me-2 fs-5 align-middle"></i>
                <strong>ជោគជ័យ!</strong> ស្ថានភាពស្តុកត្រូវបានផ្លាស់ប្តូររួចរាល់។
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
    <?php endif; ?>

    <!-- Summary Statistics Cards -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-lg-3">
            <div class="stat-card p-3 p-md-4">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-muted small fw-semibold">មុខម្ហូប/ភេសជ្ជៈសរុប</span>
                    <div class="stat-icon bg-orange-subtle text-orange">
                        <i class="bi bi-box-seam-fill"></i>
                    </div>
                </div>
                <h3 class="fw-bold mb-0 text-dark" id="statTotal"><?= $totalProducts ?></h3>
                <small class="text-muted">គ្រប់មុខទំនិញក្នុងហាង</small>
            </div>
        </div>

        <div class="col-6 col-lg-3">
            <div class="stat-card p-3 p-md-4">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-muted small fw-semibold">មានក្នុងស្តុក (អាចទិញបាន)</span>
                    <div class="stat-icon bg-success-subtle text-success">
                        <i class="bi bi-check-circle-fill"></i>
                    </div>
                </div>
                <h3 class="fw-bold mb-0 text-success" id="statInStock"><?= $inStockCount ?></h3>
                <small class="text-muted">កំពុងដាក់លក់លើមុខហាង</small>
            </div>
        </div>

        <div class="col-6 col-lg-3">
            <div class="stat-card p-3 p-md-4">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-muted small fw-semibold">ដាច់ស្តុក (អស់ស្តុក)</span>
                    <div class="stat-icon bg-danger-subtle text-danger">
                        <i class="bi bi-x-circle-fill"></i>
                    </div>
                </div>
                <h3 class="fw-bold mb-0 text-danger" id="statOutOfStock"><?= $outOfStockCount ?></h3>
                <small class="text-muted">បានបិទការកុម្ម៉ង់បណ្ដោះអាសន្ន</small>
            </div>
        </div>

        <div class="col-6 col-lg-3">
            <div class="stat-card p-3 p-md-4">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-muted small fw-semibold">មុខម្ហូបពិសេស (Featured)</span>
                    <div class="stat-icon bg-warning-subtle text-warning">
                        <i class="bi bi-star-fill"></i>
                    </div>
                </div>
                <h3 class="fw-bold mb-0 text-warning"><?= $featuredCount ?></h3>
                <small class="text-muted">បង្ហាញលើទំព័រដើម</small>
            </div>
        </div>
    </div>

    <!-- Management & Control Bar -->
    <div class="card border-0 shadow-sm rounded-4 p-3 p-md-4 mb-4 bg-white">
        <div class="row g-3 align-items-center">
            <!-- Search Input -->
            <div class="col-12 col-md-4">
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0">
                        <i class="bi bi-search text-muted"></i>
                    </span>
                    <input type="text" id="adminSearchInput" class="form-control bg-light border-start-0" placeholder="ស្វែងរកតាមឈ្មោះមុខម្ហូប ឬភេសជ្ជៈ...">
                </div>
            </div>

            <!-- Category Filter -->
            <div class="col-6 col-md-3">
                <select id="adminCategoryFilter" class="form-select bg-light">
                    <option value="all">-- គ្រប់ប្រភេទទាំងអស់ --</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= e($cat['slug']) ?>"><?= e($cat['name_kh']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Stock Status Buttons -->
            <div class="col-6 col-md-5 d-flex justify-content-md-end gap-1 flex-wrap">
                <button type="button" class="btn btn-sm btn-outline-secondary filter-btn active rounded-pill px-3 py-2 fw-semibold" data-stock="all">
                    ទាំងអស់ (<span id="btnCountAll"><?= $totalProducts ?></span>)
                </button>
                <button type="button" class="btn btn-sm btn-outline-success filter-btn rounded-pill px-3 py-2 fw-semibold" data-stock="in_stock">
                    <i class="bi bi-check-circle me-1"></i> មានស្តុក (<span id="btnCountInStock"><?= $inStockCount ?></span>)
                </button>
                <button type="button" class="btn btn-sm btn-outline-danger filter-btn rounded-pill px-3 py-2 fw-semibold" data-stock="out_of_stock">
                    <i class="bi bi-x-circle me-1"></i> ដាច់ស្តុក (<span id="btnCountOutOfStock"><?= $outOfStockCount ?></span>)
                </button>
            </div>
        </div>
    </div>

    <!-- Products & Stock Management Table -->
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden bg-white">
        <div class="card-header bg-white py-3 px-3 px-md-4 border-bottom d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center gap-2">
                <i class="bi bi-boxes text-orange fs-5"></i>
                <h5 class="fw-bold mb-0 text-dark">បញ្ជីមុខម្ហូប & គ្រប់គ្រងស្តុក</h5>
            </div>
            <span class="badge bg-light text-muted border px-2 py-1">
                បង្ហាញ <span id="visibleCount"><?= $totalProducts ?></span> ក្នុងចំណោម <?= $totalProducts ?>
            </span>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width: 80px;" class="text-center">រូបភាព</th>
                        <th>ឈ្មោះមុខម្ហូប / ភេសជ្ជៈ</th>
                        <th>ប្រភេទ</th>
                        <th>តម្លៃ (៛)</th>
                        <th class="text-center" style="min-width: 170px;">
                            <i class="bi bi-boxes me-1"></i> គ្រប់គ្រងស្តុក
                        </th>
                        <th class="text-center" style="width: 100px;">ពិសេស</th>
                        <th class="text-end" style="min-width: 160px;">សកម្មភាព</th>
                    </tr>
                </thead>
                <tbody id="productsTableBody">
                    <?php if (empty($products)): ?>
                        <tr id="noProductsRow">
                            <td colspan="7" class="text-center py-5 text-muted">
                                <i class="bi bi-inbox fs-1 d-block mb-2 text-secondary"></i>
                                មិនទាន់មានទិន្នន័យមុខម្ហូបនៅឡើយទេ! សូមចុចប៊ូតុង "បន្ថែមមុខម្ហូបថ្មី"
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($products as $p): ?>
                            <?php 
                                $img = $p['image'] ?? '';
                                if (strpos($img, 'http') === 0) {
                                    $imgPath = $img;
                                } elseif (!empty($img) && file_exists(__DIR__ . '/../uploads/' . $img)) {
                                    $imgPath = '../uploads/' . $img;
                                } else {
                                    $imgPath = 'https://images.unsplash.com/photo-1546069901-ba9599a7e63c?w=100&auto=format&fit=crop&q=60';
                                }
                                $isAvailable = !empty($p['is_available']);
                                $isFeatured = !empty($p['is_featured']);
                            ?>
                            <tr class="product-row" 
                                id="row-<?= $p['product_id'] ?>"
                                data-name-kh="<?= strtolower(e($p['name_kh'])) ?>"
                                data-name-en="<?= strtolower(e($p['name_en'])) ?>"
                                data-category="<?= e($p['cat_slug']) ?>"
                                data-stock="<?= $isAvailable ? 'in_stock' : 'out_of_stock' ?>">
                                
                                <!-- Product Image -->
                                <td class="text-center">
                                    <img src="<?= e($imgPath) ?>" alt="<?= e($p['name_kh']) ?>" class="product-img-thumb" loading="lazy">
                                </td>

                                <!-- Product Names -->
                                <td>
                                    <div class="fw-bold text-dark" style="font-size: 0.98rem;"><?= e($p['name_kh']) ?></div>
                                    <small class="text-muted d-block"><?= e($p['name_en']) ?></small>
                                </td>

                                <!-- Category Badge -->
                                <td>
                                    <span class="badge bg-light text-dark border px-2 py-1 rounded-pill">
                                        <?= e($p['cat_name']) ?>
                                    </span>
                                </td>

                                <!-- Price in KHR -->
                                <td>
                                    <span class="fw-bold text-orange" style="font-size: 1.05rem;">
                                        <?= formatPrice($p['price']) ?>
                                    </span>
                                </td>

                                <!-- Stock Management Toggle Button (1-Click Instant AJAX) -->
                                <td class="text-center">
                                    <button type="button" 
                                            class="btn-stock-toggle <?= $isAvailable ? 'btn-stock-in' : 'btn-stock-out' ?>"
                                            id="stock-btn-<?= $p['product_id'] ?>"
                                            data-id="<?= $p['product_id'] ?>"
                                            data-name="<?= e($p['name_kh']) ?>"
                                            title="ចុចដើម្បីប្ដូរស្ថានភាពស្តុក">
                                        <i class="bi <?= $isAvailable ? 'bi-check-circle-fill' : 'bi-x-circle-fill' ?>"></i>
                                        <span class="stock-text"><?= $isAvailable ? 'មានក្នុងស្តុក' : 'ដាច់ស្តុក' ?></span>
                                    </button>
                                </td>

                                <!-- Featured Status -->
                                <td class="text-center">
                                    <?php if ($isFeatured): ?>
                                        <span class="text-warning" title="មុខម្ហូបពិសេស"><i class="bi bi-star-fill fs-5"></i></span>
                                    <?php else: ?>
                                        <span class="text-muted opacity-25"><i class="bi bi-star fs-5"></i></span>
                                    <?php endif; ?>
                                </td>

                                <!-- Action Buttons: Edit and Delete -->
                                <td class="text-end">
                                    <div class="d-inline-flex gap-1">
                                        <!-- Edit Button -->
                                        <a href="edit_product.php?id=<?= $p['product_id'] ?>" 
                                           class="btn btn-sm btn-outline-primary px-3 rounded-pill fw-semibold" 
                                           title="កែប្រែព័ត៌មានមុខម្ហូប">
                                            <i class="bi bi-pencil-square me-1"></i> កែប្រែ
                                        </a>

                                        <!-- Delete Button with Modal Trigger -->
                                        <button type="button" 
                                                class="btn btn-sm btn-outline-danger px-3 rounded-pill fw-semibold btn-open-delete" 
                                                data-id="<?= $p['product_id'] ?>" 
                                                data-name="<?= e($p['name_kh']) ?>"
                                                title="លុបមុខម្ហូបនេះ">
                                            <i class="bi bi-trash me-1"></i> លុប
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteConfirmModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 440px;">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-body p-4 text-center">
                <div class="bg-danger-subtle text-danger rounded-circle p-3 d-inline-flex mb-3">
                    <i class="bi bi-exclamation-triangle-fill" style="font-size: 2.2rem;"></i>
                </div>
                <h5 class="fw-bold text-dark mb-2" id="deleteModalLabel">បញ្ជាក់ការលុបទំនិញ</h5>
                <p class="text-muted small mb-3">
                    តើអ្នកពិតជាចង់លុបទំនិញ <strong class="text-danger" id="deleteItemName"></strong> នេះចេញពីប្រព័ន្ធមែនទេ?
                </p>
                <div class="p-2 px-3 bg-light rounded-3 small text-muted text-start mb-4">
                    <i class="bi bi-info-circle text-primary me-1"></i>
                    ចំណាំ៖ នៅពេលលុបរួច ទិន្នន័យមុខម្ហូបនេះនឹងមិនអាចត្រឡប់មកវិញបានឡើយ។
                </div>

                <form method="POST" action="delete_product.php" id="deleteForm">
                    <input type="hidden" name="csrf_token" value="<?= e($csrfToken) ?>">
                    <input type="hidden" name="product_id" id="deleteProductId" value="">
                    
                    <div class="d-flex gap-2 justify-content-center">
                        <button type="button" class="btn btn-light px-4 rounded-pill fw-semibold" data-bs-dismiss="modal">
                            បោះបង់
                        </button>
                        <button type="submit" class="btn btn-danger px-4 rounded-pill fw-bold" id="btnConfirmDelete">
                            <i class="bi bi-trash me-1"></i> បាទ/ចាស លុបចេញ
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Toast Container for Notifications -->
<div class="toast-container">
    <div id="liveToast" class="toast align-items-center text-white bg-dark border-0 rounded-4 shadow-lg" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="d-flex">
            <div class="toast-body d-flex align-items-center gap-2" id="toastMessage">
                <i class="bi bi-check-circle-fill text-success fs-5"></i>
                <span>បានកែប្រែដោយជោគជ័យ!</span>
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
const CSRF_TOKEN = "<?= e($csrfToken) ?>";

// Toast helper
const toastEl = document.getElementById('liveToast');
const toastMessageEl = document.getElementById('toastMessage');
const toastObj = new bootstrap.Toast(toastEl, { delay: 3000 });

function showToast(message, isSuccess = true) {
    const iconClass = isSuccess ? 'bi-check-circle-fill text-success' : 'bi-exclamation-triangle-fill text-danger';
    toastMessageEl.innerHTML = `<i class="bi ${iconClass} fs-5"></i> <span>${message}</span>`;
    toastObj.show();
}

// 1. Live Filter and Search
const searchInput = document.getElementById('adminSearchInput');
const categoryFilter = document.getElementById('adminCategoryFilter');
const filterBtns = document.querySelectorAll('.filter-btn');
const rows = document.querySelectorAll('.product-row');
const visibleCountEl = document.getElementById('visibleCount');

let currentStockFilter = 'all';

function applyFilters() {
    const query = searchInput.value.toLowerCase().trim();
    const selectedCategory = categoryFilter.value;
    let visibleCount = 0;

    rows.forEach(row => {
        const nameKh = row.getAttribute('data-name-kh') || '';
        const nameEn = row.getAttribute('data-name-en') || '';
        const category = row.getAttribute('data-category') || '';
        const stock = row.getAttribute('data-stock') || '';

        const matchQuery = !query || nameKh.includes(query) || nameEn.includes(query);
        const matchCategory = selectedCategory === 'all' || category === selectedCategory;
        const matchStock = currentStockFilter === 'all' || stock === currentStockFilter;

        if (matchQuery && matchCategory && matchStock) {
            row.style.display = '';
            visibleCount++;
        } else {
            row.style.display = 'none';
        }
    });

    visibleCountEl.textContent = visibleCount;
}

searchInput.addEventListener('input', applyFilters);
categoryFilter.addEventListener('change', applyFilters);

filterBtns.forEach(btn => {
    btn.addEventListener('click', () => {
        filterBtns.forEach(b => b.classList.remove('active'));
        btn.classList.add('active');
        currentStockFilter = btn.getAttribute('data-stock');
        applyFilters();
    });
});

// 2. Instant 1-Click Stock Toggle (AJAX)
document.querySelectorAll('.btn-stock-toggle').forEach(btn => {
    btn.addEventListener('click', async function() {
        const productId = this.getAttribute('data-id');
        const productName = this.getAttribute('data-name');
        
        // Show spinning indicator
        const originalHtml = this.innerHTML;
        this.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status"></span> កំពុងកែប្រែ...';
        this.disabled = true;

        try {
            const formData = new FormData();
            formData.append('product_id', productId);
            formData.append('csrf_token', CSRF_TOKEN);
            formData.append('ajax', '1');

            const res = await fetch('toggle_stock.php', {
                method: 'POST',
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                body: formData
            });

            const data = await res.json();
            if (data.success) {
                const row = document.getElementById(`row-${productId}`);
                if (data.is_available) {
                    this.className = 'btn-stock-toggle btn-stock-in';
                    this.innerHTML = '<i class="bi bi-check-circle-fill"></i> <span class="stock-text">មានក្នុងស្តុក</span>';
                    if (row) row.setAttribute('data-stock', 'in_stock');
                } else {
                    this.className = 'btn-stock-toggle btn-stock-out';
                    this.innerHTML = '<i class="bi bi-x-circle-fill"></i> <span class="stock-text">ដាច់ស្តុក</span>';
                    if (row) row.setAttribute('data-stock', 'out_of_stock');
                }

                // Update Counters
                document.getElementById('statInStock').textContent = data.count_in_stock;
                document.getElementById('statOutOfStock').textContent = data.count_out_of_stock;
                document.getElementById('btnCountInStock').textContent = data.count_in_stock;
                document.getElementById('btnCountOutOfStock').textContent = data.count_out_of_stock;

                showToast(`បានប្ដូរស្ថានភាពស្តុក "${productName}" ទៅជា "${data.status_text}"!`);
                applyFilters();
            } else {
                this.innerHTML = originalHtml;
                showToast(data.error || 'មានបញ្ហាក្នុងការកែប្រែស្តុក', false);
            }
        } catch (err) {
            this.innerHTML = originalHtml;
            showToast('មិនអាចភ្ជាប់ទៅកាន់ Server បានទេ', false);
        } finally {
            this.disabled = false;
        }
    });
});

// 3. Delete Modal Trigger
const deleteModal = new bootstrap.Modal(document.getElementById('deleteConfirmModal'));
const deleteItemNameEl = document.getElementById('deleteItemName');
const deleteProductIdInput = document.getElementById('deleteProductId');

document.querySelectorAll('.btn-open-delete').forEach(btn => {
    btn.addEventListener('click', function() {
        const id = this.getAttribute('data-id');
        const name = this.getAttribute('data-name');

        deleteProductIdInput.value = id;
        deleteItemNameEl.textContent = `"${name}"`;
        deleteModal.show();
    });
});
</script>

</body>
</html>
