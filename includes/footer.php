<?php
// includes/footer.php
?>
<footer class="footer-section pt-5 pb-4 mt-5" id="contact">
    <div class="container">
        <div class="row g-4 pb-4 border-bottom border-secondary border-opacity-25">
            <div class="col-lg-4 col-md-6">
                <div class="d-flex align-items-center gap-2 mb-3">
                    <span class="brand-icon"><i class="bi bi-cup-hot-fill"></i></span>
                    <div>
                        <h5 class="mb-0 text-white fw-bold"><?= SHOP_NAME ?></h5>
                        <small class="text-warning"><?= SHOP_NAME_KH ?></small>
                    </div>
                </div>
                <p class="small text-white-50">
                    សេវាកម្មលក់ម្ហូប និងភេសជ្ជៈស្រស់ៗ អនាម័យខ្ពស់ ដឹកជញ្ជូនរហ័សទាន់ចិត្តដល់គេហដ្ឋានបងប្អូនក្នុងភូមិ និងឃុំយើង។
                </p>
                <div class="d-flex gap-2">
                    <span class="badge bg-orange px-3 py-2" style="border-radius:20px;"><i class="bi bi-check-circle me-1"></i> ធ្វើថ្មីៗរាល់ថ្ងៃ</span>
                    <span class="badge bg-secondary px-3 py-2" style="border-radius:20px;"><i class="bi bi-bicycle me-1"></i> ដឹកជញ្ជូនរហ័ស</span>
                </div>
            </div>

            <div class="col-lg-4 col-md-6">
                <h6 class="text-white fw-bold mb-3"><i class="bi bi-telephone-outbound text-orange me-2"></i>លេខទូរស័ព្ទទំនាក់ទំនង</h6>
                <ul class="list-unstyled small text-white-50">
                    <li class="mb-2">
                        <i class="bi bi-phone text-orange me-2"></i> Metfone: 
                        <a href="tel:<?= PHONE_1 ?>" class="text-white fw-bold text-decoration-none"><?= PHONE_1_DISPLAY ?></a>
                    </li>
                    <li class="mb-2">
                        <i class="bi bi-phone text-orange me-2"></i> Smart: 
                        <a href="tel:<?= PHONE_2 ?>" class="text-white fw-bold text-decoration-none"><?= PHONE_2_DISPLAY ?></a>
                    </li>
                    <li class="mb-2">
                        <i class="bi bi-telegram text-info me-2"></i> Telegram: 
                        <a href="https://t.me/<?= ltrim(TELEGRAM_USERNAME, '@') ?>" target="_blank" class="text-info fw-bold text-decoration-none">
                            @<?= ltrim(TELEGRAM_USERNAME, '@') ?> (ឆាតផ្ទាល់)
                        </a>
                    </li>
                </ul>
            </div>

            <div class="col-lg-4 col-md-12">
                <h6 class="text-white fw-bold mb-3"><i class="bi bi-clock-history text-orange me-2"></i>ម៉ោងបើកលក់</h6>
                <p class="text-white-50 small mb-1">ច័ន្ទ ដល់ អាទិត្យ (បើកលក់រាល់ថ្ងៃ)</p>
                <p class="text-white fw-bold">៦:៣០ ព្រឹក ដល់ ៩:០០ យប់</p>
                <p class="text-white-50 small">ទទួលរៀបចំម្ហូប និងភេសជ្ជៈសម្រាប់កម្មវិធីជួបជុំគ្រួសារក្នុងភូមិផងដែរ។</p>
            </div>
        </div>

        <div class="d-flex flex-wrap justify-content-between align-items-center pt-3 small text-white-50">
            <p class="mb-0">&copy; <?= date('Y') ?> <strong><?= SHOP_NAME ?></strong>. រក្សាសិទ្ធិគ្រប់យ៉ាង។</p>
            <p class="mb-0">Powered by PHP & Neon PostgreSQL</p>
        </div>
    </div>
</footer>

<!-- Telegram Order Modal (Premium UI) -->
<div class="modal fade" id="orderModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header">
                <h5 class="modal-title fw-bold text-white d-flex align-items-center gap-2">
                    <i class="bi bi-telegram"></i> បញ្ជាទិញតាម Telegram
                </h5>
                <button type="button" class="btn-close btn-close-white shadow-none" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <div class="d-flex align-items-center gap-3 p-3 bg-light rounded-4 mb-3 border">
                    <img id="modalProductImage" src="" alt="Product" class="rounded-3 object-fit-cover shadow-sm" style="width: 76px; height: 76px;">
                    <div>
                        <h6 class="fw-bold mb-1 text-dark" id="modalProductName"></h6>
                        <span class="badge bg-orange text-white" id="modalProductCategory"></span>
                        <div class="h5 text-orange fw-bold mt-1 mb-0" id="modalProductPrice"></div>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold small text-muted">ចំនួនកុម្ម៉ង់ (QUANTITY)៖</label>
                    <div class="d-flex align-items-center gap-2">
                        <button class="btn btn-outline-secondary qty-control-btn" type="button" id="btnQtyMinus"><i class="bi bi-dash-lg"></i></button>
                        <input type="number" id="orderQty" class="form-control text-center fw-bold fs-5" value="1" min="1" max="50" style="max-width: 90px; border-radius: 12px;">
                        <button class="btn btn-outline-secondary qty-control-btn" type="button" id="btnQtyPlus"><i class="bi bi-plus-lg"></i></button>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold small text-muted">ចំណាំបន្ថែម (SPECIAL NOTE)៖</label>
                    <textarea id="orderNote" class="form-control rounded-3" rows="2" placeholder="ឧ. មិនដាក់ហិរ, ទឹកកកច្រើន, ដាក់ថង់ដាច់ដោយឡែក..."></textarea>
                </div>

                <div class="d-flex justify-content-between align-items-center p-3 rounded-4 mb-3" style="background:#fff7ed; border:1px solid #fed7aa;">
                    <span class="fw-bold text-dark">តម្លៃសរុប (Total)៖</span>
                    <span class="h3 text-orange fw-bold mb-0" id="modalTotalPrice">$0.00</span>
                </div>

                <div class="p-2 px-3 bg-light border rounded-3 small text-muted">
                    <i class="bi bi-info-circle-fill text-primary me-1"></i>
                    នៅពេលចុចខាងក្រោម ប្រព័ន្ធនឹងបើកកម្មវិធី <strong>Telegram</strong> ដោយសរសេរសារកុម្ម៉ង់ជូនរួចជាស្រេច!
                </div>
            </div>
            <div class="modal-footer bg-light border-0">
                <button type="button" class="btn btn-light px-3 fw-semibold" data-bs-dismiss="modal">បោះបង់</button>
                <button type="button" class="btn btn-telegram px-4 fw-bold" id="btnConfirmTelegram">
                    <i class="bi bi-telegram me-1"></i> ផ្ញើសារកុម្ម៉ង់ឥឡូវនេះ
                </button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    const TELEGRAM_USERNAME = "<?= TELEGRAM_USERNAME ?>";
    const SHOP_NAME = "<?= SHOP_NAME ?>";
</script>
<script src="assets/js/app.js"></script>
</body>
</html>
