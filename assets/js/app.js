// assets/js/app.js - Modern Food & Drinks Ordering with Full Shopping Cart & Dynamic Options

document.addEventListener('DOMContentLoaded', function () {

    let selectedProduct = null;

    // Modals and Offcanvas Instances
    const orderModalElem = document.getElementById('orderModal');
    let orderModal = orderModalElem ? new bootstrap.Modal(orderModalElem) : null;

    const cartOffcanvasElem = document.getElementById('cartOffcanvas');
    let cartOffcanvas = cartOffcanvasElem ? new bootstrap.Offcanvas(cartOffcanvasElem) : null;

    // =====================================================
    // 1. FORMAT CURRENCY (Cambodian Riel - KHR)
    // =====================================================
    function formatKHR(price) {
        return new Intl.NumberFormat('en-US', {
            maximumFractionDigits: 0
        }).format(price) + ' ៛';
    }

    // =====================================================
    // 2. SHOPPING CART MANAGEMENT (កន្ត្រកទំនិញ)
    // =====================================================
    const CART_STORAGE_KEY = 'food_shop_cart_v1';

    function getCart() {
        try {
            const raw = localStorage.getItem(CART_STORAGE_KEY);
            return raw ? JSON.parse(raw) : [];
        } catch (e) {
            return [];
        }
    }

    function saveCart(cart) {
        try {
            localStorage.setItem(CART_STORAGE_KEY, JSON.stringify(cart));
        } catch (e) {}
        renderCartUI();
    }

    function addToCart(item) {
        let cart = getCart();

        // Check if an identical item exists (same ID and identical options)
        const existingIdx = cart.findIndex(c => c.uniqueKey === item.uniqueKey);

        if (existingIdx >= 0) {
            cart[existingIdx].qty += item.qty;
        } else {
            cart.push(item);
        }

        saveCart(cart);

        // Close order modal and open cart drawer
        if (orderModal) {
            orderModal.hide();
        }
        if (cartOffcanvas) {
            cartOffcanvas.show();
        }
    }

    function updateCartItemQty(index, change) {
        let cart = getCart();
        if (!cart[index]) return;

        cart[index].qty += change;

        if (cart[index].qty <= 0) {
            cart.splice(index, 1);
        }

        saveCart(cart);
    }

    function removeCartItem(index) {
        let cart = getCart();
        if (!cart[index]) return;

        cart.splice(index, 1);
        saveCart(cart);
    }

    function clearCart() {
        if (confirm('តើលោកអ្នកពិតជាចង់សម្អាតកន្ត្រកទាំងអស់មែនទេ?')) {
            saveCart([]);
        }
    }

    // =====================================================
    // 3. RENDER CART UI & BADGES
    // =====================================================
    function renderCartUI() {
        const cart = getCart();

        let totalItems = 0;
        let totalPrice = 0;

        cart.forEach(item => {
            totalItems += item.qty;
            totalPrice += item.price * item.qty;
        });

        // Update all badge counters across navbar and mobile
        const badges = document.querySelectorAll('.cart-badge');
        badges.forEach(b => {
            b.textContent = totalItems;
            b.style.display = totalItems > 0 ? 'inline-block' : 'none';
        });

        // Header Count in Cart Drawer
        const cartHeaderCount = document.getElementById('cartHeaderCount');
        if (cartHeaderCount) cartHeaderCount.textContent = totalItems;

        const cartTotalItemsText = document.getElementById('cartTotalItemsText');
        if (cartTotalItemsText) cartTotalItemsText.textContent = `${totalItems} មុខ`;

        // Cart Drawer Total
        const cartTotalPriceElem = document.getElementById('cartTotalPrice');
        if (cartTotalPriceElem) cartTotalPriceElem.textContent = formatKHR(totalPrice);

        // Mobile Floating Bar
        const mobileCartBar = document.getElementById('mobileCartBar');
        const mobileCartBadge = document.getElementById('mobileCartBadge');
        const mobileCartCount = document.getElementById('mobileCartCount');
        const mobileCartTotal = document.getElementById('mobileCartTotal');

        if (mobileCartBar) {
            if (totalItems > 0) {
                mobileCartBar.style.display = 'block';
                if (mobileCartBadge) mobileCartBadge.textContent = totalItems;
                if (mobileCartCount) mobileCartCount.textContent = totalItems;
                if (mobileCartTotal) mobileCartTotal.textContent = formatKHR(totalPrice);
            } else {
                mobileCartBar.style.display = 'none';
            }
        }

        // Render Cart Items List
        const listElem = document.getElementById('cartItemsList');
        const checkoutSection = document.getElementById('cartCheckoutSection');

        if (!listElem) return;

        if (cart.length === 0) {
            listElem.innerHTML = `
                <div class="cart-empty-state">
                    <div style="font-size: 3rem; margin-bottom: 0.5rem;">🛒</div>
                    <h6 class="fw-bold text-dark">មិនទាន់មានមុខម្ហូបក្នុងកន្ត្រកទេ</h6>
                    <p class="small text-muted mb-3">សូមជ្រើសរើសមុខម្ហូប ឬភេសជ្ជៈឆ្ងាញ់ៗដើម្បីបន្ថែម</p>
                    <button class="btn btn-outline-orange btn-sm rounded-pill px-3" data-bs-dismiss="offcanvas">
                        <i class="bi bi-grid me-1"></i> មើលមុខម្ហូបទាំងអស់
                    </button>
                </div>
            `;
            if (checkoutSection) checkoutSection.style.display = 'none';
            return;
        }

        if (checkoutSection) checkoutSection.style.display = 'block';

        let html = '';
        cart.forEach((item, idx) => {
            const itemTotal = item.price * item.qty;

            html += `
                <div class="cart-item-card d-flex align-items-center gap-3">
                    <img src="${item.image}" alt="${item.name}" class="cart-item-img shadow-xs">
                    <div class="flex-grow-1 min-w-0">
                        <div class="cart-item-title text-truncate">${item.name}</div>
                        <div class="cart-item-option-badge">${item.optionsSummary}</div>
                        ${item.note ? `<div class="small text-muted fst-italic" style="font-size:0.72rem;">📝 ${item.note}</div>` : ''}
                        <div class="fw-bold text-orange small mt-1">${formatKHR(itemTotal)}</div>
                    </div>
                    <div class="d-flex flex-column align-items-end justify-content-between gap-2">
                        <button class="btn btn-sm btn-link text-danger p-0 cart-btn-remove" data-index="${idx}" title="លុប">
                            <i class="bi bi-trash"></i>
                        </button>
                        <div class="d-flex align-items-center gap-1 bg-light rounded-pill p-1 border">
                            <button class="btn btn-outline-secondary cart-qty-btn cart-btn-minus" data-index="${idx}">-</button>
                            <span class="px-2 small fw-bold">${item.qty}</span>
                            <button class="btn btn-outline-secondary cart-qty-btn cart-btn-plus" data-index="${idx}">+</button>
                        </div>
                    </div>
                </div>
            `;
        });

        listElem.innerHTML = html;

        // Attach Cart Item Event Listeners
        listElem.querySelectorAll('.cart-btn-minus').forEach(btn => {
            btn.addEventListener('click', function () {
                const idx = parseInt(this.getAttribute('data-index'), 10);
                updateCartItemQty(idx, -1);
            });
        });

        listElem.querySelectorAll('.cart-btn-plus').forEach(btn => {
            btn.addEventListener('click', function () {
                const idx = parseInt(this.getAttribute('data-index'), 10);
                updateCartItemQty(idx, 1);
            });
        });

        listElem.querySelectorAll('.cart-btn-remove').forEach(btn => {
            btn.addEventListener('click', function () {
                const idx = parseInt(this.getAttribute('data-index'), 10);
                removeCartItem(idx);
            });
        });
    }

    // Clear Cart Button Event
    const btnClearCart = document.getElementById('btnClearCart');
    if (btnClearCart) {
        btnClearCart.addEventListener('click', clearCart);
    }

    // =====================================================
    // 4. ORDER MODAL INTERACTION & DYNAMIC OPTIONS
    // =====================================================
    const orderButtons = document.querySelectorAll('.btn-order-telegram');
    const tasteOptionsWrap = document.getElementById('tasteOptionsWrap');
    const drinkPackOptionsWrap = document.getElementById('drinkPackOptionsWrap');
    const qtyInput = document.getElementById('orderQty');
    const btnMinus = document.getElementById('btnQtyMinus');
    const btnPlus = document.getElementById('btnQtyPlus');

    orderButtons.forEach(btn => {
        btn.addEventListener('click', function () {
            const id = this.getAttribute('data-id');
            const name = this.getAttribute('data-name');
            const price = parseInt(this.getAttribute('data-price'), 10) || 0;
            const category = this.getAttribute('data-category');
            const categorySlug = (this.getAttribute('data-category-slug') || '').toLowerCase();
            const image = this.getAttribute('data-image');

            selectedProduct = {
                id,
                name,
                price, // Base single unit price
                category,
                categorySlug,
                image
            };

            document.getElementById('modalProductName').textContent = name;
            document.getElementById('modalProductCategory').textContent = category;
            document.getElementById('modalProductPrice').textContent = formatKHR(price);
            document.getElementById('modalProductImage').src = image;
            if (qtyInput) qtyInput.value = 1;
            document.getElementById('orderNote').value = '';

            // Check if Drink or Alcohol
            const isDrinkOrAlcohol = (categorySlug === 'drinks' || categorySlug === 'alcohol');

            if (isDrinkOrAlcohol) {
                // ភេសជ្ជៈ ឬគ្រឿងស្រវឹង៖ លាក់ជម្រើសហិរ/ផ្អែម/ប្រៃ ហើយបង្ហាញទំហំកញ្ចប់ (១កំប៉ុង, ១យួរ, កន្លះកេះ, ១កេះ)
                if (tasteOptionsWrap) tasteOptionsWrap.style.display = 'none';
                if (drinkPackOptionsWrap) drinkPackOptionsWrap.style.display = 'block';

                // Reset pack size to 1 can
                const defaultPack = document.querySelector('input[name="pack_size"][data-multiplier="1"]');
                if (defaultPack) defaultPack.checked = true;

                // Update pack price preview descriptions
                const p1 = document.getElementById('packPrice1');
                const p6 = document.getElementById('packPrice6');
                const p12 = document.getElementById('packPrice12');
                const p24 = document.getElementById('packPrice24');

                if (p1) p1.textContent = formatKHR(price * 1);
                if (p6) p6.textContent = formatKHR(price * 6);
                if (p12) p12.textContent = formatKHR(price * 12);
                if (p24) p24.textContent = formatKHR(price * 24);

            } else {
                // ម្ហូបអាហារ & អាហារសម្រន់៖ បង្ហាញជម្រើសរសជាតិ (ហិរ, ផ្អែម, ប្រៃ)
                if (tasteOptionsWrap) tasteOptionsWrap.style.display = 'block';
                if (drinkPackOptionsWrap) drinkPackOptionsWrap.style.display = 'none';

                // Reset taste options
                const defaultSpice = document.querySelector('input[name="spice_level"][value="មិនហិរ"]');
                if (defaultSpice) defaultSpice.checked = true;

                const defaultSweet = document.querySelector('input[name="sweet_level"][value="ផ្អែមល្មម"]');
                if (defaultSweet) defaultSweet.checked = true;

                const defaultSalt = document.querySelector('input[name="salt_level"][value="រសជាតិល្មម"]');
                if (defaultSalt) defaultSalt.checked = true;
            }

            updateTotalPrice();

            if (orderModal) {
                orderModal.show();
            }
        });
    });

    // Handle Pack Size Radio Changes for Drinks
    document.querySelectorAll('input[name="pack_size"]').forEach(radio => {
        radio.addEventListener('change', function () {
            updateTotalPrice();
        });
    });

    // Calculate Modal Total Price
    function getSelectedPackMultiplier() {
        const checkedPack = document.querySelector('input[name="pack_size"]:checked');
        return checkedPack ? parseInt(checkedPack.getAttribute('data-multiplier'), 10) || 1 : 1;
    }

    function updateTotalPrice() {
        if (!selectedProduct || !qtyInput) return;

        let qty = parseInt(qtyInput.value, 10) || 1;
        qty = Math.max(1, Math.min(50, qty));
        qtyInput.value = qty;

        const isDrinkOrAlcohol = (selectedProduct.categorySlug === 'drinks' || selectedProduct.categorySlug === 'alcohol');
        const multiplier = isDrinkOrAlcohol ? getSelectedPackMultiplier() : 1;

        const effectiveUnitPrice = selectedProduct.price * multiplier;
        const total = effectiveUnitPrice * qty;

        const totalElem = document.getElementById('modalTotalPrice');
        if (totalElem) {
            totalElem.textContent = formatKHR(total);
        }
    }

    if (btnMinus && btnPlus && qtyInput) {
        btnMinus.addEventListener('click', function () {
            let val = parseInt(qtyInput.value, 10) || 1;
            if (val > 1) {
                qtyInput.value = val - 1;
                updateTotalPrice();
            }
        });

        btnPlus.addEventListener('click', function () {
            let val = parseInt(qtyInput.value, 10) || 1;
            if (val < 50) {
                qtyInput.value = val + 1;
                updateTotalPrice();
            }
        });

        qtyInput.addEventListener('input', updateTotalPrice);
    }

    // =====================================================
    // 5. HELPER: EXTRACT CURRENT MODAL ITEM DATA
    // =====================================================
    function getModalItemData() {
        if (!selectedProduct) return null;

        const qty = Math.max(1, Math.min(50, parseInt(qtyInput.value, 10) || 1));
        const note = document.getElementById('orderNote').value.trim();
        const isDrinkOrAlcohol = (selectedProduct.categorySlug === 'drinks' || selectedProduct.categorySlug === 'alcohol');

        let optionsSummary = '';
        let effectivePrice = selectedProduct.price;
        let uniqueKey = '';

        if (isDrinkOrAlcohol) {
            const checkedPack = document.querySelector('input[name="pack_size"]:checked');
            const packName = checkedPack ? checkedPack.value : '១កំប៉ុង / ១ដប';
            const multiplier = getSelectedPackMultiplier();

            effectivePrice = selectedProduct.price * multiplier;
            optionsSummary = `ទំហំ៖ ${packName}`;
            uniqueKey = `${selectedProduct.id}_pack_${packName}_${note}`;
        } else {
            const spiceLevel = document.querySelector('input[name="spice_level"]:checked')?.value || 'មិនហិរ';
            const sweetLevel = document.querySelector('input[name="sweet_level"]:checked')?.value || 'ផ្អែមល្មម';
            const saltLevel = document.querySelector('input[name="salt_level"]:checked')?.value || 'រសជាតិល្មម';

            optionsSummary = `${spiceLevel} • ${sweetLevel} • ${saltLevel}`;
            uniqueKey = `${selectedProduct.id}_taste_${spiceLevel}_${sweetLevel}_${saltLevel}_${note}`;
        }

        return {
            id: selectedProduct.id,
            name: selectedProduct.name,
            price: effectivePrice,
            basePrice: selectedProduct.price,
            category: selectedProduct.category,
            categorySlug: selectedProduct.categorySlug,
            image: selectedProduct.image,
            qty: qty,
            optionsSummary: optionsSummary,
            note: note,
            uniqueKey: uniqueKey
        };
    }

    // =====================================================
    // 6. ADD TO CART BUTTON EVENT
    // =====================================================
    const btnAddToCart = document.getElementById('btnAddToCart');
    if (btnAddToCart) {
        btnAddToCart.addEventListener('click', function () {
            const itemData = getModalItemData();
            if (!itemData) return;
            addToCart(itemData);
        });
    }

    // =====================================================
    // 7. ORDER NOW BUTTON (Direct Telegram for 1 item)
    // =====================================================
    const btnConfirmTelegram = document.getElementById('btnConfirmTelegram');
    if (btnConfirmTelegram) {
        btnConfirmTelegram.addEventListener('click', function () {
            const itemData = getModalItemData();
            if (!itemData) return;

            const totalPrice = itemData.price * itemData.qty;

            let msg = `👋 សួស្តី! ខ្ញុំចង់កុម្ម៉ង់ពី *${SHOP_NAME}*៖\n\n`;
            msg += `🍽️ *មុខម្ហូប/ភេសជ្ជៈ:* ${itemData.name}\n`;
            msg += `🔢 *ចំនួន:* ${itemData.qty}\n`;
            msg += `💵 *តម្លៃឯកតា:* ${formatKHR(itemData.price)}\n`;
            msg += `💰 *តម្លៃសរុប:* ${formatKHR(totalPrice)}\n\n`;

            msg += `📋 *ជម្រើស & ចំណាំ៖*\n`;
            msg += `• ${itemData.optionsSummary}\n`;
            if (itemData.note) {
                msg += `• 📝 ចំណាំបន្ថែម៖ ${itemData.note}\n`;
            }

            msg += `\n📍 សូមជួយផ្ញើទីតាំង និងដឹកជូនខ្ញុំផង។ សូមអរគុណ!`;

            sendTelegramMessage(msg);

            if (orderModal) {
                orderModal.hide();
            }
        });
    }

    // =====================================================
    // 8. CHECKOUT ENTIRE CART VIA TELEGRAM
    // =====================================================
    const btnCheckoutAllTelegram = document.getElementById('btnCheckoutAllTelegram');
    if (btnCheckoutAllTelegram) {
        btnCheckoutAllTelegram.addEventListener('click', function () {
            const cart = getCart();
            if (cart.length === 0) {
                alert('កន្ត្រកកុម្ម៉ង់របស់អ្នកមិនទាន់មានទំនិញនៅឡើយទេ!');
                return;
            }

            const custName = (document.getElementById('custName')?.value || '').trim();
            const custPhone = (document.getElementById('custPhone')?.value || '').trim();

            let totalAllPrice = 0;
            let totalAllQty = 0;

            let msg = `👋 សួស្តី! ខ្ញុំចង់កុម្ម៉ង់ពី *${SHOP_NAME}* តាមរយៈកន្ត្រកទំនិញ៖\n\n`;
            msg += `🛒 *បញ្ជីមុខម្ហូប និងភេសជ្ជៈកុម្ម៉ង់៖*\n`;
            msg += `-----------------------------------\n`;

            cart.forEach((item, index) => {
                const itemTotal = item.price * item.qty;
                totalAllPrice += itemTotal;
                totalAllQty += item.qty;

                msg += `${index + 1}. *${item.name}*\n`;
                msg += `   • ចំនួន៖ ${item.qty}\n`;
                msg += `   • ជម្រើស៖ ${item.optionsSummary}\n`;
                if (item.note) {
                    msg += `   • ចំណាំ៖ ${item.note}\n`;
                }
                msg += `   • តម្លៃ៖ ${formatKHR(itemTotal)}\n\n`;
            });

            msg += `-----------------------------------\n`;
            msg += `📦 *ចំនួនសរុបទាំងអស់៖* ${totalAllQty} មុខ\n`;
            msg += `💰 *តម្លៃសរុបត្រូវទូទាត់៖* ${formatKHR(totalAllPrice)}\n`;

            if (custName || custPhone) {
                msg += `\n👤 *ព័ត៌មានអ្នកកុម្ម៉ង់៖*\n`;
                if (custName) msg += `• ឈ្មោះ៖ ${custName}\n`;
                if (custPhone) msg += `• លេខទូរស័ព្ទ/ទីតាំង៖ ${custPhone}\n`;
            }

            msg += `\n📍 សូមជួយផ្ញើទីតាំង និងដឹកជូនខ្ញុំផង។ សូមអរគុណ!`;

            sendTelegramMessage(msg);

            // Optional: Ask to clear cart after checkout
            if (confirm('តើអ្នកចង់សម្អាតកន្ត្រកបន្ទាប់ពីផ្ញើសារកុម្ម៉ង់រួចទេ?')) {
                saveCart([]);
                if (cartOffcanvas) cartOffcanvas.hide();
            }
        });
    }

    // Helper: Send message to Telegram
    function sendTelegramMessage(msg) {
        const encoded = encodeURIComponent(msg);
        let tgUrl = '';

        if (typeof TELEGRAM_USERNAME === 'undefined' || !TELEGRAM_USERNAME) {
            alert('សូមកំណត់ Telegram Username ក្នុង config/database.php ជាមុនសិន!');
            return;
        } else if (TELEGRAM_USERNAME === 'YOUR_TELEGRAM_USERNAME') {
            alert('ម្ចាស់ហាងមិនទាន់បានកំណត់ Telegram Username នៅឡើយទេ!');
            tgUrl = `https://t.me/share/url?url=&text=${encoded}`;
        } else if (TELEGRAM_USERNAME.startsWith('+') || /^\d+$/.test(TELEGRAM_USERNAME)) {
            tgUrl = `https://t.me/+${TELEGRAM_USERNAME.replace(/^\+/, '')}`;
            alert('សូមប្រើ Telegram Username ដើម្បីផ្ញើសារកុម្ម៉ង់ដោយស្វ័យប្រវត្តិ។');
            return;
        } else {
            tgUrl = `https://t.me/${TELEGRAM_USERNAME}?text=${encoded}`;
        }

        window.open(tgUrl, '_blank');
    }

    // =====================================================
    // 9. SEARCH PRODUCTS
    // =====================================================
    const searchInput = document.getElementById('searchInput');
    if (searchInput) {
        searchInput.addEventListener('input', function () {
            const query = this.value.toLowerCase().trim();
            const cards = document.querySelectorAll('.product-card-wrapper');

            cards.forEach(card => {
                const name = (card.getAttribute('data-name') || '').toLowerCase();
                if (name.includes(query)) {
                    card.style.display = '';
                } else {
                    card.style.display = 'none';
                }
            });
        });
    }

    // Initial render of cart on load
    renderCartUI();
});
