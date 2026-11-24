/* =====================================================
   7. THANH TOÁN
===================================================== */

function renderCheckout() {
    const checkoutItems = document.getElementById('checkoutOrderItems');
    const checkoutTotal = document.getElementById('checkoutTotal');
    const checkoutSubmit = document.getElementById('checkoutSubmit');

    if (!checkoutItems || !checkoutTotal) return;

    const cart = getCart();

    if (cart.length === 0) {
        checkoutItems.innerHTML = `
            <div class="checkout-empty">
                <strong>GIỎ HÀNG ĐANG TRỐNG</strong>
                <span>Hãy thêm sản phẩm trước khi tạo đơn.</span>
            </div>
        `;

        checkoutTotal.textContent = '0đ';

        if (checkoutSubmit) {
            checkoutSubmit.disabled = true;
        }

        return;
    }

    if (checkoutSubmit) {
        checkoutSubmit.disabled = false;
    }

    let total = 0;

    checkoutItems.innerHTML = cart.map(item => {
        const quantity = Number(item.quantity) || 0;
        const price = Number(item.price) || 0;
        const itemTotal = price * quantity;

        total += itemTotal;

        return `
            <article class="checkout-order-item">

                <div class="checkout-order-item-info">
                    <h3>${escapeHtml(item.productName)}</h3>

                    <span>
                        ${escapeHtml(item.variantName)}
                        · SL ${quantity}
                    </span>

                    ${
                        item.note
                            ? `<span>Ghi chú: ${escapeHtml(item.note)}</span>`
                            : ''
                    }
                </div>

                <div class="checkout-order-item-meta">
                    <span>
                        ${price.toLocaleString('vi-VN')}đ
                    </span>

                    <strong>
                        ${itemTotal.toLocaleString('vi-VN')}đ
                    </strong>
                </div>

            </article>
        `;
    }).join('');

    checkoutTotal.textContent =
        `${total.toLocaleString('vi-VN')}đ`;
}

async function submitOrder() {
    const checkoutSubmit = document.getElementById('checkoutSubmit');

    if (!checkoutSubmit) return;

    const cart = getCart();

    if (cart.length === 0) {
        alert('Giỏ hàng đang trống.');
        return;
    }

    const customerNameInput =
        document.getElementById('customerName');

    const customerName =
        customerNameInput?.value.trim() || '';

    const items = cart.map(item => ({
        item_type: item.itemType || 'product',
        product_id: item.productId,
        variant_id: item.variantId,
        topping_id: item.toppingId,
        quantity: item.quantity,
        note: item.note || ''
    }));

    checkoutSubmit.disabled = true;
    checkoutSubmit.textContent = 'ĐANG TẠO ĐƠN...';
    
    try {

        const response = await fetch(
            'api/create-order.php',
            {
                method: 'POST',

                headers: {
                    'Content-Type': 'application/json'
                },

                body: JSON.stringify({
                    customer_name: customerName,
                    items: items
                })
            }
        );

        const result = await response.json();

        if (!response.ok || !result.success) {
            throw new Error(
                result.message ||
                'Không thể tạo đơn hàng.'
            );
        }

        saveCart([]);
        updateCartCount();
        renderCheckout();
        renderCart();

        console.log(
            'Order created:',
            result.data
        );

        alert(
            `Tạo đơn thành công!\nMã đơn: ${result.data.order_code}`
        );

        setTimeout(() => {
            window.location.href = 'index.php'
        }, 500);

    } catch (error) {

        console.error(
            'Create order error:',
            error
        );

        alert(
            error.message ||
            'Có lỗi xảy ra khi tạo đơn.'
        );

    } finally {

        checkoutSubmit.disabled = false;

        checkoutSubmit.innerHTML = `
            XÁC NHẬN ĐƠN
            <span>→</span>
        `;
    }
}

document.addEventListener('click', event => {

    const button = event.target.closest(
        '#checkoutSubmit'
    );

    if (!button) return;

    submitOrder();
});

