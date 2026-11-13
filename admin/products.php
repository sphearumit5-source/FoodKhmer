<?php
// admin/products.php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
requireAdminAuth();

$pdo = getDB();

$stmt = $pdo->query("SELECT p.*, c.name_kh AS cat_name 
                     FROM products p
                     JOIN categories c ON p.category_id = c.category_id
                     ORDER BY p.product_id DESC");
$products = $stmt->fetchAll();

$csrfToken = getCsrfToken();
?>
<!DOCTYPE html>
<html lang="km">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>បញ្ជីផលិតផល - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Kantumruy+Pro:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body class="bg-light">

<div class="container-fluid">
    <div class="row">
        <nav class="col-md-3 col-lg-2 d-md-block admin-sidebar p-3 text-white">
            <div class="d-flex align-items-center gap-2 mb-4 pb-3 border-bottom border-secondary">
                <span class="brand-icon text-orange"><i class="bi bi-cup-hot-fill"></i></span>
                <div>
                    <h6 class="mb-0 fw-bold text-white">ផ្ទាំងគ្រប់គ្រង Admin</h6>
                    <small class="text-muted"><?= e($_SESSION['admin_name']) ?></small>
                </div>
            </div>

            <ul class="nav flex-column mb-auto">
                <li class="nav-item"><a href="dashboard.php" class="nav-link"><i class="bi bi-speedometer2 me-2"></i> ផ្ទាំងគ្រប់គ្រង</a></li>
                <li class="nav-item"><a href="products.php" class="nav-link active"><i class="bi bi-box-seam me-2"></i> មុខម្ហូប/ភេសជ្ជៈ</a></li>
                <li class="nav-item"><a href="add_product.php" class="nav-link"><i class="bi bi-plus-circle me-2"></i> បន្ថែមមុខម្ហូបថ្មី</a></li>
                <li class="nav-item mt-4 pt-3 border-top border-secondary"><a href="../index.php" target="_blank" class="nav-link text-info"><i class="bi bi-box-arrow-up-right me-2"></i> មើលគេហទំព័រ</a></li>
                <li class="nav-item"><a href="logout.php" class="nav-link text-danger"><i class="bi bi-box-arrow-right me-2"></i> ចាកចេញ</a></li>
            </ul>
        </nav>

        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h4 class="fw-bold mb-0">គ្រប់គ្រងមុខម្ហូប និងភេសជ្ជៈ</h4>
                    <small class="text-muted">ចំនួនទំនិញសរុប៖ <?= count($products) ?></small>
                </div>
                <a href="add_product.php" class="btn btn-orange">
                    <i class="bi bi-plus-circle me-1"></i> បន្ថែមមុខម្ហូបថ្មី
                </a>
            </div>

            <?php if (isset($_GET['msg']) && $_GET['msg'] === 'deleted'): ?>
                <div class="alert alert-success alert-dismissible fade show">
                    <i class="bi bi-check-circle-fill me-1"></i> បានលុបទំនិញដោយជោគជ័យ!
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <div class="card border-0 shadow-sm rounded-3">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 70px;">រូបភាព</th>
                                <th>ឈ្មោះម្ហូប (ខ្មែរ)</th>
                                <th>ឈ្មោះជាអង់គ្លេស</th>
                                <th>ប្រភេទ</th>
                                <th>តម្លៃ (៛)</th>
                                <th>ស្ថានភាព</th>
                                <th class="text-end">សកម្មភាព</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($products)): ?>
                                <tr><td colspan="7" class="text-center py-4 text-muted">មិនទាន់មានទំនិញនៅឡើយទេ!</td></tr>
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
                                    ?>
                                    <tr>
                                        <td><img src="<?= e($imgPath) ?>" class="rounded" style="width: 50px; height: 50px; object-fit: cover;"></td>
                                        <td class="fw-bold"><?= e($p['name_kh']) ?></td>
                                        <td><?= e($p['name_en']) ?></td>
                                        <td><span class="badge bg-secondary"><?= e($p['cat_name']) ?></span></td>
                                        <td class="fw-bold text-orange"><?= formatPrice($p['price']) ?></td>
                                        <td>
                                            <?= $p['is_available'] ? '<span class="badge bg-success">មានក្នុងស្តុក</span>' : '<span class="badge bg-danger">អស់ស្តុក</span>' ?>
                                        </td>
                                        <td class="text-end">
                                            <a href="edit_product.php?id=<?= $p['product_id'] ?>" class="btn btn-sm btn-outline-primary me-1" title="កែប្រែ">
                                                <i class="bi bi-pencil-square"></i>
                                            </a>

                                            <form method="POST" action="delete_product.php" class="d-inline" onsubmit="return confirm('តើអ្នកពិតជាចង់លុបទំនិញនេះមែនទេ?');">
                                                <input type="hidden" name="csrf_token" value="<?= e($csrfToken) ?>">
                                                <input type="hidden" name="product_id" value="<?= $p['product_id'] ?>">
                                                <button type="submit" class="btn btn-sm btn-outline-danger" title="លុប">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
