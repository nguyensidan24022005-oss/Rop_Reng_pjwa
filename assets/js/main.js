/* =====================================================
   1. CHUNG: THANH ĐIỀU HƯỚNG & NĂM HIỆN TẠI
===================================================== */

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

/* =====================================================
   2. TIỆN ÍCH CHUNG
===================================================== */

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

function escapeHtml(value) {
    return String(value ?? '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
}

function saveCart(cart) {
    localStorage.setItem(
        CART_STORAGE_KEY,
        JSON.stringify(cart)
    );
}

/* =====================================================
   3. GIỎ HÀNG: THÔNG BÁO & THÊM SẢN PHẨM
===================================================== */

function showCartToast(message) {
    let toast = document.getElementById('cartToast');

    if (!toast) {
        toast = document.createElement('div');
        toast.id = 'cartToast';

        toast.style.position = 'fixed';
        toast.style.right = '24px';
        toast.style.bottom = '24px';
        toast.style.zIndex = '9999';
        toast.style.pointerEvents = 'none';
        toast.style.padding = '14px 20px';
        toast.style.borderRadius = '10px';
        toast.style.background = '#D9E75B';
        toast.style.color = '#293706';
        toast.style.fontSize = '13px';
        toast.style.fontWeight = '800';
        toast.style.boxShadow = '0 10px 30px rgba(0, 0, 0, 0.5)';
        toast.style.opacity = '0';
        toast.style.transform = 'translateY(10px)';
        toast.style.transition = 'all 0.25s ease';

        document.body.appendChild(toast);
    }

    toast.textContent = message;

    requestAnimationFrame(() => {
        toast.style.opacity = '1';
        toast.style.transform = 'translateY(0)';
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

/* =====================================================
   4. GIỎ HÀNG: KHỞI TẠO & HIỂN THỊ
===================================================== */

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

        const quantity = Number(item.quantity) || 0;
        const price = Number(item.price) || 0;
        const cartKey = escapeHtml(item.cartKey);
        const itemTotal = price * quantity;

        subtotal += itemTotal;

        return `
            <article class="cart-item">

                <div class="cart-item-info">

                    <h3>
                        ${escapeHtml(item.productName)}
                    </h3>

                    <span>
                        ${escapeHtml(item.variantName)}
                    </span>

                    <strong>
                        ${price.toLocaleString('vi-VN')}đ
                    </strong>
                </div>

                <div class="cart-item-note">
                    <label for="cart-note-${cartKey}">
                        GHI CHÚ
                    </label>

                    <textarea
                        id="cart-note-${cartKey}"
                        class="cart-note-input"
                        data-cart-note="${cartKey}"
                        rows="2"
                        placeholder="Ví dụ: ít đá, ít ngọt,..."
                    >${escapeHtml(item.note)}</textarea>
                </div>

                <div class="cart-item-quantity">

                    <button
                        type="button"
                        class="cart-quantity-button"
                        data-cart-action="decrease"
                        data-cart-key="${cartKey}"
                    >
                        −
                    </button>

                    <span>
                        ${quantity}
                    </span>

                    <button
                        type="button"
                        class="cart-quantity-button"
                        data-cart-action="increase"
                        data-cart-key="${cartKey}"
                    >
                        +
                    </button>

                </div>

                <button
                    type="button"
                    class="cart-item-remove"
                    data-cart-action="remove"
                    data-cart-key="${cartKey}"
                >
                    XÓA
                </button>

            </article>
        `;

    }).join('');

    cartSubtotal.textContent =
        `${subtotal.toLocaleString('vi-VN')}đ`;
}

/* =====================================================
   5. GIỎ HÀNG: CẬP NHẬT & GHI CHÚ
===================================================== */

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

/* =====================================================
   6. GIỎ HÀNG: DRAWER
===================================================== */

const cartToggle = document.getElementById('cartToggle');
const cartDrawer = document.getElementById('cartDrawer');
const cartClose = document.getElementById('cartClose');
const cartOverlay = document.getElementById('cartOverlay');
const cartCheckout = document.getElementById('cartCheckout');

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

cartCheckout?.addEventListener('click', () => {
    window.location.href = 'checkout.php';
});

document.addEventListener('keydown', event => {

    if (event.key === 'Escape') {
        closeCartDrawer();
    }
});

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

/* =====================================================
   8. QUẢN TRỊ: DANH SÁCH ĐƠN HÀNG
===================================================== */

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
                                    ${escapeHtml(order.order_code)}
                                </span>
                            </td>

                            <td>
                                <span class="admin-order-customer">
                                    ${
                                        escapeHtml(
                                            order.customer_name || '—'
                                        )
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
                                ${escapeHtml(getOrderStatusLabel(
                                    order.status
                                ))}
                                </span>
                            </td>
                            
                            <td>
                                <span class="admin-order-date">
                                    ${escapeHtml(formatOrderDate(
                                        order.created_at
                                    ))}
                                </span>
                            </td>
                            
                            <td>
                                <button
                                    type="button"
                                    class="admin-order-view"
                                    data-order-id="${escapeHtml(order.id)}"
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

/* =====================================================
   9. QUẢN TRỊ: CHUYỂN TRANG & CẬP NHẬT TRẠNG THÁI
===================================================== */

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

    updateButton.disabled = true;
    updateButton.innerHTML =
        'ĐANG CẬP NHẬT...';

    try {

        const response = await fetch(
            'api/update-order-status.php',
            {
                method: 'POST',

                headers: {
                    'Content-Type': 'application/json'
                },

                body: JSON.stringify({
                    order_id: Number(orderId),
                    status: status
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

async function loadAdminDashboardStats() {

    const dashboard =
        document.querySelector('.admin-dashboard-grid');

    if (!dashboard) {
        return;
    }

    try {

        const response = await fetch(
            'api/dashboard-stats.php'
        );

        const result = await response.json();

        if (
            !response.ok ||
            !result.success
        ) {
            throw new Error(
                result.message ||
                'Không thể tải thống kê.'
            );
        }

        const stats = result.data;

        const setText = (id, value) => {

            const element =
                document.getElementById(id);

            if (element) {
                element.textContent = value;
            }
        };

        const formatNumber = value => {
            return Number(value || 0)
                .toLocaleString('vi-VN');
        };

        const formatCurrency = value => {
            return `${formatNumber(value)}đ`;
        };

        setText(
            'dashboardTotalOrders',
            formatNumber(stats.orders.total)
        );

        setText(
            'dashboardPendingOrders',
            formatNumber(stats.orders.pending)
        );

        setText(
            'dashboardConfirmedOrders',
            formatNumber(stats.orders.confirmed)
        );

        setText(
            'dashboardCompletedOrders',
            formatNumber(stats.orders.completed)
        );

        setText(
            'dashboardCancelledOrders',
            formatNumber(stats.orders.cancelled)
        );

        setText(
            'dashboardTodayRevenue',
            formatCurrency(stats.revenue.today)
        );

        setText(
            'dashboardMonthRevenue',
            formatCurrency(stats.revenue.month)
        );

        setText(
            'dashboardTotalRevenue',
            formatCurrency(stats.revenue.total)
        );

        setText(
            'dashboardTotalProducts',
            formatNumber(stats.products.total)
        );

        setText(
            'dashboardActiveProducts',
            formatNumber(stats.products.active)
        );

        setText(
            'dashboardInactiveProducts',
            formatNumber(stats.products.inactive)
        );

    } catch (error) {

        console.error(
            'load dashboard stats error:',
            error
        );

    }
}

document.addEventListener(
    'DOMContentLoaded',
    () => {

        if (
            document.querySelector(
                '.admin-dashboard-grid'
            )
        ) {
            loadAdminDashboardStats();
        }

    }
);

async function loadAdminProducts() {

    const tableWrapper =
        document.getElementById(
            'adminProductsTableWrapper'
        );

    const productCount =
        document.getElementById(
            'adminProductCount'
        );

    if (!tableWrapper) {
        return;
    }

    tableWrapper.innerHTML = `
        <div class="admin-loading">
            ĐANG TẢI SẢN PHẨM...
        </div>
    `;

    try {

        const response = await fetch(
            'api/admin-products.php'
        );

        const result =
            await response.json();

        if (
            !response.ok ||
            !result.success
        ) {
            throw new Error(
                result.message ||
                'Không thể tải sản phẩm.'
            );
        }

        const products =
            Array.isArray(result.data)
                ? result.data
                : [];

        if (productCount) {

            productCount.textContent =
                products.length;
        }

        if (products.length === 0) {

            tableWrapper.innerHTML = `
                <div class="admin-empty">
                    CHƯA CÓ SẢN PHẨM
                </div>
            `;

            return;
        }

        tableWrapper.innerHTML = `
            <table class="admin-products-table">
            
                <thead>
                
                    <tr>
                        <th>Ảnh</th>
                        <th>SẢN PHẨM</th>
                        <th>DANH MỤC</th>
                        <th>GIÁ</th>
                        <th>SIZE</th>
                        <th>TRẠNG THÁI</th>
                        <th>NHÃN</th>
                    </tr>

                </thead>

                <tbody>

                    ${products.map(product => {

                        const variants =
                            Array.isArray(product.variants)
                                ? product.variants
                                : [];

                        const activeVariants =
                            variants.filter(
                                variant =>
                                    Number(
                                        variant.is_active
                                    ) === 1
                            );

                        const variantHtml =
                            activeVariants.length > 0
                                ? `
                                    <div class="admin-product-variants">
                                    
                                        ${
                                            activeVariants
                                                .map(variant => `
                                                    <span class="admin-product-variant">
                                                    
                                                        <span>
                                                            ${escapeHtml(
                                                                variant.name
                                                            )}
                                                        </span>
                                                        
                                                        <strong>
                                                            ${Number(
                                                                variant.price
                                                            ).toLocaleString(
                                                                'vi-VN'
                                                            )}đ
                                                        </strong>
                                                        
                                                    </span>
                                                `)
                                                .join('')
                                        }
                                        
                                    </div>
                                `
                                : '-';

                        const flags = [];

                        if (
                            Number(
                                product.is_best_seller
                            ) === 1
                        ) {
                            flags.push(
                                'BEST SELLER'
                            );
                        }

                        if (
                            Number(
                                product.is_new
                            ) === 1
                        ) {
                            flags.push(
                                'MỚI'
                            );
                        }

                        if (
                            Number(
                                product.is_seasonal
                            ) === 1
                        ) {
                            flags.push(
                                'SẢN PHẨM MÙA'
                            );
                        }

                        const flagsHtml =
                            flags.length > 0
                                ? `
                                    <div class="admin-product-flags">
                                    
                                        ${
                                            flags
                                                .map(flag => `
                                                    <span class="admin-product-flag">
                                                        ${escapeHtml(flag)}
                                                    </span>
                                                `)
                                                .join('')
                                        }
                                        
                                    </div>
                                `
                                : '-';

                        const imageHtml =
                            product.image
                                ? `
                                    <div class="admin-product-image">
                                    
                                        <img
                                            src="${escapeHtml(
                                                product.image
                                            )}"
                                            alt="${escapeHtml(
                                                product.name
                                            )}"
                                            loading="lazy"
                                        >
                                        
                                    </div>
                                `
                                : `
                                    <div class="admin-product-image">
                                    
                                        <div class="admin-product-image-placeholder">
                                            RỐP RẺNG
                                        </div>
                                        
                                    </div>
                                `;

                        const statusHtml =
                            Number(
                                product.is_active
                            ) === 1
                                ? `
                                    <span class="admin-product-status admin-product-status-active">
                                        ĐANG BÁN
                                    </span>
                                `
                                : `
                                    <span class="admin-product-status admin-product-status-inactive">
                                        ĐANG ẨN
                                    </span>
                                `;

                        return `
                            <tr>
                            
                                <td>
                                    ${imageHtml}
                                </td>
                                
                                <td>
                                
                                    <div class="admin-product-name">
                                        ${escapeHtml(
                                            product.name
                                        )}
                                    </div>
                                    
                                </td>
                                
                                <td>
                                
                                    <span class="admin-product-category">
                                        ${escapeHtml(
                                            product.category_name
                                        )}
                                    </span>
                                    
                                </td>
                                
                                <td>
                                
                                    <span class="admin-product-price">
                                        ${Number(
                                            product.price
                                        ).toLocaleString(
                                            'vi-VN'
                                        )}đ
                                    </span>
                                    
                                </td>
                                
                                <td>
                                    ${variantHtml}
                                </td>
                                
                                <td>
                                    ${statusHtml}
                                </td>
                                
                                <td>
                                    ${flagsHtml}
                                </td>
                                
                            </tr>
                        `;

                    }).join('')}

                </tbody>

            </table>
        `;
                        
    } catch (error) {

        console.error(
            'Load admin products error:',
            error
        );

        tableWrapper.innerHTML = `
            <div class="admin-error">
                KHÔNG THỂ TẢI DANH SÁCH SẢN PHẨM
            </div>
        `;

    }
}

document.addEventListener(
    'click',
    event => {

        const refreshButton =
            event.target.closest(
                '#adminProductRefresh'
            );

        if (!refreshButton) {
            return;
        }

        loadAdminProducts();

    }
);

document.addEventListener(
    'DOMContentLoaded',
    () => {

        if (
            document.getElementById(
                'adminProductsTableWrapper'
            )
        ) {
            loadAdminProducts();
        }

    }
);