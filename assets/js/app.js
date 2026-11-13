
// assets/js/app.js

document.addEventListener('DOMContentLoaded', function () {

    let selectedProduct = null;

    const orderModalElem = document.getElementById('orderModal');

    let orderModal = null;

    if (orderModalElem) {
        orderModal = new bootstrap.Modal(orderModalElem);
    }

    // បង្ហាញតម្លៃជារៀលខ្មែរ
    function formatKHR(price) {
        return new Intl.NumberFormat('en-US', {
            maximumFractionDigits: 0
        }).format(price) + ' ៛';
    }

    // ប៊ូតុងកម្ម៉ង់ Telegram
    const orderButtons = document.querySelectorAll('.btn-order-telegram');

    orderButtons.forEach(btn => {

        btn.addEventListener('click', function () {

            const id = this.getAttribute('data-id');
            const name = this.getAttribute('data-name');
            const price = parseInt(this.getAttribute('data-price'), 10) || 0;
            const category = this.getAttribute('data-category');
            const image = this.getAttribute('data-image');

            selectedProduct = {
                id,
                name,
                price,
                category,
                image
            };

            document.getElementById('modalProductName').textContent = name;
            document.getElementById('modalProductCategory').textContent = category;
            document.getElementById('modalProductPrice').textContent = formatKHR(price);
            document.getElementById('modalProductImage').src = image;
            document.getElementById('orderQty').value = 1;
            document.getElementById('orderNote').value = '';

            // កំណត់តម្លៃលំនាំដើមសម្រាប់ជម្រើសរសជាតិ
            const defaultSpice = document.querySelector('input[name="spice_level"][value="មិនហិរ"]');
            if (defaultSpice) defaultSpice.checked = true;

            const defaultSweet = document.querySelector('input[name="sweet_level"][value="ផ្អែមល្មម"]');
            if (defaultSweet) defaultSweet.checked = true;

            const defaultSalt = document.querySelector('input[name="salt_level"][value="រសជាតិល្មម"]');
            if (defaultSalt) defaultSalt.checked = true;

            updateTotalPrice();

            if (orderModal) {
                orderModal.show();
            }
        });

    });

    // ប៊ូតុងបន្ថយ និងបង្កើនចំនួន
    const btnMinus = document.getElementById('btnQtyMinus');
    const btnPlus = document.getElementById('btnQtyPlus');
    const qtyInput = document.getElementById('orderQty');

    // គណនាតម្លៃសរុប
    function updateTotalPrice() {

        if (!selectedProduct || !qtyInput) return;

        let qty = parseInt(qtyInput.value, 10) || 1;

        qty = Math.max(1, Math.min(50, qty));

        qtyInput.value = qty;

        const total = selectedProduct.price * qty;

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

        qtyInput.addEventListener('input', function () {
            updateTotalPrice();
        });
    }

    // ប៊ូតុងបញ្ជាក់កម្ម៉ង់តាម Telegram
    const btnConfirmTelegram = document.getElementById('btnConfirmTelegram');

    if (btnConfirmTelegram) {

        btnConfirmTelegram.addEventListener('click', function () {

            if (!selectedProduct) return;

            const qty = Math.max(
                1,
                Math.min(50, parseInt(qtyInput.value, 10) || 1)
            );

            const note = document.getElementById('orderNote').value.trim();
            const spiceLevel = document.querySelector('input[name="spice_level"]:checked')?.value || 'មិនហិរ';
            const sweetLevel = document.querySelector('input[name="sweet_level"]:checked')?.value || 'ផ្អែមល្មម';
            const saltLevel = document.querySelector('input[name="salt_level"]:checked')?.value || 'រសជាតិល្មម';

            const totalPrice = selectedProduct.price * qty;

            let msg = `👋 សួស្តី! ខ្ញុំចង់កុម្ម៉ង់ពី *${SHOP_NAME}*៖\n\n`;

            msg += `🍽️ *មុខម្ហូប/ភេសជ្ជៈ:* ${selectedProduct.name}\n`;
            msg += `🔢 *ចំនួន:* ${qty}\n`;
            msg += `💵 *តម្លៃឯកតា:* ${formatKHR(selectedProduct.price)}\n`;
            msg += `💰 *តម្លៃសរុប:* ${formatKHR(totalPrice)}\n\n`;

            msg += `📋 *ចំណាំរសជាតិ & គ្រឿងផ្សំ៖*\n`;
            msg += `• 🌶️ ជាតិហិរ៖ ${spiceLevel}\n`;
            msg += `• 🍬 ជាតិផ្អែម៖ ${sweetLevel}\n`;
            msg += `• 🧂 ជាតិប្រៃ៖ ${saltLevel}\n`;

            if (note) {
                msg += `• 📝 ចំណាំបន្ថែម៖ ${note}\n`;
            }

            msg += `\n📍 សូមជួយផ្ញើទីតាំង និងដឹកជូនខ្ញុំផង។ សូមអរគុណ!`;

            const encoded = encodeURIComponent(msg);

            let tgUrl = '';

            if (typeof TELEGRAM_USERNAME === 'undefined' || !TELEGRAM_USERNAME) {

                alert('សូមកំណត់ Telegram Username ក្នុង config/database.php ជាមុនសិន!');
                return;

            } else if (TELEGRAM_USERNAME === 'YOUR_TELEGRAM_USERNAME') {

                alert('ម្ចាស់ហាងមិនទាន់បានកំណត់ Telegram Username នៅឡើយទេ!');
                tgUrl = `https://t.me/share/url?url=&text=${encoded}`;

            } else if (
                TELEGRAM_USERNAME.startsWith('+') ||
                /^\d+$/.test(TELEGRAM_USERNAME)
            ) {

                // Telegram Phone Number
                tgUrl = `https://t.me/+${TELEGRAM_USERNAME.replace(/^\+/, '')}`;

                alert('សូមប្រើ Telegram Username ដើម្បីផ្ញើសារកម្ម៉ង់ដោយស្វ័យប្រវត្តិ។');

                return;

            } else {

                // Telegram Username
                tgUrl = `https://t.me/${TELEGRAM_USERNAME}?text=${encoded}`;

            }

            window.open(tgUrl, '_blank');

            if (orderModal) {
                orderModal.hide();
            }

        });

    }

    // ស្វែងរកមុខម្ហូប
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

});
