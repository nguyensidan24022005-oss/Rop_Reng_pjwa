document.addEventListener('DOMContentLoaded', () => {

    const menuToggle = document.getElementById('menuToggle');
    const mainNavigation = document.getElementById('mainNavigation');

    if (menuToggle && mainNavigation) {

        menuToggle.addEventListener('click', () => {

            const isOpen = mainNavigation.classList.toggle('is-open');

            menuToggle.setAttribute('aria-expanded', isOpen);

        });

        const navLinks = mainNavigation.querySelectorAll('a');

        navLinks.forEach(link => {

            link.addEventListener('click', () => {

                mainNavigation.classList.remove('is-open');

                menuToggle.setAttribute('aria-expanded', 'false');

            });

        });

    }

    const currentYear = document.getElementById('currentYear');

    if (currentYear) {
        currentYear.textContent = new Date().getFullYear();
    }

});

const CART_STORAGE_KEY = 'ropRengCart';

function getCart() {
    try {
        const cart = JSON.parse(
            localStorage.getItem(CART_STORAGE_KEY)
        );

        return Array.isArray(cart) ? cart : [];
    } catch (error) {
        return [];
    }
}

function saveCart(cart) {
    localStorage.setItem(
        CART_STORAGE_KEY,
        JSON.stringify(cart)
    );
}

function showCartToast(message) {
    let toast = document.getElementById('cartToast');

    if (!toast) {
        toast = document.createElement('div');
        toast.id = 'cartToast';

        toast.style.position = 'fixed';
        toast.style.right = '24px';
        toast.style.bottom = '24px';
        toast.style.zIndex = '9999';
        toast.style.padding = '14px 20px';
        toast.style.borderRadius = '10px';
        toast.style.background = '#D9E75B';
        toast.style.color = '#293706';
        toast.style.fontSize = '13px';
        toast.style.fontWeight = '800';
        toast.style.boxShadow = '0, 10px 30px rgba(0, 0, 0, 0.5)';
        toast.style.opacity = '0';
        toast.style.transform = 'translate(10px)';
        toast.style.transition = 'all o.25s ease';

            document.body.appendChild(toast);
        }

    toast.textContent = message;

    requestAnimationFrame(() => {
        toast.style.opacity = '1';
        toast.style.transform = 'translate(0)';
    });

    clearTimeout(toast.hidenTimer);

    toast.hidenTimer = setTimeout(() => {
        toast.style.opacity = '0';
        toast.style.transform = 'translateY(10px)';
    }, 1800);

}

function addToCart(product) {

    const cart = getCart();

    const existingItem = cart.find(
        item => item.cartKey === product.cartKey
    );

    if (existingItem) {

        existingItem.quantity += 1;

    } else {

        cart.push({
            cartKey: product.cartKey,
            productId: product.productId,
            productName: product.productName,
            variantId: product.variantId,
            variantName: product.variantName,
            price: product.price,
            quantity: 1,
            note: ''
        });

    }

    saveCart(cart);

    updateCartCount();

    showCartToast(
        `${product.productName} đã được thêm vào giỏ hàng`
    );
}

function updateCartCount() {

    const cart = getCart();

    const totalQuantity = cart.reduce(
        (total, item) => total + item.quantity,
        0
    );

    const cartCount = document.getElementById('cartCount');

    if (!cartCount) {
        return;
    }

    cartCount.textContent = totalQuantity;
}

document.addEventListener('click', event => {

    const button = event.target.closest('.menu-add-to-cart');

    if (!button) {
        return;
    }

    const productId = button.dataset.productId;
    const productName = button.dataset.productName;

    let variantId = button.dataset.variantId;
    let variantName = button.dataset.variantName;
    let price = Number(button.dataset.price);

    const productCard = button.closest('.menu-product-card');

    const variantSelect = productCard?.querySelector(
        '.menu-variant-select'
    );

    if (variantSelect) {

        const selectedOption =
            variantSelect.options[variantSelect.selectedIndex];

        variantId = selectedOption.value;
        variantName = selectedOption.dataset.variantName;
        price = Number(selectedOption.dataset.price);

    }

    if (!productId || !variantId || !price) {
        return;
    }

    addToCart({
        cartKey: `${productId}-${variantId}`,
        productId: Number(productId),
        productName,
        variantId: Number(variantId),
        variantName,
        price,
    });

});

document.addEventListener('DOMContentLoaded', () => {
    updateCartCount();
    renderCheckout();
});

function renderCart() {

    const cartBody = document.getElementById('cartDrawerBody');
    const cartSubtotal = document.getElementById('cartSubtotal');
    const cartCheckout = document.getElementById('cartCheckout');

    if (!cartBody || !cartSubtotal) {
        return;
    }

    const cart = getCart();

    if (cart.length === 0) {

        cartBody.innerHTML = `
            <div class="cart-empty">
                <p>GIỎ HÀNG ĐANG TRỐNG</p>
                <span>Hãy chọn một món.</span>
            </div>
        `;

        cartSubtotal.textContent = '0đ';

        if (cartCheckout) {
            cartCheckout.disabled= true;
        }

        return;
    }

    if (cartCheckout) {
        cartCheckout.disabled = false;
    }

    let subtotal = 0;

    cartBody.innerHTML = cart.map(item => {

        const itemTotal = item.price * item.quantity;

        subtotal += itemTotal;

        return `
            <article class="cart-item">

                <div class="cart-item-info">

                    <h3>
                        ${item.productName}
                    </h3>

                    <span>
                        ${item.variantName}
                    </span>

                    <strong>
                        ${item.price.toLocaleString('vi-VN')}đ
                    </strong>
                </div>

                <div class="cart-item-note">
                    <label for="cart-note-${item.cartKey}">
                        GHI CHÚ
                    </label>

                    <textarea
                        id="cart-note-${item.cartKey}"
                        class="cart-note-input"
                        data-cart-note="${item.cartKey}"
                        rows="2"
                        placeholder="Ví dụ: ít đá, ít ngọt,..."
                    >${item.note || ''}</textarea>
                </div>

                <div class="cart-item-quantity">

                    <button
                        type="button"
                        class="cart-quantity-button"
                        data-cart-action="decrease"
                        data-cart-key="${item.cartKey}"
                    >
                        −
                    </button>

                    <span>
                        ${item.quantity}
                    </span>

                    <button
                        type="button"
                        class="cart-quantity-button"
                        data-cart-action="increase"
                        data-cart-key="${item.cartKey}"
                    >
                        +
                    </button>

                </div>

                <button
                    type="button"
                    class="cart-item-remove"
                    data-cart-action="remove"
                    data-cart-key="${item.cartKey}"
                >
                    XÓA
                </button>

            </article>
        `;

    }).join('');

    cartSubtotal.textContent =
        `${subtotal.toLocaleString('vi-VN')}đ`;
}

function updateCartItemQuantity(cartKey, action) {

    const cart = getCart();

    const item = cart.find(
        item => item.cartKey === cartKey
    );

    if (!item) {
        return;
    }

    if (action === 'increase') {
        item.quantity += 1;
    }

    if (action === 'decrease') {
        item.quantity -= 1;
    }

    if (action === 'remove') {

        const updatedCart = cart.filter(
            item => item.cartKey !== cartKey
        );

        saveCart(updatedCart);

        updateCartCount();

        renderCart();

        return;
    }

    const updatedCart = cart.filter(
        item => item.quantity > 0
    );

    saveCart(updatedCart);

    updateCartCount();

    renderCart();
}

document.addEventListener('click', event => {

    const button = event.target.closest(
        '[data-cart-action]'
    );

    if (!button) {
        return;
    }

    const action = button.dataset.cartAction;
    const cartKey = button.dataset.cartKey;

    if (!action || !cartKey) {
        return;
    }

    updateCartItemQuantity(
        cartKey,
        action
    );

});

document.addEventListener('input', event => {
    const textarea = event.target.closest(
        '[data-cart-note]'
    );

    if (!textarea) return;

    const cartKey = textarea.dataset.cartNote;
    const cart = getCart();
    const item = cart.find(
        item => item.cartKey === cartKey
    );

    if (!item) return;

    item.note = textarea.value;
    saveCart(cart);
});

const cartToggle = document.getElementById('cartToggle');
const cartDrawer = document.getElementById('cartDrawer');
const cartClose = document.getElementById('cartClose');
const cartOverlay = document.getElementById('cartOverlay');

function openCartDrawer() {

    if (!cartDrawer || !cartOverlay) {
        return;
    }

    renderCart();

    cartDrawer.classList.add('is-open');
    cartOverlay.classList.add('is-open');

    cartDrawer.setAttribute('aria-hidden', 'false');

    document.body.style.overflow = 'hidden';
}

function closeCartDrawer() {

    if (!cartDrawer || !cartOverlay) {
        return;
    }

    cartDrawer.classList.remove('is-open');
    cartOverlay.classList.remove('is-open');

    cartDrawer.setAttribute('aria-hidden', 'true');

    document.body.style.overflow = '';
}

cartToggle?.addEventListener('click', openCartDrawer);

cartClose?.addEventListener('click', closeCartDrawer);

cartOverlay?.addEventListener('click', closeCartDrawer);

document.addEventListener('keydown', event => {

    if (event.key === 'Escape') {
        closeCartDrawer();
    }

});

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
        const itemTotal = item.price * item.quantity;

        total += itemTotal;

        return `
            <article class="checkout-order-item">

                <div class="checkout-order-item-info">
                    <h3>${item.productName}</h3>

                    <span>
                        ${item.variantName}
                        · SL ${item.quantity}
                    </span>

                    ${
                        item.note
                            ? `<span>Ghi chú: ${item.note}</span>`
                            : ''
                    }
                </div>

                <div class="checkout-order-item-meta">
                    <span>
                        ${item.price.toLocaleString('vi-VN')}đ
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
        product_id: item.productId,
        variant_id: item.variantId,
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

        console.log(
            'Order created:',
            result.data
        );

        alert(
            `Tạo đơn thành công!\nMã đơn: ${result.data.order_code}`
        );

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

async function loadAdminOrders() {

    const tableWrapper =
        document.getElementById(
            'adminOrdersTableWrapper'
        );

    const orderCount =
        document.getElementById(
            'adminOrderCount'
        );

    if (!tableWrapper) return;

    tableWrapper.innerHTML = `
        <div class="admin-loading">
            ĐANG TẢI ĐƠN HÀNG...
        </div>
    `;

    try {

        const response = await fetch(
            'api/orders.php'
        );

        const result = await response.json();

        if (
            !response.ok ||
            !result.success
        ) {
            throw new Error(
                result.message ||
                'Không thể tải đơn hàng.'
            );
        }

        const orders = Array.isArray(result.data)
            ? result.data
            : [];

        if (orderCount) {
            orderCount.textContent =
                orders.length;
        }

        if (orders.length === 0) {

            tableWrapper.innerHTML = `
                <div class="admin-empty">
                    CHƯA CÓ ĐƠN HÀNG
                </div>
            `;

            return;
        }

        tableWrapper.innerHTML = `
            <table class="admin-orders-table">
            
                <thead>
                    <tr>
                        <th>MÃ ĐƠN</th>
                        <th>KHÁCH HÀNG</th>
                        <th>TỔNG TIỀN</th>
                        <th>TRẠNG THÁI</th>
                        <th>THỜI GIAN</th>
                        <th></th>
                    </tr>
                </thead>
                
                <tbody>
                
                    ${orders.map(order => `
                        
                        <tr>
                        
                            <td>
                                <span class="admin-order-code">
                                    ${order.order_code}
                                </span>
                            </td>

                            <td>
                                <span class="admin-order-customer">
                                    ${
                                        order.customer_name ||
                                        '—'
                                    }
                                </span>
                            </td>
                            
                            <td>
                                <span class="admin-order-total">
                                    ${
                                        Number(order.total)
                                            .toLocaleString('vi-VN')
                                    }đ
                                </span>
                            </td>
                            
                            <td>
                                <span class="admin-order-status">
                                    ${getOrderStatusLabel(
                                        order.status
                                    )}
                                </span>
                            </td>
                            
                            <td>
                                <span class="admin-order-date">
                                    ${formatOrderDate(
                                        order.created_at
                                    )}
                                </span>
                            </td>
                            
                            <td>
                                <button
                                    type="button"
                                    class="admin-order-view"
                                    data-order-id="${order.id}"
                                >
                                    XEM
                                </button>
                            </td>
                            
                        </tr>
                        
                    `).join('')}
                    
                </tbody>
                
            </table>
        `;
    } catch (error) {

        console.error(
            'Load admin orders error:',
            error
        );

        tableWrapper.innerHTML = `
            <div class="admin-error">
                KHÔNG THỂ TẢI DANH SÁCH ĐƠN HÀNG
            </div>
        `;
    }
}

function getOrderStatusLabel(status) {

    const labels = {
        pending: 'ĐANG XỬ LÝ',
        confirmed: 'ĐANG THỰC HIỆN MÓN',
        completed: 'HOÀN THÀNH',
        cancelled: 'ĐÃ HỦY'
    };

    return labels[status] || status;
}

function formatOrderDate(dateString) {

    if (!dateString) {
        return '—';
    }

    const date = new Date(
        dateString.replace(' ', 'T')
    );

    if (Number.isNaN(date.getTime())) {
        return dateString;
    }

    return date.toLocaleString(
        'vi-VN',
        {
            day: '2-digit',
            month: '2-digit',
            year: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        }
    );
}

document.addEventListener(
    'click',
    event => {

        const refreshButton =
            event.target.closest(
                '#adminOrdersRefresh'
            );

        if (!refreshButton) return;

        loadAdminOrders();
    }
);

document.addEventListener(
    'DOMContentLoaded',
    () => {

        if (
            document.getElementById(
                'adminOrdersTableWrapper'
            )
        ) {
            loadAdminOrders();
        }

    }
);

document.addEventListener('click', event => {

    const button = event.target.closest(
        '.admin-order-view'
    );

    if (!button) return;

    const orderId = button.dataset.orderId;

    if (!orderId) return;

    window.location.href =
        `admin/order-detail.php?id=${encodeURIComponent(orderId)}`;
});

async function updateOrderStatus() {

    const statusSelect =
        document.getElementById('orderStatus');

    const updateButton =
        document.getElementById('updateOrderStatus');

    if (!statusSelect || !updateButton) {
        return;
    }

    const urlParams =
        new URLSearchParams(
            window.location.search
        );

    const orderId =
        urlParams.get('id');

    const status =
        statusSelect.value;

    if (!orderId || !status) {
        return;
    }

    updateButton.innerHTML =
        'ĐANG CẬP NHẬT...';

    try {

        const response = await fetch(
            '../api/update-order-status.php',
            {
                method: 'POST',

                headers: {
                    'Content-Type': 'application/json'
                },

                body: JSON.stringify({
                    order_id: Number(orderId),
                    status: statucs
                })
            }
        );

        const result =
            await response.json();

        if (
            !response.ok ||
            !result.success
        ) {
            throw new Error(
                result.message ||
                'Không thể cập nhật trạng thái.'
            );
        }

        alert(
            'Cập nhật trạng thái thành công.'
        );

        window.location.reload();

    } catch (error) {

        console.error(
            'Update order status error:',
            error
        );

        alert(
            error.message ||
            'Có lỗi xảy ra khi cập nhật trạng thái.'
        );

    } finally {

        updateButton.disabled = false;

        updateButton.innerHTML = `
            CẬP NHẬT TRẠNG THÁI
            <span>→</span>
        `;
    }
}

document.addEventListener(
    'click',
    event => {

        const button =
            event.target.closest(
                '#updateOrderStatus'
            );

        if (!button) {
            return;
        }

        updateOrderStatus();
    }
);