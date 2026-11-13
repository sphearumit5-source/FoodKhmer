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
            <p class="mb-0">ហាងលក់ម្ហូបអាហារ និងភេសជ្ជៈខ្មែរ</p>
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
                    <label class="form-label fw-bold small text-muted">ចំនួនកុម្ម៉ង់ ៖</label>
                    <div class="d-flex align-items-center gap-2">
                        <button class="btn btn-outline-secondary qty-control-btn" type="button" id="btnQtyMinus"><i class="bi bi-dash-lg"></i></button>
                        <input type="number" id="orderQty" class="form-control text-center fw-bold fs-5" value="1" min="1" max="50" style="max-width: 90px; border-radius: 12px;">
                        <button class="btn btn-outline-secondary qty-control-btn" type="button" id="btnQtyPlus"><i class="bi bi-plus-lg"></i></button>
                    </div>
                </div>

                <!-- ជម្រើសរសជាតិសម្រាប់ម្ហូបអាហារ & អាហារសម្រន់ (ហិរ, ផ្អែម, ប្រៃ) -->
                <div id="tasteOptionsWrap" class="p-3 bg-light rounded-4 border mb-3">
                    <div class="small fw-bold text-orange mb-2">
                        <i class="bi bi-sliders me-1"></i> កំណត់រសជាតិម្ហូប ៖
                    </div>

                    <!-- កម្រិតហិរ -->
                    <div class="taste-option-title">
                        <i class="bi bi-fire text-danger"></i> កម្រិតជាតិហិរ ៖
                    </div>
                    <div class="taste-chip-group">
                        <label class="taste-chip">
                            <input type="radio" name="spice_level" value="មិនហិរ" checked>
                            <span>មិនហិរ</span>
                        </label>
                        <label class="taste-chip">
                            <input type="radio" name="spice_level" value="ហិរតិច">
                            <span>ហិរតិច</span>
                        </label>
                        <label class="taste-chip">
                            <input type="radio" name="spice_level" value="ហិរល្មម">
                            <span>ហិរល្មម</span>
                        </label>
                        <label class="taste-chip">
                            <input type="radio" name="spice_level" value="ហិរខ្លាំង">
                            <span>ហិរខ្លាំង</span>
                        </label>
                    </div>

                    <!-- កម្រិតផ្អែម -->
                    <div class="taste-option-title">
                        <i class="bi bi-cup-straw text-primary"></i> កម្រិតជាតិផ្អែម ៖
                    </div>
                    <div class="taste-chip-group">
                        <label class="taste-chip">
                            <input type="radio" name="sweet_level" value="ផ្អែមល្មម" checked>
                            <span>ផ្អែមល្មម</span>
                        </label>
                        <label class="taste-chip">
                            <input type="radio" name="sweet_level" value="ផ្អែមតិច">
                            <span>ផ្អែមតិច</span>
                        </label>
                        <label class="taste-chip">
                            <input type="radio" name="sweet_level" value="មិនផ្អែម">
                            <span>មិនផ្អែម</span>
                        </label>
                        <label class="taste-chip">
                            <input type="radio" name="sweet_level" value="ផ្អែមខ្លាំង">
                            <span>ផ្អែមខ្លាំង</span>
                        </label>
                    </div>

                    <!-- កម្រិតប្រៃ -->
                    <div class="taste-option-title">
                        <i class="bi bi-droplet-half text-secondary"></i> កម្រិតជាតិប្រៃ ៖
                    </div>
                    <div class="taste-chip-group mb-0">
                        <label class="taste-chip">
                            <input type="radio" name="salt_level" value="រសជាតិល្មម" checked>
                            <span>រសជាតិល្មម</span>
                        </label>
                        <label class="taste-chip">
                            <input type="radio" name="salt_level" value="ប្រៃតិច / សាប">
                            <span>ប្រៃតិច / សាប</span>
                        </label>
                        <label class="taste-chip">
                            <input type="radio" name="salt_level" value="ប្រៃដិត">
                            <span>ប្រៃដិត</span>
                        </label>
                    </div>
                </div>

                <!-- ជម្រើសកញ្ចប់ទំហំសម្រាប់ភេសជ្ជៈ & គ្រឿងស្រវឹង (១កំប៉ុង, ១យួរ, កន្លះកេះ, ១កេះ) -->
                <div id="drinkPackOptionsWrap" class="p-3 bg-light rounded-4 border mb-3" style="display:none;">
                    <div class="small fw-bold text-orange mb-2">
                        <i class="bi bi-box-seam me-1"></i> ជ្រើសរើសទំហំកញ្ចប់ ៖
                    </div>

                    <div class="pack-chip-group">
                        <label class="pack-chip">
                            <input type="radio" name="pack_size" value="១កំប៉ុង / ១ដប" data-multiplier="1" checked>
                            <span>
                                <span class="pack-name">🥫 ១កំប៉ុង / ១ដប</span>
                                <span class="pack-desc" id="packPrice1">តម្លៃធម្មតា</span>
                            </span>
                        </label>
                        <label class="pack-chip">
                            <input type="radio" name="pack_size" value="១យួរ" data-multiplier="6">
                            <span>
                                <span class="pack-name">📦 ១យួរ (៦កំប៉ុង)</span>
                                <span class="pack-desc" id="packPrice6">គុណនឹង ៦</span>
                            </span>
                        </label>
                        <label class="pack-chip">
                            <input type="radio" name="pack_size" value="កន្លះកេះ" data-multiplier="12">
                            <span>
                                <span class="pack-name">📦 កន្លះកេះ (១២កំប៉ុង)</span>
                                <span class="pack-desc" id="packPrice12">គុណនឹង ១២</span>
                            </span>
                        </label>
                        <label class="pack-chip">
                            <input type="radio" name="pack_size" value="១កេះ" data-multiplier="24">
                            <span>
                                <span class="pack-name">📦 ១កេះ (២៤កំប៉ុង)</span>
                                <span class="pack-desc" id="packPrice24">គុណនឹង ២៤</span>
                            </span>
                        </label>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold small text-muted">ចំណាំបន្ថែមផ្សេងៗ ៖</label>
                    <textarea id="orderNote" class="form-control rounded-3" rows="2" placeholder="ឧ. មិនដាក់ប៊ីចេង, ទឹកកកច្រើន, ដាក់ថង់ដាច់ដោយឡែក..."></textarea>
                </div>

                <div class="d-flex justify-content-between align-items-center p-3 rounded-4 mb-3" style="background:#fff7ed; border:1px solid #fed7aa;">
                    <span class="fw-bold text-dark">តម្លៃសរុប ៖</span>
                    <span class="h3 text-orange fw-bold mb-0" id="modalTotalPrice">0 ៛</span>
                </div>

                <div class="p-2 px-3 bg-light border rounded-3 small text-muted">
                    <i class="bi bi-info-circle-fill text-primary me-1"></i>
                    លោកអ្នកអាចដាក់ចូលក្នុង <strong>កន្ត្រក</strong> ដើម្បីជ្រើសរើសបន្ថែម ឬចុច <strong>កុម្ម៉ង់ភ្លាមៗ</strong>!
                </div>
            </div>
            <div class="modal-footer bg-light border-0 d-flex flex-wrap gap-2">
                <button type="button" class="btn btn-outline-secondary px-3" data-bs-dismiss="modal">បិទ</button>
                <button type="button" class="btn btn-outline-orange px-3 fw-bold" id="btnAddToCart">
                    <i class="bi bi-cart-plus me-1"></i> ដាក់ក្នុងកន្ត្រក
                </button>
                <button type="button" class="btn btn-telegram px-3 fw-bold flex-grow-1" id="btnConfirmTelegram">
                    <i class="bi bi-telegram me-1"></i> កុម្ម៉ង់ភ្លាមៗ
                </button>
            </div>
        </div>
    </div>
</div>

<!-- ==================================
     SHOPPING CART OFFCANVAS DRAWER (កន្ត្រកទំនិញ)
=================================== -->
<div class="offcanvas offcanvas-end" tabindex="-1" id="cartOffcanvas" aria-labelledby="cartOffcanvasLabel" style="max-width: 440px; width: 100%;">
    <div class="offcanvas-header border-bottom bg-light py-3">
        <h5 class="offcanvas-title fw-bold text-dark d-flex align-items-center gap-2" id="cartOffcanvasLabel">
            <span class="btn btn-orange btn-sm rounded-circle p-1 d-inline-flex align-items-center justify-content-center" style="width:32px; height:32px;">
                <i class="bi bi-cart3"></i>
            </span>
            កន្ត្រកកុម្ម៉ង់របស់ខ្ញុំ
            <span class="badge bg-orange rounded-pill" id="cartHeaderCount">0</span>
        </h5>
        <button type="button" class="btn-close shadow-none" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>

    <div class="offcanvas-body d-flex flex-column p-3">
        <!-- List of items in Cart -->
        <div id="cartItemsList" class="flex-grow-1 overflow-auto pe-1 mb-3">
            <!-- Dynamic Cart Items Rendered by JS -->
        </div>

        <!-- Cart Summary & Checkout -->
        <div id="cartCheckoutSection" class="border-top pt-3 mt-auto">
            <div class="mb-3">
                <label class="form-label small fw-bold text-muted mb-1">ព័ត៌មានអ្នកទទួល (ជាជម្រើស) ៖</label>
                <input type="text" id="custName" class="form-control form-control-sm rounded-3 mb-2" placeholder="👤 ឈ្មោះរបស់អ្នក">
                <input type="text" id="custPhone" class="form-control form-control-sm rounded-3" placeholder="📞 លេខទូរស័ព្ទ / ភូមិ-ឃុំ ដឹកជញ្ជូន">
            </div>

            <div class="d-flex justify-content-between align-items-center p-3 rounded-4 mb-3" style="background:#fff7ed; border:1px solid #fed7aa;">
                <div>
                    <span class="fw-bold text-dark d-block">តម្លៃសរុបទាំងអស់ ៖</span>
                    <small class="text-muted" id="cartTotalItemsText">0 មុខ</small>
                </div>
                <span class="h3 text-orange fw-bold mb-0" id="cartTotalPrice">0 ៛</span>
            </div>

            <button type="button" class="btn btn-telegram w-100 py-2 fw-bold rounded-3 shadow-sm mb-2" id="btnCheckoutAllTelegram">
                <i class="bi bi-telegram me-2"></i> ផ្ញើកុម្ម៉ង់ទាំងអស់តាម Telegram
            </button>

            <button type="button" class="btn btn-link btn-sm text-danger text-decoration-none w-100" id="btnClearCart">
                <i class="bi bi-trash3 me-1"></i> សម្អាតកន្ត្រកទាំងអស់
            </button>
        </div>
    </div>
</div>

<!-- ==================================
     MOBILE STICKY FLOATING CART BAR
=================================== -->
<div id="mobileCartBar" class="d-lg-none" style="display:none;">
    <div class="container py-2">
        <div class="d-flex justify-content-between align-items-center p-2 px-3 rounded-pill shadow-lg" style="background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%); color: #fff;">
            <div class="d-flex align-items-center gap-2">
                <div class="position-relative">
                    <span class="btn btn-orange btn-sm rounded-circle p-2 d-flex align-items-center justify-content-center" style="width:38px; height:38px;">
                        <i class="bi bi-cart3"></i>
                    </span>
                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" id="mobileCartBadge">0</span>
                </div>
                <div>
                    <small class="d-block text-white-50" style="font-size:0.7rem;">កន្ត្រករបស់អ្នក (<span id="mobileCartCount">0</span>)</small>
                    <strong class="text-warning" id="mobileCartTotal">0 ៛</strong>
                </div>
            </div>
            <button class="btn btn-orange btn-sm px-3 rounded-pill fw-bold" type="button" data-bs-toggle="offcanvas" data-bs-target="#cartOffcanvas">
                មើលកន្ត្រក <i class="bi bi-arrow-right"></i>
            </button>
        </div>
    </div>
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
